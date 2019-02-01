<?php
/**
 * Created by PhpStorm.
 * User: Tom
 * Date: 1. 2. 2019
 * Time: 9:53
 */

/**
 * Class Query
 *
 * @author Tomáš Babický tomas.babicky@websta.de
 */
class Query
{
    const ENABLED_OPERATORS = ['=', '<', '>', '<=', '>=', '!='];

    /**
     * @var Database $database
     */
    private $database;

    /**
     * @var array $columns
     */
    private $columns;

    /**
     * @var Table $table
     */
    private $table;

    /**
     * @var array $condition
     */
    private $condition;

    /**
     * @var array $orderBy
     */
    private $orderBy;

    /**
     * @var array $groupBy
     */
    private $groupBy;

    private $leftJoin;

    private $innerJoin;

    /**
     * @var string $query
     */
    private $query;

    /**
     * @var int $limit
     */
    private $limit;

    private $isSelect;

    private $isInsert;

    private $isUpdate;

    private $isDelete;

    /**
     * @var array $updateData
     */
    private $updateData;

    /**
     * @var array $insertData
     */
    private $insertData;

    /**
     * Query constructor.
     *
     * @param Database $database
     */
    public function __construct(Database $database)
    {
        $this->database = $database;
    }

    /**
     * Query destructor.
     */
    public function __destruct()
    {
        $this->database = null;
        $this->columns  = null;
        $this->table    = null;
    }

    /**
     * @param array $columns
     *
     * @return Query
     * @throws Exception
     *
     */
    public function select(array $columns)
    {
        foreach ($columns as $column) {
            if (!$this->table->columnExists($column)) {
                throw new Exception(sprintf('Column "%s" does not exist.', $column));
            }
        }

        $this->isSelect = true;
        $this->columns = $columns;

        return $this;
    }

    /**
     * @param string $table
     *
     * @return Query
     */
    public function from($table)
    {
        $this->table = new Table($this->database, $table);

        return $this;
    }

    /**
     * @param $column
     * @param $operator
     * @param $value
     *
     * @return Query
     * @throws Exception
     */
    public function where($column, $operator, $value)
    {
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }

        if (!in_array($operator, self::ENABLED_OPERATORS, true)) {
            throw  new Exception(sprintf('Unknown operator "%s".', $column));
        }

        if (!is_scalar($value)) {
            throw new Exception('Searched value is not scalar.');
        }

        $this->condition[] = ['column' => $column, 'operator' => $operator, 'value' => $value];

