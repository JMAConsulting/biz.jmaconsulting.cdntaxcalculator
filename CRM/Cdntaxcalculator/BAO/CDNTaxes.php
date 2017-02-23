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

  static protected $_totalTax = '';
  
  static public function getTotalTaxes($state = NULL) {
    if (!self::$_totalTax) {
      global $cdnTaxes;
      foreach ($cdnTaxes as $type => $amount) {
        self::$_totalTax[$type] = $amount['HST_GST'] + $amount['PST'];
      }
    }
    if ($state) {
      return CRM_Utils_Array::value($state, self::$_totalTax);
    }
    else {
      return self::$_totalTax;
    }
  }

  /**
   * Given a contact_id, returns the GST tax rate given the contact's
   * province.
   *
   * FIXME: ensure it is the billing address?
   */
  static function getTotalTaxesForContact($contact_id) {
    global $cdnTaxes;

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
      return $cdnTaxes[$province]['HST_GST'] + $cdnTaxes[$province]['PST'];
    }

    return 0;
  }

}
