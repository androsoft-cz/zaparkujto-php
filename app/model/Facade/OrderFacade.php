<?php

namespace App\Model\Facade;

use App\Model\Exceptions\Runtime\OrderException;
use App\Model\Exceptions\Runtime\PaymentException;
use App\Model\Exceptions\RuntimeException;
use App\Model\OrganizationRequest;
use App\Model\Orm\Contacts\Contact;
use App\Model\Orm\Credits\Credit;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Orders\OrdersRepository;
use App\Model\Orm\Orders\VerifiedOrder;
use App\Model\Orm\Organizations\Organization;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Model\Orm\Users\User;
use App\Model\Orm\Users\UsersRepository;
use App\Model\Payment\PaymentService;
use Nette\Utils\DateTime;
use Nette\Utils\Random;
use stdClass;
use Tracy\Debugger;

final class OrderFacade
{
    /** @var OrganizationRequest */
    private $organizationRequest;

    /** @var OrdersRepository */
    private $ordersRepository;

    /** @var UsersRepository */
    private $usersRepository;

    /** @var PaymentService */
    private $paymentService;

    /** @var PaymentCardFacade */
    private $paymentCardFacade;


    /**
     * OrderFacade constructor.
     *
     * @param OrganizationRequest    $organizationRequest
     * @param OrdersRepository       $ordersRepository
     * @param ReservationsRepository $reservationsRepository
     * @param UsersRepository        $usersRepository
     * @param PaymentService         $paymentService
     */
    public function __construct(
        OrganizationRequest $organizationRequest,
        OrdersRepository $ordersRepository,
        ReservationsRepository $reservationsRepository,
        UsersRepository $usersRepository,
        PaymentService $paymentService,
        PaymentCardFacade $paymentCardFacade
    ) {
        $this->organizationRequest = $organizationRequest;
        $this->ordersRepository = $ordersRepository;
        $this->reservationsRepository = $reservationsRepository;
        $this->usersRepository = $usersRepository;
        $this->paymentService = $paymentService;
        $this->paymentCardFacade = $paymentCardFacade;
    }


    public function getUser(string $email): User
    {
        if (!($user = $this->usersRepository->getBy(['username' => $email]))) {
            $user = new User;
            $user->organization = $this->organizationRequest->getOrganization();
            $user->username = $email;
            $user->setPassword(Random::generate(20));
            $user->contact = new Contact;
            $user->contact->email = $email;
            $user->contact->state = 'Česká republika';
        }

        return $user;
    }


    public function createOrder(User $user = NULL, array $reservations, int $paymentType = Order::PAYMENT_TYPE_CREDIT, $usePaymentCard = NULL): Order
    {
        $order = new Order();
        $order->user = $user;
        $order->state = $order::STATE_CREATED;
        $order->paymentType = $paymentType;

        /** @var Reservation $reservation */
        $reservation = $reservations[0];
        $order->payeeId = $reservation->place->organization->id;

        if ($paymentType === Order::PAYMENT_TYPE_SMS) {
            $order->paidAt = $reservation->from;
            $order->state = Order::STATE_PAID;
        }

        $order->reservations->set($reservations);
        foreach ($reservations as $reservation) {
            $reservation->user = $user;
        }

        if (is_bool($usePaymentCard)) {
            $order->authorizationPayment = $usePaymentCard;
            $order->paymentCard = NULL;
        }

        if (is_int($usePaymentCard)) {
            $paymentCard = $this->paymentCardFacade->getPaymentCard($usePaymentCard);
            if (!$paymentCard || ($user && $user->id !== $paymentCard->user->id)) {
                throw new OrderException('Payment card mishmash.');
            }

            $order->authorizationPayment = FALSE;
            $order->paymentCard = $paymentCard;
        }

        try {
            // Save order
            $this->ordersRepository->persistAndFlush($order);
        } catch (\Exception $e) {
            Debugger::log($e);
            throw new OrderException('Failed during persisting order', 0, $e);
        }

        return $order;
    }


    public function cancelOrderByReservation(int $id): Order
    {
        /** @var Reservation $reservation */
        $reservation = $this->reservationsRepository->getById($id);

        return $this->cancelOrder($reservation->order->id);
    }


    public function cancelOrder(int $id): Order
    {
        /** @var Order $order */
        $order = $this->ordersRepository->getById($id);
        $order->state = Order::STATE_STORNO;
        $this->ordersRepository->persistAndFlush($order);

        return $order;
    }


