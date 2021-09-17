<?php
/**
 *
 * Created by PhpStorm.
 * Filename: SelectTestQueryFactory.php
 * User: Tomáš Babický
 * Date: 15.09.2021
 * Time: 1:43
 */

namespace PQL\Tests;

use PQL\Database;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Expressions\ArrayValue;
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

class SelectTestQueryFactory
{
    private SelectBuilder $query;

    private Database $database;

    public static string $nameSpace = 'PQL\\Tests\\InputData';

    private TableExpression $commentsTable;

    private TableExpression $usersTable;

    public function __construct()
    {
        $server = new Server();
        $this->database = $server->getDatabase('test');

        $this->query = $this->database->selectQuery();

        $this->commentsTable = new TableExpression($this->database, 'comments');
        $this->usersTable = new TableExpression($this->database, 'User');
    }

    public function testColumnsFrom() : SelectBuilder
    {
        $query = clone $this->query;

        $query->select(new Column('id', $this->commentsTable));
        $query->select(new Column('text', $this->commentsTable));
        $query->select(new Column('rok', $this->commentsTable));
        $query->select(new Column('userId', $this->commentsTable));

        $query->from($this->commentsTable);

        return $query;
    }

    public function testDistinctColumn() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->distinct(new Column('rok', $commentTable));
        $query->from($commentTable);

        return $query;
    }

    public function testDistinctFunctionColumn() : SelectBuilder
    {
        $query = clone $this->query;

        $query->distinct(
            new FunctionExpression(
                'strtoupper',
                [new Column('text', $this->commentsTable)]
            )
        );
        $query->from($this->commentsTable);

        return $query;
    }

    public function testInnerJoinTableOnCondition() : SelectBuilder
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

        return $query;
    }

    public function testCrossJoin() : SelectBuilder
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

        return $query;
    }

    public function testLeftJoinTableOnCondition() : SelectBuilder
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

        return $query;
    }

    public function testSingleArgumentFunction() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));
        $query->select(new FunctionExpression('strtoupper', [new Column('text', $commentTable)]));

        $query->from($commentTable);

        return $query;
    }

    public function testExpressions() : SelectBuilder
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

        return $query;
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

    public function testWhereSingleCondition() : SelectBuilder
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

        return $query;
    }

    public function testWhereDualCondition() : SelectBuilder
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

        return $query;
    }

    public function testWhereEquals() : SelectBuilder
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
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereNotEquals1() : SelectBuilder
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
                new Operator('!='),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereNotEquals2() : SelectBuilder
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
                new Operator('<>'),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereGreater() : SelectBuilder
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
                new Operator('>'),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereLess() : SelectBuilder
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
                new Operator('<'),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereLessInc() : SelectBuilder
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
                new Operator('<='),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereGreaterInc() : SelectBuilder
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
                new Operator('>='),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereIn() : SelectBuilder
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
                new Operator('IN'),
                new ArrayValue([2020, 2021])
            )
        );

        return $query;
    }

    public function testWhereNotIn() : SelectBuilder
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
                new Operator('NOT IN'),
                new ArrayValue([2020, 2021])
            )
        );

        return $query;
    }

    public function testWhereIsNull() : SelectBuilder
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
                new Operator('IS NULL'),
                null
            )
        );

        return $query;
    }

    public function testWhereIsNotNull() : SelectBuilder
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
                new Operator('IS NOT NULL'),
                null
            )
        );

        return $query;
    }

    public function testWhereBetween() : SelectBuilder
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
                new Operator('BETWEEN'),
                new ArrayValue([2017, 2020])
            )
        );

        return $query;
    }

    public function testWhereBetweenInclusive() : SelectBuilder
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
                new Operator('BETWEEN_INCLUSIVE'),
                new ArrayValue([2017, 2020])
            )
        );

        return $query;
    }

    public function testSingleGroupBy() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->groupBy(new Column('userId', $commentTable));

        return $query;
    }

    public function testAggregateFunctionWithoutGroupBy() : SelectBuilder
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

        return $query;
    }

    public function testAggregateFunctionWithGroupBy() : SelectBuilder
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

        return $query;
    }

    public function testSingleHaving() : SelectBuilder
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

        return $query;
    }

    public function testDualHaving() : SelectBuilder
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

        return $query;
    }

    public function testSingleOrderByColumnAsc() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);
        $query->orderBy(new Column('rok', $commentTable));

        return $query;
    }

    public function testSingleOrderByColumnDesc() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = new TableExpression($this->database, 'comments');

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);
        $query->orderBy(new Column('rok', $commentTable), 'DESC');

        return $query;
    }

    public function testSingleOrderByFunctionAsc() : SelectBuilder
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

        return $query;
    }

    public function testSingleOrderByAggregateFunctionAsc() : SelectBuilder
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

        return $query;
    }

    public function testLimit() : SelectBuilder
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

        return $query;
    }

    public function testOffset() : SelectBuilder
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

        return $query;
    }

    public function testLimitOffset() : SelectBuilder
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

        return $query;
    }

    public function testHavingEquals() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('='),
                new IntegerValue('6060')
            )
        );

        return $q;
    }

    public function testHavingLargerThan() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('>'),
                new IntegerValue('6060')
            )
        );

        return $q;
    }

    public function testHavingLargerThanEquals() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('>='),
                new IntegerValue('6060')
            )
        );

        return $q;
    }


    public function testHavingSmallerThan() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('<'),
                new IntegerValue('6060')
            )
        );

        return $q;
    }

    public function testHavingSmallerThanEquals() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('<='),
                new IntegerValue('6060')
            )
        );

        return $q;
    }

    public function testHavingNotEquals1() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('!='),
                new IntegerValue('6060')
            )
        );

        return $q;
    }

    public function testHavingNotEquals2() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('<>'),
                new IntegerValue('6060')
            )
        );

        return $q;
    }

    public function testHavingIn() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('IN'),
                new ArrayValue([6060, 8060, 2018])
            )
        );

        return $q;
    }

    public function testHavingNotIn() : SelectBuilder
    {
        $q = clone $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('NOT IN'),
                new ArrayValue([6060, 8060, 2018])
            )
        );

        return $q;
    }

    public function testHavingBetween() : SelectBuilder
    {
        $q = $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('BETWEEN'),
                new ArrayValue([6060, 8080])
            )
        );

        return $q;
    }

    public function testHavingBetweenInclusive() : SelectBuilder
    {
        $q = $this->testAggregateFunctionWithGroupBy();

        $q->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new Operator('BETWEEN_INCLUSIVE'),
                new ArrayValue([6060, 8080])
            )
        );

        return $q;
    }

}