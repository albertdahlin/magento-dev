<?php
include('stream-original.php');
class Zend_Log_Writer_Stream
 extends original_zend_log_writer_stream
{
    public function __construct($streamOrUrl, $mode = null)
    {
        $config = whdev::getConfig();
        if ($config->getLogFile()) {
            $streamOrUrl = $config->getLogFile();
        }
        parent::__construct($streamOrUrl, $mode);
    }
}
