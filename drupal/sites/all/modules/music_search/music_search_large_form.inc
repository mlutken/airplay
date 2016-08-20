<?php

function apms_large_form($form_state) 
{
	$aSettings = ap_read_settings();
	$sCountArtists 	= number_format( $aSettings['artist__count'], 0, "", " " );	
	$sCountAlbums 	= number_format( $aSettings['album__count'], 0, "", " " );	
	$sCountSongs 	= number_format( $aSettings['song__count'], 0, "", " " );	


    $form['apms_search_for_large'] = array (
    '#type' => 'radios',
    '#default_value' => ap_user_search_for(),
    '#options' => array (
        'artist'    => "<div class='label'><span class='text'>" . t('Artist') . "</span><span class='count'>($sCountArtists)</span></div>",
//      'title'         => t('Title (album/song)'),
        'album'     => "<div class='label'><span class='text'>" . t('Album') . "</span><span class='count'>($sCountAlbums)</span></div>",
        'song'      => "<div class='label'><span class='text'>" . t('Song') . "</span><span class='count'>($sCountSongs)</span></div>",
        ),
    '#attributes' => array( 'onclick' => "ap_music_search_autocomplete_set_search_for(this);" , 
                            "class" => 'ap_radio_horizontal' ) 
    );  

    $form['apms_lookup_large_artist'] = array(
        '#type' => 'textfield',
        '#size' => 25,
        '#maxlength' => 50,
        '#autocomplete_path'    => 'music-search/autocomplete_artist',
        '#attributes'           => array ( 'class' => 'ap_form-edit-large' )
    );
  $form['apms_lookup_large_album'] = array(
        '#type' => 'textfield',
        '#size' => 25,
        '#maxlength' => 50,
        '#autocomplete_path'    => 'music-search/autocomplete_album',
        '#attributes'           => array ( 'class' => 'ap_form-edit-large', 'style' => 'visibility:hidden;display:none' )
    );
    $form['apms_lookup_large_song'] = array(
        '#type' => 'textfield',
        '#size' => 25,
        '#maxlength' => 50,
        '#autocomplete_path'    => 'music-search/autocomplete_song',
        '#attributes'           => array ( 'class' => 'ap_form-edit-large', 'style' => 'visibility:hidden;display:none' )
    );
    $form['apms_large_form_submit'] = array(
          '#type'     => 'submit'
        , '#value'    => t('Search music')
    );

	
	// apms_large_form_alter_autocomplete_path(this);
	
/*	$form['apms_lookup_large_all'] = array(
		'#type' => 'textfield',
		'#size' => 25,
		'#maxlength' => 64,
		'#autocomplete_path' 	=> 'music-search/autocomplete_all',
		'#attributes'			=> array ( 'class' => 'form-edit-large')
	);*/
	
	
	return $form;
}


function apms_large_form_submit($form, &$form_state) {
	$search_for = $form_state['values']['apms_search_for_large'];
	$lookup = $form_state['values']['apms_lookup_large_' . $search_for ];
	
    $_SESSION['search_for'] = $search_for;

	if ( $search_for == "artist" ) {
		$sql = "SELECT COUNT(*) FROM {artist} WHERE artist_name = '%s'";
		db_set_active('airplay_music');
		$res = db_query( $sql, array( $lookup ) );
		$a = db_fetch_array($res);
        ////drupal_set_message( "artist: COUNT(*): '" . $a['COUNT(*)'] . "'  lookup: '" . $lookup . "'") ;  
		db_set_active('default');
		if ( $a && $a['COUNT(*)'] == 1 && $lookup != "" ) {
	 		$artist_url_name = airplay_name_to_url($lookup);	
			$form_state['redirect'] = array( ap_artist_suburl() . "/{$artist_url_name}", array() ); 
	 		return;
	 	}
	}
    else if ( $search_for == "album" || $search_for == "song" ) {
        $item = $search_for;
        $sql = "SELECT COUNT(*) FROM {$item}_simple WHERE {$item}_simple_name = '%s'";
        db_set_active('airplay_music');
        $res = db_query( $sql, array( $lookup ) );
        $a = db_fetch_array($res);
        db_set_active('default');
        ////drupal_set_message( "COUNT(*): " . $a['COUNT(*)'] ) ;  
        if ( $a && $a['COUNT(*)'] == 1 ) {
            $sql = "
                    SELECT artist_name, {$item}_name, {$item}_simple_name
                    FROM artist
                    INNER JOIN {$item} ON {$item}.artist_id=artist.artist_id 
                    INNER JOIN {$item}_simple ON {$item}_simple.{$item}_simple_id={$item}.{$item}_simple_id 
                    WHERE {$item}_simple_name = '%s'
                   " ;


            db_set_active('airplay_music');
            $res = db_query( $sql, array( $lookup ) );
            $a = db_fetch_array($res);
            db_set_active('default');
        
            $artist_url_name = airplay_name_to_url($a['artist_name']);    
            $item_url_name = airplay_name_to_url($a["{$item}_simple_name"]);    
            $form_state['redirect'] = array( ap_artist_suburl() . "/{$artist_url_name}/" . ap_item_suburl($item) . "/${item_url_name}" , array() ); 
            return;
        }
    }
	
	$form_state['redirect'] = array("music-search/results", 
			array(
			"search_for" 	=> "$search_for", 
			"lookup" 		=> "$lookup"
			) ); 
// //  	 		drupal_set_message( "Artist url name: " . $artist_url_name ) ;	
}

