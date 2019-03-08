<?php
use Tracy\Debugger;
use Nette\Loaders\RobotLoader;
use BTree\BtreeJ;

require __DIR__ . '/vendor/autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->setTempDirectory(__DIR__ . '/temp');
$loader->setAutoRefresh();
$loader->register();

Debugger::enable();
Debugger::$maxDepth = 2000;
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

$database = new Database('test');

$query1 = new Query($database);
$res1   = $query1->select(['id', 'text'])
    ->max('id')
    ->min('id')
    ->avg('id')
    ->sum('id')
    ->count('id')
    ->median('id')
    ->from('test')
    ->run();
echo $res1;

/*
$query2 = new Query($database);
$res2   = $query2->select(['id', 'text'])->from('test')->where('id', '<>', $query1)->run();
echo $res2;
*/

//Table::create($database, 'test', ['id', 'jmeno']);

/*

$database = new Database('test');

$myNew = $database->getTable('myNew');

//bdump($database);
//bdump($tables);
bdump($myNew);
//bdump($myNew->getRows());

$query = new Query($database);
$res = $query->select(['jmeno', 'prijmeni'])->from('myNew')->run();
bdump($res);
echo $res;

$query = new Query($database);
$res = $query->add('myNew', ['jmeno' => 'ffawdwawdwaa', 'prijmeni' => 'zjfjewdwawdadawdwaz'])->run();
bdump($res);
echo $res;

$query = new Query($database);
$res = $query->select(['jmeno', 'prijmeni'])->from('myNew')->run();
bdump($res);
echo $res;
//$query->update('myNew', ['prijmeni' => 'bbbb'])->where('jmeno', '=', 'a')->run();

