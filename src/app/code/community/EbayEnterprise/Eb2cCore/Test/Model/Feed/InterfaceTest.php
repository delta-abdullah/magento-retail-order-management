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

/**
 * Feed Interface Test
 *
 */
class EbayEnterprise_Eb2cCore_Test_Model_Feed_InterfaceTest extends EbayEnterprise_Eb2cCore_Test_Base
{
	/**
	 * Really the only test you can do with an interface, I think.
	 *
	 */
	public function testInterfaceExists()
	{
		$this->assertTrue(interface_exists('EbayEnterprise_Eb2cCore_Model_Feed_Interface'));
	}
}