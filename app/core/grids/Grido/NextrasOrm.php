<?php

namespace App\Core\Grids\Grido;

use Exception;
use Grido\Components\Filters\Condition;
use Grido\DataSources\IDataSource;
use Nette\Templating\Helpers;
use Nextras\Orm\Collection\ICollection;

class NextrasOrm implements IDataSource
{

    /** @var ICollection */
    private $collection;

    /**
     * @param ICollection $collection
     */
    public function __construct(ICollection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * @param Condition $condition
     * @param ICollection $collection
     */
    protected function makeWhere(Condition $condition, ICollection &$collection)
    {
        if ($condition->callback) {
            callback($condition->callback)->invokeArgs([$condition->value, $collection]);
        } else {
            list ($column, $value) = $condition->__toArray();
            $collection = $collection->findBy([$column => $value]);
        }

    }

    /**
     * INTERFACE  **************************************************************
     */

    /**
     * @return int
     */
    public function getCount()
    {
        return (int) $this->collection->countStored();
    }

    /**
     * @return ICollection
     */
    public function getData()
    {
        return $this->collection;
    }

    /**
     * @param array $conditions
     */
    public function filter(array $conditions)
    {
        foreach ($conditions as $condition) {
            $this->makeWhere($condition, $this->collection);
        }
    }

    /**
     * @param int $offset
     * @param int $limit
     */
    public function limit($offset, $limit)
    {
        $this->collection = $this->collection->limitBy($limit, $offset);
    }

    /**
     * @param array $sorting
     */
    public function sort(array $sorting)
    {
        foreach ($sorting as $column => $sort) {
            $this->collection = $this->collection->orderBy($column, $sort);
        }
    }

    /**
     * @param mixed $column
     * @param array $conditions
     * @param int $limit
     */
    public function suggest($column, array $conditions, $limit)
    {
        $collection = clone $this->collection;
        is_string($column) && $collection = $collection->orderBy($column);

        $collection->limitBy($limit);

        foreach ($conditions as $condition) {
            $this->makeWhere($condition, $collection);
        }

        $items = [];
        foreach ($collection as $row) {
            if (is_string($column)) {
                $value = (string) $row[$column];
            } elseif (is_callable($column)) {
                $value = (string) $column($row);
            } else {
                $type = gettype($column);
                throw new Exception("Column of suggestion must be string or callback, $type given.");
            }

            $items[$value] = Helpers::escapeHtml($value);
        }

        is_callable($column) && sort($items);

        return array_values($items);
    }
}
