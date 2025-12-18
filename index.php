<?php

use Netpromotion\Profiler\Profiler;
use Netpromotion\Profiler\Adapter\TracyBarAdapter;
use pql\Database;
use pql\QueryBuilder\Query;
use pql\QueryBuilder\Select\AggregateFunctions\Count;
use pql\QueryBuilder\Select\Column;
use pql\Table;
use Tracy\Debugger;
use Nette\Loaders\RobotLoader;

require __DIR__ . '/vendor/autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->setTempDirectory(__DIR__ . '/temp');
$loader->setAutoRefresh();
$loader->register();

Debugger::enable(Debugger::Development);
Debugger::$maxDepth = 2000;
Debugger::$logDirectory = __DIR__ . '/log';
Debugger::getBar()->addPanel(new TracyBarAdapter());
Profiler::enable();

echo '<meta charset="UTF-8">';

/*
$root = new \BTree\BtreeJ();
$root->create($root);

for ($i = 1; $i <= 1000; $i++) {
    $root = $root->insert($i);
}

$search = $root->search($root, 142);

bdump($root, '$root');
bdump($search, '$search');
*/

//$root->write(__DIR__ .'/data/test/index/test.index');

//$root = BtreeJ::read(__DIR__ .'/data/test/index/test.index');

//bdump($root);

/*
$root = new BTree(3);

$start = microtime(true);
for($i = 0; $i <= 20; $i++) {
    $root->insert($i);
}

bdump($root);

$end = microtime(true);

bdump($end-$start, 'adding');

$start = microtime(true);
$search = $root->searchN($root, 17);
$start = microtime(true);




bdump($search, 'search');
*/

//Database::create('test');

$database = new Database('test');


Table::create($database, 'test',
    [
        new \pql\TableColumn('id', 'int',false),
        new \pql\TableColumn('jmeno', 'string', true),
        new \pql\TableColumn('datum', 'string', false),
        new \pql\TableColumn('pocet', 'string', false)
    ]
);

/*
function hashJoin($table1, $table2, $condition) {
    // hash phase
    $h = [];

    /*
    $columns = array_keys($table2[0]);
    $columnToLeftJoin = [];

    foreach ($columns as $column) {
        $columnToLeftJoin[$column] = null;
    }

    unset($columnToLeftJoin[$index2]);
    */

/*
    foreach ($table2 as $s) {
        $h[$s[$condition[1]]][] = $s;
    }

    // Joins phase
    $result = [];

    foreach ($table1 as $r) {
        if (isset($h[$r[$condition[0]]])) {
            foreach ($h[$r[$condition[0]]] as $s) {
                $result[] = array_merge($s, $r);
            }
        } else {
            // $result[] = array_merge($r, $columnToLeftJoin);
        }
    }

    return $result;
}

$table1 = [
    ['clanek_id' => 1, 'author_id' => 1, 'text' => "foo1"],
    ['clanek_id' => 2, 'author_id' => 2, 'text' => "foo2"],
    ['clanek_id' => 3, 'author_id' => 3, 'text' => "foo3"],
    ['clanek_id' => 4, 'author_id' => 4, 'text' => "foo4"],
    ['clanek_id' => 5, 'author_id' => 5, 'text' => "foo5"],
    ['clanek_id' => 6, 'author_id' => 8, 'text' => "foo6"]
];

$table2 = [
    ['user_id' => 1, 'user_name' => "A", 'user_surname' => "Whales"],
    ['user_id' => 2, 'user_name' => "B", 'user_surname' => "Spiders"],
    ['user_id' => 3, 'user_name' => "C", 'user_surname' => "Ghosts"],
    ['user_id' => 4, 'user_name' => "D", 'user_surname' => "Zombies"],
    ['user_id' => 5, 'user_name' => "E", 'user_surname' => "Buffy"],
    ['user_id' => 8, 'user_name' => "E", 'user_surname' => "foo6d"]
];

    $cond = [
        ['author_id', 'user_id'],
        ['text', 'user_surname']
    ];

$result = $table1;


foreach ($cond as $condItem) {
    $result = hashJoin($result, $table2, $condItem);

    //bdump($result, '$result');
}


//bdump($joined, '$joined');

foreach ($result as $row) {
    bdump($row, '$row');

}

*/

$database = new Database('test');

//\pql\Table::create($database, 'test');

//$myNew = $database->getTable('test');

//bdump($database);
//bdump($tables);
//bdump($myNew);
//bdump($myNew->getRows());

/*
Profiler::start('adding');
for ($i = 0; $i < 10000; $i++) {
    $insertQuery = new Query($database);
    $res = $insertQuery->add('test', ['id' => $i, 'a' => chr($i)])->run();
    $insertQuery = null;
}
Profiler::finish('adding');
*/

/*
Profiler::start('delete');
$deleteQuery = new Query($database);
$deleteQuery->delete('test')
    ->where('a', '=', "44")
    ->run();
Profiler::finish('delete');
*/


/*
$query = new Query($database);
$query->update('test', ['a' => '888'])->where('id', '=', '96')->run();
*/




Profiler::start('select');

$query2 = new Query($database);

$count = new Count( 'pocet');
$acount = new Column( 'a.pocet');

