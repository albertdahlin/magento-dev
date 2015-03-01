<?php
namespace MageTools;

/**
 * Product creator.
 * 
 * @copyright Copyright (C) 2015 Albert Dahlin
 * @author Albert Dahlin <info@albertdahlin.com>
 * @license GNU GPL v3.0 <http://www.gnu.org/licenses/gpl-3.0.html>
 */
class ProductCreator
 implements MageToolsModule
{
    /**
     * Product description.
     * 
     * @var string
     * @access protected
     */
    static protected $_description = <<<TEXT
Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas tempor turpis id ex tincidunt, nec imperdiet augue pharetra. Aliquam cursus eros sed convallis viverra. Nunc blandit nisl turpis, ultricies sodales sapien lobortis eget. Pellentesque at volutpat nulla. Nam lacinia risus ullamcorper convallis sagittis. Etiam congue, massa vel volutpat semper, turpis libero facilisis nulla, mollis efficitur felis urna quis odio. Nulla justo massa, molestie sed tellus dapibus, commodo tristique orci. Vestibulum sagittis lacus a imperdiet ullamcorper.

Praesent elit enim, eleifend eget justo sed, lacinia convallis urna. Aliquam erat volutpat. Pellentesque scelerisque ipsum dolor, in mattis felis mollis in. Morbi pharetra tristique ex, sed pharetra orci suscipit vel. Proin venenatis massa sit amet nulla condimentum, at consequat sapien finibus. Proin odio mi, molestie ut volutpat in, fermentum ut turpis. In in massa ut mauris egestas vulputate nec sit amet neque. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia Curae; Sed eget malesuada felis. Ut vitae risus volutpat, posuere ex ut, hendrerit nisl. Cras aliquam fringilla metus, id aliquam augue tincidunt quis.
TEXT;

    /**
     * Product short description.
     * 
     * @var string
     * @access protected
     */
    static protected $_shortDescription = 'Praesent elit enim, eleifend eget justo sed, lacinia convallis urna.';

    /**
     * Module title.
     * 
     * @static
     * @access public
     * @return string
     */
    static public function getTitle()
    {
        return 'Create Products';
    }

    /**
     * Key identifier.
     * 
     * @static
     * @access public
     * @return string
     */
    static public function getKey()
    {
        return 'p';
    }

    /**
     * Is module Mage dependant.
     * 
     * @static
     * @access public
     * @return boolean
     */
    static public function isMageDependant()
    {
        return true;
    }

    /**
     * Run module.
     * 
     * @static
     * @access public
     * @return void
     */
    static public function run()
    {
        $window = new \Dahl\PhpTerm\Window;
        $input = $window->getInput();
        $output = $window->getOutput();
        $key    = $input->getKeys();

        $output->cls()->setPos();
        echo "Enter information. Press ESC to abort.\n\n";
        if (($amount = $input->readLine('How many products? ', true, '\d')) === false) {
            echo "Exit.\n";
            return;
        }

        echo "\n\nEnter the product name.\n   %d will be replaced with the produt number.\n\n";
        if (($name = $input->readLine('Product name [Product %d] ? ')) === false) {
            echo "Exit.\n";
            return;
        }

        if ($name === '') {
            $name = 'Product %d';
        }
        $replace = false;
        if (strpos($name, '%d') !== false) {
            $replace = true;
        }

        echo "\n\nSelect category:\n";
        $rootCategory = \Mage::getModel('catalog/category')->load(1);
        if (!$rootCategory->getId()) {
            echo "\nThe root category does not exists\n";
        } else {
            self::_printCategories($rootCategory);
            if (($categoryIds = $input->readLine("Enter category ids, separate with comma (,): ", true, '\d,')) === false) {
                echo "Exit.\n";
                return;
            }
            $categoryIds = explode(',', $categoryIds);
        }

        $productName = $name;
        $adapter = \Mage::getSingleton('core/resource')->getConnection('core_read');
        $tableStatus = $adapter->showTableStatus('catalog_product_entity');
        $autoIncrement = $tableStatus['Auto_increment'];
        $websiteIds  = array(0, 1);
        $progressbar = $window->addElement('bar', 'ProgressBar')
            ->setStyle('position: fixed; top: 1; middle: 50%;')
            ->setWidth('90%')
            ->setMax($amount);

        $info = $window->addElement('info')
            ->setStyle('position: fixed; top: 3;')
            ->setText('');

        $output->cls()->setPos();

        $defaultData = array(
            'type_id'           => \Mage_Catalog_Model_Product_Type::TYPE_SIMPLE,
            'attribute_set_id'  => 4,
            'description'       => self::$_description,
            'short_description' => self::$_shortDescription,
            'name'              => $productName,
            'weight'            => 0,
            'status'            => \Mage_Catalog_Model_Product_Status::STATUS_ENABLED,
            'visibility'        => \Mage_Catalog_Model_Product_Visibility::VISIBILITY_BOTH,
            'category_ids'      => $categoryIds,
            'website_ids'       => $websiteIds,
            'tax_class_id'      => 2,
            'price'             => 12,
        );
        $stockData = array(
            'qty'                           => 100,
            'is_in_stock'                   => 1,
            'manage_stock'                  => 1,
            'use_config_enable_qty_inc'     => 1,
            'use_config_qty_increments'     => 1,
            'use_config_manage_stock'       => 1,
            'use_config_min_qty'            => 1,
            'use_config_min_sale_qty'       => 1,
            'use_config_max_sale_qty'       => 1,
            'use_config_backorders'         => 1,
            'use_config_notify_stock_qty'   => 1,
            'is_qty_decimal'                => 0,
        );
        $text = array();
        for ($i = 0; $i < $amount; $i++) {
            if ($replace) {
                $productName = sprintf($name, $autoIncrement + $i);
            }
            try {
                $product = \Mage::getModel('catalog/product')
                    ->setData($defaultData)
                    ->setName($productName)
                    ->setSku(substr(md5(microtime()), 0, 8))
                    ->setStockData($stockData)
                    ->save();

            } catch (Exception $e) {
                echo \dahbug::dump($e->getMessage() . "\n");
            }
            $text[] = "Created product \"{$productName}\"";
            if (count($text) > 6) {
                array_shift($text);
            }

            $progressbar->setPosition($i + 1);
            $info->setText(implode("\n", $text));
            $window->render();
        }
        echo "\n\nDone createing {$amount} products.\n";
    }

    /**
     * Print category tree with ids.
     * 
     * @param Mage_Catalog_Model_Category $category
     * @param int $recursion
     * @static
     * @access protected
     * @return void
     */
    static protected function _printCategories($category, $recursion = 0)
    {
        $categories = \Mage::getResourceModel('catalog/category_collection')
            ->addAttributeToFilter('parent_id', $category->getId())
            ->addAttributeToSelect(array('name'));
        foreach ($categories as $child) {
            echo "\n";
            echo str_repeat('   ', $recursion);
            echo "[{$child->getId()}]  {$child->getName()}";
            self::_printCategories($child, $recursion + 1);
        }
        echo "\n";
    }
}
