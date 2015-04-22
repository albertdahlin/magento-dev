<?php
include('stream-original.php');
class Zend_Log_Writer_Stream
 extends original_zend_log_writer_stream
{
    protected $_logFileStream = null;
    public function __construct($streamOrUrl, $mode = null)
    {
        $config = dahl_dev::getConfig();
        if ($config->getLogFile()) {
            $this->_logFileStream = $streamOrUrl;
            $streamOrUrl = $config->getLogFile();
        }
        parent::__construct($streamOrUrl, $mode);
    }

    public function write($message)
    {
        $line = "\033[32;1m=== " . $this->_logFileStream . " ===\033[39;0m\n";
        @fwrite($this->_stream, $line);

        parent::write($message);
    }
}
