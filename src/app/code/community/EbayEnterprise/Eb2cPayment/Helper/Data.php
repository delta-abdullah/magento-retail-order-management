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

class EbayEnterprise_Eb2cPayment_Helper_Data extends Mage_Core_Helper_Abstract
	implements EbayEnterprise_Eb2cCore_Helper_Interface
{
	const STATUS_HANDLER_PATH = 'eb2cpayment/api_status_handler';

	public $apiModel;
	protected $_operation;

	public function __construct()
	{
		$cfg = $this->getConfigModel(null);

		$this->_operation = array(
			'get_gift_card_balance'           => $cfg->apiOptStoredValueBalance,
			'get_gift_card_redeem'            => $cfg->apiOptStoredValueRedeem,
			'get_gift_card_redeem_void'       => $cfg->apiOptStoredValueRedeemVoid,
			'get_paypal_do_authorization'     => $cfg->apiOptPaypalDoAuthorization,
			'get_paypal_do_express_checkout'  => $cfg->apiOptPaypalDoExpressCheckout,
			'get_paypal_do_void'              => $cfg->apiOptPaypalDoVoid,
			'get_paypal_get_express_checkout' => $cfg->apiOptPaypalGetExpressCheckout,
			'get_paypal_set_express_checkout' => $cfg->apiOptPaypalSetExpressCheckout,
		);
	}

	/**
	 * @see EbayEnterprise_Eb2cCore_Helper_Interface::getConfigModel
	 * Get payment config instantiated object.
	 * @param mixed $store
	 * @return EbayEnterprise_Eb2cCore_Model_Config_Registry
	 */
	public function getConfigModel($store=null)
	{
		return Mage::getModel('eb2ccore/config_registry')
			->setStore($store)
			->addConfigModel(Mage::getSingleton('eb2cpayment/config'));
	}

	/**
	 * Getting the NS constant value
	 *
	 * @return string, the ns value
	 */
	public function getXmlNs()
	{
		$cfg = $this->getConfigModel(null);
		return $cfg->apiXmlNs;
	}

	/**
	 * Getting the Payment NS constant value
	 *
	 * @return string, the ns value
	 */
	public function getPaymentXmlNs()
	{
		$cfg = $this->getConfigModel(null);
		return $cfg->apiPaymentXmlNs;
	}

	/**
	 * Generate eb2c API operation Uri from configuration settings and constants
	 * @param string $optIndex, the operation index of the associative array
	 * @return string, the generated operation Uri
	 */
	public function getOperationUri($optIndex)
	{
		$cfg = $this->getConfigModel(null);
		return Mage::helper('eb2ccore')->getApiUri($cfg->apiService, $this->_operation[$optIndex]);
	}

	/**
	 * Generate eb2c API Universally unique ID used to globally identify to request.
	 * @param int $entityId, the magento sales_flat_quote entity_id
	 * @return string, the request id
	 */
	public function getRequestId($entityId)
	{
		$cfg = Mage::helper('eb2ccore')->getConfigModel(null);
		return implode('-', array(
			$cfg->clientId,
			$cfg->storeId,
			$entityId
		));
	}

	/**
	 * EBC-238: Return the configured tender type bin the card number is in.
	 * If none, empty string.
	 * @param string $pan the card number
	 * @return string the tender type
	 */
	public function getTenderType($pan)
	{
		$cfg = $this->getConfigModel();
		$tenderTypes = array('GS', 'SP', 'SV', 'VL');
		foreach($tenderTypes as $tenderType) {
			list($low, $high) = explode('-', $cfg->getConfig('svc_bin_range_' . $tenderType));
			$low = trim($low);
			$high = trim($high);
			if ($low <= $pan && $pan <= $high) {
				return $tenderType;
			}
		}
		return '';
	}
	/**
	 * EBC-238: Return the URL with the correct tender type code.
	 * @param string $optIndex the operation index of the associative array
	 * @param string $pan the card number
	 * @return string
	 */
	public function getSvcUri($optIndex, $pan)
	{
		$tenderType = $this->getTenderType($pan);
		if ($tenderType === '') {
			return '';
		}
		$uri = $this->getOperationUri($optIndex);
		return preg_replace('/GS\.xml$/', "{$tenderType}.xml", $uri);
	}
}
