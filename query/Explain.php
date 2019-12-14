<?php

namespace query;

use Exception;

/**
 * Class Explain
 *
 * @package query
 */
class Explain extends BaseQuery
{

    /**
     * @inheritDoc
     */
    public function run()
    {
        throw new Exception('Unsupported operation.');
    }
}