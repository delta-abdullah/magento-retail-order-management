<?php
/**
 * @category   TrueAction
 * @package    TrueAction_Eb2c
 * @copyright  Copyright (c) 2013 True Action Network (http://www.trueaction.com)
 */
class TrueAction_Eb2c_Inventory_Model_Allocation extends Mage_Core_Model_Abstract
{
	protected $_helper;
	protected $_details;

	public function __construct()
	{
		$this->_helper = $this->_getHelper();
	}

	/**
	 * Get helper instantiated object.
	 *
	 * @return TrueAction_Eb2c_Inventory_Helper_Data
	 */
	protected function _getHelper()
	{
		if (!$this->_helper) {
			$this->_helper = Mage::helper('eb2cinventory');
		}
		return $this->_helper;
	}

	/**
	 * Get Details instantiated object.
	 *
	 * @return TrueAction_Eb2c_Inventory_Model_Details
	 */
	protected function _getDetails()
	{
		if (!$this->_details) {
			$this->_details = Mage::getModel('eb2cinventory/details');
		}
		return $this->_details;
	}

	/**
	 * Allocating all items brand new quote from eb2c.
	 *
	 * @param Mage_Sales_Model_Quote $quote, the quote to allocate iventory items in eb2c for
	 *
	 * @return string the eb2c response to the request.
	 */
	public function allocateQuoteItems($quote)
	{
		$allocationResponseMessage = '';
		try{
			// build request
			$allocationRequestMessage = $this->buildAllocationRequestMessage($quote);

			// make request to eb2c for quote items allocation
			$allocationResponseMessage = $this->_getHelper()->getCoreHelper()->callApi(
				$allocationRequestMessage,
				$this->_getHelper()->getOperationUri('allocate_inventory')
			);
		}catch(Exception $e){
			Mage::logException($e);
		}

		return $allocationResponseMessage;
	}

	/**
	 * Build  Allocation request.
	 *
	 * @param Mage_Sales_Model_Quote $quote, the quote to generate request xm from
	 *
	 * @return DOMDocument The xml document, to be sent as request to eb2c.
	 */
	public function buildAllocationRequestMessage($quote)
	{
		$domDocument = $this->_getHelper()->getDomDocument();
		$allocationRequestMessage = $domDocument->addElement('AllocationRequestMessage', null, $this->_getHelper()->getXmlNs())->firstChild;
		$allocationRequestMessage->setAttribute('requestId', $this->_getHelper()->getRequestId($quote->getEntityId()));
		$allocationRequestMessage->setAttribute('reservationId', $this->_getHelper()->getReservationId($quote->getEntityId()));
		if ($quote) {
			foreach($quote->getAllAddresses() as $addresses){
				if ($addresses){
					foreach ($addresses->getAllItems() as $item) {
						try{
							// creating quoteItem element
							$quoteItem = $allocationRequestMessage->createChild(
								'OrderItem',
								null,
								array('lineId' => $item->getId(), 'itemId' => $item->getSku())
							);

							// add quanity
							$quoteItem->createChild(
								'Quantity',
								(string) $item->getQty() // integer value doesn't get added only string
							);

							$shippingAddress = $quote->getShippingAddress();
							// creating shipping details
							$shipmentDetails = $quoteItem->createChild(
								'ShipmentDetails',
								null
							);

							// add shipment method
							$shipmentDetails->createChild(
								'ShippingMethod',
								$shippingAddress->getShippingMethod()
							);

							// add ship to address
							$shipToAddress = $shipmentDetails->createChild(
								'ShipToAddress',
								null
							);

							// add ship to address Line1
							$shipToAddress->createChild(
								'Line1',
								$shippingAddress->getStreet(1)
							);

							// add ship to address City
							$shipToAddress->createChild(
								'City',
								$shippingAddress->getCity()
							);

							// add ship to address MainDivision
							$shipToAddress->createChild(
								'MainDivision',
								$shippingAddress->getRegion()
							);

							// add ship to address CountryCode
							$shipToAddress->createChild(
								'CountryCode',
								$shippingAddress->getCountryId()
							);

							// add ship to address PostalCode
							$shipToAddress->createChild(
								'PostalCode',
								$shippingAddress->getPostcode()
							);
						}catch(Exception $e){
							Mage::logException($e);
						}
					}
				}
			}
		}
		return $domDocument;
	}

