<?php

require_once 'cdntaxcalculator.civix.php';
require_once 'civicrm_constants.php';

use CRM_Cdntaxcalculator_ExtensionUtil as E;

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
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function cdntaxcalculator_civicrm_navigationMenu(&$menu) {
  _cdntaxcalculator_civix_insert_navigation_menu($menu, 'Administer/CiviContribute', [
    'label' => E::ts('Canadian Tax Calculator'),
    'name' => 'cdntaxcalculator',
    'url' => 'civicrm/admin/setting/cdntaxcalculator',
    'permission' => 'administer CiviCRM',
    'operator' => 'OR',
    'separator' => 0,
  ]);
  _cdntaxcalculator_civix_navigationMenu($menu);
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
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildAmount: priceSetId is empty. Returning early.');
    return;
  }

  if (!is_array($feeBlock) || empty($feeBlock)) {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildAmount: feeBlock is empty or not an array. Returning early.');
    return;
  }

  // These are also checked in select_province.tpl to see if we should display
  // a mention about taxes being calculated based on the contact's address,
  // as well as to check whether to popup if we don't have a province.
  $has_taxable_amounts = CRM_Cdntaxcalculator_BAO_CDNTaxes::hasTaxableAmounts($feeBlock);
  $has_address_based_taxes = ($has_taxable_amounts && $pageType != 'event');

  if (!$has_taxable_amounts) {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildAmount: no taxable amounts found. Returning early.');
    return;
  }

  $contact_id = $form->_contactID;
  $province_id = NULL;
  $country_id = NULL;
  $country_name = '';
  $province_name = '';
  $taxes = [];

  $form_name = get_class($form);

  CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildAmount: ' . $form_name);

  if (in_array($form_name, ['CRM_Event_Form_ParticipantFeeSelection', 'CRM_Event_Form_Participant'])) {
    $event_id = $form->_eventId;
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id, $contact_id);
    $province_id = $taxes['province_id'];
  }
  elseif ($pageType == 'event') {
    $event_id = $form->get('id');

    // This isn't really used, but here just as a safeguard.
    if (empty($event_id)) {
      $event_id = $form->get('eventId');
    }

    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id, $contact_id);
    $province_id = $taxes['province_id'];
  }
  else {
    // Country/Province ID was passed an an URL argument
    // ex: redirecting after selecting a province from the popup.
    if ($t = CRM_Utils_Request::retrieveValue('cdntax_country_id', 'Positive')) {
      $country_id = $t;

      // Reset the province now, in case it's another country, where selecting a province is not mandatory.
      $province_id = NULL;
    }

    if ($t = CRM_Utils_Request::retrieveValue('cdntax_province_id', 'Positive')) {
      $province_id = $t;
    }

    // Necessary if returning back from the 'confirm' page.
    // This is a bit dangerous and could cause weird bugs in the backend,
    // hence only using on the front-end contribution form.
    // XXX: We check against the country_id, because the user might
    // be from another country, so the province can be empty.
    // We also check against the 'reset' variable, because we do not want to
    // confuse, for example, an admin, using the form as a user.
    $check_reset = CRM_Utils_Request::retrieveValue('reset', 'Positive');

    if (empty($country_id) && $formName == 'CRM_Contribute_Form_Contribution_Main' && !$check_reset) {
      $province_id = $session->get('cdntax_province_id');
      $country_id = $session->get('cdntax_country_id');

      CRM_Cdntaxcalculator_BAO_CDNTaxes::trace("buildAmount: [session] $province_id / $country_id");
    }

    // The user is logged-in (or New Membership for a specific Contact).
    // XXX: We check against the country_id, because the user might
    // be from another country, so the province can be empty.
    if (empty($country_id) && !empty($contact_id)) {
      $country_id = cdn_getContactTaxCountry($contact_id);
      $province_id = cdn_getStateProvince($contact_id);

      CRM_Cdntaxcalculator_BAO_CDNTaxes::trace("buildAmount: [primary address cid:$contact_id] $province_id / $country_id");
    }

    // FIXME: when is this used?
    // FIXME: potential info leak if we let users lookup provinces of any contact?
    if (empty($country_id) && empty($contact_id) && CRM_Utils_Request::retrieveValue('contactId', 'Positive')) {
      Civi::log()->warning('cdntaxcalculator: found deprecated use-case where contactId was passed by URL.');
      $contact_id = CRM_Utils_Request::retrieveValue('contactId', 'Positive');
      $province_id = cdn_getStateProvince($contact_id);
      $country_id = cdn_getContactTaxCountry($contact_id);

      CRM_Cdntaxcalculator_BAO_CDNTaxes::trace("buildAmount: [primary address URL cid:$contact_id] $province_id / $country_id");
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
      $form->assign('cdntaxcalculator_location_name', $country_name . ', ' . $province_name);

      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForProvince($province_id);
    }
  }

  $locale = CRM_Core_I18n::getLocale();

  $settings = [
    'country_id' => $country_id,
    'country_name' => $country_name,
    'province_id' => $province_id,
    'province_name' => $province_name,
    'has_taxable_amounts' => $has_taxable_amounts,
    'has_address_based_taxes' => $has_address_based_taxes,
    // FIXME: this code is horrible, sorry!
    'setting_address_type' => Civi::settings()->get('cdntaxcalculator_address_type'),
    'setting_text_select_location' => Civi::settings()->get('cdntaxcalculator_text_select_location_' . $locale),
    'setting_text_current_location' => E::ts(Civi::settings()->get('cdntaxcalculator_text_current_location_' . $locale), [1 => ($province_name ? $province_name : $country_name)]),
    'setting_text_change_location' => Civi::settings()->get('cdntaxcalculator_text_change_location_' . $locale),
    'setting_text_help' => Civi::settings()->get('cdntaxcalculator_text_help_' . $locale),
  ];

  CRM_Core_Resources::singleton()->addSetting(array(
    'cdntaxcalculator' => $settings,
  ));

  $form->assign('cdntaxcalculator_settings', $settings);

  CRM_Core_Region::instance('page-footer')->add(array(
    'template' => 'CRM/Cdntaxcalculator/select_province.tpl',
  ));

  // We always apply this, because the tax rate might be 0%
  // for non-Canada, therefore we need to remove the default tax
  // that CiviCRM may have added.
  CRM_Cdntaxcalculator_BAO_CDNTaxes::applyTaxRatesToPriceset($feeBlock, $taxes);

  // NB: we also assign to cdnTaxRates because for some reason
  // sometimes taxRates is sent through json_encode and that does
  // not go well with templates/CRM/Price/Page/LineItem.tpl
  $form->assign('taxRates', $taxes);
  $form->assign('cdnTaxRates', $taxes);

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

  CRM_Cdntaxcalculator_BAO_CDNTaxes::trace("buildAmount: saved in session: $province_id / $country_id");
}

