<?php

namespace App\Console\Order;

use App\Model\Facade\OrderFacade;
use App\Model\Orm\Orders\Order;
use App\Model\Orm\Orders\OrdersRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Tracy\ILogger;

final class CheckCommand extends Command
{
    /** @var ILogger */
    private $logger;

    /** @var OrdersRepository @inject */
    public $ordersRepository;

    /** @var OrderFacade @inject */
    public $orderFacade;


    public function __construct(
        ILogger $logger,
        OrderFacade $orderFacade
    )
    {
        parent::__construct();

        $this->logger = $logger;
        $this->orderFacade = $orderFacade;
    }


    protected function configure()
    {
        $this->setName('app:order:check')
            ->setDescription('Check if orders are paid');
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $orders = $this->ordersRepository->findBy(['paymentType' => Order::PAYMENT_TYPE_GATE, 'paymentId!=' => NULL, 'payeeId!=' => NULL, 'state' => [Order::STATE_CREATED, Order::STATE_WAITING, Order::STATE_PROCESSING, Order::STATE_PARTIALY_REFUNDED]]);
        $success = $errors = 0;

        foreach ($orders as $order) {
            try {
                $this->orderFacade->verifyOrderPayment($order->id);
                $success++;
            } catch (\Exception $e) {
                $this->logger->log($e, ILogger::EXCEPTION);
                $errors++;
            }
        }

        $output->writeln(sprintf('Check if orders are paid: success (%d) / errors (%d).', $success, $errors));

        return 0;
    }

}
