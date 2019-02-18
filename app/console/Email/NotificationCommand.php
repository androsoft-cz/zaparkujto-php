<?php

namespace App\Console\Email;

use App\Model\Facade\NotificationFacade;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;

final class NotificationCommand extends Command
{

    /** @var ILogger */
    private $logger;

    /** @var NotificationFacade */
    private $notificationFacade;


    public function __construct(
        ILogger $logger,
        NotificationFacade $notificationFacade
    )
    {
        parent::__construct();

        $this->logger = $logger;
        $this->notificationFacade = $notificationFacade;
    }


    protected function configure()
    {
        $this->setName('app:email:notification')
            ->setDescription('Send email notification');
    }


    protected function execute(InputInterface $input, OutputInterface $output) // @codingStandardsIgnoreLine
    {
        $reservations = $this->notificationFacade->getSoonExpireReservations();
        $success = 0;
        $errors = 0;

        foreach ($reservations as $reservation) {
            try {
                $this->notificationFacade->sendSoonExpireNotification($reservation);
                $success++;
            } catch (\Exception $e) {
                $this->logger->log($e, ILogger::EXCEPTION);
                $errors++;
            }
        }

        $output->writeln(sprintf('Notifications: success (%d) / errors (%d).', $success, $errors));

        return 0;
    }

}
