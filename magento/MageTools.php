<?php

/**
 * Mage tools bootstrap class.
 * 
 * @package
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class MageTools
{
    /**
     * Run mage tools.
     * 
     * @static
     * @access public
     * @return void
     */
    static public function run()
    {
        include_once buildPath(DAHL_DEVROOT, 'lib', 'PhpTerm', 'Autoload.php');
        self::_init();
        $input   = new \Dahl\PhpTerm\Input\Keyboard;
        $output  = new \Dahl\PhpTerm\Output\Terminal;
        $key     = $input->getKeys();
        $classes = self::_getClasses();

        $output
            ->cls()
            ->setPos();

        echo "What do you want to do?\n\n";
        foreach ($classes as $idx => $class) {
            echo "  [{$idx}]    {$class::getTitle()}\n";
        }
        echo "\nSelect tool or \"ESC\" to exit: ";

        $choice = $input->readChar(
            implode('', array_keys($classes)), 
            array($key::ESC)
        );
        if ($choice == $key::ESC) {
            echo "Exit\n";
            exit();
        }
        echo $choice . "\n";
        $classes[$choice]::run();
    }

    /**
     * Initialize MageTools.
     * 
     * @static
     * @access protected
     * @return void
     */
    static protected function _init()
    {
        include_once buildPath(dirname(__file__), 'MageTools', 'Interface.php');
    }

    /**
     * Get class names for Mage tools
     * 
     * @static
     * @access protected
     * @return array
     */
    static protected function _getClasses()
    {
        $files   = glob(buildPath(dirname(__file__), 'MageTools', '*'));
        $classes = array();
        $isMage  = defined('DAHL_MAGEROOT');

        foreach ($files as $file) {
            $className = '\\MageTools\\' . pathinfo($file, PATHINFO_FILENAME);
            if ($className == '\\MageTools\\Interface') {
                continue;
            }
            include_once $file;

            if (!class_exists($className)) {
                throw new Exception("{$className} is not declared in file {$file}");
            }

            if (!is_subclass_of($className, '\MageTools\MageToolsModule')) {
                throw new Exception("{$className} needs to implement interface MageToolsModule");
            }

            if ($className::isMageDependant()) {
                if ($isMage) {
                    $classes[$className::getKey()] = $className;
                }
            } else {
                $classes[$className::getKey()] = $className;
            }
        }

        return $classes;
    }
}

MageTools::run();
