<?php

namespace Dahl\MageTools;
use Dahl\MageTools\App as App;

abstract class PluginAbstract
 implements PluginInterface
{
    /**
     * Config data
     * 
     * @var \Dahl\PhpTerm\Window
     * @access protected
     */
    static protected $_window;

    /**
     * Set window object
     * 
     * @param Dahl\PhpTerm\Window $window
     * @static
     * @access public
     * @return void
     */
    static public function setWindow($window)
    {
        self::$_window = $window;
    }
}
