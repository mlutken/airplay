<?php
// $Id: translation_status.report.inc,v 1.1 2008/09/05 19:07:42 nedjo Exp $

/**
 * @file
 * Code required only when rendering the tanslation status report.
 */

/**
 * Render a report on the availability of translations for installed modules.
 */
function translation_status_report() {
  $languages = language_list('enabled');
  unset($languages[1]['en']);
  if (count($languages[1])) {
    $modules = module_rebuild_cache();
    // Needed for system_sort_modules_by_info_name().
    module_load_include('inc', 'system', 'system.admin');
    uasort($modules, 'system_sort_modules_by_info_name');
    foreach ($modules as $name => $module) {
      // Skip Drupal core.
      if (in_array($module->info['package'], array('Core - optional', 'Core - required'))) {
        continue;
      }
      // Ensure this module is compatible with this version of core.
      if (!isset($module->info['core']) || $module->info['core'] != DRUPAL_CORE_COMPATIBILITY) {
        continue;
      }
      foreach (array_keys($languages[1]) as $language) {
        // Collect all files for this component in all enabled languages, named
        // as $langcode.po or with names ending with $langcode.po. This allows
        // for filenames like node-module.de.po to let translators use small
        // files and be able to import in smaller chunks.
        $translations = file_scan_directory(dirname($module->filename) .'/translations', '(^|\.)('. $language .')\.po$', array('.', '..', 'CVS'), 0, FALSE);
        $modules[$name]->info['translations'][$language] = $translations ? TRUE : FALSE;
      }
      if (!isset($module->info['package']) || !$module->info['package']) {
        $module->info['package'] = t('Other');
      }
      $data[$module->info['package']][$module->name] = $module->info;
    }
    ksort($data);
    return theme('translation_status_report', $data);
  }
  else {
    drupal_set_message(t('No non-English languages found. To display module translation status, please enable at least one language.'), 'warning');
  }
}

/**
 * Theme a translation status report.
 *
 * @ingroup themeable
 */
function theme_translation_status_report($data) {
  $languages = language_list('enabled');
  unset($languages[1]['en']);
  $header = array(
    t('Package'),
    t('Module'),
    t('File name'),
  );
  foreach ($languages[1] as $key => $language) {
    $header[] = $key;
  }
  $rows = array();
  $displayed = array();
  $present = theme('image', 'misc/watchdog-ok.png', t('present'), t('present'));
  $missing = theme('image', 'misc/watchdog-error.png', t('missing'), t('missing'));
  foreach ($data as $package => $modules) {
    foreach ($modules as $module => $info) {
      $row = array(
        isset($displayed[$package]) ? '' : $package,
        $info['name'],
        $module,
      );
      $displayed[$package] = TRUE;
      foreach (array_keys($languages[1]) as $language) {
        $row[] = $info['translations'][$language] ? $present : $missing;
      }
      $rows[] = $row;
    }
  }
  return theme('table', $header, $rows);
}

