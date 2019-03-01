<?php
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
echo '<meta charset="UTF-8">';

$root = new \BTree\BtreeJ();

for ($i = 1; $i < 1000; $i++) {
    $root->insert($i);
}

bdump(\BTree\BtreeJ::$cache);

bdump($root, '$root');

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

/*
$database = new Database('test');
$tables = $database->getTables();

//Table::create($database, 'myNewsss', ['id', 'ch']);
//$res = Table::delete($database, 'zz');

$myNew = $tables['myNew'];

//bdump($database);
//bdump($tables);
//bdump($myNew);
//bdump($myNew->getRows());

$query = new Query($database);
//$res = $query->select(['jmeno', 'prijmeni', 'id','name'])->from('myNew')->leftJoin('myNews')->on('jmeno', '=', 'name')->limit(5)->run();
//bdump($res);
//echo $res;

//$query->add('myNew', ['jmeno' => 'a', 'prijmeni' => 'zzz'])->run();
$query->update('myNew', ['prijmeni' => 'bbbb'])->where('jmeno', '=', 'a')->run();
*/
