<?php

function apms_large_form_OLD_ML($form, &$form_state) 
{
	$aSettings = ap_read_settings();
	$sCountArtists 	= number_format( $aSettings['artist__count'], 0, "", " " );	
	$sCountAlbums 	= number_format( $aSettings['album__count'], 0, "", " " );	
	$sCountSongs 	= number_format( $aSettings['song__count'], 0, "", " " );	


    $form['apms_search_for_large'] = array (
    '#type' => 'radios',
//    '#default_value' => ap_user_search_for(),
    '#default_value' => 'artist',
    '#options' => array (
        'artist'    => "<div class='label'><span class='text'>" . t('Artist') . "</span><span class='count'>($sCountArtists)</span></div>",
//      'title'         => t('Title (album/song)'),
        'album'     => "<div class='label'><span class='text'>" . t('Album') . "</span><span class='count'>($sCountAlbums)</span></div>",
        'song'      => "<div class='label'><span class='text'>" . t('Song') . "</span><span class='count'>($sCountSongs)</span></div>",
        ),
    '#attributes' => array( 'onclick' => array("ap_music_search_autocomplete_set_search_for(this);") , 
                            "class" => array('ap_radio_horizontal') ) 
    );  

    $form['apms_lookup_large_artist'] = array(
        '#type' => 'textfield',
        '#size' => 25,
        '#maxlength' => 50,
        '#autocomplete_path'    => 'music-search/autocomplete_artist',
        '#attributes'           => array ( 'class' => array('ap_form-edit-large') )
    );
    $form['apms_lookup_large_album'] = array(
        '#type' => 'textfield',
        '#size' => 25,
        '#maxlength' => 50,
        '#autocomplete_path'    => 'music-search/autocomplete_album',
        '#attributes'           => array ( 'class' => array('ap_form-edit-large'), 'style' => array('visibility:hidden;display:none') )
    );
    $form['apms_lookup_large_song'] = array(
        '#type' => 'textfield',
        '#size' => 25,
        '#maxlength' => 50,
        '#autocomplete_path'    => 'music-search/autocomplete_song',
        '#attributes'           => array ( 'class' => array('ap_form-edit-large'), 'style' => array('visibility:hidden;display:none') )
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


function apms_large_form_submit_OLD_ML($form, &$form_state) 
{
	$search_for = $form_state['values']['apms_search_for_large'];
	$lookup = $form_state['values']['apms_lookup_large_' . $search_for ];
	
    $_SESSION['search_for'] = $search_for;

	if ( $search_for == "artist" ) {
		$sql = "SELECT COUNT(*) FROM {artist} WHERE artist_name = :artist_name";
		db_set_active('airplay_music');
		$res = db_query( $sql, array( ':artist_name' => $lookup ) );
		$a = $res->fetchAssoc();
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
        $sql = "SELECT COUNT(*) FROM {$item}_simple WHERE {$item}_simple_name = :simple_name";
        db_set_active('airplay_music');
        $res = db_query( $sql, array( ':simple_name' => $lookup ) );
        $a = $res->fetchAssoc();
        db_set_active('default');
        if ( $a && $a['COUNT(*)'] == 1 ) {
            $sql = "
                    SELECT artist_name, {$item}_name, {$item}_simple_name
                    FROM artist
                    INNER JOIN {$item} ON {$item}.artist_id=artist.artist_id 
                    INNER JOIN {$item}_simple ON {$item}_simple.{$item}_simple_id={$item}.{$item}_simple_id 
                    WHERE {$item}_simple_name = :simple_name
                   " ;


            db_set_active('airplay_music');
            $res = db_query( $sql, array( ':simple_name' => $lookup ) );
            $a = $res->fetchAssoc();
            db_set_active('default');
        
            $artist_url_name = airplay_name_to_url($a['artist_name']);    
            $item_url_name = airplay_name_to_url($a["{$item}_simple_name"]);    
            $form_state['redirect'] = array( ap_artist_suburl() . "/{$artist_url_name}/" . ap_item_suburl($item) . "/${item_url_name}" , array() ); 
            return;
        }
    }
	
	drupal_set_message( "search_for: $search_for, lookup: $lookup" ) ;	
    $form_state['redirect'] = array( 'music-search/results',
        array(  
        'query' => array(
                'search_for'    => "$search_for",
                'lookup'        => "$lookup",
            ),
        'fragment' => '',
    ),
);
    
	
}

function apms_large_form($form, &$form_state) 
{
	$default_search_for = "all";
	$default_lookup = t("Search for artist, album or song");

	$form['apms_search_for_all'] = array(
        '#type' => 'hidden',
		'#default_value' => $default_search_for
    );
    $form['apms_lookup_large_all'] = array(
        '#type' => 'textfield',
		'#default_value' => $default_lookup,
        '#size' => 25,
        '#maxlength' => 50,
        '#autocomplete_path'    => 'music-search/autocomplete_all',
        '#attributes'           => array ( 'class' => array('ap_form-edit-large'), 'onFocus' => "this.className = 'ap_form-edit-large-focus'; if (this.value == '$default_lookup') { this.value=''; }" )
    );
    $form['apms_large_form_submit'] = array(
          '#type'     => 'submit'
        , '#value'    => '' // t('Search music')
    );
	return $form;
}


function apms_large_form_submit($form, &$form_state) 
{
    /* Reset media_format on new search */
    global $user;
    $_SESSION['media_format'] = "ALL";
    
	$search_for = $form_state['values']['apms_search_for_all'];
	$lookup = $form_state['values']['apms_lookup_large_all'];
    
	if ( $search_for == "artist" ) {
		//$sql = "SELECT COUNT(*) FROM artist WHERE artist_name = :artist_name";
        $sql = "SELECT COUNT(*) FROM ( SELECT artist_name FROM artist WHERE artist_name = :artist_name LIMIT 0, 2 ) AS Res";
        
		db_set_active('airplay_music_v1');
		$res = db_query( $sql, array( ':artist_name' => $lookup ) );
		$a = $res->fetchAssoc();
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
        //$sql = "SELECT COUNT(*) FROM {$item}_simple WHERE {$item}_simple_name = :simple_name";
        $item_type = 1;
        if ($search_for == "song") {
            $item_type = 2;
        }
        
        //$sql = "SELECT COUNT(*) FROM item_base WHERE item_type = :item_type AND item_base_name = :item_base_name";
        $sql = "SELECT COUNT(*) FROM ( SELECT item_base_name FROM item_base WHERE item_type = :item_type AND item_base_name = :item_base_name LIMIT 0, 2 ) AS Res";
        db_set_active('airplay_music_v1');
        $res = db_query( $sql, array( ':item_type' => $item_type, ':item_base_name' => $lookup ) );
        $a = $res->fetchAssoc();
        db_set_active('default');

        if ( $a && $a['COUNT(*)'] == 1 ) {
            $sql = "
                SELECT artist_name, item_base_name
                FROM artist
                INNER JOIN item_base ON artist.artist_id = item_base.artist_id
                WHERE item_type = :item_type AND item_base_name = :item_base_name
            ";
            db_set_active('airplay_music_v1');
            $res = db_query( $sql, array( ':item_type' => $item_type, ':item_base_name' => $lookup ) );
            $a = $res->fetchAssoc();
            db_set_active('default');
        
            $artist_url_name = airplay_name_to_url($a['artist_name']);    
            $item_url_name = airplay_name_to_url($a["item_base_name"]);    
            $form_state['redirect'] = array( ap_artist_suburl() . "/{$artist_url_name}/" . ap_item_suburl($item) . "/${item_url_name}" , array() ); 
            return;
        }
    }
	
//	drupal_set_message( "search_for: $search_for, lookup: $lookup" ) ;	
    $form_state['redirect'] = array( 'music-search/results',
        array(  
        'query' => array(
                'search_for'    => "$search_for",
                'lookup'        => "$lookup",
            ),
        'fragment' => '',
    ),
);
}
