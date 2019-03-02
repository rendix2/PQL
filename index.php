<?php
use Tracy\Debugger;
use Nette\Loaders\RobotLoader;
use BTree\BTree;
use BTree\Node;

require __DIR__ . '/vendor/autoload.php';

$loader = new RobotLoader();
$loader->addDirectory(__DIR__);
$loader->setTempDirectory(__DIR__ . '/temp');
$loader->setAutoRefresh();
$loader->register();

Debugger::enable();
Debugger::$maxDepth = 2000;
echo '<meta charset="UTF-8">';


/**
$root = new BTree();


for ($i = 1; $i <= 8; $i++) {
    $root = $root->insert($i);
    //bdump($root,'root after insert');
}

*/

$tree = new BtreeG();

$root = $tree->create($tree);
//$root->root = $tree;

bdump($tree, 'tree');

$lastRoot = null;


for ($i = 1; $i <= 98; $i++) {
    $tree->insert($tree, $i);
    //bdump($root,'root after insert');
}


/*
$node->insert($root, 1);
$node->insert($root, 2);
$node->insert($root, 3);
$node->insert($root, 4);
$node->insert($root, 5);
$node->insert($root, 6);
$node->insert($root, 7);
$node->insert($root, 8);
$node->insert($root, 9);
$node->insert($root, 10);
$node->insert($root, 11);
$node->insert($root, 12);
$node->insert($root, 13);
$node->insert($root, 14);
$node->insert($root, 15);
$node->insert($root, 16);
$node->insert($root, 17);
$node->insert($root, 18);
$node->insert($root, 19);
*/



//bdump($node, '$node');
bdump($root, '$root');
//$root->insert($root, 5);
//$root->insert($node, 5);


//;bdump($root);
//bdump($node);

/*
$start = microtime(true);
for($i = 0; $i <= 20; $i++) {
    $root->insert($i);
}

bdump($root);

$end = microtime(true);



bdump($end-$start, 'adding');
*/

/*
$start = microtime(true);
$search = $root->searchN($root, 17);
$start = microtime(true);




bdump($search, 'search');
*/



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
//$query->update('myNew', ['prijmeni' => 'bbbb'])->where('jmeno', '=', 'a')->run();
