<?php

use Netpromotion\Profiler\Adapter\TracyBarAdapter;
use Netpromotion\Profiler\Profiler;
use Nette\Loaders\RobotLoader;
use PQL\Bootstrap;
use PQL\Database\Index\BtreePlus;
use PQL\Database\Query\Builder\Expressions\Column;
use PQL\Database\Query\Builder\Expressions\WhereCondition;
use PQL\Database\Server;
use PQL\TestDataCreator;
use PQL\TestRunner;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionGreaterEquals;
use PQL\Tests\InputData\TestLeftJoinTableOnConditionNotInArray;
use Tracy\Debugger;

require_once 'Bootstrap.php';
$bootstrap = new Bootstrap();
$bootstrap->app();

echo '<meta charset="UTF-8">';


$testDataCreator = new TestDataCreator();
$testDataCreator->run();

$testRunner = new TestRunner();
$testRunner->run();

/*$factory = new \PQL\Tests\SelectTestQueryFactory();
$q = $factory->testInnerJoinTableOnCondition();*/




/*$q = $factory->testInnerJoinTableOnCondition();

$testRunner->print($q);*/






/*$server = new Server();
$testDatabase = $server->getDatabase('test');


$updateQuery = $testDatabase->deleteQuery();

$commentsTable =     new \PQL\Database\Query\Builder\Expressions\TableExpression(
    $testDatabase,
    'comments'
);

$updateQuery->from($commentsTable);*/


/*$updateQuery->set(
    new \PQL\Database\Query\Builder\Expressions\Set(
        new Column(
            'rok',
            $commentsTable
        ),
        new \PQL\Database\Query\Builder\Expressions\IntegerValue(2050)
    )
);

$updateQuery->where(new WhereCondition(
    new Column(
        'rok',
        $commentsTable
    ),
    new \PQL\Database\Query\Builder\Expressions\Operator( '='),
    new \PQL\Database\Query\Builder\Expressions\IntegerValue(2015)

));

echo $updateQuery->print();
$updateQuery->execute();
*/

/*$row1 = new stdClass();
$row1->id = 1;
$row1->name = 'tom';

$row2 = new stdClass();
$row2->id = 2;
$row2->name = 'tom2';

$tree = new BtreePlus();
$tree->insert($row1);
$tree->insert($row2);

bdump($tree);

$tree->delete($row2);

bdump($tree);*/



/*
$fromQuery = $database->selectQuery();
$fromQuery->select(new Column('id', $commentTable));
$fromQuery->select(new Column('text', $commentTable));
$fromQuery->select(new Column('rok', $commentTable));
$fromQuery->select(new Column('userId', $commentTable));
$fromQuery->from($commentTable);

$query = $database->selectQuery();

$query->distinct(new Column('rok', $commentTable));

//$query->select(new Column('id', $commentTable));
///*$query->select(new Column('id', $userTable));
//$query->select(new Column('username', $userTable));*/
//$query->select(new Column('text', $commentTable));
//$query->select(new Column('rok', $commentTable));
//$query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));
//$query->select(new FunctionExpression('strtolower', [new Column('text', $commentTable)]));
/*$query->select(
    new Plus(
        new IntegerValue(1),
        new Plus(
            new IntegerValue(2),
            new Minus(
                new IntegerValue(3),
                new IntegerValue(4),
            )
        ),
        'k'
    )
);*/
/*
$query->from($commentTable);
/*$query->leftJoin(
    $userTable, [
        new JoinConditionExpression(
            new Column('id', $userTable),
            new Operator('='),
            new Column('userId', $commentTable)
        ),
        new WhereCondition(
            new Column('username', $userTable),
            new Operator('='),
            new StringValue('xpy')
        ),
    ],
);*/
/*
$query->groupBy(new Column('rok', $commentTable));
//$query->groupBy(new Column('id', $userTable));
//$query->groupBy(new Column('text', $commentTable));

//$query->where(new WhereCondition(new Column('username', $userTable), new Operator('IS NOT NULL'), null));
//$query->where(new WhereCondition(new Column('username', $userTable), new Operator('='), new StringValue('xpy')));
//$query->where(new WhereCondition(new Column('rok', $commentTable), new Operator('='), new \PQL\Database\Query\Builder\Expressions\IntegerValue('2015')));

//$query->orderBy(new Column('username', $userTable), 'ASC');
//$query->limit(10);
//$query->offset(35);

//$query->having(new HavingCondition(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]), new Operator('='), new IntegerValue(2)));
//$query->having(new HavingCondition(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]), new Operator('>='), new IntegerValue(1)));

dump($query);

$intersectQuery = clone $query;
$intersectQuery->where(new WhereCondition(new Column('rok', $commentTable), new Operator('='), new IntegerValue(2020)));

//$query->except($intersectQuery);
/*$query->where(new WhereCondition(
    new FunctionExpression('strtolower', [new Column('text', $commentTable)]),
    new Operator('='),
    new StringValue('wda2021')
));*/

/*echo $query->printQuery();
$query->execute();
echo '<br><br>';
echo $query->printResult();*/


/*$intersectQuery->execute();
echo $intersectQuery->printResult();*/