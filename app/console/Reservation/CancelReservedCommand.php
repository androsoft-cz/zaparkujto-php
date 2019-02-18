<?php

namespace App\Console\Reservation;

use App\Model\Facade\ReservationFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;

final class CancelReservedCommand extends Command
{

    /** @var ILogger */
    private $logger;

    /** @var ReservationFacade */
    private $reservationFacade;


    public function __construct(
        ILogger $logger,
        ReservationFacade $reservationFacade
    )
    {
        parent::__construct();

        $this->logger = $logger;
        $this->reservationFacade = $reservationFacade;
    }


    protected function configure()
    {
        $this->setName('app:reservation:cancelReserved')
            ->setDescription('Cancel blocked reservation');
    }


    protected function execute(InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        $reservations = $this->reservationFacade->getCancelableReservedReservations();
        $success = 0;
        $errors = 0;

        foreach ($reservations as $reservation) {
            try {
                $this->reservationFacade->cancelReserved($reservation);
                $success++;
            } catch (\Exception $e) {
                $this->logger->log($e, ILogger::EXCEPTION);
                $errors++;
            }
        }

        $output->writeln(sprintf('Canceled: success (%d) / errors (%d).', $success, $errors));

        return 0;
    }

}
