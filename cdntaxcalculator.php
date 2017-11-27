<?php

require_once 'cdntaxcalculator.civix.php';
require_once 'civicrm_constants.php';

use CRM_CiviDiscount_ExtensionUtil as E;

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
function cdntaxcalculator_civicrm_buildAmount($pageType, &$form, &$feeBlock) {
  // FIXME: what is this for?
  # $prop = new ReflectionProperty(get_class($form), '_id');
  # if ($prop->isProtected()) {
  #  return;
  # }

  $session = CRM_Core_Session::singleton();
  $formName = get_class($form);

  $priceSetId = $form->get('priceSetId');

  if (empty($priceSetId)) {
    return;
  }

  if (!is_array($feeBlock) || empty($feeBlock)) {
    return;
  }

  // These are also checked in select_province.tpl to see if we should display
  // a mention about taxes being calculated based on the contact's address,
  // as well as to check whether to popup if we don't have a province.
  $has_taxable_amounts = CRM_Cdntaxcalculator_BAO_CDNTaxes::hasTaxableAmounts($feeBlock);
  $has_address_based_taxes = ($has_taxable_amounts && $pageType != 'event');

  if (!$has_taxable_amounts) {
    return;
  }

  $contact_id = $form->_contactID;
  $province_id = NULL;
  $country_id = NULL;
  $country_name = '';
  $taxes = [];

  $form_name = get_class($form);

  if ($form_name == 'CRM_Event_Form_ParticipantFeeSelection') {
    $event_id = $form->_eventId;
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id, $contact_id);
  }
  elseif ($pageType == 'event') {
    $event_id = $form->get('id');
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id, $contact_id);
    $province_id = $taxes['province_id'];
  }
  else {
    // Country/Province ID was passed an an URL argument
    // ex: redirecting after selecting a province from the popup.
    if (!empty($_GET['cdntax_country_id'])) {
      $country_id = intval($_GET['cdntax_country_id']);
    }

    if (!empty($_GET['cdntax_province_id'])) {
      $province_id = intval($_GET['cdntax_province_id']);
    }

    // Necessary if returning back from the 'confirm' page.
    // This is a bit dangerous and could cause weird bugs in the backend,
    // hence only using on the front-end contribution form.
    if (empty($province_id) && $formName == 'CRM_Contribute_Form_Contribution_Main') {
      $province_id = $session->get('cdntax_province_id');
    }

    // The user is logged-in.
    if (empty($province_id) && !empty($contact_id)) {
      $province_id = cdn_getStateProvince($contact_id);
    }

    // FIXME: when is this used?
    // FIXME: potential info leak if we let users lookup provinces of any contact?
    if (empty($province_id) && empty($contact_id) && !empty($_GET['contactId'])) {
      $contact_id = $_GET['contactId'];
      $province_id = cdn_getStateProvince($contact_id);
    }

    if (empty($country_id)) {
      $country_id = $session->get('cdntax_country_id');
    }

    if ($country_id) {
      $country_name = CRM_Core_PseudoConstant::country($country_id);
      $form->assign('cdntaxcalculator_location_name', $country_name);
    }

    if ($province_id) {
      $province_name = CRM_Core_PseudoConstant::stateProvince($province_id);
      $form->assign('cdntaxcalculator_location_name', $province_name);

      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForProvince($province_id);
    }
  }

  $settings = [
    'country_id' => $country_id,
    'country_name' => $country_name,
    'province_id' => $province_id,
    'province_name' => $province_name,
    'has_taxable_amounts' => $has_taxable_amounts,
    'has_address_based_taxes' => $has_address_based_taxes,
  ];

  CRM_Core_Resources::singleton()->addSetting(array(
    'cdntaxcalculator' => $settings,
  ));

  CRM_Core_Region::instance('page-footer')->add(array(
    'template' => 'CRM/Cdntaxcalculator/select_province.tpl',
  ));

  // We always apply this, because the tax rate might be 0%
  // for non-Canada, therefore we need to remove the default tax
  // that CiviCRM may have added.
  CRM_Cdntaxcalculator_BAO_CDNTaxes::applyTaxRatesToPriceset($feeBlock, $taxes);

  $form->assign('taxRates', $taxes);

  // FIXME? This should not be required, but without this, the individual
  // line items were showing the old tax rates/amount on a contribution page
  // with numeric textfields.
  $priceSet = $form->_priceSet;
  $priceSet['fields'] = $feeBlock;
  $form->assign('priceSet', $priceSet);

  // This is kept for later:
  // - to show tax rates on the confirm page
  // - to avoid re-asking for the province if the user clicks 'back'.
  $session->set('cdntax_province_id', $province_id);
  $session->set('cdntax_country_id', $country_id);
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
  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    // Sets the province to the valued selected from the location popup.
    // Tax calculations on this form are handled by buildAmount
    $session = CRM_Core_Session::singleton();
    $defaults = [];

    if (!empty($_GET['cdntax_country_id'])) {
      $defaults['billing_country_id-5'] = intval($_GET['cdntax_country_id']);
    }

    if (!empty($_GET['cdntax_province_id'])) {
      $defaults['billing_state_province_id-5'] = intval($_GET['cdntax_province_id']);
    }

    $form->setDefaults($defaults);
  }
  elseif (in_array($formName, ['CRM_Contribute_Form_Contribution_Confirm', 'CRM_Contribute_Form_Contribution_ThankYou'])) {
    $session = CRM_Core_Session::singleton();
    $province_id = NULL;

    if (!empty($form->_params['billing_state_province_id-5'])) {
      $province_id = $form->_params['billing_state_province_id-5'];
    }
    else {
      $province_id = $session->get('cdntax_province_id');
    }

    if ($province_id) {
      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForProvince($province_id);

      if (!empty($form->_lineItem)) {
        CRM_Cdntaxcalculator_BAO_CDNTaxes::recalculateTaxesOnLineItems($form->_lineItem, $taxes);
      }

      $form->assign('taxRates', $taxes);
    }
  }

  // This tax override applies to backend "new membership" form
  // when not using a priceset.
  if ($formName == 'CRM_Member_Form_Membership' && $form->_action & CRM_Core_Action::ADD && $form->_contactID) {
    $taxRates = CRM_Core_Smarty::singleton()->get_template_vars('taxRates');
    $taxRates = json_decode($taxRates, TRUE);
    $contact_id = $form->_contactID;

    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForContact($contact_id);
    foreach ($taxRates as &$values) {
      $values = $taxes['TAX_TOTAL'];
    }

    $form->assign('taxRates', json_encode($taxRates));
  }
  elseif ($formName == 'CRM_Contribute_Form_Contribution' && $form->_action & CRM_Core_Action::UPDATE && $form->_contactID) {
    $taxRates = CRM_Core_Smarty::singleton()->get_template_vars('taxRates');
    $taxRates = json_decode($taxRates, TRUE);
    $contact_id = $form->_contactID;

    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForContact($contact_id);
    foreach ($taxRates as &$values) {
      $values = $taxes['TAX_TOTAL'];
    }

    $form->assign('taxRates', json_encode($taxRates));
  }
  elseif ($formName == 'CRM_Member_Form_MembershipRenewal' && $form->_action == CRM_Core_Action::RENEW && $form->_contactID) {
    // This form doesn't seem to use the 'taxRates' variable,
    // so we have to hack the allMembershipInfo variable, which includes pre-calculated total amounts.
    // see: CRM/Member/Form/MembershipRenewal.php
    $contact_id = $form->_contactID;
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForContact($contact_id);

    $allMembershipTypeDetails = CRM_Member_BAO_Membership::buildMembershipTypeValues($form);
    $allMembershipInfo = array();

    $taxRates = CRM_Core_PseudoConstant::getTaxRates();

    foreach ($taxRates as $ft => &$values) {
      $taxRates[$ft] = $taxes['TAX_TOTAL'];
    }

    // This is to fetch the default financial_type_id,
    // normally fetched from $defaults, but that's complicated in this case,
    // besides, buildForm() has already run.
    $e = $form->getElement('financial_type_id');
    $default_financial_type = $e->_values[0];

    $taxRate = CRM_Utils_Array::value($default_financial_type, $taxRates);
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForContact($contact_id);

    $invoiceSettings = CRM_Core_BAO_Setting::getItem(CRM_Core_BAO_Setting::CONTRIBUTE_PREFERENCES_NAME, 'contribution_invoice_settings');
    // FIXME: 4.7: $invoiceSettings = Civi::settings()->get('contribution_invoice_settings');

    // auto renew options if enabled for the membership
    $options = CRM_Core_SelectValues::memberAutoRenew();

    foreach ($allMembershipTypeDetails as $key => $membershipType) {
      if (empty($membershipType['is_active'])) {
        continue;
      }

      $taxAmount = NULL;
      $totalAmount = CRM_Utils_Array::value('minimum_fee', $membershipType);

      if (CRM_Utils_Array::value($membershipType['financial_type_id'], $taxRates)) {
        $taxAmount = ($taxRate / 100) * CRM_Utils_Array::value('minimum_fee', $membershipType);
        $totalAmount = $totalAmount + $taxAmount;
      }

      // build membership info array, which is used to set the payment information block when
      // membership type is selected.
      $allMembershipInfo[$key] = array(
        'financial_type_id' => CRM_Utils_Array::value('financial_type_id', $membershipType),
        'total_amount' => CRM_Utils_Money::format($totalAmount, NULL, '%a'),
        'total_amount_numeric' => $totalAmount,
      );

      if ($taxAmount) {
        $allMembershipInfo[$key]['tax_message'] = E::ts("Includes %1 amount of %2", array(1 => CRM_Utils_Array::value('tax_term', $invoiceSettings), 2 => CRM_Utils_Money::format($taxAmount)));
      }
      else {
        $allMembershipInfo[$key]['tax_message'] = E::ts("Non-taxable.");
      }

      if (!empty($membershipType['auto_renew'])) {
        $allMembershipInfo[$key]['auto_renew'] = $options[$membershipType['auto_renew']];
      }
    }

    $form->assign('allMembershipInfo', json_encode($allMembershipInfo));
  }

  if ($formName == "CRM_Event_Form_Registration_Confirm") {
    $event_id = $form->get('id');
    $contact_id = $form->_contactID;
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id, $contact_id);
    $form->assign('taxRates', $taxes);
  }
}

