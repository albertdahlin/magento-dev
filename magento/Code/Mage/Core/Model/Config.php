<?php
include('config-original191.php');
class Mage_Core_Model_Config
 extends original_mage_core_model_config
{
    public function __construct($sourceData = null)
    {
        parent::__construct($sourceData);
    }

    /**
     * Load modules configuration
     *
     * @return Mage_Core_Model_Config
     */
    public function loadModules()
    {
        parent::loadModules();

        //dahbug::write($this->getNode('modules')->asNiceXml());

        return $this;
    }

    /**
     * Load declared modules configuration
     *
     * @param   null $mergeConfig depricated
     * @return  Mage_Core_Model_Config
     */
    protected function _loadDeclaredModules($mergeConfig = null)
    {
        $moduleFiles = $this->_getDeclaredModuleFiles();
        if (!$moduleFiles) {
            return ;
        }

        Varien_Profiler::start('config/load-modules-declaration');

        $unsortedConfig = new Mage_Core_Model_Config_Base();
        $unsortedConfig->loadString('<config/>');
        $fileConfig = new Mage_Core_Model_Config_Base();

        // load modules declarations
        foreach ($moduleFiles as $file) {
            $fileConfig->loadFile($file);
            $unsortedConfig->extend($fileConfig);
        }

        $devConfig = dahl_dev::getConfig();
        foreach ($devConfig->getModules() as $name => $data) {
            foreach ($data['declareFiles'] as $file) {
                $fileConfig->loadFile($file);
                dahbug::methods($fileConfig);
                dahbug::write($fileConfig->getNode()->asNiceXml());
                $unsortedConfig->extend($fileConfig);
            }
        }

        $moduleDepends = array();
        foreach ($unsortedConfig->getNode('modules')->children() as $moduleName => $moduleNode) {
            if (!$this->_isAllowedModule($moduleName)) {
                continue;
            }

            $depends = array();
            if ($moduleNode->depends) {
                foreach ($moduleNode->depends->children() as $depend) {
                    $depends[$depend->getName()] = true;
                }
            }
            $moduleDepends[$moduleName] = array(
                'module'    => $moduleName,
                'depends'   => $depends,
                'active'    => ('true' === (string)$moduleNode->active ? true : false),
            );
        }

        // check and sort module dependence
        $moduleDepends = $this->_sortModuleDepends($moduleDepends);

        // create sorted config
        $sortedConfig = new Mage_Core_Model_Config_Base();
        $sortedConfig->loadString('<config><modules/></config>');

        foreach ($unsortedConfig->getNode()->children() as $nodeName => $node) {
            if ($nodeName != 'modules') {
                $sortedConfig->getNode()->appendChild($node);
            }
        }

        foreach ($moduleDepends as $moduleProp) {
            $node = $unsortedConfig->getNode('modules/'.$moduleProp['module']);
            $sortedConfig->getNode('modules')->appendChild($node);
        }

        $this->extend($sortedConfig);

        Varien_Profiler::stop('config/load-modules-declaration');

        return $this;
    }

}
