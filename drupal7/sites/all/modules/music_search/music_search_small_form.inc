<?php

function apms_small_form($form, &$form_state) 
{
	////dfb($form_state, "AAAAAAAAAAA apms_small_form");
	
	$form['apms_search_for_small'] = array (
	'#type' => 'radios',
	'#default_value' => 'artist',
	'#options' => array (
		'artist'   		=> t('Artist') ,
 		'album'   		=> t('Album'),
 		'song' 			=> t('Song'),
// 		'title' 		=> t('Title (album/song)'),
// 		'all'   		=> t('All'),
		),
		'#attributes' => array('onclick' => array("apms_small_form_alter_autocomplete_path(this);") )	
	);	
	
	$form['apms_lookup_small'] = array(
		'#type' => 'textfield',
		'#size' => 18,
		'#maxlength' => 64,
		'#autocomplete_path' => 'music-search/autocomplete_artist'
	);
	
	$form['apms_small_form_submit'] = array(
		'#type' => 'submit',
		'#value' => t('Search music')
	);
	return $form;
}


function apms_small_form_submit($form, &$form_state) {
	$lookup = $form_state['values']['apms_lookup_small'];
	$search_for = $form_state['values']['apms_search_for_small'];
	$form_state['redirect'] = array("music-search/results", array("lookup" => "$lookup", "search_for" => "$search_for" ) ); 
// 	drupal_set_message( "1 lookup: " . $lookup ) ;	
// 	drupal_set_message( "2 search_for: " . $search_for ) ;	
}
