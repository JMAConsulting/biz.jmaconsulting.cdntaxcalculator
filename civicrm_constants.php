<?php

define('MEMBERSHIP_FIELD_ID', 3);
define('GST_HST_FA_ID', 14);

global $cdnTaxes;

$cdnTaxes = array(
  1101 => array( // British Columbia
    'HST_GST' => 5,
    'PST' => 7,
  ),
  1100 => array( // Alberta
    'HST_GST' => 5,
    'PST' => NULL,
  ),
  1111 => array( // Saskatchewan
    'HST_GST' => 5,
    'PST' => 5,
  ),
  1102 => array( // Manitoba
    'HST_GST' => 5,
    'PST' => 8,
  ),
  1108 => array( // Ontario
    'HST_GST' => 13,
    'PST' => NULL,
  ),
  1110 => array( // Québec
    'HST_GST' => 5,
    'PST' => 9.975,
  ),
  1103 => array( // New Brunswick
    'HST_GST' => 13,
    'PST' => NULL,
  ),
  1106 => array( // Nova Scotia
    'HST_GST' => 15,
    'PST' => NULL,
  ),
  1109 => array( // Prince Edward Island
    'HST_GST' => 14,
    'PST' => NULL,
  ),
  1104 => array( // Newfoundland and Labrador
    'HST_GST' => 13,
    'PST' => NULL,
  ),
);

global $stateFAMapping;

$stateFAMapping = array(
  1101 => 15, // British Columbia
  1111 => 16, // Saskatchewan
  1102 => 17, // Manitoba
  1110 => 18, // Québec
);

