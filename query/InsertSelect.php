<?php

namespace query;

use Exception;

/**
 * Class InsertSelect
 *
 * @package query
 */
class InsertSelect extends BaseQuery
{
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function run()
    {
        throw new Exception('Unsupported operation.');
    }
}
