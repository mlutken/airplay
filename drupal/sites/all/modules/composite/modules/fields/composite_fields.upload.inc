<?php

/**
 * Field subtype api function for 'upload' field
 */
function composite_fields_composite_field_upload_api(&$reference, $op, $node = NULL, $a4 = NULL) {
  switch ($op) {
    // Generate and insert an informative human-readable string into ['info']
    case 'info':
      $reference['info'] = t('File attachments');
      break;

    // Return a rendering of the reference item
    case 'view':
      if (isset($node->files) && user_access('view uploaded files') && count($node->files)) {
        return theme('upload_attachments', $node->files);
      }    
      break;
  }
}

/**
 * Potentials callback function for 'upload' field.
 *
 * Return an 'upload' item for the fields tab, if the node type has attachments enabled. 
 */
function composite_fields_composite_field_upload_potentials($node) {
  $options = array();
  if (variable_get("upload_$node->type", 1) == 1) {
    $options['upload'] = t('File attachments');    
  }
  return $options;
}

