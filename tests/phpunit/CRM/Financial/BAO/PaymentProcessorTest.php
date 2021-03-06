<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 5                                                  |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2019                                |
 +--------------------------------------------------------------------+
 | This file is a part of CiviCRM.                                    |
 |                                                                    |
 | CiviCRM is free software; you can copy, modify, and distribute it  |
 | under the terms of the GNU Affero General Public License           |
 | Version 3, 19 November 2007 and the CiviCRM Licensing Exception.   |
 |                                                                    |
 | CiviCRM is distributed in the hope that it will be useful, but     |
 | WITHOUT ANY WARRANTY; without even the implied warranty of         |
 | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.               |
 | See the GNU Affero General Public License for more details.        |
 |                                                                    |
 | You should have received a copy of the GNU Affero General Public   |
 | License and the CiviCRM Licensing Exception along                  |
 | with this program; if not, contact CiviCRM LLC                     |
 | at info[AT]civicrm[DOT]org. If you have questions about the        |
 | GNU Affero General Public License or the licensing of CiviCRM,     |
 | see the CiviCRM license FAQ at http://civicrm.org/licensing        |
 +--------------------------------------------------------------------+
 */

/**
 * Class CRM_Financial_BAO_PaymentProcessorTypeTest
 * @group headless
 */
class CRM_Financial_BAO_PaymentProcessorTest extends CiviUnitTestCase {

  public function setUp() {
    parent::setUp();
  }

  /**
   * Check method create()
   */
  public function testGetCreditCards() {
    $params = [
      'name' => 'API_Test_PP_Type',
      'title' => 'API Test Payment Processor Type',
      'class_name' => 'CRM_Core_Payment_APITest',
      'billing_mode' => 'form',
      'payment_processor_type_id' => 1,
      'is_recur' => 0,
      'domain_id' => 1,
      'accepted_credit_cards' => json_encode([
        'Visa' => 'Visa',
        'Mastercard' => 'Mastercard',
        'Amex' => 'Amex',
      ]),
    ];
    $paymentProcessor = CRM_Financial_BAO_PaymentProcessor::create($params);
    $expectedCards = [
      'Visa' => 'Visa',
      'Mastercard' => 'Mastercard',
      'Amex' => 'Amex',
    ];
    $cards = CRM_Financial_BAO_PaymentProcessor::getCreditCards($paymentProcessor->id);
    $this->assertEquals($cards, $expectedCards, 'Verify correct credit card types are returned');
  }

  /**
   * Test the processor retrieval function.
   *
   * @throws \CiviCRM_API3_Exception
   */
  public function testGetProcessors() {
    $testProcessor = $this->dummyProcessorCreate();
    $testProcessorID = $testProcessor->getID();
    $liveProcessorID = $testProcessorID + 1;

    $processors = CRM_Financial_BAO_PaymentProcessor::getPaymentProcessors(['BackOffice', 'TestMode']);
    $this->markTestIncomplete('Not working yet :-(');
    $this->assertEquals([$testProcessorID, 0], array_keys($processors), 'Only the test processor and the manual processor should be returned');

    $processors = CRM_Financial_BAO_PaymentProcessor::getPaymentProcessors(['BackOffice', 'TestMode'], [$liveProcessorID]);
    $this->assertEquals([$testProcessorID], array_keys($processors), 'Only the test processor should be returned');

    $processors = CRM_Financial_BAO_PaymentProcessor::getPaymentProcessors(['BackOffice', 'TestMode'], [$testProcessorID]);
    $this->assertEquals([$testProcessorID], array_keys($processors), 'Only the test processor should be returned');

    $processors = CRM_Financial_BAO_PaymentProcessor::getPaymentProcessors(['BackOffice', 'LiveMode']);
    $this->assertEquals([$liveProcessorID, 0], array_keys($processors), 'Only the Live processor and the manual processor should be returned');

    $processors = CRM_Financial_BAO_PaymentProcessor::getPaymentProcessors(['BackOffice', 'LiveMode'], [$liveProcessorID]);
    $this->assertEquals([$liveProcessorID], array_keys($processors), 'Only the Live processor should be returned');

  }

}
