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


class EbayEnterprise_Eb2cProduct_Model_Feed_Extractor
{
	const CALLBACK_CONFIG_PATH = 'eb2cproduct/feed_attribute_mappings';
	/**
	 * XPath expression to extract a sku from any Item node.
	 */
	const SKU_XPATH = 'ItemId/ClientItemId|UniqueID|ClientItemId';
	/**
	 * Array of callback configuration
	 * @var array
	 */
	protected $_callbacks = array();
	/**
	 * Constructor should load up all of the callback configuration for product
	 * feed extraction.
	 */
	public function __construct()
	{
		$cfg = Mage::helper('eb2cproduct')->getConfigModel();
		$this->_callbacks = $cfg->getConfigData(self::CALLBACK_CONFIG_PATH);
	}
	/**
	 * Extract data from a single item using the callback configuration.
	 * only callback methods on key value array with type not disabled
	 * @param  DOMXPath $xpath       DOMXPath object loaded with the DOMDocument to extract data from
	 * @param  DOMNode  $contextNode DOMNode to be used as the context for all XPath queries
	 * @return array Extracted data
	 */
	public function extractItem(DOMXPath $xpath, DOMNode $contextNode, Mage_Catalog_Model_Product $product)
	{
		$coreHelper = Mage::helper('eb2ccore/feed');
		$itemData = array();
		foreach ($this->_callbacks as $attribute => $callback) {
			if ($callback['type'] !== 'disabled') {
				$result = $xpath->evaluate($callback['xpath'], $contextNode);
				if ($this->_validateResult($result)) {
					$callback['parameters'] = array($result, $product);
					$itemData[$attribute] = $coreHelper->invokeCallback($callback);
				}
			}
		}
		return $itemData;
	}
	/**
	 * in order to determine if the result from the xpath evaluate fail
	 * because of a bad xpath expression or if the result is actually the value of
	 * expression we pass to. The evaluate method return false on failure of bad xpath expression
	 * and the actual value false if the expression we pass it is the boolean value false
	 * this method will test the return result of the evaluate if the result is false it will return
	 * false if the evualte result is a DOMNodeList object with item on it will return true or false
	 * if the DOMNodeList object don't have an item, any non boolean false value will return true
	 * @param mixed $result
	 * @return bool
	 */
	protected function _validateResult($result)
	{
		return !($result instanceof DOMNodeList && $result->length === 0);
	}

	public function extractSku(DOMXPath $xpath, DOMNode $contextNode)
	{
			return $xpath->query(self::SKU_XPATH, $contextNode)->item(0)->nodeValue;
	}
}
