<?php // $Id: theme-settings.php,v 1.4 2009/11/03 22:22:04 jmburnz Exp $

/**
 * @file theme-settings.php
 */

/**
* Implementation of THEMEHOOK_settings() function.
*
* @param $saved_settings
*   array An array of saved settings for this theme.
* @return
*   array A form array.
*/
function phptemplate_settings($saved_settings) {
  /*
   * The default values for the theme variables. Make sure $defaults exactly
   * matches the $defaults in the template.php file.
   */
  $defaults = array(
    'airplaytheme2_color_global'    => 'airplaytheme2_tan',
    'airplaytheme2_color_highlight' => 'airplaytheme2_red',
  );

  // Merge the saved variables and their default values
  $settings = array_merge($defaults, $saved_settings);

  // Create the form widgets using Forms API
  $form['airplaytheme2_color_global'] = array(
    '#type' => 'select',
    '#title' => t('Page Colors'),
    '#default_value' => $settings['airplaytheme2_color_global'],
    '#description' => t('Select the default color for blocks, borders and other page colors and backgrounds.'),
    '#options' => array(
      'airplaytheme2_p-gray' => t('Gray'),
      'airplaytheme2_tan'  => t('Tan'),
    ),
  );
  $form['airplaytheme2_color_highlight'] = array(
    '#type' => 'select',
    '#title' => t('Highlight Colors'),
    '#default_value' => $settings['airplaytheme2_color_highlight'],
    '#description' => t('Select the default color for the header nav, search box and drop down menus.'),
    '#options' => array(
      'airplaytheme2_red'    => t('Ruby Red'),
      'airplaytheme2_blue'   => t('Saphire Blue'),
      'airplaytheme2_green'  => t('Emerald Green'),
      'airplaytheme2_orange' => t('Sunset Orange'),
      'airplaytheme2_h-gray' => t('Cloudy Gray'),
      'airplaytheme2_brown'  => t('Chocolate Brown'),
    ),
  );

  // Return the additional form widgets
  return $form;
}
