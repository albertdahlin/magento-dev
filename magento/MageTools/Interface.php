<?php

namespace MageTools;

interface MageToolsModule
{
    static public function getTitle();
    static public function getKey();
    static public function isMageDependant();
    static public function run();
}
