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

require_once 'EbayEnterprise/Eb2cOrder/controllers/OrderController.php';

class EbayEnterprise_Eb2cOrder_Controller_OrderControllerTest extends EbayEnterprise_Eb2cCore_Test_Base
{
	// @var Mock_EbayEnterprise_Eb2cOrder_GuestController
	protected $_controller;
	// @var Mock_Mage_Customer_Model_Session
	protected $_session;
	// @var Mock_Mage_Core_Controller_Request_Http
	protected $_request;

	/**
	 * mock the request, customer session, and controller instance to test with.
	 */
	public function setUp()
	{
		$this->_session = $this->getModelMockBuilder('customer/session')
			->disableOriginalConstructor()
			->setMethods(array('isLoggedIn'))
			->getMock();

		$this->_request = $this->getMockBuilder('Mage_Core_Controller_Request_Http')
			->disableOriginalConstructor()
			->setMethods(array('getParam'))
			->getMock();

		$this->_controller = $this->getMockBuilder('EbayEnterprise_Eb2cOrder_OrderController')
			->disableOriginalConstructor()
			->setMethods(array('_loadValidOrder', '_canViewOrder', 'loadLayout', 'renderLayout', 'getRequest', '_redirect'))
			->getMock();
		$this->_controller->expects($this->any())
			->method('getRequest')
			->will($this->returnValue($this->_request));
	}
	/**
	 * verify the order and shipment are setup correctly.
	 * verify the request is handled properly.
	 */
	public function testPrintOrderShipmentAction()
	{
		$this->_request->expects($this->any())
			->method('getParam')
			->will($this->returnValue('theid'));
		$this->_controller->expects($this->any())
			->method('_loadValidOrder')
			->will($this->returnValue(true));
		$this->_controller->expects($this->any())
			->method('_canViewOrder')
			->will($this->returnValue(true));
		$this->_controller->expects($this->once())
			->method('loadLayout')
			->with($this->isType('string'))
			->will($this->returnValue($this->getModelMock('core/layout')));
		// if all went well, a call to renderLayout should
		// be observed.
		$this->_controller->expects($this->once())
			->method('renderLayout')
			->will($this->returnSelf());
		$this->_controller->printOrderShipmentAction();
	}
	/**
	 * provide datasets so the printOrderShipmentAction method does not attempt
	 * to render any content.
	 * @return array()
	 */
	public function providePrintOrderFailData()
	{
		return array(
			array(false, true, 2, true),
			array(true, false, '2', false),
			array(true, true, null, true),
		);
	}
	/**
	 * if any of the following is false redirect to either to the order history page or the guest form.
	 * 	- _loadValidOrder
	 * 	- _canViewOrder
	 * 	- no shipment id is given
	 * @param bool  $loaded   whether the order was loaded or not
	 * @param bool  $viewable whether the order should be viewable
	 * @param mixed $shipId   shipment id
	 * @dataProvider providePrintOrderFailData
	 */
	public function testPrintOrderShipmentActionFailure($loaded, $viewable, $shipId, $isLoggedIn)
	{
		$this->replaceByMock('singleton', 'customer/session', $this->_session);
		$this->_request->expects($this->any())
			->method('getParam')
			->will($this->returnValue($shipId));
		$this->_controller->expects($this->any())
			->method('_loadValidOrder')
			->will($this->returnValue($loaded));
		$this->_controller->expects($this->any())
			->method('_canViewOrder')
			->will($this->returnValue($viewable));
		$this->_controller->expects($this->once())
			->method('_redirect')
			->with($this->isType('string'));
		$this->_session->expects($this->once())
			->method('isLoggedIn')
			->will($this->returnValue($isLoggedIn));
		$this->_controller->printOrderShipmentAction();
	}
}
