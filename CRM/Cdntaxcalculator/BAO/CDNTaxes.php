<?php

class CRM_Cdntaxcalculator_BAO_CDNTaxes extends CRM_Core_DAO  {

  /**
   * Calculates the tax amounts for a priceset / fee block.
   */
  static public function applyTaxesToPriceset(&$feeBlock, &$taxes) {
    foreach ($feeBlock as &$fee) {
      if (!is_array($fee['options'])) {
        continue;
      }

      foreach ($fee['options'] as &$option) {
        // Checking for tax_rate is a way to check if the priceset field is taxable.
        // This assumes that the global tax rate is set to non-zero.
        if (!empty($option['tax_rate'])) {
          $option['tax_rate'] = $taxes['TAX_TOTAL'];
          $option['tax_amount'] = $taxes['TAX_TOTAL'] * $option['amount'] / 100;
          $has_taxable_amounts = TRUE;
        }
      }
    }
  }

  /**
   * FIXME: lineItems is an array of lineitems?
   */
  static public function recalculateTaxesOnLineItems(&$lineItems, &$taxes) {
    foreach ($lineItems as &$item) {
      foreach ($item as &$x) {
        // Checking for tax_rate is a way to check if the priceset field is taxable.
        // This assumes that the global tax rate is set to non-zero.
        if (!empty($x['tax_rate'])) {
          $taxes['PST_AMOUNT_TOTAL'] += $taxes['PST'] * $x['line_total'] / 100;
          $taxes['HST_GST_AMOUNT_TOTAL'] += $taxes['HST_GST'] * $x['line_total'] / 100;
        }
      }
    }
  }

  /**
   *
   */
  static public function getTotalTaxes($province_id = NULL) {
    $cdnTaxes = self::getTaxDefinitions();

    $taxes = [
      'TAX_TOTAL' => 0,
      'HST_GST' => 0,
      'HST_GST_LABEL' => '',
      'PST' => 0,
      'PST_LABEL' => '',
      'PST_AMOUNT_TOTAL' => 0,
      'HST_GST_AMOUNT_TOTAL' => 0,
      'province_id' => $province_id,
    ];

    if ($province_id) {
      $taxes = $cdnTaxes[$province_id];
      $taxes['TAX_TOTAL'] = $taxes['HST_GST'] + $taxes['PST'];
    }

    // This happens for non-Canada locations.
    // We need a 0% tax rate.
    return $taxes;
  }

  /**
   * Given a contact_id, returns the GST tax rate given the contact's
   * province.
   *
   * FIXME: ensure it is the billing address?
   */
  static function getTaxesForContact($contact_id) {
    $cdnTaxes = self::getTaxDefinitions();

    $taxes = [
      'TAX_TOTAL' => 0,
      'HST_GST' => 0,
      'HST_GST_LABEL' => '',
      'PST' => 0,
      'PST_LABEL' => '',
      'PST_AMOUNT_TOTAL' => 0,
      'HST_GST_AMOUNT_TOTAL' => 0,
      'province_id' => 0,
    ];

    if (empty($contact_id)) {
      throw new CRM_Core_Exception('Missing contact_id');
    }

/*
    $result = civicrm_api3('Address', 'getsingle', array(
      'id' => $contact_id,
      'return.state_province' => 1,
      'return.country' => 1,
    ));
*/

    $dao = CRM_Core_DAO::executeQuery('SELECT a.state_province_id, country.name as country
      FROM civicrm_address a
      LEFT JOIN civicrm_country country ON (country.id = a.country_id)
      WHERE a.contact_id = %1
      ORDER BY a.is_primary DESC, a.is_billing DESC LIMIT 1', [
      1 => [$contact_id, 'Positive'],
    ]);

    $dao->fetch();

    if (strtolower($dao->country) == 'canada' && !empty($dao->state_province_id)) {
      $province = $dao->state_province_id;
      $taxes = $cdnTaxes[$province];
      $taxes['TAX_TOTAL'] = $taxes['HST_GST'] + $taxes['PST'];
      $taxes['province_id'] = $province;
    }

