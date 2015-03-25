<?php
define('DAHL_DEVROOT', dirname(__file__));
if (
    strpos($_SERVER['SCRIPT_NAME'], 'composer') !== false
    || strpos($_SERVER['SCRIPT_NAME'], 'magerun') !== false
    || strpos($_SERVER['SCRIPT_NAME'], 'cron.php') !== false
    || strpos($_SERVER['SCRIPT_NAME'], 'install.php') !== false
) {

/**
 * Will on only execute if it is a PHP process and not a phar file or
 * other type of binary file.
 */
if (PHP_SAPI === 'cli'
    && isset($_SERVER['_'])
    && PHP_BINDIR . DIRECTORY_SEPARATOR . 'php' !== $_SERVER['_']) {
    return;
}

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
if (isset($_SERVER['DOCUMENT_ROOT'])) {
    if (file_exists(buildPath($_SERVER['DOCUMENT_ROOT'], 'app', 'Mage.php'))) {
        define('DAHL_MAGEROOT', $_SERVER['DOCUMENT_ROOT']);
    }
}
if (isset($_SERVER['PWD']) && !defined('DAHL_MAGEROOT')) {
    if (file_exists(buildPath($_SERVER['PWD'], 'app', 'Mage.php'))) {
        define('DAHL_MAGEROOT', $_SERVER['PWD']);
    } else if (file_exists(buildPath(dirname($_SERVER['PWD']), 'app', 'Mage.php'))) {
        define('DAHL_MAGEROOT', dirname($_SERVER['PWD']));
    }
}

if (defined('DAHL_MAGEROOT')) {
    include buildPath(DAHL_DEVROOT, 'magento.php');
} else {
    include buildPath(DAHL_DEVROOT, 'default.php');
}

