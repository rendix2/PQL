<?php
/**
 *
 * Created by PhpStorm.
 * Filename: UpdateExecutor.php
 * User: Tomáš Babický
 * Date: 21.09.2021
 * Time: 21:40
 */

namespace PQL\Query;

use PQL\Database\Query\Builder\UpdateBuilder;
use PQL\Database\Query\Select\Condition\WhereConditionExecutor;
use PQL\Database\Storage\IStorage;
use PQL\Database\Storage\StandardStorage;

/**
 * Class UpdateExecutor
 *
 * @package PQL\Query
 */
class UpdateExecutor
{
    /**
     * @var UpdateBuilder $query
     */
    private UpdateBuilder $query;

    /**
     * @var IStorage $storage
     */
    private IStorage $storage;

    /**
     * @var WhereConditionExecutor $whereConditionExecutor
     */
    private WhereConditionExecutor $whereConditionExecutor;

    /**
     * @param UpdateBuilder $query
     */
    public function __construct(UpdateBuilder $query)
    {
        $this->query = $query;
        $this->storage = new StandardStorage($this->query->getTable()->getTable());

        $this->whereConditionExecutor = new WhereConditionExecutor();
    }

    public function run() : bool
    {
        $rows = $this->storage->getAllTableData();

        if (count($this->query->getWhereConditions())) {
            foreach ($rows as $row) {
                foreach ($this->query->getSetExpressions() as $setExpression) {
                    foreach ($this->query->getWhereConditions() as $whereCondition) {
                        if ($this->whereConditionExecutor->run($row, $whereCondition)) {
                            $row->{$setExpression->getColumn()->evaluate()} = $setExpression->getValue()->evaluate();
                        }
                    }
                }
            }

        } else {
            foreach ($rows as $row) {
                foreach ($this->query->getSetExpressions() as $setExpression) {
                    $row->{$setExpression->getColumn()->evaluate()} = $setExpression->getValue()->evaluate();
                }
            }
        }

        return $this->storage->update($rows);
    }
}