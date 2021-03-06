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
 * This api exposes CiviCRM PaymentProcessor.
 *
 * @package CiviCRM_APIv3
 */

/**
 * Add/Update a PaymentProcessor.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 */
function civicrm_api3_payment_processor_create($params) {
  if (empty($params['id']) && empty($params['payment_instrument_id'])) {
    $params['payment_instrument_id'] = civicrm_api3('PaymentProcessorType', 'getvalue', [
      'id' => $params['payment_processor_type_id'],
      'return' => 'payment_instrument_id',
    ]);
  }
  return _civicrm_api3_basic_create(_civicrm_api3_get_BAO(__FUNCTION__), $params, 'PaymentProcessor');
}

/**
 * Adjust Metadata for Create action.
 *
 * The metadata is used for setting defaults, documentation & validation.
 *
 * @param array $params
 *   Array of parameters determined by getfields.
 */
function _civicrm_api3_payment_processor_create_spec(&$params) {
  $params['payment_processor_type_id']['api.required'] = 1;
  $params['is_default']['api.default'] = 0;
  $params['is_test']['api.default'] = 0;
  $params['is_active']['api.default'] = TRUE;
  $params['domain_id']['api.default'] = CRM_Core_Config::domainID();
  $params['financial_account_id']['api.default'] = CRM_Financial_BAO_PaymentProcessor::getDefaultFinancialAccountID();
  $params['financial_account_id']['api.required'] = TRUE;
  $params['financial_account_id']['title'] = ts('Financial Account for Processor');
}

/**
 * Deletes an existing PaymentProcessor.
 *
 * @param array $params
 *
 * @return array
 *   API result array
 */
function civicrm_api3_payment_processor_delete($params) {
  return _civicrm_api3_basic_delete(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Retrieve one or more PaymentProcessor.
 *
 * @param array $params
 *   Array of name/value pairs.
 *
 * @return array
 *   API result array
 */
function civicrm_api3_payment_processor_get($params) {
  return _civicrm_api3_basic_get(_civicrm_api3_get_BAO(__FUNCTION__), $params);
}

/**
 * Set default getlist parameters.
 *
 * @see _civicrm_api3_generic_getlist_defaults
 *
 * @param array $request
 *
 * @return array
 */
function _civicrm_api3_payment_processor_getlist_defaults(&$request) {
  return [
    'description_field' => [
      'payment_processor_type_id',
      'description',
    ],
    'params' => [
      'is_test' => 0,
      'is_active' => 1,
    ],
  ];
}

/**
 * Action payment.
 *
 * @param array $params
 *
 * @return array
 *   API result array.
 *
 * @throws \API_Exception
 */
function civicrm_api3_payment_processor_pay($params) {
  $processor = Civi\Payment\System::singleton()->getById($params['payment_processor_id']);
  $processor->setPaymentProcessor(civicrm_api3('PaymentProcessor', 'getsingle', ['id' => $params['payment_processor_id']]));
  try {
    $result = $processor->doPayment($params);
  }
  catch (\Civi\Payment\Exception\PaymentProcessorException $e) {
    $code = $e->getErrorCode();
    $errorData = $e->getErrorData();
    if (empty($code)) {
      $code = 'EXTERNAL_FAILURE';
    }
    throw new API_Exception('Payment failed', $code, $errorData, $e);
  }
  return civicrm_api3_create_success(array($result), $params);
}

/**
 * Action payment.
 *
 * @param array $params
 */
function _civicrm_api3_payment_processor_pay_spec(&$params) {
  $params['payment_processor_id'] = [
    'api.required' => 1,
    'title' => ts('Payment processor'),
    'type' => CRM_Utils_Type::T_INT,
  ];
  $params['amount'] = [
    'api.required' => TRUE,
    'title' => ts('Amount to pay'),
    'type' => CRM_Utils_Type::T_MONEY,
  ];
}

/**
 * Action refund.
 *
 * @param array $params
 *
 * @return array
 *   API result array.
 * @throws CiviCRM_API3_Exception
 */
function civicrm_api3_payment_processor_refund($params) {
  $processor = Civi\Payment\System::singleton()->getById($params['payment_processor_id']);
  $processor->setPaymentProcessor(civicrm_api3('PaymentProcessor', 'getsingle', array('id' => $params['payment_processor_id'])));
  if (!$processor->supportsRefund()) {
    throw API_Exception('Payment Processor does not support refund');
  }
  $result = $processor->doRefund($params);
  return civicrm_api3_create_success(array($result), $params);
}

/**
 * Action Refund.
 *
 * @param array $params
 *
 */
function _civicrm_api3_payment_processor_refund_spec(&$params) {
  $params['payment_processor_id'] = [
    'api.required' => 1,
    'title' => ts('Payment processor'),
    'type' => CRM_Utils_Type::T_INT,
  ];
  $params['amount'] = [
    'api.required' => TRUE,
    'title' => ts('Amount to refund'),
    'type' => CRM_Utils_Type::T_MONEY,
  ];
}
