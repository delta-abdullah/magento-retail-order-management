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

class EbayEnterprise_Eb2cOrder_Test_Model_Overrides_Enterprise_RmaTest
	extends EbayEnterprise_Eb2cCore_Test_Base
{
	public function tearDown()
	{
		parent::tearDown();
		// delete the previous helper
		Mage::unregister('_helper/eb2corder');
	}

	public function testRmaRewrite()
	{
		$this->assertInstanceOf(
			'EbayEnterprise_Eb2cOrder_Overrides_Model_Enterprise_Rma',
			Mage::getModel('enterprise_rma/rma')
		);
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testRmaEmailSuppressionOn($testMethod)
	{
		$this->replaceCoreConfigRegistry(array(
			'transactionalEmailer' => 'eb2c'
		));
		$testModel = $this->getModelMock('enterprise_rma/rma', array('_sendRmaEmailWithItems', 'getIsSendAuthEmail'));
		$testModel->expects($this->never())
			->method('_sendRmaEmailWithItems')
			->will($this->returnSelf());
		$testModel->expects($this->any())
			->method('getIsSendAuthEmail')
			->will($this->returnValue(true));
		$testModel->$testMethod();
	}

	/**
	 * @dataProvider dataProvider
	 */
	public function testRmaEmailSuppressionOff($testMethod)
	{
		$this->replaceCoreConfigRegistry(array(
			'transactionalEmailer' => 'mage'
		));
		$testModel = $this->getModelMock('enterprise_rma/rma', array('_sendRmaEmailWithItems', 'getIsSendAuthEmail'));
		$testModel->expects($this->once())
			->method('_sendRmaEmailWithItems')
			->will($this->returnSelf());
		$testModel->expects($this->any())
			->method('getIsSendAuthEmail')
			->will($this->returnValue(true));
		$testModel->$testMethod();
	}
}
