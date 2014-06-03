<?php
/**
 * Copyright (c) 2013-2014 eBay Enterprise, Inc.
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * 
 * @copyright   Copyright (c) 2013-2014 eBay Enterprise, Inc. (http://www.ebayenterprise.com/)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

Mage::log(sprintf('[%s] Upgrading Eb2cOrder 1.0.0.22 -> 1.1.0.0', __CLASS__), Zend_Log::INFO);
$installer = new Mage_Sales_Model_Resource_Setup('core_setup');
$installer->startSetup();
try{
	$installer->addAttribute('order', 'eb2c_order_create_request', array(
		'type' => Varien_Db_Ddl_Table::TYPE_TEXT,
		'visible' => true,
		'required' => false,
	));
} catch (Exception $e) {
	Mage::logException($e);
}
$installer->endSetup();
