<?php

/**
 * Class SubQueryHelper
 *
 */
class SubQueryHelper
{
    /**
     * run subQuery
     *
     * check if it has one only row and only one column
     *
     * @param Query $subQuery
     *
     * @return Result SubQuery
     * @throws Exception
     */
    public static function runAndCheckOneRowOneColumn(Query $subQuery)
    {
        $subQueryResult = self::runAndCheckOneColumn($subQuery);

        if ($subQueryResult->getRowsCount() > 1){
            throw new Exception('SubQuery fetched more than one row.');
        }

        return $subQueryResult;
    }

    /**
     * run subQuery
     *
     * check if it has one only row and only one column
     *
     * @param Query $subQuery
     *
     * @return Result SubQuery
     * @throws Exception
     */
    public static function runAndCheckOneColumn(Query $subQuery)
    {
        $subQueryResult = $subQuery->run();

        if (!($subQueryResult instanceof Result)) {
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

