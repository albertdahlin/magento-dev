<?php
define('DAHL_DEVROOT', dirname(__file__));
if (file_exists(DAHL_DEVROOT . '/local.php')) {
    include(DAHL_DEVROOT . '/local.php');
}

if (isset($_SERVER['DAHL_MAGENTO'])) {
    include(DAHL_DEVROOT . '/magento.php');
} else {
    include(dirname(__file__) . '/default.php');
}
