<?php


$versionInfo = Mage::getVersionInfo();
if ($versionInfo['major'] == 1) {
    switch ($versionInfo['minor']) {
        case 7:
            include('package-original1702.php');
            break;
        case 9:
            include('package-original191.php');
            break;
    }
}
class Mage_Core_Model_Design_Package
 extends original_mage_core_model_design_package
{
    /**
     * Get filename by specified theme parameters
     *
     * @param array $file
     * @param $params
     * @return string
     */
    protected function _renderFilename($file, array $params)
    {
        if ($filename = dahl_dev::getConfig()->renderFilename($file, $params)) {
            return $filename;
        }

        return parent::_renderFilename($file, $params);
    }
}