    public function refundOrder(int $orderId): Order
    {
        /** @var Order $order */
        $order = $this->ordersRepository->getById($orderId);
        $this->paymentService->refundPayment($order->reservations->get()->fetch(), $order->price);
        $order->state = Order::STATE_FULL_REFUNDED;

        // => reservation
        foreach ($order->reservations as $reservation) {
            $reservation->state = Reservation::STATE_ADMIN_REFUNDED;
            $reservation->originAt = new DateTime();
            $reservation->originPrice = $reservation->price;
            $reservation->price = 0;
            $reservation->to = new DateTime();
        }

        // Persist order
        $this->ordersRepository->persistAndFlush($order);

        return $order;
    }


    public function getOrganization(int $orderId): ?Organization
    {
        /** @var Order $order */
        $order = $this->ordersRepository->getById($orderId);

        if (!$order || !$order->user) {
            return NULL;
        }

        return $order->user->organization;
    }


    public function updatePaymentProcess(Order $order, string $paymentId, stdClass $gatewayResult, string $paymentCardNumber = NULL): ?Order
    {
        try {
            $paymentCard = $order->paymentCard;
            if ($order->authorizationPayment && !$paymentCard) {
                $paymentCard = $this->paymentCardFacade->savePaymentCard($paymentCardNumber ?? 'xxxx', $order);
            }

            $order->paymentId = $paymentId;
            $order->gatewayResult = json_encode($gatewayResult);
            $order->paymentCard = $paymentCard;
            $this->ordersRepository->persistAndFlush($order);
            return $order;
        } catch (RuntimeException $e) {
            throw new OrderException('Failed during persists payment ID', $e->getCode(), $e->getPrevious());
        }
    }


    public function verifyOrderPayment(int $orderId): ?VerifiedOrder
    {
        /** @var Order $order */
        $order = $this->ordersRepository->getById($orderId);
        if (!$order) {
            throw new OrderException('Order not found');
        }

        try {
            $paymentVerifyResponse = $this->paymentService->verifyPayment($order);

            // Process only order in state WAITING (waiting for gopay response)
            if ($order->state === Order::STATE_WAITING) {
                $order->email = $paymentVerifyResponse['payer']->contact->email ?: NULL;

                if ($paymentVerifyResponse['state'] === PaymentService::PAYMENT_STATE_PAID) {
                    $order->paidAt = new DateTime();
                    $order->state = $order::STATE_PAID;
                } elseif ($paymentVerifyResponse['state'] === PaymentService::PAYMENT_STATE_CANCELED) {
                    $order->state = $order::STATE_STORNO;
                }

                if ($order->isModified()) {
                    $this->ordersRepository->persistAndFlush($order);
                }
            }

            return new VerifiedOrder($order, $paymentVerifyResponse);
        } catch (PaymentException $e) {
            throw new OrderException('Failed during verify payment');
        } catch (RuntimeException $e) {
            throw new OrderException('Failed during persists order');
        }
    }


    public function getStateTranslationKey(int $state): string
    {
        switch ($state) {
            case Order::STATE_CREATED:
                return 'driver.createdReservation';
            case Order::STATE_WAITING:
                return 'driver.waitingReservation';
            case Order::STATE_PAID:
                return 'driver.paidReservation';
            case Order::STATE_STORNO:
                return 'driver.canceledReservation';
            case Order::STATE_PROCESSING:
                return 'driver.processingReservation';
            case Order::STATE_FULL_REFUNDED:
                return 'driver.fullRefundedReservation';
            case Order::STATE_PARTIALY_REFUNDED:
                return 'driver.partlyRefundedReservation';
        }

        return '';
    }


    public function payOrderByCredit(Order $order, User $user): ?Credit
    {
        if ($order->paymentType !== Order::PAYMENT_TYPE_CREDIT) {
            throw new OrderException('Cannot paid by credit');
        }

        if ($order->state === Order::STATE_CREATED) {
            $order->paidAt = new DateTime();
            $order->state = $order::STATE_PAID;
            $this->ordersRepository->persistAndFlush($order);
        }

        return $this->paymentService->payByCredit($order, $user);
    }


    public function payOrderByFree(Order $order): void
    {
        try {
            if ($order->paymentType !== Order::PAYMENT_TYPE_FREE) {
                throw new OrderException('Cannot pay free');
            }

            if ($order->state === Order::STATE_CREATED) {
                $order->paidAt = new DateTime();
                $order->state = Order::STATE_PAID;
                $this->ordersRepository->persistAndFlush($order);
            }
        } catch (RuntimeException $e) {
            throw new OrderException('Failed during free pay');
        }
    }
}
