<?php
namespace Dahl\MageTools\Plugins;
use Dahl\MageTools\AbortException;
use Dahl\MageTools\PluginAbstract;

class Purge
 extends PluginAbstract
{
    static public function getTitle()
    {
        return 'Delete stuff';
    }

    static public function getKey()
    {
        return 'd';
    }

    static public function isMageDependant()
    {
        return true;
    }

    static public function run()
    {

    }
}
