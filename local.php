<?php
include('/var/www/dahbug.php');
ini_set('display_errors', 1);
if ($config->getProject() !== 'deval') {
    $_SERVER['MAGE_IS_DEVELOPER_MODE'] = 1;
}
$config->setLogFile('/var/www/log');
