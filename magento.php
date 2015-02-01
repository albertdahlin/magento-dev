<?php

/**
 * This class bootstraps the dev app.
 * 
 * @package dahl_dev
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com> 
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class dahl_dev
{
    /**
     * Singleton instance of dahl_dev
     * 
     * @var dahl_dev
     * @access protected
     */
    static protected $_instance;
    
    /**
     * Dev configuration data object.
     * 
     * @var Varien_Object
     * @access protected
     */
    protected $_config;

    /**
     * Initializes dev app.
     * 
     * @static
     * @access public
     * @return void
     */
    static public function init()
    {
        self::$_instance = new dahl_dev();
    }

    /**
     * Retrieve the config object.
     * 
     * @static
     * @access public
     * @return Varien_Object
     */
    static public function getConfig()
    {
        return self::$_instance->_config;
    }

    /**
     * Constructor method. Initializes config.
     * 
     * @access public
     * @return void
     */
    public function __construct()
    {
        if (!$this->_initConfig()) {
            return;
        }
        $this->_readConfigFiles();
        $this->_initSetup();
    }

    /**
     * Writes a log message to the log file.
     * 
     * @param string $string 
     * @access protected
     * @return void
     */
    protected function log($string)
    {
        $logFile = $this->_config->getLogFile();
        file_put_contents($logFile, 'DAAL_DEV: ' . $string . "\n", FILE_APPEND);
    }

    /**
     * Initialize configuration. Reads the settings from .json files.
     * 
     * @access protected
     * @return boolean
     */
    protected function _initConfig()
    {
        $mageRoot    = $_SERVER['DOCUMENT_ROOT'];
        if (!file_exists($mageRoot . '/app/Mage.php')) {
            return false;
        }
        include($mageRoot . '/app/Mage.php');
        
        $includePath = get_include_path();

        $config = new Varien_Object;
        $config->setMageRoot($mageRoot);
        $config->setDevRoot(DAAL_DEVROOT);
        $this->_config = $config;

        set_include_path($config->getDevRoot() . DS . 'magento' . DS . 'Code'. PS . $includePath);

        return true;
    }

    /**
     * Includes config files.
     * 
     * @access protected
     * @return void
     */
    protected function _readConfigFiles()
    {
        $config   = $this->_config;
        $devRoot  = $config->getDevRoot();
        $mageRoot = $config->getMageRoot();

        $configFiles = array(
            $devRoot . '/magento/config.php',
            $devRoot . '/magento/local.php',
            $mageRoot . '/dev/config.php',
            $mageRoot . '/dev/local.php'
        );

        foreach ($configFiles as $filename) {
            if (file_exists($filename)) {
                include $filename;
            }
        }
    }

    /**
     * Initializes setup from configuration options.
     * 
     * @access protected
     * @return void
     */
    protected function _initSetup()
    {
        $config = $this->_config;

        if ($config->getDeveloperMode()) {
            $_SERVER['MAGE_IS_DEVELOPER_MODE'] = 1;
            ini_set('display_errors', 1);
        }
    }
}


/**
 * Bootstrap dev app.
 */
dahl_dev::init();
