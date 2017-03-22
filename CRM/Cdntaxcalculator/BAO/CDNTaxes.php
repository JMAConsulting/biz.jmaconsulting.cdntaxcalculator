<?php
/*
 +--------------------------------------------------------------------+
 | CiviCRM version 4.5                                                |
 +--------------------------------------------------------------------+
 | Copyright CiviCRM LLC (c) 2004-2014                                |
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
 *
 * @package CRM
 * @copyright CiviCRM LLC (c) 2004-2014
 * $Id$
 *
 */

/**
 * Base class for admin forms
 */
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

    $result = civicrm_api3('Contact', 'getsingle', array(
      'id' => $contact_id,
      'return.state_province' => 1,
      'return.country' => 1,
    ));

    if (strtolower($result['country']) == 'canada' && $result['state_province_id']) {
      $province = $result['state_province_id'];
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

}
