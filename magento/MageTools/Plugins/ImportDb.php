<?php
namespace Dahl\MageTools\Plugins;
use Dahl\MageTools\AbortException;
use Dahl\MageTools\PluginAbstract;

class ImportDb
 extends PluginAbstract
{
    static protected $_remote;
    static protected $_dump;

    static public function getTitle()
    {
        return 'Import Database';
    }

    static public function getKey()
    {
        return 'i';
    }

    static public function isMageDependant()
    {
        return true;
    }


    static public function run()
    {
        $config = self::getConfig();
        $window = self::$_window;
        $input = $window->getInput();
        $output = $window->getOutput();
        $key    = $input->getKeys();

        $output->cls()->setPos();

        $options = self::_getOptions($config->getImportDb());

        if (!count($options)) {
            echo self::getUsageHelp();
            return;
        }

        $window->addElement('1')
            ->setStyle('middle: 50%; text-align: center;')
            ->setText("\nIMPORT DATABASE\n \nImport from:\n");
        $list = '';
        foreach ($options as $idx => $title) {
            $list .=  "[{$idx}]    {$title}\n";
        }

        $window->addElement('2')
            ->setStyle('middle: 50%')
            ->setText($list);

        $window->render();

        $choice = $input->readChar(
            'lrLR',
            array($key::ESC)
        );

        $window->removeElements();

        if ($choice == $key::ESC) {
            echo "Exit\n";
            return;
        }

        switch (strtolower($choice)) {
            case 'l':
                $handler = new ImportDb\LocalFile($config->getImportDbDumpdir());
                break;
            case 'r':
                $handler = new ImportDb\RemoteLocation($config->getImportDbRemote());
                break;
        }

        \dahbug::dump($handler);

        \dahbug::dump($config->getDbImport());
    }

    static public function getConfig()
    {
        return \dahl_dev::getConfig();
    }

    static public function getUsageHelp()
    {
        return <<<HELP
There is nothing to import anywhere.... :,(

HELP;
    }

    static protected function _getOptions($config)
    {
        $options = array();

        if (isset($config['dumpdir'])) {
            $options['l'] = "Local file in {$config['dumpdir']}";
        }

        if (isset($config['remote']) && count($config['remote'])) {
            $options['r'] = "Remote Location";
        }

        return $options;
    }
}
