
<?php

require_once 'cdntaxcalculator.civix.php';
global $cdnTaxes;

$cdnTaxes = array(
  1101 => array( // British Columbia
    'HST_GST' => 5,
    'PST' => 7,
  ),
  1100 => array( // Alberta
    'HST_GST' => 5,
    'PST' => NULL,
  ),
  1111 => array( // Saskatchewan
    'HST_GST' => 5,
    'PST' => 5,
  ),
  1102 => array( // Manitoba
    'HST_GST' => 5,
    'PST' => 8,
  ),
  1108 => array( // Ontario
    'HST_GST' => 13,
    'PST' => NULL,
  ),
  1110 => array( // Québec
    'HST_GST' => 5,
    'PST' => 9.975,
  ),
  1103 => array( // New Brunswick
    'HST_GST' => 13,
    'PST' => NULL,
  ),
  1106 => array( // Nova Scotia
    'HST_GST' => 15,
    'PST' => NULL,
  ),
  1109 => array( // Prince Edward Island
    'HST_GST' => 14,
    'PST' => NULL,
  ),
  1104 => array( // Newfoundland and Labrador
    'HST_GST' => 13,
    'PST' => NULL,
  ),
);

/**
 * Implementation of hook_civicrm_config
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function cdntaxcalculator_civicrm_config(&$config) {
  _cdntaxcalculator_civix_civicrm_config($config);
}

/**
 * Implementation of hook_civicrm_xmlMenu
 *
 * @param $files array(string)
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function cdntaxcalculator_civicrm_xmlMenu(&$files) {
  _cdntaxcalculator_civix_civicrm_xmlMenu($files);
}

/**
 * Implementation of hook_civicrm_install
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function cdntaxcalculator_civicrm_install() {
  _cdntaxcalculator_civix_civicrm_install();
}

/**
 * Implementation of hook_civicrm_uninstall
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function cdntaxcalculator_civicrm_uninstall() {
  _cdntaxcalculator_civix_civicrm_uninstall();
}

/**
 * Implementation of hook_civicrm_enable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function cdntaxcalculator_civicrm_enable() {
  _cdntaxcalculator_civix_civicrm_enable();
}

/**
 * Implementation of hook_civicrm_disable
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function cdntaxcalculator_civicrm_disable() {
  _cdntaxcalculator_civix_civicrm_disable();
}

/**
 * Implementation of hook_civicrm_upgrade
 *
 * @param $op string, the type of operation being performed; 'check' or 'enqueue'
 * @param $queue CRM_Queue_Queue, (for 'enqueue') the modifiable list of pending up upgrade tasks
 *
 * @return mixed  based on op. for 'check', returns array(boolean) (TRUE if upgrades are pending)
 *                for 'enqueue', returns void
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function cdntaxcalculator_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _cdntaxcalculator_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implementation of hook_civicrm_managed
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function cdntaxcalculator_civicrm_managed(&$entities) {
  _cdntaxcalculator_civix_civicrm_managed($entities);
}

/**
 * Implementation of hook_civicrm_caseTypes
 *
 * Generate a list of case-types
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function cdntaxcalculator_civicrm_caseTypes(&$caseTypes) {
  _cdntaxcalculator_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implementation of hook_civicrm_alterSettingsFolders
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function cdntaxcalculator_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _cdntaxcalculator_civix_civicrm_alterSettingsFolders($metaDataFolders);
}


function cdntaxcalculator_civicrm_buildAmount($pageType, &$form, &$amount) {
  if ($form->_id == 1 && $pageType == 'membership') {
    global $cdnTaxes;
    if ($form->_flagSubmitted) {
      $state = $form->_submitValues['state_province-Primary'];
    }
    else {
      $state = cdn_getStateProvince($cid);
    }
    $cid = CRM_Core_Session::singleton()->get('userID');
    if ($state) {
      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTotalTaxes($state);
      foreach ($amount[3]['options'] as $key => &$values) {
        $values['tax_rate'] = $taxes;
        $values['tax_amount'] = $values['tax_rate'] * $values['amount'] / 100;
      }
    }
  }
}


function cdn_getStateProvince($cid) {
  $params = array(
    'contact_id' => $cid,
  );
  $address = civicrm_api3('Address', 'getsingle', $params);
  return isset($address['state_province_id']) ? $address['state_province_id'] : NULL;
}

function cdntaxcalculator_civicrm_buildForm($formName, &$form) {
}

function cdntaxcalculator_civicrm_postProcess($formName, &$form) {
}


