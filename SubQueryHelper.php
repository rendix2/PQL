<?php

namespace pql;

use Exception;

/**
 * Class SubQueryHelper
 *
 * @author rendix2 <rendix2@seznam.cz>
 * @package pql
 */
class SubQueryHelper
{
    /**
     * Run subQuery.
     *
     * Check if it has one only row and only one column.
     *
     * @param Query $subQuery
     *
     * @return TableResult SubQuery
     * @throws Exception
     */
    public static function runAndCheckOneRowOneColumn(Query $subQuery)
    {
        $subQueryResult = self::runAndCheckOneColumn($subQuery);

        if ($subQueryResult->getRowsCount() > 1) {
            throw new Exception('SubQuery fetched more than one row.');
        }

        return $subQueryResult;
    }

    /**
     * Run subQuery.
     *
     * Check if it has one only row and only one column.
     *
     * @param Query $subQuery
     *
     * @return TableResult SubQuery
     * @throws Exception
     */
    public static function runAndCheckOneColumn(Query $subQuery)
    {
        $subQueryResult = $subQuery->run();

        if (!($subQueryResult instanceof TableResult)) {
            throw new Exception('SubQuery has no result.');
        }

        if (!$subQueryResult->getColumnsCount()) {
            throw new Exception('Subquery has no column.');
        }

        if ($subQueryResult->getColumnsCount() > 1) {
            throw new Exception('SubQuery has more than one column');
        }

        return $subQueryResult;
    }
}
