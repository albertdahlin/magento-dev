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

    /**
     * Get skin file url
     *
     * @param string $file
     * @param array $params
     * @return string
     */
    public function getSkinUrl($file = null, array $params = array())
    {
        Varien_Profiler::start(__METHOD__);
        if (empty($params['_type'])) {
            $params['_type'] = 'skin';
        }
        if (empty($params['_default'])) {
            $params['_default'] = false;
        }
        $this->updateParamDefaults($params);

        /**
         * 1.7 fix
         */
        if (isset($this->_fallback)) {
            $fallback = $this->_fallback->getFallbackScheme(
                $params['_area'],
                $params['_package'],
                $params['_theme']
            );
        } else {
            $fallback = array(
                array(),
                array('_theme' => $this->getFallbackTheme()),
                array('_theme' => self::DEFAULT_THEME),
            );
        }

        if (!empty($file)) {
            $result = $this->_fallback(
                $file,
                $params,
                $fallback
            );
            if ($result === 1) {
                return dahl_dev::getConfig()->renderSkinUrl($file, $params);
            }
        }
        $result = $this->getSkinBaseUrl($params) . (empty($file) ? '' : $file);
        Varien_Profiler::stop(__METHOD__);
        return $result;
    }

    /**
     * Check whether requested file exists in specified theme params
     *
     * Possible params:
     * - _type: layout|template|skin|locale
     * - _package: design package, if not set = default
     * - _theme: if not set = default
     * - _file: path relative to theme root
     *
     * @see Mage_Core_Model_Config::getBaseDir
     * @param string $file
     * @param array $params
     * @return string|false
     */
    public function validateFile($file, array $params)
    {
        if ($fileName = dahl_dev::getConfig()->renderFilename($file, $params)) {
            return $fileName;
        }
        $fileName = parent::_renderFilename($file, $params);
        $testFile = (empty($params['_relative']) ? '' : Mage::getBaseDir('design') . DS) . $fileName;
        if (!file_exists($testFile)) {
            return false;
        }
        return $fileName;
    }
}
