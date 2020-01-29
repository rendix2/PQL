<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 29. 1. 2020
 * Time: 17:00
 */

namespace pql\QueryExecute;

use pql\Query;

/**
 * Class UpdateSelect
 *
 * @package pql\QueryExecute
 * @author  Tomáš Babický tomas.babicky@websta.de
 */
class UpdateSelect extends BaseQuery
{

    /**
     * @inheritDoc
     */
    public function run()
    {
        $selectQuery = new Select($this->query->getUpdateData());
        $selectQuery->run();

        $results = [];

        foreach ($selectQuery->result as $updateDate) {
            $updateQuery = new Query($this->query->getDatabase());
            $updateQuery->update($this->query->getTable()->getName(), $updateDate);

            foreach ($this->query->getWhereConditions() as $whereCondition) {
                $updateQuery = $updateQuery->where(
                    $whereCondition->getColumn(),
                    $whereCondition->getOperator(),
                    $whereCondition->getValue()
                );
            }

            if ($this->query->getLimit()) {
                $updateQuery->limit($this->query->getLimit());
            }

            if ($this->query->getOffset()) {
                $updateQuery->offset($this->query->getOffset());
            }

            $results[] = $updateQuery->run();
        }

        return $results;
    }
}
