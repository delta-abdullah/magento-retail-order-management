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

class EbayEnterprise_Catalog_Test_Helper_Map_CategoryTest extends EbayEnterprise_Eb2cCore_Test_Base
{
    /**
     * Provide DOMNodeLists of CategoryLink/Name nodes and a collection of categories.
     *
     * @return array[]
     */
    public function provideCategoryNameNodes()
    {
        $doc = Mage::helper('eb2ccore')->getNewDomDocument();
        $doc->loadXML('
			<CategoryLinks>
				<CategoryLink import_mode="Update">
					<Name>Luma Root</Name>
				</CategoryLink>
				<CategoryLink import_mode="Update">
					<Name>Luma Root-Shoes</Name>
				</CategoryLink>
				<CategoryLink import_mode="Update">
					<Name>Luma Root-Shoes-Boots</Name>
				</CategoryLink>
				<!-- The item should end up in Luma Root/Outerwear/Jackets, but not Luma Root/Outerwear -->
				<CategoryLink import_mode="Update">
					<Name>Luma Root-Outerwear-Jackets</Name>
				</CategoryLink>
			</CategoryLinks>
		');
        $xp = new DOMXpath($doc);
        $nodes = $xp->query('/CategoryLinks/CategoryLink[@import_mode!="Delete"]/Name');
        $catCol = $this->getResourceModelMockBuilder('catalog/category_collection')
            ->setMethods(array('addAttributeToSelect', 'getColumnValues', 'getAllIds'))
            ->getMock();
        $catCol
            ->expects($this->any())
            ->method('addAttributeToSelect')
            ->with($this->identicalTo(array('name', 'path', 'id')))
            ->will($this->returnSelf());
        $ids = array(0, 1, 2, 3, 30, 31);
        $names = array('Root Catalog', 'Luma Root', 'Shoes', 'Boots', 'Outerwear', 'Jackets');
        $paths = array(
            '0', // Root Catalog
            '0/1', // Luma Root
            '0/1/2', // Luma Root-Shoes
            '0/1/2/3', // Luma Root-Shoes-Boots
            '0/1/30', // Luma Root-Outerwear
            '0/1/30/31', // Luma Root-Outerwear-Jackets
        );
        $catCol
            ->expects($this->any())
            ->method('getColumnValues')
            ->will($this->returnValueMap(array(
                array('name', $names),
                array('path', $paths),
            )));
        $catCol
            ->expects($this->any())
            ->method('getAllIds')
            ->will($this->returnValue($ids));
        return array(
            array($nodes, $catCol)
        );
    }
    /**
     * Confirm that extractCategoryIds returns the expected ids for the given xml nodes.
     *
     * @dataProvider provideCategoryNameNodes
     */
    public function testExtractCategoryIds(DOMNodeList $nodes, Mage_Catalog_Model_Resource_Category_Collection $catCol)
    {
        $this->replaceByMock('resource_model', 'catalog/category_collection', $catCol);
        $this->assertSame(
            array(1,2,3,31), // everything except Luma Root-Outerwear
            Mage::helper('ebayenterprise_catalog/map_category')->extractCategoryIds($nodes)
        );
    }
}