/**
 * Implements hook_civicrm_pre().
 */
function cdntaxcalculator_civicrm_pre($op, $objectName, $id, &$params) {
  // This rewrites part of CRM_Contribute_BAO_Contribution::checkTaxAmount(),
  // which is called mainly just in one place in the 'add' function.
  if ($objectName == 'Contribution' && ($op == 'create' || $op == 'edit')) {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::checkTaxAmount($params);
  }

  // FIXME: this needs a config UI.
  // It separates the GST/PST into separate Financial Accounts.
  // Disabled for now, since not very much tested on 4.7 and not essential.
  if (FALSE && $objectName == 'FinancialItem' && $op == 'create') {
    if ($params['financial_account_id'] == GST_HST_FA_ID) {
      // Split financial item and save
      $smarty = CRM_Core_Smarty::singleton();

      //FIXME: get submitted state rather than saved state
      $province_id = cdn_getStateProvince($params['contact_id']);

      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForProvince($province_id);

      if ($taxes['TAX_TOTAL'] > 0) {
        $pstAmount = NULL;

        if (!empty($taxes['HST_GST'])) {
          $params['description'] = E::ts('GST/HST');
          if (!empty($taxes['PST'])) {
            $totalAmount = ($params['amount'] * 100) / ($taxes['HST_GST'] + $taxes['PST']);
            $params['amount'] = ($totalAmount * $taxes['HST_GST']) / 100;  
            $pstAmount = ($totalAmount * $taxes['PST']) / 100;
            
            global $stateFAMapping;
            $smarty->assign('pstFinancialAccount', $stateFAMapping[$state]);
          }
        }
        elseif (!empty($taxes['PST'])) {
          $params['description'] = E::ts('PST');
          global $stateFAMapping;
          $params['financial_acoount_id'] = $stateFAMapping[$state];
        }
        $smarty->assign('pstAmount', $pstAmount);
      }
    }
  }
}

/**
 * Implements hook_civicrm_post().
 *
 * FIXME/TODO: the objective here is to separate GST and PST (where applicable)
 * in the Financial Items (iirc).
 * This needs more testing.
 */
/*
function cdntaxcalculator_civicrm_post($op, $objectName, $id, &$objectRef) {
  if ($objectName == 'FinancialItem' && $op == 'create') {
    $pstAmount = CRM_Core_Smarty::singleton()->get_template_vars('pstAmount');
    if ($pstAmount) {
      $itemParams = array(
        'transaction_date' => $objectRef->transaction_date,
        'contact_id' => $objectRef->contact_id,
        'currency' => $objectRef->currency,
        'amount' => $pstAmount,
        'description' => E::ts('PST'),
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
*/
