<?php

namespace Dahl\MageTools;
use Dahl\MageTools\App as App;

/**
 * Abstract class for plugins.
 * 
 * @abstract
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
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
