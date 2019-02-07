<?php
use Tracy\Debugger;
use Nette\Loaders\RobotLoader;

require __DIR__ . '/vendor/autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->setTempDirectory(__DIR__ . '/temp');
$loader->setAutoRefresh(true);
$loader->register();

Debugger::enable();
Debugger::$maxDepth = 5;

echo '<meta charset="UTF-8">';

$r = new BTree(2);
$r = $r->create(2);
//$r->leaf = false;

$r->insert(5);
$r->insert(9);
$r->insert(3);
$r->insert(7);
$r->insert(1);
$r->insert(2);
$r->insert(8);
$r->insert(6);
$r->insert(0);
$r->insert(4);

for ($i = 15; $i < 1000; $i++) {
    $r->insert($i);
}

//bdump($r->search($r), 'seaarch');

//$r->traverse($r);


bdump($r, '$r index');


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