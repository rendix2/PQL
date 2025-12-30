<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 4. 2. 2020
 * Time: 11:55
 */

namespace pql\QueryBuilder;

use Exception;

trait Offset
{
    private int $offset;

    public function getOffset(): int
    {
        return $this->offset;
    }

    public function offset(int $offset): Offset|SelectQuery
    {
        if (!is_numeric($offset)) {
            throw new Exception('Offset is not a number.');
        }

        if ($offset === 0) {
            throw new Exception('Zero offset does not make sense.');
        }

        if ($offset < 0) {
            throw new Exception('Negative offset does not make sense.');
        }

        $this->offset = $offset;

        return $this;
    }
}
