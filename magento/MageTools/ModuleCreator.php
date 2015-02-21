<?php
namespace MageTools;

class ModuleCreator
 implements MageToolsModule
{
    static public function getTitle()
    {
        return 'Create a module';
    }

    static public function getKey()
    {
        return 'm';
    }

    static public function isMageDependant()
    {
        return false;
    }

    static public function run()
    {
    }
}
