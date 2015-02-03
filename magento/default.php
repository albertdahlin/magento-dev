<?php
/**
 * This file is included in a method. In this scope the $config object
 * is declared.
 * To override or add your own settings, copy this file to local.php
 * and add your changes there.
 */

$config->setLogFile('/var/www/test');
$config->setMageDevmode(true);
$config->setModulePath('~/modules');
$config->setModuleUrl('http://%s.example.com');
