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

class EbayEnterprise_Eb2cInventory_Test_Model_QuantityTest
	extends EbayEnterprise_Eb2cCore_Test_Base
{
	protected $_quantity;

	/**
	 * setUp method
	 */
	public function setUp()
	{
		parent::setUp();
		$this->_quantity = Mage::getModel('eb2cinventory/quantity');
	}
	/**
	 * Data provider for the testBuildQuantityRequestMessage test. Providers a quote
	 * scripted to provider items to be used in building a request message.
	 * @return array Arg array with a quote
	 */
	public function providerBuildQuantityRequestMessage()
	{
		$quote = $this->getModelMock('sales/quote', array('getAllVisibleItems'));
		$items = array();
		for ($i = 0; $i < 4; $i++) {
			$item = $this->getModelMock('sales/quote_item', array('getSku'));
			$item
				->expects($this->any())
				->method('getSku')
				->will($this->returnValue("SKU_TEST_{$i}"));
			$items[] = $item;
		}

		$quote->expects($this->any())->method('getAllVisibleItems')->will($this->returnValue($items));

		return array(
			array($quote),
		);
	}
	/**
	 * testing Building Quantity Request Message
	 *
	 * @param Mage_Sales_Model_Quote $quote Quote to build request for
	 * @dataProvider providerBuildQuantityRequestMessage
	 */
	public function testBuildQuantityRequestMessage($quote)
	{
		// script the inventory helper to return all items given as inventoried
		$helper = $this->getHelperMock('eb2cinventory/data', array('getInventoriedItems'));
		$helper->expects($this->once())->method('getInventoriedItems')->will($this->returnArgument(0));
		$this->replaceByMock('helper', 'eb2cinventory', $helper);

		$qtyRequestMsg = Mage::helper('eb2ccore')->getNewDomDocument();
		$qtyRequestMsg->preserveWhiteSpace = false;
		$qtyRequestMsg->loadXML(
			'<?xml version="1.0" encoding="UTF-8"?>
			<QuantityRequestMessage xmlns="http://api.gsicommerce.com/schema/checkout/1.0">
			<QuantityRequest lineId="item0" itemId="SKU_TEST_0"/>
			<QuantityRequest lineId="item1" itemId="SKU_TEST_1"/>
			<QuantityRequest lineId="item2" itemId="SKU_TEST_2"/>
			<QuantityRequest lineId="item3" itemId="SKU_TEST_3"/>
			</QuantityRequestMessage>'
		);
		$qty = Mage::getModel('eb2cinventory/quantity');
		$this->assertSame(
			$qtyRequestMsg->C14N(),
			EcomDev_Utils_Reflection::invokeRestrictedMethod($qty, '_buildRequestMessage', array($quote))->C14N()
		);
	}
	public function provideForTestGetAvailStockFromResponse()
	{
		return array(
			array('non-empty-message'),
			array('empty-message')
		);
	}
	/**
	 * Test parsing quantity data from a quantity response.
	 * Test handling of empty response message.
	 * @dataProvider provideForTestGetAvailStockFromResponse
	 */
	public function testGetAvailStockFromResponse($scenario)
	{
		$data = array(
			'non-empty-message' => array(
				'<?xml version="1.0" encoding="UTF-8"?>
				<QuantityResponseMessage xmlns="http://api.gsicommerce.com/schema/checkout/1.0">
					<QuantityResponse itemId="1234-TA" lineId="1">
						<Quantity>1020</Quantity>
					</QuantityResponse>
					<QuantityResponse itemId="4321-TA" lineId="1">
						<Quantity>55</Quantity>
					</QuantityResponse>
				</QuantityResponseMessage>',
				array('1234-TA' => 1020, '4321-TA' => 55)
			),
			'empty-message' => array(' ', array())
		);

		list($message, $result) = $data[$scenario];
		$this->assertSame(
			$result,
			Mage::getModel('eb2cinventory/quantity')->getAvailableStockFromResponse($message)
		);
	}
	protected function _mockQuoteItem($sku, $name, $qty)
	{
		$item = $this->getModelMock('sales/quote_item', array('getSku', 'getQty', 'getName'));
		$item->expects($this->any())->method('getSku')->will($this->returnValue($sku));
		$item->expects($this->any())->method('getName')->will($this->returnValue($name));
		$item->expects(is_null($qty) ? $this->never() : $this->any())->method('getQty')->will($this->returnValue($qty));
		return $item;
	}
	/**
	 * Test updating a quote with the response from the inventory service.
	 * When the available quantity is greater or equal to than the requested quantity, it should not change.
	 * When the available stock is less than the request quantity and greater than 0, it should be set to the available quantity.
	 * When the available stock is 0, the item should be removed from the quote.
	 */
	public function testUpdateQuoteWithResponse()
	{
		$response = '</MockInventoryQuantityResponse>';
		$availSku = 'sku-123';
		$availName = 'Available';
		$availStock = 500;
		$limitedSku = 'sku-234';
		$limitedName = 'Limited';
		$limitedStock = 2;
		$unavailSku = 'sku-345';
		$unavailName = 'Unavailable';
		$nonManagedSku = 'sku-456';
		$nonManagedName = 'Non-Managed';

		$availableStock = array(
			$availSku => $availStock,
			$limitedSku => $limitedStock,
			$unavailSku => 0,
		);
		$dataHelper = $this->getHelperMock('eb2cinventory/data', array('getInventoriedItems'));
		$this->replaceByMock('helper', 'eb2cinventory', $dataHelper);
		$helper = $this->getHelperMock('eb2cinventory/quote', array('removeItemFromQuote', 'updateQuoteItemQuantity'));
		$this->replaceByMock('helper', 'eb2cinventory/quote', $helper);
		$quantity = $this->getModelMock('eb2cinventory/quantity', array('getAvailableStockFromResponse'));
		$quantity
			->expects($this->any())
			->method('getAvailableStockFromResponse')
			->with($this->identicalTo($response))
			->will($this->returnValue($availableStock));
		$quote = $this->getModelMock('sales/quote', array('getAllVisibleItems'));

		// available item request inventory < available stock
		$availableItem = $this->_mockQuoteItem($availSku, $availName, $availStock - 1);
		// limited item request inventory > available stock
		$limitedStockItem = $this->_mockQuoteItem($limitedSku, $limitedName, $limitedStock + 1);
		// unavailable item request quantity > 0 and available stock === 0
		$unavailItem = $this->_mockQuoteItem($unavailSku, $unavailName, 1);
		// non-managed items don't really matter - they won't be sent and won't be returned in the response
		// passing null for qty to _mockQuoteItem will ensure qty is never checked
		$nonManagedItem = $this->_mockQuoteItem($nonManagedSku, $nonManagedName, null);
		$items = array($availableItem, $limitedStockItem, $unavailItem, $nonManagedItem);

		$dataHelper->expects($this->any())
			->method('getInventoriedItems')
			->will($this->returnArgument(0));
		$helper
			->expects($this->once())
			->method('removeItemFromQuote')
			->with($this->identicalTo($quote), $this->identicalTo($unavailItem))
			->will($this->returnSelf());
		$helper
			->expects($this->once())
			->method('updateQuoteItemQuantity')
			->with($this->identicalTo($quote), $this->identicalTo($limitedStockItem), $this->identicalTo($limitedStock))
			->will($this->returnSelf());
		$quote
			->expects($this->any())
			->method('getAllVisibleItems')
			->will($this->returnValue($items));

		$quantity->updateQuoteWithResponse($quote, $response);
	}
	/**
	 * When given a false-y value as the response message - empty string, null, false, etc. -
	 * this method should just return without taking any action.
	 */
	public function testUpdateQuoteWithEmptyResponseDoesNothing()
	{
		$response = '';
		$quote = $this->getModelMock('sales/quote', array('getAllItems'));
		$quantity = $this->getModelMock('eb2cinventory/quantity', array('getAvailableStockFromResponse'));
		// quote shouldn't be updated so should not be saved
		$quote->expects($this->never())->method('getAllItems');
		// don't even attempt to get inventory data from an empty response
		$quantity->expects($this->never())->method('getAvailableStockFromResponse');

		$this->assertSame($quantity, $quantity->updateQuoteWithResponse($quote, $response));
	}
}