//$query2->select()->select(new \pql\QueryBuilder\PFunction(NumberFormat::FUNCTION_NAME, 'pocet', [5 , ',' , $thousands_sep = '_']))
$query2->select()->select(null, new Column('datum'))
    ->from(new \pql\QueryBuilder\From\TableFromExpression('test'))
    //->innerJoin(new \pql\QueryBuilder\From\Table('test'), [new \pql\Condition(new Column('a.pocet') ,'=', 'b.pocet')], 'b')
    //->where('pocet', '>', 2)
    //->where('pocet', '<', 5)
    ->orderBy(new Column('pocet'), true);
    //->where(new Column('pocet'), '=', 7.0)
    //->groupBy('datum');
//->having($count, '=', 5);

$querySelect = new \pql\QueryBuilder\From\QueryFromExpression($query2);
$equalsOperator = new \pql\QueryBuilder\Operator\Equals();


$q1 = new Query($database);
$q1->select()
    ->select(null, new Column('id'))
    ->select(null, new Column('datum'))
    ->select(null, new Column('pocet'))
    ->from(new \pql\QueryBuilder\From\TableFromExpression('test'))
    ->where(new Column('pocet'), new \pql\QueryBuilder\Operator\Equals(), new \pql\QueryBuilder\Select\ValueExpression(45.0));

$query3 = $q1   ;

/*$querySelect2 = new \pql\QueryBuilder\From\Query($query3);*/


/*$query4 = new Query($database);
$query4->select()->select(null, new Column('datum'))
    ->from($querySelect2, 'a')
    ->crossJoin($querySelect2)
    ->leftJoin($querySelect2, [new \pql\Condition($acount, $equalsOperator, new Column('f.pocet'))], 'f')
    ->rightJoin($querySelect2, [new \pql\Condition($acount, $equalsOperator, new Column('g.pocet'))], 'g')
    ->innerJoin($querySelect2, [new \pql\Condition($acount, $equalsOperator, new Column('h.pocet'))], 'h')
    ->fullJoin($querySelect2, [new \pql\Condition($acount, $equalsOperator, new Column('ch.pocet'))], 'ch')
    ->orderBy(new Column('a.pocet'))
    ->groupBy(new Column('a.pocet'));*/

//
//bdump($query2);
//
//$query1 = new Query($database);
//$query1->select()->select(['a.pocet', 'b.pocet'])
//    ->from('test', 'a')
//    ->innerJoin('test', [new \pql\Condition('a.pocet', '=', 'b.pocet')], 'b')
//    ->where('a.pocet', '>', '2')
//    ->where('b.pocet', '<', '5')
//    ->orderBy('a.pocet', true)
//    ->unionAll($query2);
//
//$query3 = new Query($database);
//$query3->select()->select(['a.pocet', 'b.pocet'])
//->from($query1)
//    ->crossJoin($query1, 'b')
//    ->leftJoin($query1, [new \pql\Condition('a.pocet', '=', 'b.pocet')], 'b')
//    ->rightJoin($query1, [new \pql\Condition('a.pocet', '=', 'b.pocet')], 'b')
//    ->innerJoin($query1, [new \pql\Condition('a.pocet', '=', 'b.pocet')], 'b')
//    ->fullJoin($query1, [new \pql\Condition('a.pocet', '=', 'b.pocet')], 'b')
//->orderBy('a.pocet')
//->groupBy('a.pocet');

$plus = new \pql\QueryBuilder\Select\Minus(new \pql\QueryBuilder\Select\ValueExpression(1), new \pql\QueryBuilder\Select\ValueExpression(15));

$ex = new \pql\QueryBuilder\Select\Expression(new \pql\QueryBuilder\Select\Plus($plus));

$q2 = new Query($database);
$q2->select()->select(null, $ex);
$res2 = $q2->run();

bdump($q2);
echo $q2 .'<br><br>';
echo $res2 .'<br><br>';



$res3 = $query3->run();
echo $query3;

echo $res3;
// bdump($query3);

Profiler::finish('select');

$insertUqery = new Query($database);
$insertUqery->insert()->insert('test', ['id' => 1, 'datum' => '45.5.2015', 'pocet' => 45])->run();


/*
$j = new \BTree\BtreeJ();
//$j->create($j);

for ($i = 1; $i <= 10; $i++) {
    $j->insert($i);
}

bdump($j, '$j');
*/

/*
$query = new Query($database);
$res = $query->add('test', ['id' => 55, 'text' => 'zjfjewdwawdadawdwaz'])->run();
bdump($res);
echo $res;
*/

/*
$query = new Query($database);
$res = $query->select(['id', 'text'])
    ->count('text')
    ->from('test')
    ->orderBy('id', false)
    ->orderBy('text')
    ->groupBy('text')
    ->where('id','>', 5)
    //->having('count', '=', 2)
    ->run();
echo $res;

$query = new Query($database);
$res = $query->select(['id', 'text'])
    ->count('text')
    ->from('test')
    ->where('id', 'IN', [1,3])
    ->run();
echo $res;
*/


/*
Profiler::start('add');
$myNew->addColumn('testfd', TableColumn::STRING);
Profiler::finish('add');
*/

//$query->update('myNew', ['prijmeni' => 'bbbb'])->where('jmeno', '=', 'a')->run();


/*$tree = new \pql\BTree\BtreePlus();

Profiler::start('create tree');
for ($i = 0; $i <= 50; $i++) {
    bdump($i, '$i');

    $tree->insert($i);

    bdump($tree->search($i), 'FIRST SEARCH $i');
    $tree->delete($i);
//    bdump($tree->delete($i), 'DELETE $i');
    bdump($tree->search($i), 'SEARCH AFTER DELETE $i');

    $tree->insert($i);

    bdump($tree->search($i), 'SEARCH AFTER INSERT $i');
}*/