    return $taxes;
  }

  /**
   * The tax rate for an event by using the "place of provision",
   * i.e. the province where the event is held.
   *
   * If there is no location associated with the event, it will
   * default the state_province of the current CiviCRM 'domain'.
   */
  static function getTaxesForEvent($event_id) {
    $cdnTaxes = self::getTaxDefinitions();

    if (empty($event_id)) {
      CRM_Core_Error::fatal('Empty event_id');
    }

    $province_id = NULL;
    $country_id = NULL;

    $taxes = [
      'TAX_TOTAL' => 0,
      'HST_GST' => 0,
      'HST_GST_LABEL' => '',
      'PST' => 0,
      'PST_LABEL' => '',
      'PST_AMOUNT_TOTAL' => 0,
      'HST_GST_AMOUNT_TOTAL' => 0,
      'province_id' => 0,
    ];

    // FIXME: Is there a simpler way of getting the event location?
    $result = civicrm_api3('Event', 'get', [
      'id' => $event_id,
      'return.loc_block_id' => 1,
      'api.LocBlock.get' => [
        'api.Address.get' => [],
      ],
    ]);

    foreach ($result['values'] as $key => $val) {
      if (isset($val['api.LocBlock.get'])) {
        foreach ($val['api.LocBlock.get']['values'] as $loc) {
          if (isset($loc['api.Address.get'])) {
            foreach ($loc['api.Address.get']['values'] as $addr) {
              if (!empty($addr['state_province_id'])) {
                $province_id = $addr['state_province_id'];
                $country_id = $addr['country_id'];
              }
            }
          }
        }
      }
    }

    if (empty($province_id)) {
      $domain_id = CRM_Core_Config::domainID();

      $result = civicrm_api3('Domain', 'getsingle', [
        'id' => $domainID,
      ]);

      if (empty($result['domain_address']['state_province_id'])) {
        CRM_Core_Error::fatal("The state/province of the default domain is not set (Administer > Communications > Organisation address)");
      }

      $province_id = $result['domain_address']['state_province_id'];
      $country_id = $result['domain_address']['country_id'];
    }

    if (empty($province_id) || empty($country_id)) {
      CRM_Core_Error::fatal("Failed to find a default country/province for the event.");
    }

    if ($country_id == 1039) {
      $taxes = $cdnTaxes[$province_id];
      $taxes['TAX_TOTAL'] = $taxes['HST_GST'] + $taxes['PST'];
      $taxes['province_id'] = $province_id;
    }

    return $taxes;
  }

  /**
   * Checks to see if there are any taxable amounts in the priceset.
   */
  static function hasTaxableAmounts($feeBlock) {
    foreach ($feeBlock as $fee) {
      if (!is_array($fee['options'])) {
        continue;
      }

      foreach ($fee['options'] as &$option) {
        if (!empty($option['tax_rate'])) {
          return TRUE;
        }
      }
    }

    return FALSE;
  }

  /**
   *
   */
  static function getTaxDefinitions() {
    global $cdnTaxes;

    if (!empty($tax_rates)) {
      return $cdnTaxes;
    }

    include_once 'civicrm_constants.php';
    @include_once 'civicrm_constants.local.php';

    return $cdnTaxes;
  }

  /**
   * Rewrites part of CRM_Contribute_BAO_Contribution::checkTaxAmount()
   * but using the correct tax rates.
   */
  static public function checkTaxAmount(&$params, $isLineItem = FALSE) {
    if (empty($params['contact_id'])) {
      Civi::log()->warning('Cdntaxcalculator checkTaxAmount: contact_id not found: ' . print_r($params, 1));
      return;
    }

    $contact_id = $params['contact_id'];
    $taxes = CRM_Cdntaxcalculator_BAO_CDNTaxes::getTaxesForContact($contact_id);
    $taxRates = CRM_Core_PseudoConstant::getTaxRates();

    foreach ($taxRates as $ft => &$values) {
      $taxRates[$ft] = $taxes['TAX_TOTAL'];
    }

    // When updating an existing contribution, the line_total is the actual
    // correct amount. Let's recalculate taxes.
    if (!empty($params['id'])) {
      $tax_rate = $taxRates[$params['financial_type_id']] / 100;

      $total_amount = 0;
      $total_tax = 0;

      foreach ($params['line_item'] as $setID => &$priceField) {
        foreach ($priceField as $priceFieldID => &$priceFieldValue) {
          // CiviCRM does weird recalculations of taxes, and often not in our advantage.
          // Since the user enters a total amount (with tax), then CiviCRM splits the amount,
          // we need to recombine the line_total + tax_amount, then reverse-calculate taxes.
          $tmp_total = $priceFieldValue['line_total'] + $priceFieldValue['tax_amount'];
          $priceFieldValue['tax_amount'] = round($priceFieldValue['line_total'] * $tax_rate, 2);

          $total_amount += $priceFieldValue['line_total'];
          $total_tax += $priceFieldValue['tax_amount'];
        }
      }

      $params['tax_amount'] = $total_tax;
      $params['total_amount'] = $total_amount + $total_tax;
    }
    elseif (isset($params['financial_type_id'])) {
      // If the financial type is not taxable, we want to avoid falling into the "else" below.
      if (!array_key_exists($params['financial_type_id'], $taxRates)) {
        $params['tax_amount'] = 0;
        return;
      }

      // FIXME: the original checkTaxAmount() verified for: empty($params['skipLineItem']) && !$isLineItem,
      // and did not calculate taxes when that was the case. skipLineItem is usually used when processing a
      // membership (and the contribution has already been processed).
      // However, while testing adding a membership from the backend, this was the only time that this
      // function was getting called (for Contribution.create), so we are recalculating no matter what.
      // Also, what harm can it do?

      // New Contribution and update of contribution with tax rate financial type
      $tax_rate = $taxRates[$params['financial_type_id']] / 100;

      // [ML] Recalculate the total_amount, since the original checkTaxAmount calculated incorrectly.
      $total_amount = 0;
      $total_tax = 0;

      foreach ($params['line_item'] as $setID => &$priceField) {
        foreach ($priceField as $priceFieldID => &$priceFieldValue) {
          // CiviCRM does weird recalculations of taxes, and often not in our advantage.
          // Since the user enters a total amount (with tax), then CiviCRM splits the amount,
          // we need to recombine the line_total + tax_amount, then reverse-calculate taxes.
          $tmp_total = $priceFieldValue['line_total'] + $priceFieldValue['tax_amount'];
          $priceFieldValue['line_total'] = round($tmp_total / (1 + $tax_rate), 2);
          $priceFieldValue['tax_amount'] = $tmp_total - $priceFieldValue['line_total'];

          $total_amount += $priceFieldValue['line_total'];
          $total_tax += $priceFieldValue['tax_amount'];
        }
      }

      $params['tax_amount'] = $total_tax;
      $params['total_amount'] = $total_amount + $total_tax;
    }
    elseif (isset($params['api.line_item.create'])) {
      Civi::log()->warning('checkTax CDN: FIXME FIXME NOT TESTED!');

      // Update total amount of contribution using lineItem
      $taxAmountArray = array();
      foreach ($params['api.line_item.create'] as $key => $value) {
        if (isset($value['financial_type_id']) && array_key_exists($value['financial_type_id'], $taxRates)) {
          $taxRate = $taxRates[$value['financial_type_id']];
          $taxAmount = CRM_Contribute_BAO_Contribution_Utils::calculateTaxAmount($value['line_total'], $taxRate);
          $taxAmountArray[] = round($taxAmount['tax_amount'], 2);
        }
      }
      $params['tax_amount'] = array_sum($taxAmountArray);
      $params['total_amount'] = $params['total_amount'] + $params['tax_amount'];
    }
    else {
      Civi::log()->warning('checkTax CDN: [else] VERIFY - use-case not very tested: ' . print_r($params, 1));

      // update line item of contrbution
      if (isset($params['financial_type_id']) && array_key_exists($params['financial_type_id'], $taxRates) && $isLineItem) {
        $taxRate = $taxRates[$params['financial_type_id']];
        $taxAmount = CRM_Contribute_BAO_Contribution_Utils::calculateTaxAmount($params['line_total'], $taxRate);
        $params['tax_amount'] = round($taxAmount['tax_amount'], 2);
      }
    }
  }

}