	/**
	 * update quote with allocation reponse data.
	 *
	 * @param Mage_Sales_Model_Order $quote the quote we use to get allocation reqponse from eb2c
	 * @param string $allocationResponseMessage the xml reponse from eb2c
	 *
	 * @return void
	 */
	public function processAllocation($quote, $allocationResponseMessage)
	{
		$allocationResult = array();
		if (trim($allocationResponseMessage) !== '') {
			$doc = $this->_getHelper()->getDomDocument();

			// load response string xml from eb2c
			$doc->loadXML($allocationResponseMessage);
			$i = 0;
			$allocationResponse = $doc->getElementsByTagName('AllocationResponse');
			$allocationMessage = $doc->getElementsByTagName('AllocationResponseMessage');
			foreach($allocationResponse as $response) {
				$allocationData = array(
					'lineId' => $response->getAttribute('lineId'),
					'itemId' => $response->getAttribute('itemId'),
					'qty' => (int) $allocationResponse->item($i)->nodeValue,
					'reservation_id' => $allocationMessage->item(0)->getAttribute('reservationId'),
					'reservation_expires' => Mage::getModel('core/date')->date('Y-m-d H:i:s')
				);

				if ($quoteItem = $quote->getItemById($allocationData['lineId'])) {
					// update quote with eb2c data.
					if ($result = $this->_updateQuoteWithEb2cAllocation($quoteItem, $allocationData)) {
						$allocationResult[] = $result;
					}
				}

				$i++;
			}
		}

		return $allocationResult;
	}

	/**
	 * update quote with allocation reponse data.
	 *
	 * @param Mage_Sales_Model_Quote_Item $quoteItem the item to be updated with eb2c data
	 * @param array $quoteData the data from eb2c for the quote item
	 *
	 * @return void
	 */
	protected function _updateQuoteWithEb2cAllocation($quoteItem, $quoteData)
	{
		$results = '';
		// get quote from quoteitem
		$quote = $quoteItem->getQuote();

		// save reservation data to inventory detail
		$inventoryDetails = $this->_getDetails()->loadByQuoteItemId($quoteItem->getItemId());
		$inventoryDetails->setItemId($quoteItem->getItemId())
			->setReservationId($quoteData['reservation_id'])
			->setReservationExpires($quoteData['reservation_expires'])
			->setQtyReserved($quoteData['qty'])
			->save();

		// Set the message allocation failure
		if ($quoteData['qty'] > 0 && $quoteItem->getQty() > $quoteData['qty']) {
			$results = 'Sorry, we only have ' . $quoteData['qty'] . ' of item "' . $quoteItem->getSku() . '" in stock.';
		} elseif ($quoteData['qty'] <= 0) {
			$results = 'Sorry, item "' . $quoteItem->getSku() . '" out of stock.';
		}

		return $results;
	}

	/**
	 * Rolling back allocation request.
	 *
	 * @param Mage_Sales_Model_Quote $quote, the quote to generate request xmlfrom
	 *
	 * @return void
	 */
	public function rollbackAllocation($quote)
	{
		$rollbackAllocationResponseMessage = '';
		try{
			// build request
			$rollbackAllocationRequestMessage = $this->buildRollbackAllocationRequestMessage($quote);

			// make request to eb2c for inventory rollback allocation
			$rollbackAllocationResponseMessage = $this->_getHelper()->getCoreHelper()->callApi(
				$rollbackAllocationRequestMessage,
				$this->_getHelper()->getOperationUri('rollback_allocation')
			);
		}catch(Exception $e){
			Mage::logException($e);
		}

		return $rollbackAllocationResponseMessage;
	}

	/**
	 * Build  Rollback Allocation request.
	 *
	 * @param Mage_Sales_Model_Quote $quote, the quote to generate request xml from
	 *
	 * @return DOMDocument The xml document, to be sent as request to eb2c.
	 */
	public function buildRollbackAllocationRequestMessage($quote)
	{
		$domDocument = $this->_getHelper()->getDomDocument();
		$rollbackAllocationRequestMessage = $domDocument->addElement('RollbackAllocationRequestMessage', null, $this->_getHelper()->getXmlNs())->firstChild;
		$rollbackAllocationRequestMessage->setAttribute('requestId', $this->_getHelper()->getRequestId($quote->getEntityId()));
		$rollbackAllocationRequestMessage->setAttribute('reservationId', $this->_getHelper()->getReservationId($quote->getEntityId()));

		return $domDocument;
	}
}
