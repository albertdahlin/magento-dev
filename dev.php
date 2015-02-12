<?php
define('DAHL_DEVROOT', dirname(__file__));

function buildPath() {
    $args = func_get_args();
    return implode(DIRECTORY_SEPARATOR, $args);
}
/**
 * Put your local includes or changes in local.php
 */
if (file_exists(buildPath(DAHL_DEVROOT, 'local.php'))) {
    include buildPath(DAHL_DEVROOT, 'local.php');
}


/**
 * Check if the request is for a Magento site.
 */
if (file_exists(buildPath($_SERVER['DOCUMENT_ROOT'], 'app', 'Mage.php'))) {
    include buildPath(DAHL_DEVROOT, 'magento.php');
} else {
    include buildPath(DAHL_DEVROOT, 'default.php');
}
