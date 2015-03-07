<?php

namespace Dahl\MageTools\Plugins\ModuleCreator;
use Dahl\MageTools\Plugins\ModuleCreator;
use Dahl\MageTools\App;

/**
 * Options class. Takes care of applying options to the xml config file.
 * 
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class Options
{
    /**
     * Returns module dir.
     * 
     * @param array $config
     * @access protected
     * @return string
     */
    protected function _getModuleDir($subPath)
    {
        $config = ModuleCreator::getConfig();
        $dir = implode(
            DIRECTORY_SEPARATOR,
            array(
                'app',
                'code',
                $config['codepool'],
                $config['namespace'],
                $config['moduleName'],
                $subPath,
            )
        );

        return $dir;
    }
    /**
     * Add block
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionLowerB(&$config)
    {
        $class = implode(
            '_',
            array(
                $config['namespace'],
                $config['moduleName'],
                'Block'
            )
        );
        $config['xml']->setNode(
            "global/blocks/{$config['identifier']}/class",
            $class
        );
        $dir = $this->_getModuleDir('Block');
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
    }

    /**
     * Add helper
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionLowerH(&$config)
    {
        $class = implode(
            '_',
            array(
                $config['namespace'],
                $config['moduleName'],
                'Helper'
            )
        );
        $config['xml']->setNode(
            "global/helpers/{$config['identifier']}/class",
            $class
        );
        $dir = $this->_getModuleDir('Helper');
        $helper = "<?php\n"
                . "\n"
                . "class {$class}_Data\n"
                . " extends Mage_Core_Helper_Abstract\n"
                . "{\n"
                . "\n"
                . "}\n";

        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
        file_put_contents(MODULE_ROOT . '/' . $dir . '/Data.php', $helper);
    }

    /**
     * Add Model
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionLowerM(&$config)
    {
        $class = implode(
            '_',
            array(
                $config['namespace'],
                $config['moduleName'],
                'Model'
            )
        );
        $config['xml']->setNode(
            "global/models/{$config['identifier']}/class",
            $class
        );
        $dir = $this->_getModuleDir('Model');
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
    }

    /**
     * Add Resource Model
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionLowerR(&$config)
    {
        $class = implode(
            '_',
            array(
                $config['namespace'],
                $config['moduleName'],
                'Model',
                'Resource'
            )
        );
        $identifier = "{$config['identifier']}_resource";
        $config['xml']->setNode(
            "global/models/{$config['identifier']}/resourceModel",
            $identifier
        );
        $config['xml']->setNode(
            "global/models/{$identifier}/class",
            $class
        );
        $config['xml']->setNode(
            "global/models/{$identifier}/entities/some_model/table",
            'some_table'
        );
        $dir = $this->_getModuleDir('Model/Resource');
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
    }

    /**
     * Setup
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionLowerS(&$config)
    {
        $class = implode(
            '_',
            array(
                $config['namespace'],
                $config['moduleName'],
                'Model',
                'Resource',
                'Setup',
            )
        );
        $identifier = "{$config['identifier']}_setup";
        $config['xml']->setNode(
            "global/resources/{$identifier}/setup/module",
            "{$config['namespace']}_{$config['moduleName']}"
        );
        $config['xml']->setNode(
            "global/resources/{$identifier}/setup/class",
            $class
        );
        $dir = $this->_getModuleDir('Model/Resource');
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
        $setup  = "<?php\n"
                . "\n"
                . "class {$class}\n"
                . " extends Mage_Core_Model_Resource_Setup\n"
                . "{\n"
                . "\n"
                . "}\n";

        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
        file_put_contents(MODULE_ROOT . '/' . $dir . '/Setup.php', $setup);
        $dir = $this->_getModuleDir("sql/{$identifier}");
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
        $install = "<?php\n"
                 . "\n"
                 . '$installer = $this;' . "\n"
                 . '$installer->startSetup();' . "\n"
                 . '$connection = $installer->getConnection();' . "\n"
                 . "\n"
                 . '$installer->endSetup();' . "\n";

        file_put_contents(MODULE_ROOT . '/' . $dir . '/install-1.0.0.php', $install);
    }

    /**
     * Add frontend layout xml
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionLowerL(&$config)
    {
        $identifier = $config['identifier'];
        $config['xml']->setNode(
            "frontend/layout/updates/{$identifier}/file",
            $identifier . '.xml'
        );
        $dir = "app/design/frontend/base/default/layout";
        $xml ="<?xml version=\"1.0\"?>\n<layout>\n</layout>\n";
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
        file_put_contents(MODULE_ROOT . '/' . $dir . "/{$identifier}.xml", $xml);
    }

    /**
     * Add adminhtml layout xml 
     * 
     * @param array $config 
     * @access public
     * @return void
     */
    public function optionUpperL(&$config)
    {
        $identifier = $config['identifier'];
        $config['xml']->setNode(
            "adminhtml/layout/updates/{$identifier}/file",
            $identifier . '.xml'
        );
        $dir = "app/design/adminhtml/default/default/layout";
        $xml ="<?xml version=\"1.0\"?>\n<layout>\n</layout>\n";
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
        file_put_contents(MODULE_ROOT . '/' . $dir . "/{$identifier}.xml", $xml);
    }

    /**
     * Add frontend controller
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionLowerC(&$config)
    {
        $input  = App::getWindow()->getInput();
        $identifier = $config['identifier'];
        $frontname = $input->readLine("\nEnter Frontname [{$identifier}]: ", true, 'a-zA-Z\d_-');
        if (!$frontname) {
            $frontname = $identifier;
        }
        $class = "{$config['namespace']}_{$config['moduleName']}_IndexController";
        $config['xml']->setNode(
            "frontend/routers/{$identifier}/use",
            'standard'
        );

        $config['xml']->setNode(
            "frontend/routers/{$identifier}/args/module",
            "{$config['namespace']}_{$config['moduleName']}"
        );

        $config['xml']->setNode(
            "frontend/routers/{$identifier}/args/frontName",
            $frontname
        );
        $dir = $this->_getModuleDir('controllers');
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
        $controller = "<?php\n"
                    . "\n"
                    . "class {$class}\n"
                    . " extends Mage_Core_Controller_Front_Action\n"
                    . "{\n"
                    . "    public function indexAction()\n"
                    . "    {\n"
                    . "        \$this->loadLayout();\n"
                    . "        \$this->renderLayout();\n"
                    . "    }\n"
                    . "}\n";

        file_put_contents(MODULE_ROOT . '/' . $dir . "/IndexController.php", $controller);
    }

    /**
     * Add adminhtml controller
     * 
     * @param array $config
     * @access public
     * @return void
     */
    public function optionUpperC(&$config)
    {
        $identifier = $config['identifier'];
        $class = "{$config['namespace']}_{$config['moduleName']}_Adminhtml";
        $config['xml']->setNode(
            "admin/routers/adminhtml/args/modules/{$config['namespace']}_{$config['moduleName']}",
            $class
        );
        $node = $config['xml']->getNode("admin/routers/adminhtml/args/modules/{$config['namespace']}_{$config['moduleName']}");
        $node->addAttribute('before', 'Mage_Adminhtml');

        $dir = $this->_getModuleDir('controllers/Adminhtml');
        @mkdir(MODULE_ROOT . '/' . $dir, 0777, true);
    }
}
