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
        $this->_initLocal();
    }

    static public function getConfig()
    {
        return self::$_instance->_config;
    }

    protected function _initConfig()
    {
        $mageRoot = $_SERVER['DOCUMENT_ROOT'];
        include($mageRoot . '/app/Mage.php');
        $includePath = get_include_path();
        $config = new Varien_Object;
        $config->setMageRoot($mageRoot);
        $config->setDevRoot(DAHL_DEVROOT);

        set_include_path($config->getDevRoot() . DS . 'magento' . DS . 'Code'. PS . $includePath);
        $host = $_SERVER['HTTP_HOST'];
        $host = explode('.', $host);
        $host = array_reverse($host);

        if (count($host) > 3) {
            $config->setProject($host[3]);
        }

        $this->_config = $config;
    }

    protected function _initLocal()
    {
        $config = $this->_config;
        if (file_exists($config->getDevRoot() . '/magento/local.php')) {
            include($config->getDevRoot() . '/magento/local.php');
        }
    }
}


dahl_dev::init();