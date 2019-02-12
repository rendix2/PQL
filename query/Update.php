<?php
namespace query;

use Query;
use SplFileObject;
use Table;

/**
 * Class Update
 *
 * @author  rendix2
 * @package query
 */
class Update
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * @var array $res
     */
    private $res;

    /**
     * Update constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Update destructor.
     */
    public function __destruct()
    {
        $this->query = null;
        $this->res   = null;
    }

    /**
     *
     */
    public function run()
    {
        $this->where();
        $this->limit();

        $file = new SplFileObject($this->query->getTable()->getFileName(),'rwb');

        foreach ($this->res as $line => $values) {
            $file->seek($line + 1);
            $file->fwrite(implode(Table::COLUMN_DELIMITER, $values));
        }

        return count($this->res);
    }

    private function where()
    {
        $wheres = $this->query->getWhereCondition();
        $rows   = $this->query->getTable()->getRows();
        $res    = [];

        foreach ($wheres as $where) {
            foreach ($rows as $rowNumber => $row) {
                foreach ($row as $column => $value) {
                    if ($where['column'] === $column) {
                        if ($where['operator'] === '=') {
                            if ($where['value'] === $value) {
                                $res[$rowNumber] = $row;
                            }
                        }

                        if ($where['operator'] === '>') {
                            if ($where['value'] > $value) {
                                $res[$rowNumber] = $row;
                            }
                        }

                        if ($where['operator'] === '>=') {
                            if ($where['value'] >= $value) {
                                $res[$rowNumber] = $row;
                            }
                        }

                        if ($where['operator'] === '<') {
                            if ($where['value'] < $value) {
                                $res[$rowNumber] = $row;
                            }
                        }

                        if ($where['operator'] === '<=') {
                            if ($where['value'] <= $value) {
                                $res[$rowNumber] = $row;
                            }
                        }

                        if ($where['operator'] === '!=' || $where['operator'] === '<>') {
                            if ($where['value'] !== $value) {
                                $res[$rowNumber] = $row;
                            }
                        }
                    }
                }
            }
        }

        $this->res = $res;
    }

    private function limit()
    {
        $limit = $this->query->getLimit();

        $this->res = array_slice($this->res, $limit);
    }
}

