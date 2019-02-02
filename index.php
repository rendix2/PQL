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
$res = $query->select(['jmeno', 'prijmeni'])->from('myNew')->orderBy('jmeno', true)->groupBy('jmeno')->where('jmeno', '=', 1)->limit(8)->run();

bdump($res);
echo $res;