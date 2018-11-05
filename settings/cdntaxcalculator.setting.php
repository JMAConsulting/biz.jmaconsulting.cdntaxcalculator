<?php

use CRM_Cdntaxcalculator_ExtensionUtil as E;

return [
  'cdntaxcalculator_address_type' => [
    'group_name' => 'domain',
    'group' => 'cdntaxcalculator',
    'name' => 'cdntaxcalculator_address_type',
    'type' => 'Integer',
    'default' => 1,
    'add' => '1.0',
    'is_domain' => 1,
    'is_contact' => 0,
    'title' => E::ts('Address Type'),
    'description' => E::ts("Address type to be used for calculating taxes on memberhips or other type of non-event contributions. By default, the billing address is recommended since it is always included in the contribution form billing block."),
    'help_text' => E::ts("Address type to be used for calculating taxes on memberhips or other type of non-event contributions. By default, the billing address is recommended since it is always included in the contribution form billing block."),
    'quick_form_type' => 'Select',
    'html_type' => 'Select',
    'select_options' => [
      1 => E::ts('Billing Address'),
      2 => E::ts('Primary Address'),
    ],
  ],
];
