<?php
/**
 * This file is included in a method. In this scope the $config object
 * is declared.
 * To override or add your own settings, copy this file to local.php
 * and add your changes there.
 */

/**
 * All Magento log and exceptions will be printed to this file
 * instead.
 */
$config->setLogFile('/var/www/test');
/**
 * Enable Magento Developer mode.
 */
$config->setMageDevmode(true);
/**
 * If you have all your modules in one dir you can set this dir
 * and url here.
 * Then you can load a module like this:
 * $config->loadExternal('some-module');
 * 
 * This will load a module from /var/www/modules/some-module and include
 * static fils on http://some-module.example.com
 * 
 */
$config->setModulePath('/var/www/modules');
$config->setModuleUrl('http://%s.example.com');
