<?php

namespace App\Model\Orm\Orders;

use App\Model\Orm\AbstractMapper;
use InvalidArgumentException;
use Nette\Utils\DateTime;

class OrdersMapper extends AbstractMapper
{
    protected function createStorageReflection()
    {
        $reflection = parent::createStorageReflection();
        $reflection->setMapping('payeeId', 'payee_id');
        return $reflection;
    }


    public function getNextVs(int $vsPrefix): string
    {
        // Format OOYYNNNNNN
        $now = new DateTime();
        $actYear = $now->format('y');

        $org = str_pad((string) $vsPrefix, 2, '0', STR_PAD_LEFT);
        $prefix = $org . $actYear;

        if (strlen($prefix) !== 4) {
            throw new InvalidArgumentException('VS prefix generation fail.');
        }

        $builder = $this->builder()
            ->select('MAX([vs]) as [vs]')
            ->where('[vs] LIKE %like_', $prefix);

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        $row = $result->fetch();
        if (!$row || $row->vs === NULL) {
            $count = 1;
        } else {
            $count = (int) substr($row->vs, 5, 6);
            $count++;
        }

        $suffix = str_pad((string) $count, 6, '0', STR_PAD_LEFT);
        if (strlen($suffix) !== 6) {
            throw new InvalidArgumentException('VS suffix generation fail.');
        }

        return $prefix . $suffix;
    }

    public function getTotalUsers($organizationId, DateTime $from = NULL, DateTime $to = NULL)
    {
        $builder = $this->builder()
            ->select('COUNT(user_id) AS total')
            ->where('orders.payee_id = %i', $organizationId)
            ->andWhere('orders.state = %i', ORDER::STATE_PAID);

        if ($from) {
            $builder = $builder->andWhere('DATE(orders.created_at) >= %any', $from->format('Y-m-d'));
        }

        if ($to) {
            $builder = $builder->andWhere('DATE(orders.created_at) <= %any', $to->format('Y-m-d'));
        }

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        $row = $result->fetch();

        if ($row && $row->total) {
            return $row->total;
        }

        return 0;
    }

    public function getUsersPerYearMonth(int $organizationId, \DateTime $from, \DateTime $to, array $orderStates = [Order::STATE_PAID]): array
    {
        $builder = $this->builder()
            ->select('COUNT(orders.user_id) AS total, DATE_FORMAT(orders.created_at, "%%Y-%%m") AS dateGroup')
            ->where('orders.payee_id = %i', $organizationId)
            ->andWhere('orders.state IN %any', $orderStates)
            ->andWhere('DATE(orders.created_at) >= %any', $from->format('Y-m-d'))
            ->andWhere('DATE(orders.created_at) <= %any', $to->format('Y-m-d'))
            ->groupBy('DATE_FORMAT(orders.created_at, "%%Y-%%m")')
            ->orderBy('DATE_FORMAT(orders.created_at, "%%Y-%%m")');

        $result = $this->connection->queryArgs(
            $builder->getQuerySql(),
            $builder->getQueryParameters()
        );

        return $result->fetchPairs('dateGroup');
    }
}
