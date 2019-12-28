<?php

/**
 * Class AggregateFunctions
 *
 * name is because of Function is key word of php
 */
class AggregateFunctions
{
    /**
     * @var string
     */
    const AVERAGE = 'avg';

    /**
     * @var string
     */
    const SUM = 'sum';

    /**
     * @var string
     */
    const MIN = 'min';

    /**
     * @var string
     */
    const MAX = 'max';

    /**
     * @var string
     */
    const MEDIAN = 'median';

    /**
     * @var string
     */
    const COUNT = 'count';

    /**
     * @var string $name
     */
    private $name;

    /**
     * @var array $params
     */
    private $params;

    /**
     * AggregateFunctions constructor.
     *
     * @param string $name
     * @param array $params
     */
    public function __construct($name, $params)
    {
        $this->name   = $name;
        $this->params = $params;
    }

    /**
     * AggregateFunctions destructor.
     */
    public function __destruct()
    {
        $this->name   = null;
        $this->params = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return mb_strtoupper($this->getName()) . '(' . implode(', ', $this->getParams()) . ')';
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->params;
    }
}
