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
    static protected $_window;
    /**
     * Run mage tools.
     * 
     * @static
     * @access public
     * @return void
     */

    static public function getWindow()
    {
        return self::$_window;
    }

    static public function run()
    {
        include_once buildPath(DAHL_DEVROOT, 'lib', 'PhpTerm', 'Autoload.php');
        self::_init();
        $window  = new \Dahl\PhpTerm\Window;
        self::$_window = $window;
        $input   = $window->getInput();
        $output  = $window->getOutput();
        $key     = $input->getKeys();
        $classes = self::_getClasses();

        $output
            ->cls()
            ->setPos();

        $window->addElement('1')
            ->setStyle('middle: 50%; text-align: center;')
            ->setText("\nMAGE TOOLS\n \nSelect an option from the list below:\n");
        $list = '';
        foreach ($classes as $idx => $class) {
            $list .=  "[{$idx}]    {$class::getTitle()}\n";
        }

        $window->addElement('2')
            ->setStyle('middle: 50%')
            ->setText($list);

        $window->addElement('3')
            ->setStyle('position: fixed; bottom: 0; left: 0')
            ->setText("Select tool or \"ESC\" to exit: ");

        $window->render();

        $choice = $input->readChar(
            implode('', array_keys($classes)), 
            array($key::ESC)
        );
        if ($choice == $key::ESC) {
            echo "Exit\n";
            return;
        }
        echo $choice . "\n";
        $classes[$choice]::setWindow($window);
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
        if (defined('DAHL_MAGEROOT')) {
            \Mage::app()->init('admin', 'store');
        }
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
        $files   = glob(buildPath(dirname(__file__), 'MageTools', '*.php'));
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