/**
 * Returns the billing address.
 */
function cdn_getContactTaxAddress($cid) {
  static $address_cache = [];

  if (!empty($address_cache[$cid])) {
    return $address_cache[$cid];
  }

  // Normally we should have only one billing/primary address,
  // but db-imports sometimes mess that up, so return the
  // first billing address found.

  $params = [
    'contact_id' => $cid,
    'api.StateProvince.get' => [],
    'api.Country.get' => [],
  ];

  $tax_location = Civi::settings()->get('cdntaxcalculator_address_type');

  if ($tax_location == 1) {
    $params['is_billing'] = 1;
  }
  else {
    $params['is_primary'] = 1;
  }

  $address = civicrm_api3('Address', 'get', $params);

  foreach ($address['values'] as $key => $value) {
    $address_cache[$cid] = $value;
    return $value;
  }

  // Fallback on the other type of address.
  if ($tax_location == 1) {
    unset($params['is_billing']);
    $params['is_primary'] = 1;
  }
  else {
    unset($params['is_primary']);
    $params['is_billing'] = 1;
  }

  $address = civicrm_api3('Address', 'get', $params);

  foreach ($address['values'] as $key => $value) {
    $address_cache[$cid] = $value;
    return $value;
  }

  return NULL;
}

/**
 * Returns the province of the primary address.
 * FIXME: Function not renamed for legacy compat.
 */
