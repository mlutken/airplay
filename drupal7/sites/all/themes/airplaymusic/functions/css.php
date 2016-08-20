<?php

$airplaymusic_csskill_path = drupal_get_path('theme', 'airplaymusic') . '/css/override/kill/';
  drupal_add_css($airplaymusic_csskill_path . 'ctools.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskill_path . 'field_ui.admin.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskill_path . 'field.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskill_path . 'node.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskill_path . 'system.menus.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskill_path . 'system.messages.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskill_path . 'user.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskill_path . 'views.css', array('group' => CSS_SYSTEM));
  
$airplaymusic_csskeep_path = drupal_get_path('theme', 'airplaymusic') . '/assets/css/override/keep/';
/*  drupal_add_css($airplaymusic_csskeep_path . 'book.admin.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskeep_path . 'book.theme-rtl.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskeep_path . 'book.theme.css', array('group' => CSS_SYSTEM));*/
  drupal_add_css($airplaymusic_csskeep_path . 'search.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskeep_path . 'system.base.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskeep_path . 'system.theme.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskeep_path . 'contextual.base.css', array('group' => CSS_SYSTEM)); 
  drupal_add_css($airplaymusic_csskeep_path . 'contextual.theme.css', array('group' => CSS_SYSTEM));
/*  drupal_add_css($airplaymusic_csskeep_path . 'openid.base.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_csskeep_path . 'openid.theme.css', array('group' => CSS_SYSTEM));*/
  drupal_add_css($airplaymusic_csskeep_path . 'field.theme.css', array('group' => CSS_SYSTEM));
  
$airplaymusic_cssbootstrap_path = drupal_get_path('theme', 'airplaymusic') . '/assets/css/bootstrap/';
  drupal_add_css($airplaymusic_cssbootstrap_path . 'bootstrap_resp.css', array('group' => CSS_SYSTEM));
  drupal_add_css($airplaymusic_cssbootstrap_path . 'bootstrap.css', array('group' => CSS_SYSTEM));

$airplaymusic_cssmain_path = drupal_get_path('theme', 'airplaymusic') . '/assets/css/';
  drupal_add_css($airplaymusic_cssmain_path . 'txt_reset_normalize.css', array('group' => CSS_SYSTEM));
