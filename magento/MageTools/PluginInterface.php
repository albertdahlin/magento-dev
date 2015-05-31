<?php

namespace Dahl\MageTools;

/**
 * Mage tools plugin interface.
 * 
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
interface PluginInterface
{
    /**
     * Should return the plugin title.
     * 
     * @static
     * @access public
     * @return string
     */
    static public function getTitle();

    /**
     * Return true if the plugin needs a bootstraped Magento to work,
     * false if it can be run standalone from Magento.
     * 
     * @static
     * @access public
     * @return boolean
     */
    static public function isMageDependant();

    /**
     * The entry point of the plugin.
     * 
     * @static
     * @access public
     * @return void
     */
    static public function run();

    /**
     * Set the window object reference.
     * 
     * @param Dahl\PhpTerm\Window $window
     * @static
     * @access public
     * @return void
     */
    static public function setWindow($window);
}
