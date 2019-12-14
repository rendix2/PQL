<?php

/**
 * Class Optimizer
 */
class Optimizer
{
    /**
     * @var Query $query
     */
    private $query;

    /**
     * Optimizer constructor.
     *
     * @param Query $query
     */
    public function __construct(Query $query)
    {
        $this->query = $query;
    }

    /**
     * Optimizer Constructor
     */
    public function __destruct()
    {
        $this->query = null;
    }

    public function useHashJoin()
    {

    }

    public function useMergeJoin()
    {

    }

    public function useNestedLoopJoin()
    {

    }


}