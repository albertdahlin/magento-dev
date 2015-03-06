<?php

namespace Dahl\MageTools;

interface PluginInterface
{
    static public function getTitle();
    static public function getKey();
    static public function isMageDependant();
    static public function run();
    static public function setWindow($window);
}