        return $this;
    }

    /**
     * @param string $column
     * @param bool   $asc
     *
     * @return Query
     * @throws Exception
     */
    public function orderBy($column, $asc = true)
    {
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }

        $this->orderBy[] = ['column' => $column, 'asc' => $asc];

        return $this;
    }

    /**
     * @param $column
     *
     * @return Query
     * @throws Exception
     *
     */
    public function groupBy($column)
    {
        if (!$this->table->columnExists($column)) {
            throw new Exception(sprintf('Column "%s" does not exist.', $column));
        }

        $this->groupBy[] = $column;

        return $this;
    }

    /**
     * @param int $limit
     *
     * @return Query
     * @throws Exception
     */
    public function limit($limit)
    {
        if (!is_numeric($limit)) {
            throw  new Exception('Limit is not a number.');
        }

        $this->limit = $limit;

        return $this;
    }

    public function leftJoin($table)
    {
        return $this;
    }

    public function innerJoin($table)
    {
        return $this;
    }

    /**
     * @param string $table
     * @param array  $data
     *
     * @return Query
     *
     */
    public function update($table, array $data)
    {
        $this->isUpdate   = true;
        $this->updateData = $data;
        $this->table      = new Table($this->database, $table);

        return $this;
    }

    public function add($table, array $data)
    {
        $this->isInsert   = true;
        $this->insertData = $data;
        $this->table      = new Table($this->database, $table);

        return $this;
    }

    public function delete($table)
    {
        $this->isDelete = true;
        $this->table    = new Table($this->database, $table);

        return $this;
    }

    private function generateWhere()
    {
        $where   = '';

        if (count($this->condition)) {
            $where = 'WHERE ';

            foreach ($this->condition as $condition) {
                $where .= sprintf('%s %s %s ', $condition['column'], $condition['operator'], $condition['value']);
            }
        }

        return $where;
    }

    private function generateGroupBy()
    {
        $groupBy = '';

        if (count($this->groupBy)) {
            $groupBy = 'GROUP BY ';

            foreach ($this->groupBy as $groupBy) {
                $groupBy .= $groupBy;
            }
        }

        return $groupBy;
    }

    private function generateOrderBy()
    {
        $orderBy = '';

        if (count($this->orderBy)) {
            $orderBy = 'ORDER BY ';

            foreach ($this->orderBy as $orderBy) {
                $type = $orderBy['asc'] ? 'ASC' : 'DESC';

                $orderBy .= sprintf('%s %s', $orderBy['column'], $type);
            }
        }

        return $orderBy;
    }

    /**
     * @return string
     */
    private function generateLimit()
    {
        $limit = '';

        if ($this->limit) {
            $limit = 'LIMIT ' . $this->limit;
        }

        return $limit;
    }

    /**
     * @return string
     */
    private function build()
    {
        if ($this->isSelect) {
            $select = sprintf('SELECT %s FROM %s ', implode(', ', $this->columns), $this->table->getName());

            $where   = $this->generateWhere();
            $orderBy = $this->generateOrderBy();
            $groupBy = $this->generateGroupBy();
            $limit   = $this->generateLimit();

            return sprintf('%s %s %s %s %s', $select, $where, $groupBy, $orderBy, $limit);
        }

        if ($this->isDelete) {
            $where   = $this->generateWhere();
            $limit   = $this->generateLimit();

            return sprintf('DELETE FROM %s %s %s', $this->table->getName(), $where, $limit);
        }

        if (count($this->isUpdate)) {
            $where   = $this->generateWhere();
            $limit   = $this->generateLimit();
            $set     = '';

            if ($this->updateData) {
                $set = 'SET ';

                foreach ($this->updateData as $column => $value) {
                    $set .= sprintf('%s = %s', $column, $value);
                }
            }

            return sprintf('UPDATE %s %s %s %s', $this->table->getName(), $set, $where, $limit);
        }

        if (count($this->isInsert)) {
            $columns = array_keys($this->insertData);
            $values  = array_values($this->insertData);

            return sprintf(
                'INSERT INTO %s (%s) VALUES (%s)',
                $this->table->getName(),
                implode(', ', $columns),
                implode(', ', $values)
            );
        }
    }

    public function show()
    {
        echo sprintf('I have built this query: %s', $this->build());
    }

    /**
     * @return Result
     */
    public function run()
    {
        /**
         * @var Row[] $tmpRows
         */
        $tmpRows = $this->table->getRows();
        $res     = [];

        if (count($this->condition)) {
            /**
             * @var Row $tmpRow
             */
            foreach ($tmpRows as $tmpRow) {

                foreach ($this->condition as $condition) {
                    if ($condition['operator'] === '=') {
                        if ($tmpRow->get()->{$condition['column']} === $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '<') {
                        if ($tmpRow->get()->{$condition['column']} < $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '>') {
                        if ($tmpRow->get()->{$condition['column']} > $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '<=') {
                        if ($tmpRow->get()->{$condition['column']} <= $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '>=') {
                        if ($tmpRow->get()->{$condition['column']} >= $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }

                    if ($condition['operator'] === '!=') {
                        if ($tmpRow->get()->{$condition['column']} !== $condition['value']) {
                            $res[] = $tmpRow;
                        }
                    }
                }
            }
        } else {
            $res = $tmpRows;
        }

        if (count($this->groupBy)) {
            $groups = [];
            $tmpGroup = [];

            foreach ($res as $row) {
                foreach ($row->get() as $column) {
                    foreach ($this->groupBy as $groupColumn) {
                        if ($column === $groupColumn) {
                            // todo
                        }
                    }
                }
            }
        }

        if (count($this->orderBy)) {
            $tmpSort = [];

            foreach ($this->orderBy as $item) {
                $tmpSort[] = [
                    'row' => Help::arrayObjectColumn($res, $item['column']),
                    'asc' => $item['asc']
                ];
            }

            foreach ($tmpSort as $column => $row) {
                if ($row['asc']) {
                    array_multisort($row['row'], SORT_ASC, $tmpRows);
                } else {
                    array_multisort($row['row'], SORT_DESC, $tmpRows);
                }
            }
        }

        if ($this->limit) {
            $limitRows = [];

            for ($i = 0; $i < $this->limit; $i++) {
                $limitRows[] = $res[$i];
            }

            $tmpRows   = $limitRows;
            $limitRows = null;
        }

        return new Result($tmpRows);
    }
}

$database = new Database('test');

$query = new Query($database);
$query->select(['hi', 'j'])->from('test')->where('hi', '=', 5)->run();
