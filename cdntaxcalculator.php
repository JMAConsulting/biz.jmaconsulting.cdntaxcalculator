<?php

require_once 'cdntaxcalculator.civix.php';
require_once 'civicrm_constants.php';

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
  $prop = new ReflectionProperty(get_class($form), '_id');
  if ($prop->isProtected())
    return;  
  if ($form->_id == MEM_PAGE_ID) {
    global $cdnTaxes;
    $cid = CRM_Core_Session::singleton()->get('userID');
    if ($form->_flagSubmitted && $form->_submitValues[PROVINCE_FIELD]) {
      $state = $form->_submitValues[PROVINCE_FIELD];
    }
    elseif ($cid) {
      $state = cdn_getStateProvince($cid);
    }
    if ($state && in_array($state, array_keys($cdnTaxes))) {
      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTotalTaxes($state);
      foreach ($amount[MEMBERSHIP_FIELD_ID]['options'] as $key => &$values) {
        $values['tax_rate'] = $taxes;
        $values['tax_amount'] = $values['tax_rate'] * $values['amount'] / 100;
      }
      foreach ($amount[2]['options'] as $key => &$values) {
        $values['tax_rate'] = $taxes;
        $values['tax_amount'] = $values['tax_rate'] * $values['amount'] / 100;
      }
    }
  }
}

function cdn_getStateProvince($cid) {
  $params = array(
    'contact_id' => $cid,
    'is_primary' => 1,
  );
  $address = civicrm_api3('Address', 'get', $params);
  if ($address['values']) {
    foreach ($address['values'] as $key => $value) {
      $state = $value['state_province_id'];
      break;
    }
  }
  return !empty($state) ? $state : NULL;
}

function cdntaxcalculator_civicrm_buildForm($formName, &$form) {
  if ($formName == "CRM_Contribute_Form_Contribution_Main" && $form->_id == MEM_PAGE_ID) {
    global $cdnTaxes;
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTotalTaxes();
    $form->assign('totaltaxes',json_encode($taxes));
    $form->assign('indtaxes',json_encode($cdnTaxes));
  }
  if ($formName == "CRM_Contribute_Form_Contribution_Confirm" && $form->_id == MEM_PAGE_ID) {
    $lineItems = $form->get('lineItem');
    global $cdnTaxes;
    $taxes = CRM_Utils_Array::value($form->_params[PROVINCE_FIELD], $cdnTaxes);
    if ($taxes) {
      foreach($lineItems as &$lineItem) {
        foreach($lineItem as $k => &$item) {
          if (in_array($k, array(2,12))) {
            $item['HST_GST'] = ($item['line_total'] * $taxes['HST_GST']) / 100;
            $item['PST'] = ($item['line_total'] * $taxes['PST']) / 100;
            $item['label'] .= ' ( $ ' . number_format($item['unit_price'], 2, '.', '') . ' + $ ' . $item['HST_GST'] . ' ' . $item['HST_GST_LABEL'];
            if ($taxes['PST']) {
              $item['label'] .= ' + $ ' . $item['PST'] . ' ' . $item['PST_LABEL'] . ' )';
            }
            else {
              $item['label'] .= ' )';
            }
          }
        }
      }
      $form->set('lineItem', $lineItems);
      $form->assign('lineItem', $lineItems);
    }
  }
}


function cdntaxcalculator_civicrm_pre($op, $objectName, $id, &$params) {
  if ($objectName == 'FinancialItem' && $op == 'create') {
    if ($params['financial_account_id'] == GST_HST_FA_ID) {
      // Split financial item and save
      $smarty = CRM_Core_Smarty::singleton();
      global $cdnTaxes;
      
      //FIXME: get submitted state rather than saved state
      $state = cdn_getStateProvince($params['contact_id']);
      
      if ($state && in_array($state, array_keys($cdnTaxes))) {
        $taxes = $cdnTaxes[$state];
        $pstAmount = NULL;
        if (!empty($taxes['HST_GST'])) {
          $params['description'] = ts('GST/HST');
          if (!empty($taxes['PST'])) {
            $totalAmount = ($params['amount'] * 100) / ($taxes['HST_GST'] + $taxes['PST']);
            $params['amount'] = ($totalAmount * $taxes['HST_GST']) / 100;  
            $pstAmount = ($totalAmount * $taxes['PST']) / 100;
            
            global $stateFAMapping;
            $smarty->assign('pstFinancialAccount', $stateFAMapping[$state]);
          }
        }
        elseif (!empty($taxes['PST'])) {
          $params['description'] = ts('PST');
          global $stateFAMapping;
          $params['financial_acoount_id'] = $stateFAMapping[$state];
        }
        $smarty->assign('pstAmount', $pstAmount);
      }
    }
  }
}

function cdntaxcalculator_civicrm_post($op, $objectName, $id, &$objectRef) {
  if ($objectName == 'FinancialItem' && $op == 'create') {
    $pstAmount = CRM_Core_Smarty::singleton()->get_template_vars('pstAmount');
    if ($pstAmount) {
      $itemParams = array(
        'transaction_date' => $objectRef->transaction_date,
        'contact_id' => $objectRef->contact_id,
        'currency' => $objectRef->currency,
        'amount' => $pstAmount,
        'description' => ts('PST'),
        'status_id' => $objectRef->status_id,
        'financial_account_id' => CRM_Core_Smarty::singleton()->get_template_vars('pstFinancialAccount'),
        'entity_table' => 'civicrm_line_item',
        'entity_id' => $objectRef->entity_id
      );
      $params = array(
        'entity_table' => 'civicrm_financial_item',
        'entity_id' => $id,
      );
      CRM_Core_Smarty::singleton()->assign('pstAmount', '');
      CRM_Core_Smarty::singleton()->assign('pstFinancialAccount', '');
      $financialTrxn = reset(CRM_Financial_BAO_FinancialItem::retrieveEntityFinancialTrxn($params));
      $trxnIds['id'] = $financialTrxn['financial_trxn_id'];
      CRM_Financial_BAO_FinancialItem::create($itemParams, NULL, $trxnIds);
    }
  }
}
