<?php
use Tracy\Debugger;
use Nette\Loaders\RobotLoader;
use BTree\BTree;
use BTree\Node;

require __DIR__ . '/vendor/autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->setTempDirectory(__DIR__ . '/temp');
$loader->setAutoRefresh(true);
$loader->register();

Debugger::enable();
Debugger::$maxDepth = 5;

echo '<meta charset="UTF-8">';


$root = new \BTree(2);
$root->insert(555);
$root->insert(5554);
$root->insert(555555);
$root->insert(5555);
$root->insert(55555);
$root->insert(5555);
$root->insert(2);
$root->insert(8885);
$root->insert(88888);
$root->insert(88854879);


bdump($root);


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

$query->add('myNew', ['jmeno' => 'a', 'prijmeni' => 'zzz'])->run();