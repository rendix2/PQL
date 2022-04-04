<?php
/**
 *
 * Created by PhpStorm.
 * Filename: Bootstrap.php
 * User: Tomáš Babický
 * Date: 17.09.2021
 * Time: 21:54
 */

namespace PQL;


use Netpromotion\Profiler\Adapter\TracyBarAdapter;
use Netpromotion\Profiler\Profiler;
use Nette\Loaders\RobotLoader;
use Tracy\Debugger;

class Bootstrap
{

    private bool $isLoaded;

    public function __construct()
    {
        $this->isLoaded = false;
    }

    public function app() : void
    {
        if ($this->isLoaded) {
            return;
        }

        $sep = DIRECTORY_SEPARATOR;

        require __DIR__ . $sep . 'vendor' . $sep . 'autoload.php';

        $loader = new RobotLoader();
        $loader->addDirectory(__DIR__);
        $loader->setTempDirectory(__DIR__ . $sep . 'temp');
        $loader->setAutoRefresh();
        $loader->register();

        Debugger::enable();
        Debugger::$maxDepth = 2000;
        Debugger::getBar()->addPanel(new TracyBarAdapter());
        Profiler::enable();

        $this->isLoaded = true;
    }

    public function test(): void
    {
        if ($this->isLoaded) {
            return;
        }

        $sep = DIRECTORY_SEPARATOR;

        require __DIR__ . $sep . 'vendor' . $sep . 'autoload.php';

        $loader = new RobotLoader();
        $loader->addDirectory(__DIR__);
        $loader->setTempDirectory(__DIR__ . $sep . 'tests' . $sep . 'temp');
        $loader->setAutoRefresh();
        $loader->register();

        Debugger::enable();
        Debugger::$maxDepth = 2000;
        Debugger::getBar()->addPanel(new TracyBarAdapter());
        Profiler::enable();

        $this->isLoaded = true;
    }

}