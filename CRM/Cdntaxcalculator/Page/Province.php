<?php

class CRM_Cdntaxcalculator_Page_Province extends CRM_Core_Page {

  /**
   * FIXME: This does not seem to be used. Remove?
   */
  function run() {
    $result = [
      'status' => 0,
    ];

    $province_id = CRM_Utils_Request::retrieveValue('state_province_id', 'Positive');

    if (!empty($province_id)) {
      $session = CRM_Core_Session::singleton();
      $session->set('cdntax_province_id', $province_id);
      $result['status'] = 1;
    }

    echo json_encode($result);
    CRM_Utils_System::civiExit();
  }

}
