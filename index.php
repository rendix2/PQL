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


$database = new Database('test');

$myNew = $database->getTable('test');

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
$query = new Query($database);

$res = $query->select(['id', 'a'])
    ->from('test')
    ->where('id', '>', "1")
    ->where('id', '<', "4")
    ->limit(50)
    ->run();

echo $res;
$res = null;
Profiler::finish('select');


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