<?php
/**
 *
 * Created by PhpStorm.
 * Filename: PrepareSelect.php
 * User: Tomáš Babický
 * Date: 15.09.2021
 * Time: 1:43
 */

namespace PQL\Tests;

use PQL\Database;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Expressions\FunctionExpression;
use PQL\Query\Builder\Expressions\HavingCondition;
use PQL\Query\Builder\Expressions\IntegerValue;
use PQL\Query\Builder\Expressions\JoinCondition;
use PQL\Query\Builder\Expressions\Minus;
use PQL\Query\Builder\Expressions\Operator;
use PQL\Query\Builder\Expressions\Plus;
use PQL\Query\Builder\Expressions\TableExpression;
use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Query\Builder\Select as SelectBuilder;
use PQL\Server;
use Tester\Assert;

class PrepareSelect
{
    private SelectBuilder $query;

    private Database $database;

    public static string $nameSpace = 'PQL\\Tests\\InputData';

    public function __construct()
    {
        $server = new Server();
        $this->database = $server->getDatabase('test');

        $this->query = $this->database->selectQuery();
    }


    public function testColumnsFrom(): array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testDistinctColumn() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->distinct(new Column('rok', $commentTable));

        $query->from($commentTable);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testInnerJoinTableOnCondition(): array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');
        $userTable = new TableExpression($this->database, 'User');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->select(new Column('id', $userTable));
        $query->select(new Column('username', $userTable));

        $query->from($commentTable);
        $query->innerJoin(
            $userTable, [
            new JoinCondition(
                new Column('id', $userTable),
                new Operator('='),
                new Column('userId', $commentTable)
            ),
        ],
        );

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testCrossJoin(): array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');
        $userTable = new TableExpression($this->database, 'User');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new Column('id', $userTable));
        $query->select(new Column('username', $userTable));

        $query->from($commentTable);
        $query->crossJoin($userTable);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testLeftJoinTableOnCondition(): array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');
        $userTable = new TableExpression($this->database, 'User');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new Column('id', $userTable));
        $query->select(new Column('username', $userTable));

        $query->from($commentTable);
        $query->leftJoin(
            $userTable, [
            new JoinCondition(
                new Column('id', $userTable),
                new Operator('='),
                new Column('userId', $commentTable)
            ),
        ],
        );

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testSingleArgumentFunction() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new FunctionExpression('strtoupper', [new Column('text', $commentTable)]));

        $query->from($commentTable);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testExpressions() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->select(
            new Plus(
                new IntegerValue(1),
                new Plus(
                    new IntegerValue(2),
                    new Minus(
                        new IntegerValue(3),
                        new IntegerValue(4),
                    )
                )
            )
        );

        $query->from($commentTable);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    /*    public function testAloneExpressions()
        {
            $query = clone $this->query;

            $query->select(
                new Plus(
                    new IntegerValue(1),
                    new Plus(
                        new IntegerValue(2),
                        new Minus(
                            new IntegerValue(3),
                            new IntegerValue(4),
                        )
                    )
                )
            );

            return $query;
        }*/

    public function testWhereSingleCondition() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new Operator('='),
                new IntegerValue('2015')
            )
        );

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testWhereDualCondition() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new Operator('='),
                new IntegerValue('2015')
            )
        );

        $query->where(
            new WhereCondition(
                new Column('userId', $commentTable),
                new Operator('='),
                new IntegerValue('1')
            )
        );

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testSingleGroupBy() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->groupBy(new Column('userId', $commentTable));

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testAggregateFunctionWithoutGroupBy() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testAggregateFunctionWithGroupBy() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $query->groupBy(new Column('rok', $commentTable));

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testSingleHaving() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $query->groupBy(new Column('rok', $commentTable));

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]),
                new Operator('='),
                new IntegerValue(3)
            )
        );
        //$query->having(new HavingCondition(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]), new Operator('>='), new IntegerValue(1)));

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testDualHaving() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $query->groupBy(new Column('rok', $commentTable));

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]),
                new Operator('='),
                new IntegerValue(3)
            )
        );
        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]),
                new Operator('='),
                new IntegerValue(6060)
            )
        );

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testSingleOrderByColumnAsc() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);
        $query->orderBy(new Column('rok', $commentTable));

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testSingleOrderByColumnDesc() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);
        $query->orderBy(new Column('rok', $commentTable), 'DESC');

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testSingleOrderByFunctionAsc() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new FunctionExpression('strtoupper', [new Column('text', $commentTable)]));

        $query->from($commentTable);
        $query->orderBy(new FunctionExpression('strtoupper', [new Column('text', $commentTable)]));

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testSingleOrderByAggregateFunctionAsc() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $query->groupBy(new Column('rok', $commentTable));
        $query->orderBy(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testLimit() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $query->groupBy(new Column('rok', $commentTable));
        $query->orderBy(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->limit(1);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testOffset() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $query->groupBy(new Column('rok', $commentTable));
        $query->orderBy(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->offset(1);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

    public function testLimitOffset() : array
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]));
        $query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->from($commentTable);

        $query->groupBy(new Column('rok', $commentTable));
        $query->orderBy(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));

        $query->limit(1);
        $query->offset(1);

        $stdRows = $query->execute();

        $arrayRows = [];

        foreach ($stdRows as $stdRow) {
            $arrayRows[] = (array)$stdRow;
        }

        return $arrayRows;
    }

}