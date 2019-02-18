<?php

namespace App\Console\Order;

use App\Model\Facade\OrderFacade;
use App\Model\Facade\ReservationFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;

final class CancelUnpaidOrdersCommand extends Command
{
    /** @var ILogger */
    private $logger;

    /** @var ReservationFacade */
    private $reservationFacade;

    /** @var OrderFacade */
    private $orderFacade;


    public function __construct(
        ILogger $logger,
        ReservationFacade $reservationFacade,
        OrderFacade $orderFacade
    ) {
        parent::__construct();

        $this->logger = $logger;
        $this->reservationFacade = $reservationFacade;
        $this->orderFacade = $orderFacade;
    }


    protected function configure(): void
    {
        $this->setName('app:order:cancelUnpaidOrders')
            ->setDescription('Cancel unpaid orders');
    }


    protected function execute(InputInterface $input, OutputInterface $output): ?int
    {
        $reservations = $this->reservationFacade->getUnpaidExpiredReservations();
        $success = 0;
        $errors = 0;

        foreach ($reservations as $reservation) {
            try {
                $this->orderFacade->cancelOrder($reservation->order->id);
                $success++;
            } catch (\Exception $e) {
                $this->logger->log($e, ILogger::EXCEPTION);
                $errors++;
            }
        }

        $output->writeln(sprintf('Unpaid orders processed. Canceled: success (%d) / errors (%d).', $success, $errors));

        return 0;
    }
}
