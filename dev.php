<?php
define('DAAL_DEVROOT', dirname(__file__));

/**
 * Put your local includes or changes in local.php
 */
if (file_exists(DAAL_DEVROOT . '/local.php')) {
    include(DAAL_DEVROOT . '/local.php');
}

/**
 * Check if the request is for a Magento site.
 */
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/app/Mage.php')) {
    include(DAAL_DEVROOT . '/magento.php');
} else {
    include(DAAL_DEVROOT . '/default.php');
}
