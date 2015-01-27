<?php
/**
 * To override or add your own settings, create local.php and
 * add your changes there.
 */

$config->addData(
    array(
        /**
         * Magento Developer Mode
         */
        'developer_mode'    => true,

        /**
         * Log file for combining all magento logs
         */
        'log_file'          => '/var/log/dahbug.log',

        /** 
         * Settings for autologin
         *
         * Modes:
         *  auto        - Logs in automatically to the configured username on pageload.
         *  no_passowrd - Requires no password when logging in.
         *
        'auto_login'        => array (
            'username'  => 'admin',
            'mode'      => 'no_password',
        )
        */
    )
);
