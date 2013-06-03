<?php
/**
 * tests the tax calculation class.
 */
class TrueAction_Eb2c_Tax_Test_Model_CalculationTests extends EcomDev_PHPUnit_Test_Case
{
	/**
	 * @var Mage_Sales_Model_Quote (mock)
	 */
	public $quote = null;
	/**
	 * @var Mage_Sales_Model_Quote_Address (mock)
	 */
	public $shipAddress=null;
	/**
	 * @var Mage_Sales_Model_Quote_Address (mock)
	 */
	public $billAddress=null;

	/**
	 * @var ReflecitonProperty(TrueAction_Eb2c_Tax_Model_TaxDutyRequest::_xml)
	 */
	public $doc = null;

	public function setUp()
	{
		$this->quote = $this->getModelMock('sales/quote', array('getCurrencyCode'));
		$this->quote->expects($this->any())
			->method('getCurrencyCode')
			->will($this->returnValue('USD'));
		$this->shipAddress = $this->getModelMock('sales/quote_address', array('getQuote'));
		$this->shipAddress->expects($this->any())
			->method('getQuote')
			->will($this->returnValue($this->quote));
		$this->billAddress = $this->getModelMock('sales/quote_address', array('getId'));
		$this->billAddress->expects($this->any())
			->method('getId')
			->will($this->returnValue(1));
		$this->cls = new ReflectionClass(
			'TrueAction_Eb2c_Tax_Model_TaxDutyRequest'
		);
		$this->doc = $this->cls->getProperty('_doc');
		$this->doc->setAccessible(true);
	}

	/**
	 * @test
	 */
	public function testGetRateRequest()
	{
		$calc = new TrueAction_Eb2c_Tax_Model_Calculation();
		$request = $calc->getRateRequest(
			$this->shipAddress,
			$this->billAddress,
			'someclass',
			null
		);
		$doc = $this->doc->getValue($request);
		$xpath = new DOMXPath($doc);
		$this->assertSame('TaxDutyRequest', $doc->firstChild->nodeName);
		$tdRequest = $doc->firstChild;
		$this->assertSame(3, $tdRequest->childNodes->length);
		$this->assertSame('Currency', $tdRequest->firstChild->nodeName);
		$this->assertSame('USD', $tdRequest->firstChild->textContent);
	}
}
