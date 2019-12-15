<?php

/**
 * Class FunctionPql
 *
 * name is because of Function is key word of php
 */
class FunctionPql
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
     * FunctionPql constructor.
     * @param string $name
     * @param array $params
     */
    public function __construct($name, $params)
    {
        $this->name   = $name;
        $this->params = $params;
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