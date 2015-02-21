<?php

namespace MageTools;

class Purge
 implements MageToolsModule
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
