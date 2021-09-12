<?php

use Netpromotion\Profiler\Adapter\TracyBarAdapter;
use Netpromotion\Profiler\Profiler;
use Nette\Loaders\RobotLoader;
use PQL\Query\Builder\Expressions\Column;
use PQL\Query\Builder\Expressions\AggregateFunctionExpression;
use PQL\Query\Builder\Expressions\FunctionExpression;
use PQL\Query\Builder\Expressions\HavingCondition;
use PQL\Query\Builder\Expressions\IntegerValue;
use PQL\Query\Builder\Expressions\JoinConditionExpression;
use PQL\Query\Builder\Expressions\Minus;
use PQL\Query\Builder\Expressions\Operator;
use PQL\Query\Builder\Expressions\Plus;
use PQL\Query\Builder\Expressions\QueryExpression;
use PQL\Query\Builder\Expressions\StringValue;
use PQL\Query\Builder\Expressions\TableExpression;
use PQL\Query\Builder\Expressions\WhereCondition;
use PQL\Server;
use Tracy\Debugger;

$sep = DIRECTORY_SEPARATOR;

require __DIR__ . $sep . 'vendor' . $sep . 'autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->addDirectory(__DIR__ . $sep . 'temp' . $sep . 'Entity');
$loader->setTempDirectory(__DIR__ . $sep . 'temp');
$loader->setAutoRefresh();
$loader->register();

Debugger::enable();
Debugger::$maxDepth = 2000;
Debugger::getBar()->addPanel(new TracyBarAdapter());
Profiler::enable();

echo '<meta charset="UTF-8">';

$server = new Server();
//dump($server);


$database = $server->getDatabase('test');

$commentTable = new TableExpression($database, 'comments', 'ic');
$userTable = new TableExpression($database, 'User', 'u');

$fromQuery = $database->selectQuery();
$fromQuery->select(new Column('id', $commentTable));
$fromQuery->select(new Column('text', $commentTable));
$fromQuery->select(new Column('rok', $commentTable));
$fromQuery->select(new Column('userId', $commentTable));
$fromQuery->from($commentTable);

$query = $database->selectQuery();

$query->select(new Column('id', $commentTable));
/*$query->select(new Column('id', $userTable));
$query->select(new Column('username', $userTable));*/
$query->select(new Column('text', $commentTable));
$query->select(new Column('rok', $commentTable));
$query->select(new AggregateFunctionExpression('sum', [new Column('rok', $commentTable)]));
$query->select(new FunctionExpression('strtolower', [new Column('text', $commentTable)]));
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

$query->groupBy(new Column('rok', $commentTable));
//$query->groupBy(new Column('id', $userTable));
//$query->groupBy(new Column('text', $commentTable));

//$query->where(new WhereCondition(new Column('username', $userTable), new Operator('IS NOT NULL'), null));
//$query->where(new WhereCondition(new Column('username', $userTable), new Operator('='), new StringValue('xpy')));
//$query->where(new WhereCondition(new Column('rok', $commentTable), new Operator('='), new \PQL\Query\Builder\Expressions\IntegerValue('2015')));

//$query->orderBy(new Column('username', $userTable), 'ASC');
//$query->limit(10);
//$query->offset(35);

//$query->having(new HavingCondition(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]), new Operator('='), new IntegerValue(2)));
$query->having(new HavingCondition(new AggregateFunctionExpression('count', [new Column('rok', $commentTable)]), new Operator('>='), new IntegerValue(1)));

dump($query);

echo $query->printQuery();
$query->execute();
echo '<br><br>';
echo $query->printResult();