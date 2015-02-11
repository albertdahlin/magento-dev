<?php
define('DAHL_DEVROOT', dirname(__file__));

/**
 * Put your local includes or changes in local.php
 */
if (file_exists(DAHL_DEVROOT . '/local.php')) {
    include(DAHL_DEVROOT . '/local.php');
}

/**
 * Check if the request is for a Magento site.
 */
if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/app/Mage.php')) {
    include(DAHL_DEVROOT . '/magento.php');
} else {
    include(DAHL_DEVROOT . '/default.php');
}
