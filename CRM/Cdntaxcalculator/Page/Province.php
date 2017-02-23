<?php

class CRM_Cdntaxcalculator_Page_Province extends CRM_Core_Page {

  function run() {
    $result = [
      'status' => 0,
    ];

    $province_id = intval($_REQUEST['state_province_id']);

    if (!empty($province_id)) {
      $session = CRM_Core_Session::singleton();
      $session->set('cdntax_province_id', $province_id);
      $result['status'] = 1;
    }

    echo json_encode($result);
    CRM_Utils_System::civiExit();
  }

}
