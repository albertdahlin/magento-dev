<?php

class dahl_dev 
{
    static protected $_instance;
    
    protected $_config;

    static public function init()
    {
        self::$_instance = new dahl_dev();
    }

    public function __construct()
    {
        $this->_initConfig();
        $this->_readConfigFiles();
        $this->_initSetup();
    }

    static public function getConfig()
    {
        return self::$_instance->_config;
    }

    protected function log($string)
    {
        $logFile = $this->_config->getLogFile();
        file_put_contents($logFile, 'DAHL_DEV: ' . $string . "\n", FILE_APPEND);
    }

    protected function _initConfig()
    {
        $mageRoot    = $_SERVER['DOCUMENT_ROOT'];
        include($mageRoot . '/app/Mage.php');
        $includePath = get_include_path();

        $config = new Varien_Object;
        $config->setMageRoot($mageRoot);
        $config->setDevRoot(DAHL_DEVROOT);
        $this->_config = $config;

        set_include_path($config->getDevRoot() . DS . 'magento' . DS . 'Code'. PS . $includePath);
    }

    protected function _readConfigFiles()
    {
        $config   = $this->_config;
        $devRoot  = $config->getDevRoot();
        $mageRoot = $config->getMageRoot();

        if (file_exists($devRoot . '/magento/local.php')) {
            include($devRoot . '/magento/local.php');
        }
        $configFiles = array(
            $devRoot . '/magento/config.json',
            $devRoot . '/magneto/local.json',
            $mageRoot . '/dev/config.json',
            $mageRoot . '/dev/local.json'
        );

        foreach ($configFiles as $file) {
            $config->addData(
                $this->_readConfigJson($file)
            );
        }
    }

    protected function _readConfigJson($filename)
    {
        if (!file_exists($filename)) {
            return array();
        }

        $file = file_get_contents($filename);
        $file = preg_replace('/\s+/', ' ', $file);
        $config = json_decode($file, true);
        if (empty($config)) {
            $this->log("File \"{$filename}\" is not in a valid json format.");
            $config = array();
        }

        return $config;
    }

    protected function _initSetup()
    {
        $config = $this->_config;

        if ($config->getDeveloperMode()) {
            $_SERVER['MAGE_IS_DEVELOPER_MODE'] = 1;
            ini_set('display_errors', 1);
        }
    }
}


dahl_dev::init();
