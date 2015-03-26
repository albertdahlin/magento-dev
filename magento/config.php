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
     * Holds external modules configuration.
     * 
     * @var array
     * @access protected
     */
    protected $_modules = array();

    /**
     * Holds external static files (template, layout, skin, js).
     * 
     * @var array
     * @access protected
     */
    protected $_staticFiles = array();

    /**
     * External urls.
     * 
     * @var array
     * @access protected
     */
    protected $_staticUrls = array();

    /**
     * Registered external modules to be loaded
     * 
     * @var array
     * @access protected
     */
    protected $_registeredModules = array();

    /**
     * Register external module to be loaded
     * 
     * @param string|array $path  The path to the root where files are located.
     * @param stirng $url   An url from where static files can be loaded.
     * @access public
     * @return void
     */
    public function enableExternal($path, $skinUrl = null)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->enableExternal($p, $skinUrl);
            }
        }

        if (is_string($path)) {
            $this->_registeredModules[$path] = $skinUrl;
        }
    }

    /**
     * Unregister external module to be loaded
     * 
     * @param string|array $path  The path to the root where files are located.
     * @access public
     * @return void
     */
    public function disableExternal($path)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                $this->disableExternal($p);
            }
        }

        if (is_string($path)) {
            unset($this->_registeredModules[$path]);
        }
    }

    /**
     * Load all registered external modules
     * 
     * @access public
     * @return void
     */
    public function loadExternalModules()
    {
        $this->_processModuleCookies();

        foreach ($this->_registeredModules as $path => $skinUrl) {
            $this->loadExternal($path, $skinUrl);
        }
    }

    /**
     * Process module cookie to enable/disable external modules
     * 
     * @access protected
     * @return void
     */
    protected function _processModuleCookies()
    {
        if (isset($_COOKIE['enableModule'])) {
            $enable = $_COOKIE['enableModule'];
            $modules = explode(",", $enable);
            foreach ($modules as $module) {
                $this->enableExternal($module);
            }
        }

        if (isset($_COOKIE['disableModule'])) {
            $disable = $_COOKIE['disableModule'];
            $modules = explode(",", $disable);
            foreach ($modules as $module) {
                $this->disableExternal($module);
            }
        }
    }

    /**
     * Load external resources.
     * 
     * @param string $path  The path to the root where files are located.
     * @param stirng $url   An url from where static files can be loaded.
     * @access public
     * @return void
     */
    public function loadExternal($path, $skinUrl = null)
    {
        if (is_array($path)) {
            foreach ($path as $p) {
                return $this->loadExternal($p, $skinUrl);
            }
        }

        $realpath = realpath($this->getModulePath() . DS . $path);
        if (!is_dir($realpath)) {
            $realpath = $path;
            $path = explode('/', $path);
            $path = end($path);
        }
        if (is_dir($realpath)) {
            if (!$skinUrl) {
                $url = sprintf($this->getModuleUrl(), $path);
            }
            $this->_collectStaticFiles($realpath, $url);

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
    protected function _collectStaticFiles($rootPath, $url)
    {
        $design = $rootPath . DS . 'app' . DS . 'design' . DS;
        $locale = $rootPath . DS . 'app' . DS . 'locale' . DS;
        $js     = $rootPath . DS . 'js' . DS;
        $skin   = $rootPath . DS . 'skin' . DS;

        $this->_collectAllFiles($design, 'design');
        $this->_collectAllFiles($locale, 'locale');
        $this->_collectAllFiles($js, 'js', $url . '/js/');
        $this->_collectAllFiles($skin, 'skin', $url . '/skin/');
    }

    /**
     * Creates a lookup table for static files by recusivly collecing
     * all files from a directory and storing them in $_staticFiles.
     * 
     * @param string $dir    The dir from where to start collecting.
     * @param string $key    A identifier key.
     * @param string $url    The url this file should be reachable from.
     * @access protected
     * @return void
     */
    protected function _collectAllFiles($dir, $key, $url = null)
    {
        if (!is_dir($dir)) {
            return;
        }
        $dirIterator    = new RecursiveDirectoryIterator($dir);
        $iterator       = new RecursiveIteratorIterator($dirIterator);
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $path = substr($file->getPathname(), strlen($dir));
                $this->_staticFiles[$key][$path] = $dir . $path;
                if ($url) {
                    $this->_staticUrls[$key][$path] = $url . $path;
                }
            }
        }
    }

    /**
     * Returns the modules configuration array.
     * 
     * @access public
     * @return array
     */
    public function getModules()
    {
        return $this->_modules;
    }

    /**
     * Returns one module's config.
     * 
     * @param string $key
     * @param string $name
     * @access public
     * @return Mage_Core_Model_Config_Base
     */
    public function getModule($name)
    {
        if (isset($this->_modules[$name])) {
            return $this->_modules[$name];
        }

        return null;
    }

    /**
     * Returns config data from a module.
     * 
     * @param string $name
     * @param string $key
     * @access public
     * @return string
     */
    public function getModuleData($name, $key)
    {
        if (isset($this->_modules[$name])) {
            return (string)$this->_modules[$name]->getNode("modules/$name/$key");
        }

        return null;
    }

    /**
     * Collects module declaration files from etc/modules.
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

        return array();
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
        $root            = (string)$module->rootDir;
        $module->codeDir = $root . DS . 'app' . DS . 'code';
    }

    /**
     * Returns an url to an external js file.
     * 
     * @param string $file
     * @access public
     * @return string
     */
    public function renderJsUrl($file)
    {
        if (isset($this->_staticUrls['js'][$file])) {
            return $this->_staticUrls['js'][$file];
        }

        return null;
    }

    /**
     * Returns a skin url to an external skin file.
     * 
     * @param mixed $file
     * @param mixed $params
     * @access public
     * @return void
     */
    public function renderSkinUrl($file, $params)
    {
        $path   = (isset($params['_area'])    ? $params['_area'] . DS    : '')
                . (isset($params['_package']) ? $params['_package'] . DS : '')
                . (isset($params['_theme'])   ? $params['_theme'] . DS   : '')
                . $file;

        if (isset($this->_staticUrls['skin'][$path])) {
            return $this->_staticUrls['skin'][$path];
        }

        return null;
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
        $path   = (isset($params['_area'])    ? $params['_area'] . DS    : '')
                . (isset($params['_package']) ? $params['_package'] . DS : '')
                . (isset($params['_theme'])   ? $params['_theme'] . DS   : '')
                . (isset($params['_type'])    ? $params['_type'] . DS    : '')
                . $file;

        $dir = null;
        switch ($params['_type']) {
            case 'skin':
                $path   = (isset($params['_area'])    ? $params['_area'] . DS    : '')
                        . (isset($params['_package']) ? $params['_package'] . DS : '')
                        . (isset($params['_theme'])   ? $params['_theme'] . DS   : '')
                        . $file;

                if (isset($this->_staticFiles['skin'][$path])) {
                    $dir = $this->_staticFiles['skin'][$path];
                }
                break;

            case 'locale':
                break;

            case 'template':
                if (isset($this->_staticFiles['design'][$path])) {
                    $magePath       = explode(DS, Mage::getBaseDir('design'));
                    $templatePath   = explode(DS, $this->_staticFiles['design'][$path]);
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
                if (isset($this->_staticFiles['design'][$path])) {
                    $dir = $this->_staticFiles['design'][$path];
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
