<?php
define('PROVINCE_FIELD', 'billing_state_province_id-5');
define('MEMBERSHIP_FIELD_ID', 3);
define('GST_HST_FA_ID', 14);
define('MEM_PAGE_ID', 1);

global $cdnTaxes;

$cdnTaxes = array(
  1100 => array( // Alberta
    'HST_GST' => 5,
    'PST' => 0,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => '',
  ),
  1101 => array( // British Columbia
    'HST_GST' => 5,
    'PST' => 0,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => '',
  ),
  1102 => array( // Manitoba
    'HST_GST' => 5,
    'PST' => 0,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => '',
  ),
  1103 => array( // New Brunswick
    'HST_GST' => 13,
    'PST' => 0,
    'HST_GST_LABEL' => 'HST',
    'PST_LABEL' => '',
  ),
  1104 => array( // Newfoundland and Labrador
    'HST_GST' => 13,
    'PST' => 0,
    'HST_GST_LABEL' => 'HST',
    'PST_LABEL' => '',
  ),
  1105 => array( // Northwest Territories.
    'HST_GST' => 5,
    'PST' => 0,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => '',
  ),
  1106 => array( // Nova Scotia
    'HST_GST' => 15,
    'PST' => 0,
    'HST_GST_LABEL' => 'HST',
    'PST_LABEL' => '',
  ),
  1107 => array( // Nunavut.
    'HST_GST' => 5,
    'PST' => 0,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => '',
  ),
  1108 => array( // Ontario
    'HST_GST' => 13,
    'PST' => 0,
    'HST_GST_LABEL' => 'HST',
    'PST_LABEL' => '',
  ),
  1109 => array( // Prince Edward Island
    'HST_GST' => 14,
    'PST' => 0,
    'HST_GST_LABEL' => 'HST',
    'PST_LABEL' => '',
  ),
  1110 => array( // Québec
    'HST_GST' => 5,
    'PST' => 9.975,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => 'PST',
  ),
  1111 => array( // Saskatchewan
    'HST_GST' => 5,
    'PST' => 0,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => '',
  ),
  1112 => array( // Yukon.
    'HST_GST' => 5,
    'PST' => 0,
    'HST_GST_LABEL' => 'GST',
    'PST_LABEL' => '',
  ),
);

global $stateFAMapping;

$stateFAMapping = array(
  1101 => 15, // British Columbia
  1111 => 16, // Saskatchewan
  1102 => 17, // Manitoba
  1110 => 18, // Québec
);

