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

use PQL\Database\Database;
use PQL\Database\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Database\Query\Builder\Expressions\ArrayValue;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\Expressions\FunctionExpression;
use PQL\Database\Query\Builder\Expressions\HavingCondition;
use PQL\Database\Query\Builder\Expressions\IntArrayValue;
use PQL\Database\Query\Builder\Expressions\IntegerValue;
use PQL\Database\Query\Builder\Expressions\JoinCondition;
use PQL\Database\Query\Builder\Expressions\Minus;
use PQL\Database\Query\Builder\Expressions\NullValue;
use PQL\Database\Query\Builder\Expressions\Power;
use PQL\Database\Query\Builder\Expressions\WhereOperator;
use PQL\Database\Query\Builder\Expressions\Plus;
use PQL\Database\Query\Builder\Expressions\TableExpression;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Query\Builder\JoinOperator;
use PQL\Database\Query\Builder\OrderByExpression;
use PQL\Database\Query\Builder\SelectBuilder as SelectBuilder;
use PQL\Database\Server;

/**
 * Class SelectTestQueryFactory
 *
 * @package PQL\Tests
 */
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

    /**
     * @return SelectBuilder
     */
    public function testColumnsFrom() : SelectBuilder
    {
        $query = clone $this->query;

        $query->select(new Column('id', $this->commentsTable));
        $query->select(new Column('text', $this->commentsTable));
        $query->select(new Column('rok', $this->commentsTable));
        $query->select(new Column('userId', $this->commentsTable));

        $query->from(clone $this->commentsTable);

        return $query;
    }

    public function testDistinctColumn() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

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
        $query = clone $this->testColumnsFrom();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $userTable));
        $query->select(new Column('username', $userTable));

        $query->innerJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        return $query;
    }

    public function testCrossJoin() : SelectBuilder
    {
        $query = clone $this->testColumnsFrom();
        $userTable = $this->usersTable;

        $query->select(new Column('id', $userTable));
        $query->select(new Column('username', $userTable));

        $query->crossJoin($userTable);

        return $query;
    }

    private function createLeftJoin() : SelectBuilder
    {
        $query = clone $this->testColumnsFrom();
        $userTable = $this->usersTable;

        $query->select(new Column('id', $userTable));
        $query->select(new Column('username', $userTable));

        return $query;
    }

    public function testLeftJoinTableOnCondition() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionGreater() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        $query->where(
            new WhereCondition(
                new Column('id', $userTable),
                new WhereOperator('>'),
                new IntegerValue(2)
            ),
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionGreaterEquals() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
                new WhereCondition(
                    new Column('id', $userTable),
                    new WhereOperator('>='),
                    new IntegerValue(15)
                ),
            ],
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionSmaller() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        $query->where(
            new WhereCondition(
                new Column('id', $userTable),
                new WhereOperator('<'),
                new IntegerValue(15)
            ),
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionSmallerEquals() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        $query->where(
            new WhereCondition(
                new Column('id', $userTable),
                new WhereOperator('<='),
                new IntegerValue(15)
            ),
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionNotEquals() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        $query->where(
            new WhereCondition(
                new Column('id', $userTable),
                new WhereOperator('!='),
                new IntegerValue(15)
            )
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionNotEquals2() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ]
        );
        $query->where(
            new WhereCondition(
                new Column('id', $userTable),
                new WhereOperator('<>'),
                new IntegerValue(15)
            )
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionInArray() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        $query->where(
            new WhereCondition(
                new Column('id', $userTable),
                new WhereOperator('IN'),
                new IntArrayValue([1, 10])
            ),
        );

        return $query;
    }

    public function testLeftJoinTableOnConditionNotInArray() : SelectBuilder
    {
        $query = clone $this->createLeftJoin();

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->leftJoin(
            $userTable, [
                new JoinCondition(
                    new Column('id', $userTable),
                    new JoinOperator('='),
                    new Column('userId', $commentTable)
                ),
            ],
        );

        $query->where(
            new WhereCondition(
                new Column('id', $userTable),
                new WhereOperator('NOT IN'),
                new IntArrayValue([1, 10])
            ),
        );

        return $query;
    }

    public function testSingleArgumentFunction() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('='),
                new IntegerValue('2015')
            )
        );

        return $query;
    }

    public function testWhereDualCondition() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('='),
                new IntegerValue('2015')
            )
        );

        $query->where(
            new WhereCondition(
                new Column('userId', $commentTable),
                new WhereOperator('='),
                new IntegerValue('1')
            )
        );

        return $query;
    }

    public function testWhereEquals() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('='),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereNotEquals1() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('!='),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereNotEquals2() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('<>'),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereGreater() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('>'),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereLess() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;
        $userTable = $this->usersTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('<'),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereLessInc() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('<='),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereGreaterInc() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('>='),
                new IntegerValue(2020)
            )
        );

        return $query;
    }

    public function testWhereIn() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('IN'),
                new IntArrayValue([2020, 2021])
            )
        );

        return $query;
    }

    public function testWhereNotIn() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('NOT IN'),
                new IntArrayValue([2020, 2021])
            )
        );

        return $query;
    }

    public function testWhereIsNull() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('IS NULL'),
                new NullValue()
            )
        );

        return $query;
    }

    public function testWhereIsNotNull() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('IS NOT NULL'),
                new NullValue()
            )
        );

        return $query;
    }

    public function testWhereBetween() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('BETWEEN'),
                new IntArrayValue([2017, 2020])
            )
        );

        return $query;
    }

    public function testWhereBetweenInclusive() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

        $query->select(new Column('id', $commentTable));
        $query->select(new Column('text', $commentTable));
        $query->select(new Column('rok', $commentTable));
        $query->select(new Column('userId', $commentTable));

        $query->from($commentTable);

        $query->where(
            new WhereCondition(
                new Column('rok', $commentTable),
                new WhereOperator('BETWEEN_INCLUSIVE'),
                new IntArrayValue([2017, 2020])
            )
        );

        return $query;
    }

    public function testSingleGroupBy() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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
                new WhereOperator('='),
                new IntegerValue(3)
            )
        );

        return $query;
    }

    public function testDualHaving() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

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
                new WhereOperator('='),
                new IntegerValue(3)
            )
        );
        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]),
                new WhereOperator('='),
                new IntegerValue(6060)
            )
        );

        return $query;
    }

    public function testSingleOrderByColumnAsc() : SelectBuilder
    {
        $query = clone $this->query;

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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

        $commentTable = $this->commentsTable;

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
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('='),
                new IntegerValue('6060')
            )
        );

        return $query;
    }

    public function testHavingLargerThan() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('>'),
                new IntegerValue('6060')
            )
        );

        return $query;
    }

    public function testHavingLargerThanEquals() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('>='),
                new IntegerValue('6060')
            )
        );

        return $query;
    }

    public function testHavingSmallerThan() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('<'),
                new IntegerValue('6060')
            )
        );

        return $query;
    }

    public function testHavingSmallerThanEquals() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('<='),
                new IntegerValue('6060')
            )
        );

        return $query;
    }

    public function testHavingNotEquals1() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('!='),
                new IntegerValue('6060')
            )
        );

        return $query;
    }

    public function testHavingNotEquals2() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('<>'),
                new IntegerValue('6060')
            )
        );

        return $query;
    }

    public function testHavingIn() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('IN'),
                new IntArrayValue([6060, 8060, 2018])
            )
        );

        return $query;
    }

    public function testHavingNotIn() : SelectBuilder
    {
        $query = clone $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('NOT IN'),
                new IntArrayValue([6060, 8060, 2018])
            )
        );

        return $query;
    }

    public function testHavingBetween() : SelectBuilder
    {
        $query = $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('BETWEEN'),
                new IntArrayValue([6060, 8080])
            )
        );

        return $query;
    }

    public function testHavingBetweenInclusive() : SelectBuilder
    {
        $query = $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new AggregateFunctionExpression(
                    'sum',
                    [new Column('rok', $this->commentsTable)]
                ),
                new WhereOperator('BETWEEN_INCLUSIVE'),
                new IntArrayValue([6060, 8080])
            )
        );

        return $query;
    }

    public function testHavingMathPlus() : SelectBuilder
    {
        $query = $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new Plus(
                    new IntegerValue(5.0),
                    new AggregateFunctionExpression(
                        'sum',
                        [new Column('rok', $this->commentsTable)]
                    )
                ),
                new WhereOperator('IN'),
                new IntArrayValue([6065, 8065])
            )
        );

        return $query;
    }

    public function testHavingMathPlus2() : SelectBuilder
    {
        $query = $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new Plus(
                    new AggregateFunctionExpression(
                        'sum',
                        [new Column('rok', $this->commentsTable)]
                    ),
                    new IntegerValue(5)
                ),
                new WhereOperator('IN'),
                new IntArrayValue([6065, 8065])
            )
        );

        return $query;
    }

    public function testHavingMathMinus() : SelectBuilder
    {
        $query = $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new Minus(
                    new IntegerValue(5.0),
                    new AggregateFunctionExpression(
                        'sum',
                        [new Column('rok', $this->commentsTable)]
                    )
                ),
                new WhereOperator('IN'),
                new IntArrayValue([-6055, -8055])
            )
        );

        return $query;
    }

    public function testHavingMathMinus2() : SelectBuilder
    {
        $query = $this->testAggregateFunctionWithGroupBy();

        $query->having(
            new HavingCondition(
                new Minus(
                    new AggregateFunctionExpression(
                        'sum',
                        [new Column('rok', $this->commentsTable)]
                    ),
                    new IntegerValue(5)
                ),
                new WhereOperator('IN'),
                new IntArrayValue([6055, 8055])
            )
        );

        return $query;
    }

    public function testHavingMathPower() : SelectBuilder
    {
        $query = $this->testAggregateFunctionWithGroupBy();

        $query->select(
            new Power(
                new AggregateFunctionExpression(
                    'count',
                    [new Column('rok', $this->commentsTable)]
                ),
                new IntegerValue(2)
            )
        );

        $query->having(
            new HavingCondition(
                new Power(
                    new AggregateFunctionExpression(
                        'sum',
                        [new Column('rok', $this->commentsTable)]
                    ),
                    new IntegerValue(2)
                ),
                new WhereOperator('IN'),
                new IntArrayValue([64963600, 8055])
            )
        );

        return $query;
    }
}
