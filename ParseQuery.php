<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 26. 2. 2019
 * Time: 13:08
 */

/**
 * Class ParseQuery
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class ParseQuery
{
    /**
     * @var string $expression
     */
    private $expression;
    /**
     * @var Query|null $query
     */
    private $query;
    /**
     * @var Database $database
     */
    private $database;

    /**
     * ParseQuery constructor.
     *
     * @param Database $database
     * @param string   $expression
     */
    public function __construct(Database $database, $expression)
    {
        $this->database   = $database;
        $this->expression = mb_strtolower($expression);

        $this->query = $this->parse();
    }

    /**
     * ParseQuery destructor.
     */
    public function __destruct()
    {
        $this->expression = null;
        $this->database   = null;
        $this->query      = null;
    }

    public function getQuery()
    {
        return $this->query;
    }

    /**
     *
     */
    private function parse()
    {
        $select = $this->selectQuery();
        $insert = $this->insertQuery();
        $delete = $this->deleteQuery();
        $update = $this->updateQuery();

        if ($select) {
            return $select;
        }

        if ($insert) {
            return $insert;
        }

        if ($delete) {
            return $delete;
        }

        if ($update) {
            return $update;
        }

        return null;
    }

    private function selectQuery()
    {
        $selectFunctions = new Nette\Tokenizer\Tokenizer([
            'select'          => 'select',
            'columns'         => '\s+',
            'count'           => 'count(\s+)',
            'sum'             => 'sum(\s+)',
            'avg'             => 'avg(\s+)',
            'min'             => 'min(\s+)',
            'max'             => 'max(\s+)',
            'from'            => 'from',
            'table'           => '\s+',
            'innerjoin'       => 'inner join',
            'innerjointables' => '\s+',
            'oni'             => 'on',
            'onconditioni'    => '\s+',
            'leftjoin'        => 'left join',
            'leftjointables'  => '\s+',
            'onl'             => 'on',
            'onconditionl'    => '\s+',
            'where'           => 'where',
            'condition'       => '\s+',
            'groupby'         => 'group by',
            'group'           => '\s+',
            'having'          => 'having',
            'have'            => '\s+',
            'orderby'         => 'order by',
            'order'           => '\s+',
            'limit'           => 'limit',
            'lim'             => '\d+',
        ]);

        $selectFunctionStream = $selectFunctions->tokenize($this->expression);

        $select = new Nette\Tokenizer\Tokenizer([
            'select'          => 'select',
            'columns'         => '\s+',
            'from'            => 'from',
            'table'           => '\s+',
            'innerjoin'       => 'inner join',
            'innerjointables' => '\s+',
            'oni'             => 'on',
            'onconditioni'    => '\s+',
            'leftjoin'        => 'left join',
            'leftjointables'  => '\s+',
            'onl'             => 'on',
            'onconditionl'    => '\s+',
            'where'           => 'where',
            'condition'       => '\s+',
            'groupby'         => 'group by',
            'group'           => '\s+',
            'having'          => 'having',
            'have'            => '\s+',
            'orderby'         => 'order by',
            'orderColumn'     => '\s+',
            'orderTypeAsc'    => 'asc',
            'orderTypeDesc'   => 'desc',
            'limit'           => 'limit',
            'lim'             => '\d+',
        ]);

        $selectStream = $select->tokenize($this->expression);

        return new Query($this->database);
    }

    private function updateQuery()
    {
        return new Query($this->database);
    }

    private function deleteQuery()
    {
        return new Query($this->database);
    }

    private function insertQuery()
    {
        $insert = new Nette\Tokenizer\Tokenizer([
            'insert'     => 'INSERT INTO',
            'table'      => '\s+',
            'columns'    => '(\s+)',
            'valuesWord' => 'VALUES',
            'values'     => '\s+',
        ]);

        $insertStream = $insert->tokenize($this->expression);

        return new Query($this->database);
    }
}
