<?php

namespace pql\QueryExecutor;

use Exception;
use pql\QueryBuilder\Query;
use pql\QueryResult\TableResult;

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
        $subQueryResult = self::runAndCheckSubQuery($subQuery);

        if (!$subQueryResult->getColumnsCount()) {
            throw new Exception('SubQuery has no column.');
        }

        if ($subQueryResult->getColumnsCount() > 1) {
            throw new Exception('SubQuery has more than one column');
        }

        return $subQueryResult;
    }

    /**
     * @param Query $subQuery
     *
     * @return TableResult
     * @throws Exception
     */
    public static function runAndCheckSubQuery(Query $subQuery)
    {
        $subQueryResult = $subQuery->run();

        if (!($subQueryResult instanceof TableResult)) {
            throw new Exception('SubQuery has no result.');
        }

        return $subQueryResult;
    }
}
