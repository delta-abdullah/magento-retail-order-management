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

class EbayEnterprise_Eb2cPayment_Test_Model_PaypalTest
	extends EbayEnterprise_Eb2cCore_Test_Base
{
	/**
	 * empty paypal table
	 * @test
	 * @loadFixture paypalTableEmpty.yaml
	 */
	public function testPaypalTableEmpty()
	{
		$collection = Mage::getModel('eb2cpayment/paypal')->getCollection();
		$this->assertEquals(0, $collection->count());
		$this->assertInstanceOf(
			'EbayEnterprise_Eb2cPayment_Model_Paypal',
			Mage::getModel('eb2cpayment/paypal')->loadByQuoteId(1)
		);
	}

	/**
	 * Retrieves list of paypal ids for some purpose
	 * @test
	 * @loadFixture paypalTableList.yaml
	 */
	public function testPaypalTableList()
	{
		$collection = Mage::getModel('eb2cpayment/paypal')->getCollection();
		// Check that number of items the same as expected value
		$this->assertEquals(2, $collection->count());
	}

	/**
	 * test loadByQuoteId method
	 * @test
	 * @loadFixture paypalTableList.yaml
	 */
	public function testLoadByQuoteId()
	{
		$paypal = Mage::getModel('eb2cpayment/paypal');
		$paypal->loadByQuoteId(51);
		$this->assertSame(
			array(
				'paypal_id' => '1',
				'quote_id' => '51',
				'eb2c_paypal_token' => 'test-00000-023939',
				'eb2c_paypal_transaction_id' => '1234',
				'eb2c_paypal_payer_id' => '9'
			),
			$paypal->getData()
		);
	}
}
