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

class EbayEnterprise_Eb2cProduct_Model_Config extends EbayEnterprise_Eb2cCore_Model_Config_Abstract
{
	protected $_configPaths = array(
		'api_return_format'                  => 'eb2cproduct/api_return_format',
		'api_service'                        => 'eb2cproduct/api_service',
		'api_xml_ns'                         => 'eb2ccore/api/xml_namespace',

		'content_feed'                       => 'eb2ccore/feed/filetransfer_imports/content_master',
		'content_feed_event_type'            => 'eb2ccore/feed/filetransfer_imports/content_master/event_type',

		'dummy_description'                   => 'eb2cproduct/dummy/description',
		'dummy_in_stock'                      => 'eb2cproduct/dummy/in_stock',
		'dummy_manage_stock'                  => 'eb2cproduct/dummy/manage_stock',
		'dummy_price'                         => 'catalog/price/default_product_price',
		'dummy_short_description'             => 'eb2cproduct/dummy/short_description',
		'dummy_stock_quantity'                => 'eb2cproduct/dummy/stock_quantity',
		'dummy_type_id'                       => 'eb2cproduct/dummy/type_id',
		'dummy_weight'                        => 'eb2cproduct/dummy/weight',

		'i_ship_feed'                         => 'eb2ccore/feed/filetransfer_imports/i_ship',
		'i_ship_feed_event_type'              => 'eb2ccore/feed/filetransfer_imports/i_ship/event_type',

		'image_feed'                          => 'eb2ccore/feed/filetransfer_exports/eb2c_outbox',
		'image_feed_event_type'               => 'eb2ccore/feed/filetransfer_exports/image_master/outbound/message_header/event_type',
		'image_export_filename_format'        => 'eb2ccore/feed/filetransfer_exports/image_master/filename_format',
		'image_export_xsd'                    => 'eb2ccore/feed/filetransfer_exports/image_master/xsd',

		'item_feed'                           => 'eb2ccore/feed/filetransfer_imports/item_master',
		'item_feed_event_type'                => 'eb2ccore/feed/filetransfer_imports/item_master/event_type',

		'pim_export_feed'                     => 'eb2ccore/feed/filetransfer_exports/eb2c_outbox',
		'pim_export_feed_event_type'          => 'eb2cproduct/pim_export_feed/outbound/message_header/event_type',
		'pim_export_feed_cutoff_date'         => 'eb2cproduct/pim_export_feed/cutoff_date',
		'pim_export_filename_format'          => 'eb2cproduct/pim_export_feed/filename_format',
		'pim_export_xsd'                      => 'eb2cproduct/pim_export_feed/xsd',

		'pricing_feed'                        => 'eb2ccore/feed/filetransfer_imports/item_pricing',
		'pricing_feed_event_type'             => 'eb2ccore/feed/filetransfer_imports/item_pricing/event_type',

		'processor_delete_batch_size'         => 'eb2cproduct/processor_delete_batch_size',
		'processor_max_total_entries'         => 'eb2cproduct/processor_max_total_entries',
		'processor_update_batch_size'         => 'eb2cproduct/processor_update_batch_size',

		'attributes_code_list'                => 'eb2cproduct/attributes_code_list',
		'read_only_attributes'                => 'eb2cproduct/readonly_attributes',

		'ext_keys'                            => 'eb2cproduct/feed/map/ext_keys',
		'ext_keys_bool'                       => 'eb2cproduct/feed/map/ext_keys_bool',

		'link_types_es_accessory'             => 'eb2cproduct/feed/related_link_types/es_accessory',
		'link_types_es_crossselling'          => 'eb2cproduct/feed/related_link_types/es_crossselling',
		'link_types_es_upselling'             => 'eb2cproduct/feed/related_link_types/es_upselling',
	);
}
