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
        $subQueryResult = $subQuery->run();

        if (!($subQueryResult instanceof Result)) {
            throw new Exception('SubQuery has no result.');
        }

        if (count($subQueryResult->getRows()) > 1){
            throw new Exception('Subquery fetched more than one row.');
        }

        $columnsCount = count($subQueryResult->getColumns());

        if (!$columnsCount) {
            throw new Exception('Subquery has no column.');
        }

        if ($columnsCount > 1) {
            throw new Exception('Subquery has more than one column');
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

        $columnsCount = count($subQueryResult->getColumns());

        if (!$columnsCount) {
            throw new Exception('Subquery has no column.');
        }

        if ($columnsCount > 1) {
            throw new Exception('Subquery has more than one column');
        }

        return $subQueryResult;
    }
}
