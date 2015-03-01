<?php
namespace MageTools;

/**
 * ModuleCreator class.
 * 
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class ModuleCreator
 implements MageToolsModule
{
    /**
     * Config data
     * 
     * @var \Dahl\PhpTerm\Window
     * @access protected
     */
    static protected $_window;

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
     * Set window object
     * 
     * @param Dahl\PhpTerm\Window $window
     * @static
     * @access public
     * @return void
     */
    static public function setWindow($window)
    {
        self::$_window = $window;
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
        include 'ModuleCreator/Options.php';
        include 'ModuleCreator/XmlConfig.php';
        $options = new ModuleCreator\Options;

        $output->cls()->setPos();
        self::_getInputData();

        foreach (self::$_config['options'] as $k => $isSelected) {
            if ($isSelected) {
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
     * @return void
     */
    static protected function _getInputData()
    {
        $window = self::$_window;
        $input  = $window->getInput();
        $output = $window->getOutput();
        $key    = $input->getKeys();
        $config = array();
        echo "Enter information. Press ESC to abort.\n\n";
        echo "Enter module namespace. First letter will be capitalized. e.g \"mage\" will be converted to \"Mage\".\n";
        if (($config['namespace'] = $input->readLine('Namespace: ', true, 'a-zA-Z')) === false) {
            echo "Exit.\n";
            return;
        }
        $config['namespace'] = ucfirst($config['namespace']);

        echo "\nEnter module name, i.e the part after {$config['namespace']}_\n";
        if (($config['moduleName'] = $input->readLine($config['namespace'] . '_', true, 'a-zA-Z')) === false) {
            echo "Exit.\n";
            return;
        }
        $config['moduleName'] = ucfirst($config['moduleName']);

        echo "\nEnter module identifier, ie the part used in Mage::getModel('<identifier/class')\n";
        if (($config['identifier'] = $input->readLine('Factory Identifier: ', true, 'a-z')) === false) {
            echo "Exit.\n";
            return;
        }

        $codePools = array(
            'o' => 'core',
            'c' => 'community',
            'l' => 'local'
        );
        echo "\nSelect code pool\n";
        foreach ($codePools as $c => $pool) {
            echo "    [{$c}] {$pool}\n";
        }
        $char = $input->readChar(implode('', array_keys($codePools)));
        $config['codepool'] = $codePools[$char];

        $output->cls()->setPos();

        $options = array(
            'c' => 'Frontend Controller',
            'l' => "Frontend Layout xml ({$config['identifier']}.xml)",
            'C' => "Adminhtml Controller",
            'L' => "Adminhtml Layout xml ({$config['identifier']}.xml)",
            't' => "Translate CSV ({$config['namespace']}_{$config['moduleName']}.csv)",
            'b' => 'Block',
            'h' => 'Helper',
            'm' => 'Model',
            'r' => 'Resource Model',
            's' => 'sql Setup',
        );
        $values = array(
            'h' => true,
        );

        $el = $window->addElement('options')
            ->setStyle('position: fixed; top: 2; left: 3;')
            ->setText('');
        echo "Use the letters to select, 'a' will select all. Press ENTER when you are done.";
        while (true) {
            $text = '';
            foreach ($options as $c => $label) {
                $use = false;
                if (isset($values[$c])) {
                    $use = $values[$c];
                }
                $yesNo = $use ? 'yes' : ' no';
                $text .= "[{$c}]   {$yesNo}  {$label}\n";
            }
            $el->setText($text);
            $window->render();
            $char = $input->readChar('a' . implode('', array_keys($options)), array($key::ENTER));
            if ($char == $key::ENTER) {
                break;
            }
            if ($char == 'a') {
                foreach ($options as $c => $option) {
                    $values[$c] = true;
                }
                continue;
            }
            if (isset($values[$char])) {
                $values[$char] = !$values[$char];
            } else {
                $values[$char] = true;
            }
        }
        $config['options'] = $values;
        $config['xml'] = new ModuleCreator\XmlConfig('<config></config>');
        $config['xml']->setNode("modules/{$config['namespace']}_{$config['moduleName']}/version", '1.0.0');

        self::$_config = $config;
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

        echo "\nCreated module \"{$config['namespace']}_{$config['moduleName']}\"\n";
    }
}
