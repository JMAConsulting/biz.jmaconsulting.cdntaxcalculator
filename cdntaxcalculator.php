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

/**
 * Implements hook_civicrm_buildAmount().
 *
 * FIXME: document..
 *
 * This gets called by priceset in particular, on both backend and frontend forms.
 * NB: the code below explicitely checks if the form is a membership form.
 */
function cdntaxcalculator_civicrm_buildAmount($pageType, &$form, &$amount) {
  // FIXME: what is this for?
  # $prop = new ReflectionProperty(get_class($form), '_id');
  # if ($prop->isProtected()) {
  #  return;
  # }

  // Based on:
  // wp-woo-civi-pmpro-sync/includes/sync/woo-civi-sync-membership.php
  $priceSetId = $form->get('priceSetId');

  if (empty($priceSetId)) {
    return;
  }

  $feeBlock =& $amount;

  if (!is_array($feeBlock) || empty($feeBlock)) {
    return;
  }

  if ($pageType != 'membership' && $pageType != 'event') {
    return;
  }

  // These are also checked in select_province.tpl to see if we should display
  // a mention about taxes being calculated based on the contact's address,
  // as well as to check whether to popup if we don't have a province.
  $has_taxable_amounts = CRM_Cdntaxcalculator_BAO_CDNTaxes::hasTaxableAmounts($feeBlock);
  $has_address_based_taxes = ($has_taxable_amounts && $pageType != 'event');

  $contact_id = $form->_contactID;
  $province_id = NULL;

  if (!empty($_GET['cdntax_province_id'])) {
    $province_id = intval($_GET['cdntax_province_id']);
  }

  if (empty($contact_id) && !empty($_GET['contactId'])) {
    // FIXME: when is this used?
    // FIXME: potential info leak if we let users lookup provinces of any contact?
    $contact_id = $_GET['contactId'];
  }

  if (empty($province_id) && !empty($contact_id)) {
    $province_id = cdn_getStateProvince($contact_id);
  }

  // This is necessary if returning back, or 'confirm'/processing/thankyou page.
  if (empty($province_id)) {
    $session = CRM_Core_Session::singleton();
    $province_id = $session->get('cdntax_province_id');
  }

  if ($province_id) {
    $province_name = CRM_Core_PseudoConstant::stateProvince($province_id);
    $form->assign('cdntaxcalculator_province_name', $province_name);
  }

  // Province selection does not apply to events, because the tax rate
  // is always based on the province of provision.
  // FIXME: we can probably remove this now that we have "has_address_based_taxes".
  if ($pageType != 'event') {
    CRM_Core_Region::instance('page-footer')->add(array(
      'template' => 'CRM/Cdntaxcalculator/select_province.tpl',
    ));
  }

  $settings = [
    'province_id' => $province_id,
    'province_name' => $province_name,
    'has_taxable_amounts' => $has_taxable_amounts,
    'has_address_based_taxes' => $has_address_based_taxes,
  ];

  CRM_Core_Resources::singleton()->addSetting(array(
    'cdntaxcalculator' => $settings,
  ));

  if ($pageType == 'event') {
    $event_id = $form->get('id');
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id);
  }
  elseif ($contact_id) {
    // FIXME: this should set the province_id so that the user cannot change it?
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForContact($contact_id);
  }
  elseif ($province_id) {
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTotalTaxes($province_id);
  }
  else {
    // At this point, we're assuming that it's not an event
    // and that the select_province.tpl popup will handle the workflow.
    return;
  }

  foreach ($feeBlock as &$fee) {
    if (!is_array($fee['options'])) {
      continue;
    }

    foreach ($fee['options'] as &$option) {
      // Checking for tax_rate is a way to check if the priceset field is taxable.
      // This assumes that the global tax rate is set to non-zero.
      if (!empty($option['tax_rate'])) {
        $option['tax_rate'] = $taxes['TAX_TOTAL'];
        $option['tax_amount'] = $option['tax_rate'] * $option['amount'] / 100;
        $has_taxable_amounts = TRUE;
      }
    }
  }

  $form->assign('taxRates', $taxes);

  // This is kept for later:
  // - to show tax rates on the confirm page
  // - to avoid re-asking for the province if the user clicks 'back'.
  $session = CRM_Core_Session::singleton();
  $session->set('cdntax_province_id', $province_id);
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

/**
 * Implements hook_civicrm_buildForm().
 */
function cdntaxcalculator_civicrm_buildForm($formName, &$form) {
  if (in_array($formName, ['CRM_Contribute_Form_Contribution_Confirm', 'CRM_Contribute_Form_Contribution_ThankYou'])) {
    $session = CRM_Core_Session::singleton();
    $province_id = $session->get('cdntax_province_id');

    if ($province_id) {
      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTotalTaxes($province_id);
      $form->assign('taxRates', $taxes);
    }
  }

  // This tax override applies to backend "new membership" form
  // when not using a priceset.
  if ($formName == 'CRM_Member_Form_Membership' && $form->_action & CRM_Core_Action::ADD && $form->_contactID) {
    $taxRates = CRM_Core_Smarty::singleton()->get_template_vars('taxRates');
    $taxRates = json_decode($taxRates, TRUE);
    $contact_id = $form->_contactID;

    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTotalTaxesForContact($contact_id);

    foreach ($taxRates as &$values) {
      $values = $taxes['TAX_TOTAL'] + 1; // FIXME ??
    }

    $form->assign('taxRates', json_encode($taxRates));
  }

  if ($formName == "CRM_Event_Form_Registration_Confirm") {
    $event_id = $form->get('id');
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id);
    $form->assign('taxRates', $taxes);
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
