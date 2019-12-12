<?php

use Netpromotion\Profiler\Profiler;
use Netpromotion\Profiler\Adapter\TracyBarAdapter;
use Tracy\Debugger;
use Nette\Loaders\RobotLoader;

require __DIR__ . '/vendor/autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->setTempDirectory(__DIR__ . '/temp');
$loader->setAutoRefresh();
$loader->register();

Debugger::enable();
Debugger::$maxDepth = 2000;
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


//Table::create($database, 'test', ['id', 'jmeno']);

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

    // join phase
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

/*


Profiler::start('select');
$query = new Query($database);

$query->select(['article_id', 'article_text', 'user_id', 'user_name', 'comment_text'])
    ->from('articles')
    ->innerJoin(
        'users',
        [
            new Condition('article_user_id', '=', 'user_id'),
            //new Condition('article_text', '=', 'user_name'),
        ]
    )
    ->leftJoin('comments', [new Condition('article_id', '=', 'comment_article_id')]);
    //->where(new Condition('user_id', '!=', 2));
    //->where(new Condition('article_id', '!=', 2));

echo $query;

echo $query->run();


$query = new Query($database);
$query->update('articles', ['article_text' => 'a'])
    ->where(new Condition('article_id', '=', 1))
    ->run();



$query = new Query($database);

$query->select(['article_id', 'article_text', 'user_id', 'user_name', 'comment_text'])
    ->from('articles')
    ->innerJoin(
        'users',
        [
            new Condition('article_user_id', '=', 'user_id'),
            //new Condition('article_text', '=', 'user_name'),
        ]
    )
    ->leftJoin('comments', [new Condition('article_id', '=', 'comment_article_id')]);
//->where(new Condition('user_id', '!=', 2));
//->where(new Condition('article_id', '!=', 2));

echo $query;

echo $query->run();
*/

//$subRes = $query->select(['pocet'])
    //->sum('pocet')
    //->from('test')
    //->where('pocet', 'in', [1, 3, 5]);
    //->where([1, 3, 5], 'in', 'pocet');
    //->where([1, 3], 'between_in', 'pocet');
    //->where('pocet', 'between_in', [1, 3]);

//echo $subRes;

/*
$res = $query->select(['id', 'datum', 'pocet'])
    //->sum('pocet')
    ->from('test')
    ->where('pocet', 'in', $subRes)
    //->where('pocet', 'in', $subRes)
    ->groupBy('datum')
    ->orderBy('pocet')
    ->limit(5);

echo $res;
    $res = $res->run();

echo $res;
$res = null;
*/
//Profiler::finish('select');


$table = [
    ['article_id' => 1, 'article_text' => 'test1',  'article_date' => '15-15-15', 'article_user_id' => 1],
    ['article_id' => 2, 'article_text' => 'test2', 'article_date' => '14-14-14', 'article_user_id' => 2],
    ['article_id' => 3, 'article_text' => 'test3', 'article_date' => '13-13-13', 'article_user_id' => 2],
    ['article_id' => 4, 'article_text' => 'test4', 'article_date' => '12-12-12', 'article_user_id' => 1],
    ['article_id' => 4, 'article_text' => 'test5','article_date' => '11-11-11', 'article_user_id' => 12],
    ['article_id' => 4, 'article_text' => 'test6','article_date' => '10-10-10', 'article_user_id' => 4],
    ['article_id' => 4, 'article_text' => 'test7','article_date' => '9-9-9', 'article_user_id' => 4],
];

$tableA = [
    ['user_id' => 1, 'user_name' => 'a', ],
    ['user_id' => 2, 'user_name' => 'b', ],
    ['user_id' => 3, 'user_name' => 'c', ],
    ['user_id' => 4, 'user_name' => 'd', ],
    ['user_id' => 4, 'user_name' => 'e', ],
];

$condition = new Condition('article_user_id', '=', 'user_id');

//bdump(\query\Join\NestedLoopJoin::fullJoin($table, $tableA, $condition), 'FULL NLJ');
//bdump(\query\Join\HashJoin::fullJoin($table, $tableA, $condition), 'FULL HASH');

bdump(\query\Join\NestedLoopJoin::innerJoin($table, $tableA, $condition), 'LEFT NLJ');
bdump(\query\Join\SortMergeJoin::innerJoin($table, $tableA, $condition), 'LEFT SMJ');
//dump(\query\Join\HashJoin::rightJoin($table, $tableA, $condition), 'RIGHT HASH');
//dump(\query\Join\SortMergeJoin::leftJoin($table, $tableA, $condition), 'LEFT MERGE');
//dump(\query\Join\SortMergeJoin::rightJoin($table, $tableA, $condition), 'RIGHT MERGE');


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
$myNew->addColumn('testfd', Column::STRING);
Profiler::finish('add');
*/



//$query->update('myNew', ['prijmeni' => 'bbbb'])->where('jmeno', '=', 'a')->run();