function cdn_getStateProvince($cid) {
  $address = cdn_getContactTaxAddress($cid);

  if (!empty($address)) {
    return $address['state_province_id'];
  }

  return NULL;
}

/**
 * Returns the country of the primary address.
 */
function cdn_getContactTaxCountry($cid) {
  $address = cdn_getContactTaxAddress($cid);

  if (!empty($address)) {
    return $address['country_id'];
  }

  return NULL;
}

/**
 * Implements hook_civicrm_buildForm().
 */
function cdntaxcalculator_civicrm_buildForm($formName, &$form) {
  if ($formName == 'CRM_Contribute_Form_Contribution_Main') {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildForm: ' . $formName);

    // Sets the province to the valued selected from the location popup.
    // Tax calculations on this form are handled by buildAmount
    $session = CRM_Core_Session::singleton();
    $defaults = [];

    if ($t = CRM_Utils_Request::retrieveValue('cdntax_country_id', 'Positive')) {
      $defaults['billing_country_id-5'] = $t;
    }

    if ($t = CRM_Utils_Request::retrieveValue('cdntax_province_id', 'Positive')) {
      $defaults['billing_state_province_id-5'] = $t;
    }

    $form->setDefaults($defaults);

    // "Pay an invoice" does not call buildAmount
    // c.f. CRM/Contribute/Form/Contribution/Main.php line 402
    if ($form->get('ccid')) {
      $province_id = cdn_getStateProvince($form->_contactID);
      $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForProvince($province_id);
      CRM_Cdntaxcalculator_BAO_CDNTaxes::recalculateTaxesOnLineItems($form->_lineItem, $taxes);

      $form->assign('lineItem', $form->_lineItem);
      $form->assign('taxRates', $taxes);
      $form->assign('cdnTaxRates', $taxes);
      $form->assign('totalTaxAmount', $taxes['HST_GST_AMOUNT_TOTAL']);
    }
  }
  elseif (in_array($formName, ['CRM_Contribute_Form_Contribution_Confirm', 'CRM_Contribute_Form_Contribution_ThankYou'])) {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildForm: ' . $formName);

    $session = CRM_Core_Session::singleton();
    $province_id = NULL;

    # FIXME: this doesn't respect the tax location setting.
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
      $form->assign('cdnTaxRates', $taxes);
    }
  }

  // This tax override applies to backend "new membership" form
  // when not using a priceset.
  if ($formName == 'CRM_Member_Form_Membership' && $form->_action & CRM_Core_Action::ADD && $form->_contactID) {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildForm: ' . $formName);

    $taxRates = CRM_Core_Smarty::singleton()->get_template_vars('taxRates');
    $taxRates = json_decode($taxRates, TRUE);
    $contact_id = $form->_contactID;

    CRM_Cdntaxcalculator_BAO_CDNTaxes::verifyTaxableAddress($contact_id);
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForContact($contact_id);

    foreach ($taxRates as &$values) {
      $values = $taxes['TAX_TOTAL'];
    }

    $form->assign('taxRates', json_encode($taxRates));
    $form->assign('cdnTaxRates', $taxRates);
  }
  elseif ($formName == 'CRM_Contribute_Form_Contribution' && ($form->_action == CRM_Core_Action::UPDATE || $form->_action == CRM_Core_Action::ADD) && $form->_contactID) {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildForm: ' . $formName);

    $taxRates = CRM_Core_Smarty::singleton()->get_template_vars('taxRates');
    $taxRates = json_decode($taxRates, TRUE);
    $contact_id = $form->_contactID;

    CRM_Cdntaxcalculator_BAO_CDNTaxes::verifyTaxableAddress($contact_id);
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxRatesForContact($contact_id);

    foreach ($taxRates as &$values) {
      $values = $taxes['TAX_TOTAL'];
    }

    $taxRates['HST_GST_LABEL'] = $taxes['HST_GST_LABEL'];
    $taxRates['PST_LABEL'] = $taxes['PST_LABEL'];

    $form->assign('taxRates', json_encode($taxRates));
    $form->assign('cdnTaxRates', $taxRates);

    // Fix the Line Items
    CRM_Cdntaxcalculator_BAO_CDNTaxes::recalculateTaxesOnLineItems($form->_lineItems, $taxes);
    $form->assign('lineItem', $form->_lineItems);

    // Fix the Total Amount
    $total_amount = CRM_Core_Smarty::singleton()->get_template_vars('totalAmount');
    $total_amount += $taxes['HST_GST_AMOUNT_TOTAL'];

    $form->assign('totalAmount', $total_amount);
    $form->assign('totalTaxAmount', $taxes['HST_GST_AMOUNT_TOTAL']);
  }
  elseif ($formName == 'CRM_Member_Form_MembershipRenewal' && $form->_action == CRM_Core_Action::RENEW && $form->_contactID) {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildForm: ' . $formName);

    // This form doesn't seem to use the 'taxRates' variable,
    // so we have to hack the allMembershipInfo variable, which includes pre-calculated total amounts.
    // see: CRM/Member/Form/MembershipRenewal.php
    $contact_id = $form->_contactID;

    CRM_Cdntaxcalculator_BAO_CDNTaxes::verifyTaxableAddress($contact_id);
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
  elseif ($formName == "CRM_Event_Form_Registration_Confirm") {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildForm: ' . $formName);

    $event_id = $form->get('id');
    $contact_id = $form->_contactID;
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForEvent($event_id, $contact_id);
    $form->assign('taxRates', $taxes);
    $form->assign('cdnTaxRates', $taxes);
  }
  elseif ($formName == 'CRM_Contribute_Form_ContributionView') {
    CRM_Cdntaxcalculator_BAO_CDNTaxes::trace('buildForm: ' . $formName);

    // Display the correct tax_rate.
    // By default, CiviCRM displays the currently configured tax rate,
    // but that rate varies by province, and varies in time.
    $contribution_id = $form->get('id');

    // CRM_Contribute_Form_ContributionView hints that this could happen.
    if (empty($contribution_id)) {
      return;
    }

    $values = CRM_Contribute_BAO_Contribution::getValuesWithMappings([
      'id' => $contribution_id,
    ]);

    // Copied from CRM_Contribute_Form_ContributionView
    $lineItems = CRM_Price_BAO_LineItem::getLineItemsByContributionID($contribution_id);

    foreach ($lineItems as $key => &$val) {
      if (empty($val['tax_rate']) || empty($val['line_total']) || $val['line_total'] == '0.00') {
        // Otherwise the UI shows '%' (or NAN) instead of '0%'
        $val['tax_rate'] = 0;
      }
      else {
        $val['tax_rate'] = round($val['tax_amount'] / $val['line_total'], 3) * 100;
      }
    }

    // TODO: set a variable for the LineItem.tpl so that we could display
    // the correct tax name? although we don't really have a way to know
    // that, except by checking the rules in buildAmount.

    $lineItems = array($lineItems);
    $form->assign('lineItem', $lineItems);
  }
}

/**
 * Implements hook_civicrm_pageRun().
 */
function cdntaxcalculator_civicrm_pageRun(&$page) {
  $pageName = $page->getVar('_name');

  if ($pageName == 'CRM_Event_Page_EventInfo') {
    $event_id = $page->getVar('_id');

    if (CRM_Cdntaxcalculator_BAO_CDNTaxes::isEventFinancialTypeTaxable($event_id)) {
      // For now, it's too complicated to recalculate taxes on this form, so just hide them.
      CRM_Core_Resources::singleton()->addStyle('.crm-price-amount-tax { display: none; }');

      CRM_Core_Region::instance('event-page-eventinfo-actionlinks-bottom')->add([
        'markup' => '<div class="crm-section crm-cdntaxcalculator-label-tax-not-included"><div class="content">' . E::ts('Taxes not included.') . '</div></div>',
        'weight' => -1,
      ]);
    }
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
