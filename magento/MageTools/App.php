<?php
namespace Dahl\MageTools;
use Exception;
/**
 * Mage tools bootstrap class.
 * 
 * @package
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class App
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

    /**
     * Run Mage tools
     * 
     * @static
     * @access public
     * @return void
     */
    static public function run()
    {
        self::_init();
        $window  = self::$_window;
        $input   = $window->getInput();
        $output  = $window->getOutput();
        $key     = $input->getKeys();
        $plugins = self::_getPlugins();

        $output
            ->cls()
            ->setPos();

        $window->addElement('1')
            ->setStyle('middle: 50%; text-align: center;')
            ->setText("\nMAGE TOOLS\n \nSelect an option from the list below:\n");

        $options = $window->addElement('2', 'Options')
            ->setStyle('middle: 50%; position: fixed; top: 5;')
            ->setOptions($plugins);

        $window->render();

        $className = $options->getOption();

        if ($className === null) {
            echo "Exit\n";
            return;
        }
        $className::setWindow($window);
        try {
            $className::run();
        } catch (AbortException $e) {
            $output->bottom()->home();
            echo $e->getMessage() . "\n";
        } catch (Exception $e) {
            $output->bottom()->home();
            echo $e->getMessage() . "\n";
        }
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
        include_once buildPath(DAHL_DEVROOT, 'lib', 'PhpTerm', 'Autoload.php');
        \Dahl\Autoload::registerBase('Dahl\\MageTools', dirname(__file__));
        if (defined('DAHL_MAGEROOT')) {
            \Mage::app()->init('admin', 'store');
        }
        self::$_window = new \Dahl\PhpTerm\Window;
    }

    /**
     * Get class names for Mage tools
     * 
     * @static
     * @access protected
     * @return array
     */
    static protected function _getPlugins()
    {
        $files   = glob(buildPath(dirname(__file__), 'Plugins', '*.php'));
        $classes = array();
        $isMage  = defined('DAHL_MAGEROOT');

        foreach ($files as $file) {
            $className = '\\Dahl\\MageTools\\Plugins\\' . pathinfo($file, PATHINFO_FILENAME);
            include_once $file;

            if (!class_exists($className)) {
                throw new Exception("{$className} is not declared in file {$file}");
            }

            if (!is_subclass_of($className, '\\Dahl\\MageTools\\PluginInterface')) {
                throw new Exception("{$className} needs to implement interface Dahl\\MageTools\\PluginInterface");
            }

            if ($className::isMageDependant()) {
                if ($isMage) {
                    $classes[$className] = $className::getTitle();
                }
            } else {
                $classes[$className] = $className::getTitle();
            }
        }

        return $classes;
    }
}

App::run();
