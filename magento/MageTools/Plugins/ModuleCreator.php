<?php
namespace Dahl\MageTools\Plugins;
use Dahl\MageTools\AbortException;
use Dahl\MageTools\PluginAbstract;

/**
 * ModuleCreator class.
 * 
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class ModuleCreator
 extends PluginAbstract
{
    /**
     * Config data
     * 
     * @var array
     * @access protected
     */
    static protected $_config;

    /**
     * Returns Mage tool title
     * 
     * @static
     * @access public
     * @return string
     */
    static public function getTitle()
    {
        return 'Create a module';
    }

    /**
     * Returns magetools key identifier.
     * 
     * @static
     * @access public
     * @return string
     */
    static public function getKey()
    {
        return 'm';
    }

    /**
     * Returns if tool is Mage dependant.
     * 
     * @static
     * @access public
     * @return void
     */
    static public function isMageDependant()
    {
        return false;
    }

    /**
     * Returns config array.
     * 
     * @static
     * @access public
     * @return array
     */
    static public function getConfig()
    {
        return self::$_config;
    }

    /**
     * Run Module creator mage tool.
     * 
     * @static
     * @access public
     * @return void
     */
    static public function run()
    {
        define('MODULE_ROOT', $_SERVER['PWD']);
        $window = self::$_window;
        $input = $window->getInput();
        $output = $window->getOutput();
        $key    = $input->getKeys();
        $options = new ModuleCreator\Options;

        $output->cls()->setPos();
        self::_getInputData();

        foreach (self::$_config['values'] as $k => $isSelected) {
            if ($isSelected) {
                if (isset(self::$_config['options'], self::$_config['options'][$k])) {
                    continue;
                }
                if ($k == strtoupper($k)) {
                    $method = 'optionUpper' . strtoupper($k);
                } else {
                    $method = 'optionLower' . strtoupper($k);
                }
                if (method_exists($options, $method)) {
                    $options->$method(self::$_config);
                }
            }
        }

        self::_saveXmls();
    }

    /**
     * Get module configuration data from user.
     * 
     * @static
     * @access protected
     * @return boolean
     */
    static protected function _getInputData()
    {
        $window = self::$_window;
        $input  = $window->getInput();
        $output = $window->getOutput();
        $key    = $input->getKeys();
        $config = self::_loadExisting();
        if ($config === false) {
            return false;
        }
        /**
         * Module Namespace.
         */
        if (!isset($config['namespace'])) {
            echo "Enter information. Press ESC to abort.\n\n";
            echo "Enter module namespace. First letter will be capitalized. e.g \"mage\" will be converted to \"Mage\".\n";
            if (($config['namespace'] = $input->readLine('Namespace: ', true, 'a-zA-Z')) === false) {
                throw new AbortException("Exit");
            }
            $config['namespace'] = ucfirst($config['namespace']);
        }

        /**
         * Module Name.
         */
        if (!isset($config['moduleName'])) {
            echo "\n\nEnter module name. i.e the part after {$config['namespace']}_\nThe first letter will be capitalized.\n";
            if (($config['moduleName'] = $input->readLine($config['namespace'] . '_', true, 'a-zA-Z')) === false) {
                throw new AbortException("Exit");
            }
            $config['moduleName'] = ucfirst($config['moduleName']);
        }

        /**
         * Module factory identifier.
         */
        if (!isset($config['identifier'])) {
            echo "\n\nEnter module factory identifier, i.e the first part used in Mage::getModel('<identifier>/class_name')\n";
            if (($config['identifier'] = $input->readLine('Factory Identifier: ', true, 'a-z_')) === false) {
                throw new AbortException("Exit");
            }
            if (!$config['identifier']) {
                $config['identifier'] = strtolower($config['moduleName']);
            }
        }

        /**
         * Module Code Pool.
         */
        if (!isset($config['codepool'])) {
            $codePools = array(
                'o' => 'core',
                'c' => 'community',
                'l' => 'local'
            );
            echo "\n\nSelect code pool:\n";
            foreach ($codePools as $c => $pool) {
                echo "    [{$c}] {$pool}\n";
            }
            $char = $input->readChar(implode('', array_keys($codePools)));
            $config['codepool'] = $codePools[$char];
        }

        /**
         * Module Options.
         */
        $output->cls()->setPos();

        $options = array(
            'b' => "Block                ({$config['namespace']}_{$config['moduleName']}_Block)",
            'h' => "Helper               ({$config['namespace']}_{$config['moduleName']}_Helper_Data)",
            'm' => "Model                ({$config['namespace']}_{$config['moduleName']}_Model)",
            'r' => 'Resource Model',
            's' => "sql Setup            (sql/{$config['identifier']}_setup/install-1.0.0.php)",
            'c' => 'Frontend Controller',
            'l' => "Frontend Layout xml  ({$config['identifier']}.xml)",
            'C' => "Adminhtml Controller",
            'L' => "Adminhtml Layout xml ({$config['identifier']}.xml)",
        );
        $values = isset($config['options']) ? $config['options'] : array('h' => true);

        $el = $window->addElement('options')
            ->setStyle('position: fixed; top: 2; left: 3;')
            ->setText('');
        echo "Use the letters to toggle settings, 'a' will toggle all. Press ENTER when you are done.";
        $allToggle = true;
        while (true) {
            $text = '';
            foreach ($options as $c => $label) {
                $use = false;
                if (isset($values[$c])) {
                    $use = $values[$c];
                }
                $yesNo = $use ? '(yes)' : ' (no)';
                $text .= "[{$c}]   {$yesNo}  {$label}\n";
            }
            $el->setText($text);
            $window->render();
            $char = $input->readChar('a' . implode('', array_keys($options)), array($key::ENTER, $key::ESC));
            if ($char == $key::ESC) {
                throw new AbortException("Exit");
            }
            if ($char == $key::ENTER) {
                break;
            }
            if ($char == 'a') {
                foreach ($options as $c => $option) {
                    $values[$c] = $allToggle;
                }
                $allToggle = !$allToggle;
                continue;
            }
            if (isset($values[$char])) {
                $values[$char] = !$values[$char];
            } else {
                $values[$char] = true;
            }
        }
        echo "\n";
        $config['values'] = $values;
        $config['xml']->setNode("modules/{$config['namespace']}_{$config['moduleName']}/version", '1.0.0');

        self::$_config = $config;
        return true;
    }

    /**
     * Check if there is already a module here. If so, load it.
     * 
     * @static
     * @access protected
     * @return array
     */
    static protected function _loadExisting()
    {
        $config = array();
        $file   = self::_selectFile();

        if ($file) {
            $config = self::_selectModule($file);
        }
        if (!isset($config['xml'])) {
            $config['xml'] = new ModuleCreator\XmlConfig('<config></config>');
        }

        return $config;
    }

    /**
     * Select a module declarations file.
     * 
     * @static
     * @access protected
     * @return string
     */
    static protected function _selectFile()
    {
        $window = self::$_window;
        $input  = $window->getInput();
        $key    = $input->getKeys();
        $file   = null;
        if (is_dir(MODULE_ROOT) . '/app/etc/modules') {
            $file = glob(MODULE_ROOT . '/app/etc/modules/*.xml');
            if (count($file) > 0) {
                echo "Multiple files found, select one or press \"N\" to create a new one:\n\n";
                $file['n'] = 'Create new module';
                foreach ($file as $k => $f) {
                    $fname = pathinfo($f, PATHINFO_BASENAME);
                    echo "  [{$k}]  {$fname}\n";
                }
                unset($file['n']);
                echo "\n";
                if (count($file) > 10) {
                    $ch = $input->readLine('Select module: ', true, 'nN'.implode('', array_keys($file)));
                } else {
                    echo "Select module: ";
                    $ch = $input->readChar('nN'.implode('', array_keys($file)), array($key::ESC));
                }
                if ($ch === false || $ch === $key::ESC) {
                    throw new AbortException("Exit");
                } elseif ($ch == 'n' || $ch == 'N') {
                    $file = null;
                } elseif (isset($file[$ch])) {
                    $file = $file[$ch];
                }
            } else {
                $file = reset($file);
            }
        }

        return $file;
    }

    /**
     * Selects a module and reads config.xml
     * 
     * @param string $file
     * @static
     * @access protected
     * @return array
     */
    static protected function _selectModule($file)
    {
        $window     = self::$_window;
        $input      = $window->getInput();
        $key        = $input->getKeys();
        $config     = array();
        $xmlFile    = file_get_contents($file);
        $declareXml = new ModuleCreator\XmlConfig($xmlFile);
        $modules    = $declareXml->xpath('/config/modules/*');

        if (count($modules) > 1) {
            $fname = pathinfo($file, PATHINFO_BASENAME);
            echo "Multiple modules found in file {$fname}, select one:\n\n";
            foreach ($modules as $k => $m) {
                $name = $m->getName();
                echo "  [{$k}]  {$name}\n";
            }
            echo "\n";
            if (count($modules) > 10) {
                $ch = $input->readLine('Select module: ', true, implode('', array_keys($modules)));
            } else {
                echo "Select module: ";
                $ch = $input->readChar(implode('', array_keys($modules)), array($key::ESC));
            }
            if ($ch === false || $ch === $key::ESC) {
                throw new AbortException("Exit");
            } elseif (isset($modules[$ch])) {
                $module = $modules[$ch];
            }
        } else {
            $module = reset($modules);
        }

        list($namespace, $moduleName) = explode('_', $module->getName());
        $codepool             = (string)$module->codePool;
        $config['namespace']  = $namespace;
        $config['moduleName'] = $moduleName;
        $config['codepool']   = $codepool;
        $config['declareXml'] = $declareXml;
        $configFile = MODULE_ROOT . "/app/code/{$codepool}/{$namespace}/{$moduleName}/etc/config.xml";
        if (is_file($configFile)) {
            $configXml = file_get_contents($configFile);
            if ($configXml) {
                $config['xml'] = new ModuleCreator\XmlConfig($configXml);

                $models = $config['xml']->xpath('/config/global/models/*');
                foreach ($models as $model) {
                    if (isset($model->class)) {
                        if (strpos($model->getName(),'_resource')) {
                            $config['options']['r'] = true;
                        } else {
                            $config['identifier']   = $model->getName();
                            $config['options']['m'] = true;
                        }
                    }
                }
                $blocks = $config['xml']->xpath('/config/global/blocks/*');
                foreach ($blocks as $block) {
                    if (isset($block->class)) {
                        $config['identifier']   = $block->getName();
                        $config['options']['b'] = true;
                    }
                }
                $helpers = $config['xml']->xpath('/config/global/helpers/*');
                foreach ($helpers as $helper) {
                    if (isset($helper->class)) {
                        $config['identifier']   = $helper->getName();
                        $config['options']['h'] = true;
                    }
                }
                $fRouter = $config['xml']->xpath('/config/frontend/routers/*');
                foreach ($fRouter as $router) {
                    if (isset($router->args, $router->args->frontName)) {
                        $config['frontName']   = $router->args->frontName;
                        $config['options']['c'] = true;
                    }
                }
                $aRouter = $config['xml']->xpath('/config/admin/routers/adminhtml/args/modules/*');
                foreach ($aRouter as $router) {
                    $config['options']['C'] = true;
                }
                $fLayout = $config['xml']->xpath('/config/frontend/layout/updates/*');
                foreach ($fLayout as $layout) {
                    if (isset($layout->file)) {
                        $config['options']['l'] = true;
                    }
                }
                $aLayout = $config['xml']->xpath('/config/adminhtml/layout/updates/*');
                foreach ($aLayout as $layout) {
                    if (isset($layout->file)) {
                        $config['options']['L'] = true;
                    }
                }
                $resources = $config['xml']->xpath('/config/global/resources/*');
                foreach ($resources as $resource) {
                    $config['options']['s'] = true;
                }
            }
        }

        return $config;
    }

    /**
     * Save module config xmls.
     * 
     * @static
     * @access protected
     * @return void
     */
    static protected function _saveXmls()
    {
        $config = self::$_config;
        $moduleEtcDir = "app/code/{$config['codepool']}/{$config['namespace']}/{$config['moduleName']}/etc";
        @mkdir(MODULE_ROOT . '/' . $moduleEtcDir, 0777, true);
        file_put_contents(
            MODULE_ROOT . "/{$moduleEtcDir}/config.xml",
            $config['xml']->getXml()
        );

        if (!isset($config['declareXml']) || !$config['declareXml']) {
            $declareXml = new ModuleCreator\XmlConfig('<config></config>');
            $declareXml->setNode(
                "modules/{$config['namespace']}_{$config['moduleName']}/active",
                'true'
            );

            $declareXml->setNode(
                "modules/{$config['namespace']}_{$config['moduleName']}/codePool",
                $config['codepool']
            );

            $declareDir = "app/etc/modules";
            @mkdir(MODULE_ROOT . '/' . $declareDir, 0777, true);
            file_put_contents(
                MODULE_ROOT . "/{$declareDir}/{$config['namespace']}_{$config['moduleName']}.xml",
                $declareXml->getXml()
            );
        }

        echo "\nCreated module \"{$config['namespace']}_{$config['moduleName']}\"\n";
    }
}
