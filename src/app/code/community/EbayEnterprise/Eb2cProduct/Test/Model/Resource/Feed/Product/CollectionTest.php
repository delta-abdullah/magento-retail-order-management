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

class EbayEnterprise_Eb2cProduct_Test_Model_Resource_Feed_Product_CollectionTest extends EbayEnterprise_Eb2cCore_Test_Base
{
	public function testGetItemId()
	{
		$product = Mage::getModel('catalog/product', array('sku' => 'sku-12345'));

		$collection = $this->getResourceModelMockBuilder('eb2cproduct/feed_product_collection')
			->disableOriginalConstructor()
			->setMethods(null)
			->getMock();

		$this->assertSame(
			'sku-12345',
			EcomDev_Utils_Reflection::invokeRestrictedMethod(
				$collection,
				'_getItemId',
				array($product)
			)
		);
	}
}