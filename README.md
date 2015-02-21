# Magento Dev setup

### Features
* Load Magento modules from anywhere.
* All Magento logs will be printend in one file.
* Includes a cli script with some useful tools.

### Installation
Add the `dev.php` file to you `auto_prepend_file` setting in php.ini. You can also include `dev.php` in your project's `index.php`.

To use the `mage` cli toolbox, create a symlink to `<dev root>/magento/mage.sh` in any dir that is included in yor $PATH environmental var. For example:

`ln -s /var/www/magento-dev/magento/mage.sh ~/bin/mage`

Now you can just type `mage` in your terminal to start the toolbox. You need to be in your Magento root dir to enable all tools.

### Configuration

When you make a request to your webserver or run php from the terminal this dev module will first include <br/>
`<dev root>/local.php` if it exists.

Put your local includes there.

Then it will check if the request is actually to a Magento project.
If a Magento project is detected, configuration will be read in the following order:

1. &lt;dev root&gt;/magento/default.php
2. &lt;dev root&gt;/magento/local.php
3. &lt;magento root&gt;/dev/default.php
4. &lt;magento root&gt;/dev/local.php

This way you can have your global config in the first two files and the project specific config in file 3 and 4.
If you add local.php to your project's .gitignore you will enable each developer to have their own settings.

Check _&lt;dev root&gt;/magento/default.php_ for documentation on each setting.

### Load external modules or files
You can load external files with this dev module. It can be either a Magento module or just template/skin/js files.
If you want to load static files you need to configure your webserver to make them accessable through an url.

To load external modules or files, add `$config->loadExternal($path, $url)` to your config files.

If you have set up `$config->setModulePath($path)` and `$config->setModuleUrl($url)` in your global config you can just add `$config->loadExternal('example')` to load the module `example` from your module dir. Check the default.php config file for more info on how to set this up.

