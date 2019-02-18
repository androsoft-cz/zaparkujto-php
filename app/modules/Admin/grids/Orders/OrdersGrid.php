<?php

namespace App\Modules\Admin\Grids\Orders;

use App\Core\Grids\BaseGrid;
use App\Core\Grids\Grido\NextrasDbal;
use App\Core\Utils\WeekdayTranslator;
use App\Model\Facade\OrderFacade;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Reservations\Reservation;
use App\Model\Orm\Reservations\ReservationsRepository;
use App\Modules\Admin\SecurePresenter;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Control;
use Nextras\Dbal\Connection;

/**
 * @author Mates
 */
class OrdersGrid extends BaseGrid
{

    /** @var OrderFacade */
    private $orderFacade;

    /** @var ReservationsRepository */
    private $reservationsRepository;

    /** @var Reservation[] */
    private $reservations = [];


    /**
     * @param \Kdyby\Translation\Translator $translator
     * @param WeekdayTranslator $weekdayTranslator
     * @param Connection $connection
     * @param OrderFacade $orderFacade
     * @param ReservationsRepository $reservationsRepository
     */
    public function __construct(
        \Kdyby\Translation\Translator $translator,
        WeekdayTranslator $weekdayTranslator,
        Connection $connection,
        OrderFacade $orderFacade,
        ReservationsRepository $reservationsRepository
    )
    {
        parent::__construct($translator, $weekdayTranslator, $connection);
        $this->orderFacade = $orderFacade;
        $this->reservationsRepository = $reservationsRepository;
    }

    /**
     * @param Control $control
     */
    public function create(Control $control) // @codingStandardsIgnoreLine
    {
        $this->setDefaultSort(['o.created_at' => 'DESC']);
        $this->setExport(NULL);

        $this->addColumnText('id', 'components.ordersgrid.id');

        $this->addColumnText('vs', 'components.ordersgrid.vs')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('rz', 'components.ordersgrid.rz')
            ->setCustomRender(function ($item) {
                $reservation = $this->getReservation($item->id);

                if ($reservation !== NULL) {
                    return $reservation->rz;
                }

                return '';
            })
            ->setFilterText();

        $this->addColumnText('email', 'components.ordersgrid.email')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('identifier', 'components.ordersgrid.placeIdentifier')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('from', 'components.ordersgrid.from')
            ->setCustomRender(function ($item) {
                $reservation = $this->getReservation($item->id);

                if ($reservation !== NULL) {
                    return $reservation->from->format('j. n. Y H:i');
                }

                return '';
            });

        $this->addColumnText('to', 'components.ordersgrid.to')
            ->setCustomRender(function ($item) {
                $reservation = $this->getReservation($item->id);

                if ($reservation !== NULL) {
                    return $reservation->to->format('j. n. Y H:i');
                }

                return '';
            });

        $this->addColumnText('price', 'components.ordersgrid.price')
            ->setSortable()
            ->setFilterText();

        $this->addColumnText('paid_at', 'components.ordersgrid.paid')
            ->setCustomRender(function ($item) {
                return $item->paid_at->format('j. n. Y H:i');
            })
            ->setSortable()
            ->setFilterDateRange();

        $this->addColumnText('payment_type', 'components.ordersgrid.paymentType')
            ->setCustomRender(function ($item) {
                $paymentTypeText = '';
                switch ($item->payment_type) {
                    case Order::PAYMENT_TYPE_GATE:
                        $paymentTypeText = 'components.ordersgrid.paymentTypeGate';
                        break;

                    case Order::PAYMENT_TYPE_CREDIT:
                        $paymentTypeText = 'components.ordersgrid.paymentTypeCredit';
                        break;

                    case Order::PAYMENT_TYPE_FREE:
                        $paymentTypeText = 'components.ordersgrid.paymentTypeFree';
                        break;

                    case Order::PAYMENT_TYPE_SMS:
                        $paymentTypeText = 'components.ordersgrid.paymentTypeSms';
                        break;
                }

                return $this->translator->translate($paymentTypeText);
            })
            ->setFilterSelect([NULL => '', Order::PAYMENT_TYPE_GATE => 'Platební brána', Order::PAYMENT_TYPE_SMS => 'SMS']);
    }


    /**
     * @param int
     */
    public function setModelWithFilter($organizationId)
    {
        /** @var $builder */
        $builder = $this->connection->createQueryBuilder()
            ->select('[o.*], [r.price], [r.rz], [r.from], [r.to], [p.identifier]')
            ->from('[orders]', 'o')
            ->leftJoin('o', 'reservations', 'r', '[r.order_id] = [o.id]')
            ->leftJoin('r', 'places', 'p', '[r.place_id] = [p.id]')
            //->leftJoin('o', 'users', 'users', '[o.user_id] = [users.id]')
            ->where('[o.state] = %i', Order::STATE_PAID)
            ->andWhere('[paid_at] IS NOT NULL')
            ->andWhere('[o.payee_id] = %i', $organizationId);

        $this->setModel(new NextrasDbal($this->connection, $builder));
    }


    /**
     * @param int $id
     */
    public function handleRefund($id)
    {
        /** @var SecurePresenter $presenter */
        $presenter = $this->getPresenter();
        $organization = $this->orderFacade->getOrganization($id);

        try {
            $presenter->checkOrganizationDataAccess($organization);
        } catch (ForbiddenRequestException $e) {
            $presenter->flashMessage('flashmessages.refundFaild', 'danger');
            $this->redirect('this');
        }

        try {
            $this->orderFacade->refundOrder($id);
            $this->presenter->flashMessage('flashmessages.refundSuccess', 'success');
        } catch (\Exception $e) {
            $this->presenter->flashMessage('flashmessages.refundFaild', 'danger');
        }

        $this->redirect('this');
    }


    /**
     * @param int $orderId
     * @return Reservation
     */
    private function getReservation($orderId)
    {
        if (!array_key_exists($orderId, $this->reservations)) {
            $this->reservations[$orderId] = $this->reservationsRepository->findByOrderId($orderId)->fetch();
        }

        return $this->reservations[$orderId];
    }
}
