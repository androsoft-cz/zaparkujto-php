<?php

namespace App\Model\Facade;

use App\Model\Orm\Orders\Order;
use App\Model\Orm\Organizations\OrganizationsRepository;
use App\Model\Orm\PaymentCards\PaymentCard;
use App\Model\Orm\PaymentCards\PaymentCardsRepository;
use App\Model\Orm\PaymentGateways\PaymentGatewaysRepository;
use App\Model\Orm\Users\UsersRepository;
use Nette\Utils\DateTime;
use Nextras\Orm\Collection\ICollection;

final class PaymentCardFacade
{
    /** @var PaymentCardsRepository */
    private $paymentCardsRepository;

    /** @var PaymentGatewaysRepository  */
    private $paymentGatewaysRepository;

    /** @var UsersRepository */
    private $usersRepository;

    /** @var OrganizationsRepository */
    private $organizationsRepository;


    public function __construct(
        PaymentCardsRepository $paymentCardsRepository,
        PaymentGatewaysRepository $paymentGatewaysRepository,
        UsersRepository $usersRepository,
        OrganizationsRepository $organizationsRepository
    ) {
        $this->paymentCardsRepository = $paymentCardsRepository;
        $this->paymentGatewaysRepository = $paymentGatewaysRepository;
        $this->usersRepository = $usersRepository;
        $this->organizationsRepository = $organizationsRepository;
    }


    public function findUsersPaymentCards(int $userId): ICollection
    {
        return $this->paymentCardsRepository->findBy(['deletedAt' => NULL, 'this->user->id' => $userId]);
    }


    public function getPaymentCard(int $paymentCardId): ?PaymentCard
    {
        $paymentCard = $this->paymentCardsRepository->getById($paymentCardId);
        if (!$paymentCard) {
            throw new \ErrorException('Payment card not found.');
        }

        return $paymentCard;
    }


    public function savePaymentCard(string $paymentCardNumber, Order $referencePay): PaymentCard
    {
        try {
            $organization = $this->organizationsRepository->getById($referencePay->payeeId);
            $paymentCard = new PaymentCard();
            $paymentCard->user = $referencePay->user;
            $paymentCard->number = $paymentCardNumber;
            $paymentCard->referencePay = $referencePay;
            $paymentCard->paymentGateway = $organization->paymentGateway;
            return $this->paymentCardsRepository->persistAndFlush($paymentCard);
        } catch (\Exception $exception) {
            throw new \ErrorException('Payment card store fails.');
        }
    }


    public function updatePaymentCardName(int $paymentCardId, string $paymentCardName): PaymentCard
    {
        $paymentCard = $this->paymentCardsRepository->getById($paymentCardId);
        if (!$paymentCard || $paymentCard->deletedAt !== NULL) {
            throw new \ErrorException('Payment card not found.');
        }

        $paymentCard->name = $paymentCardName;
        return $this->paymentCardsRepository->persistAndFlush($paymentCard);
    }


    public function deletePaymentCard(int $paymentCardId)
    {
        $paymentCard = $this->paymentCardsRepository->getById($paymentCardId);
        if (!$paymentCard) {
            throw new \ErrorException('Payment card not found.');
        }

        $paymentCard->deletedAt = new DateTime();
        return $this->paymentCardsRepository->persistAndFlush($paymentCard);
    }
}
