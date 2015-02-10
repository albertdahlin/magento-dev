<?php

class dahl_dev_config
{
    /**
     * Holds config data.
     * 
     * @var array
     * @access protected
     */
    protected $_data = array();

    /**
     * Holds name cache.
     * 
     * @var array
     * @access protected
     */
    static protected $_pathCache = array();

    /**
     * Holds external modules.
     * 
     * @var array
     * @access protected
     */
    protected $_modules = array();

    /**
     * Holds external template and layout files.
     * 
     * @var array
     * @access protected
     */
    protected $_designFiles = array();

    /**
     * Load an external module.
     * 
     * @param string $path  The path to the module root.
     * @param stirng $url   An url from where static files can be loaded.
     * @access public
     * @return void
     */
    public function loadExternal($path, $skinUrl = null)
    {
        if (is_dir($path)) {
            $realpath = $path;
            $path = explode('/', $path);
            $path = end($path);
        } else {
            $realpath = realpath($this->getModulePath() . '/' . $path);
        }
        if (is_dir($realpath)) {
            $this->_collectDesignFiles($realpath);
            if (!$skinUrl) {
                $url = sprintf($this->getModuleUrl(), $path);
            }

            $declareFiles = $this->_getDeclareFiles($realpath);
            foreach ($declareFiles as $file) {
                $fileConfig = new Mage_Core_Model_Config_Base();
                $fileConfig->loadFile($file);
                foreach ($fileConfig->getXpath('modules/*') as $module) {
                    $module->externalModule = true;
                    $module->rootDir = $realpath;
                    $module->url = $skinUrl;
                    $this->_initCodeDir($module);
                    $this->_modules[$module->getName()] = $fileConfig;
                }
            }
        }
    }

    /**
     * Collects files from external directorys.
     * 
     * @param string $rootPath 
     * @access protected
     * @return void
     */
    protected function _collectDesignFiles($rootPath)
    {
        $designDir      = $rootPath . DS . 'app' . DS . 'design' . DS;
        $dirIterator    = new RecursiveDirectoryIterator($designDir);
        $iterator       = new RecursiveIteratorIterator($dirIterator);
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $path = substr($file->getPathname(), strlen($designDir));
                $this->_designFiles[$path] = $file->getPathname();
            }
        }
    }

    /**
     * Returns modules array.
     * 
     * @access public
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * Returns module config data.
     * 
     * @param string $key 
     * @param string $name 
     * @access public
     * @return string
     */
    public function getModule($name)
    {
        if (isset($this->_modules[$name])) {
            return $this->_modules[$name];
        }

        return null;
    }

    /**
     * Returns module config data.
     * 
     * @param string $name 
     * @param string $key 
     * @access public
     * @return void
     */
    public function getModuleData($name, $key)
    {
        if (isset($this->_modules[$name])) {
            return (string)$this->_modules[$name]->getNode("modules/$name/$key");
        }

        return null;
    }

    /**
     * Loads module declaration files from etc/modules.
     * 
     * @param string $path 
     * @access public
     * @return array
     */
    protected function _getDeclareFiles($path)
    {
        $modulesDir = $path . DS . 'app' . DS . 'etc' . DS . 'modules';

        if (is_dir($modulesDir)) {
            $files = glob($modulesDir . DS . '*.xml');
            if (count($files)) {
                return $files;
            }
        }

        return null;
    }

    /**
     * Returns the code dir.
     * 
     * @param string $path 
     * @access protected
     * @return string
     */
    protected function _initCodeDir($module)
    {
        $root       = (string)$module->rootDir;
        $moduleName = $module->getName();
        $module->codeDir = $root . DS . 'app' . DS . 'code';
    }

    /**
     * Renders a template, skin or layout file.
     * 
     * @param string $file 
     * @param array $params 
     * @access public
     * @return string
     */
    public function renderFilename($file, $params)
    {
        $path   = isset($params['_area'])    ? $params['_area'] . DS    : ''
                . isset($params['_package']) ? $params['_package'] . DS : ''
                . isset($params['_theme'])   ? $params['_theme'] . DS   : ''
                . isset($params['_type'])    ? $params['_type'] . DS    : ''
                . $file;

        $dir = null;
        switch ($params['_type']) {
            case 'skin':
                break;

            case 'locale':
                break;

            case 'template':
                if (isset($this->_designFiles[$path])) {
                    $magePath       = explode(DS, Mage::getBaseDir('design'));
                    $templatePath   = explode(DS, $this->_designFiles[$path]);
                    $count          = count($magePath);

                    for ($i = 0; $i < $count; $i++) {
                        if ($magePath[$i]) {
                            if (isset($templatePath[$i])) {
                                if ($magePath[$i] !== $templatePath[$i]) {
                                    break;
                                }
                            }
                        }
                    }
                    $dir = str_repeat('../', $count - $i);
                    $dir .= implode(DS, array_splice($templatePath, $i));
                }
                break;
            default:
                if (isset($this->_designFiles[$path])) {
                    $dir = $this->_designFiles[$path];
                }
                break;
        }

        return $dir;
    }

    /**
     * Stores value into self::$_data array.
     *
     * @param mixed $key
     * @param mixed $value
     * @static
     * @access public
     * @return void
     */
    public function setPath($key, $value = null)
    {
        if (is_array($key)) {
            $this->_data = $key;
        } else if (is_array($value)) {
            foreach ($value as $k => $v) {
                $this->setPath($key . '/' . $k, $v);
            }
        } else {
            if (strpos($key, '/')) {
                $keyArr = explode('/', $key);
                $data = &$this->_data;
                foreach ($keyArr as $i => $k) {
                    if (is_array($data)) {
                        if (!isset($data[$k])) {
                            $data[$k] = array();
                        }
                        $data = &$data[$k];
                    }
                }

                $data = $value;
            } else {
                $this->_data[$key] = $value;
            }
        }
    }

    /**
     * Returns data from self::$_data.
     *
     * @param string $key
     * @static
     * @access public
     * @return mixed
     */
    public function getPath($key = '')
    {
        if ($key === '') {
            return $this->_data;
        }

        $data = $this->_data;
        $default = null;

        if (strpos($key, '/')) {
            $keyArr = explode('/', $key);
            foreach ($keyArr as $i => $k) {
                if ($k==='') {

                    return $default;
                }
                if (is_array($data)) {
                    if (!isset($data[$k])) {
                        return $default;
                    }
                    $data = $data[$k];
                } else {

                    return $default;
                }
            }

            return $data;
        }

        if (isset($data[$key])) {
            return $data[$key];
        }

        return $default;
    }

    /**
     * Set/Get attribute wrapper
     *
     * @param   string $method
     * @param   array $args
     * @return  mixed
     */
    public function __call($method, $args)
    {
        switch (substr($method, 0, 3)) {
            case 'get' :
                $key = $this->_buildPath(substr($method, 3));
                $data = $this->getPath($key, isset($args[0]) ? $args[0] : null);

                return $data;

            case 'set' :
                $key = $this->_buildPath(substr($method, 3));
                $result = $this->setPath($key, isset($args[0]) ? $args[0] : null);

                return $result;
        }

        throw new Exception("DAHL_DEV: Invalid method {$method}");
    }

    /**
     * Converts field names for setters and geters
     *
     * @return string
     */
    protected function _buildPath($name)
    {
        if (isset(self::$_pathCache[$name])) {
            return self::$_pathCache[$name];
        }
        $result = strtolower(preg_replace('/(.)([A-Z])/', "$1/$2", $name));
        self::$_pathCache[$name] = $result;

        return $result;
    }
}
