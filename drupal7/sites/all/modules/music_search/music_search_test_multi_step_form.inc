<?php

/**
 * @file This is an example multi-step form designed for Drupal 6. This code is provided for illustration purposes only. Caveat emptor. Use at your own risk. All code is licensed under GPL 2+. See http://pingv.com/node/4252.
 */


/**
 * Returns FAPI arrays.
 */
 function multistepform_form($form_state) {
  if (!empty($form_state['storage']['step'])) {
    $function = $form_state['storage']['step'];
    return $function($form_state);
  }
  else {
    return _multistepform_form_start();  
  }
}

/**
 * Form validation controller.
 */
function multistepform_form_validate($form, &$form_state) {
  if (!empty($form_state['values']['step_validate'])) {
    $function = $form_state['values']['step_validate'];
    $function($form, $form_state);
  }
}

/**
 * Form submit controller.
 */
function multistepform_form_submit($form, &$form_state) {
  if (empty($form_state['storage'])) {
    $form_state['storage'] = array();
    $form_state['storage']['values'] = array();
  }
  // Call user-defined submit function.
  if (!empty($form_state['values']['step_submit'])) {
    $function = $form_state['values']['step_submit'];
    $function($form, $form_state);
  }
  // Store submitted form values, this must happen after function call above to allow for modifying $form_state['values'].
  $this_step = $form_state['values']['this_step'];
  $form_state['storage']['values'][$this_step] = $form_state['values'];
  // Set up next step.
  if (!empty($form_state['values']['step_next'])) {
    $form_state['storage']['step'] = $form_state['values']['step_next'];
  }
  else {
    // Form complete! <FF music plays>
    $values = $form_state['storage']['values'];
    // Do something with $values.
    // Set $form['#redirect'] to not return to the first step.
    drupal_set_message(t('Complete! !values', array('!values' => print_r($values, TRUE))));
  }
}

/**
 * Start
 */
function _multistepform_form_start() {
  $form['name'] = array(
    '#type' => 'textfield',
    '#title' => t('Name'),
    '#required' => TRUE,
  );

  $form['continue'] = array(
    '#type' => 'submit',
    '#value' => 'Continue',
  );
  $form['this_step'] = array(
    '#type' => 'value',
    '#value' => 'start',
  );
  $form['step_next'] = array(
    '#type' => 'value',
    '#value' => '_multistepform_form_food',
  );
  return $form;
}

function _multistepform_form_food($form_state) {
  $form['food'] = array(
    '#type' => 'textfield',
    '#title' => t('Food'),
    '#required' => TRUE,
  );
  
  $form['continue'] = array(
    '#type' => 'submit',
    '#value' => 'Continue',
  );
  $form['this_step'] = array(
    '#type' => 'value',
    '#value' => 'food',
  );
  $form['step_validate'] = array(
    '#type' => 'value',
    '#value' => '_multistepform_form_my_validate',
  );
  $form['step_next'] = array(
    '#type' => 'value',
    '#value' => '_multistepform_form_like_music',
  );
  return $form;
}

function _multistepform_form_my_validate($form, &$form_state) {
  if ($form_state['values']['food'] != 'pizza') {
    form_set_error('food', t('You should like pizza today.')); 
  }
}

function _multistepform_form_like_music($form_state) {
  $form['like_music'] = array(
    '#type' => 'radios',
    '#title' => t('Do you like music?'),
    '#options' => array(
      0 => 'No',
      1 => 'Yes',
    ),
    '#required' => TRUE,
  );
  
  $form['continue'] = array(
    '#type' => 'submit',
    '#value' => 'Continue',
  );
  $form['this_step'] = array(
    '#type' => 'value',
    '#value' => 'like_music',
  );
  $form['step_submit'] = array(
    '#type' => 'value',
    '#value' => '_multistepform_form_my_submit',
  );
  $form['step_next'] = array(
    '#type' => 'value',
    '#value' => '_multistepform_form_music',
  );
  return $form;
}

function _multistepform_form_my_submit($form, &$form_state) {
  if ($form_state['values']['like_music'] == '0') {
    // If the user doesn't like music, well don't ask them anything more about it!
    $form_state['values']['step_next'] = '_multistepform_form_final';
  }
}

function _multistepform_form_music($form_state) {
  $form['music'] = array(
    '#type' => 'textfield',
    '#title' => t('Music'),
  );
  
  $form['continue'] = array(
    '#type' => 'submit',
    '#value' => 'Continue',
  );
  $form['this_step'] = array(
    '#type' => 'value',
    '#value' => 'music',
  );
  return $form;
}

function _multistepform_form_final($form_state) {
  $form['final'] = array(
    '#type' => 'textfield',
    '#title' => t('Any last words?'),
  );
  
  $form['continue'] = array(
    '#type' => 'submit',
    '#value' => 'Continue',
  );
  $form['this_step'] = array(
    '#type' => 'value',
    '#value' => 'final',
  );
  return $form;
}
