<?php 
// $Id$

/// Get Html string with autocomplete offset id tag.
function ap_autocomplete_offset( $offset_top_in_pixels) {
    $page_content = '';
    // include offset_top hack for all browsers except IE
    if ( !strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') ) {
        $page_content .= "<div id='autocomplete_data' offset_top=$offset_top_in_pixels ></div>";   
    }
    return $page_content;
}


function music_search_search() {
	$page_content = '';
	$sForm = drupal_get_form('apms_large_form');
    $page_content .= ap_autocomplete_offset(25);
    // // 	$page_content .= "<div class='radio_horizontal' style='float:left'>" . t('Find:') .  " </div>";
	$page_content .= render( $sForm );
	return $page_content;
}


function ap_music_search_display_results()
{
// 	dfb ($_GET, "music_search_display_album_matches _GET");
	
	$iArtistExactMatches = 0;
	$iAlbumExactMatches  = 0;
	$iSongExactMatches   = 0;
    $results_found = 0;
	$lookup = "";
	if ( isset ($_GET['lookup']) ) { 
        $lookup = $_GET['lookup'];
	}
	$search_for = "";
	
    if ( isset ($_GET['search_for']) ) { 
        $search_for = $_GET['search_for'];
    }
	$page_content = "";
    $page_search_result_description = "";

	if (strlen($lookup) >= 2) { 	      

        /* TODO: Gøre så det er muligt at bruge editor, samt sprog.
        $page_search_result_description .= "<table width='100%'>";
        $page_search_result_description .= "<tr><td>&nbsp;</td></tr>";
        $page_search_result_description .= "<tr><td><h2>Bemærk!</h2> Kunstnernavn starter måske med \"The\" som eks. The Rolling Stones, eller indeholder tegn i navnet som eks. L.O.C. og D-A-D</td></tr>";
        $page_search_result_description .= "<tr><td>&nbsp;</td></tr>";
        $page_search_result_description .= "</table>";*/

        if ( $search_for == 'artist' || $search_for == 'all' ) {
            $page_content .= music_search_display_artist_matches($lookup, $iArtistExactMatches);
            if ($page_content != "") {
                $results_found = 1;
            }
        }
        if ( $search_for == 'album' || $search_for == 'all'  || $search_for == 'title' ) {
            $page_content .= music_search_display_album_matches($lookup, $iAlbumExactMatches);
            if ($page_content != "") {
                $results_found = 1;
            }
        }
        if ( $search_for == 'song' || $search_for == 'all' || $search_for == 'title' ) {
            $page_content .= music_search_display_song_matches($lookup, $iSongExactMatches );
            if ($page_content != "") {
                $results_found = 1;
            }
        }
        $iTotalExactMatches = $iArtistExactMatches + $iAlbumExactMatches + $iSongExactMatches;
        if ( $iArtistExactMatches == 1 && $search_for == 'artist' ) {
			// Redirect if URL is not correct - /music-search/results?search_for=artist&lookup=The 1975 => /kunstner/The_1975
			$url = "/" . ap_artist_suburl() . "/" . airplay_name_to_url(ucwords($lookup));
			header("Location: {$url}",TRUE, 301);
			exit();
            //$page_content = prices_display_artist($lookup, true);
            if ($page_content != "") {
                $results_found = 1;
            }
        }
        else if ( $iAlbumExactMatches == 1 && $search_for == 'album' ) {
            $page_content = prices_display_album($lookup);
            if ($page_content != "") {
                $results_found = 1;
            }
        }
        else if ( $iSongExactMatches == 1 && $search_for == 'song' ) {
            $page_content = prices_display_song($lookup);
            if ($page_content != "") {
                $results_found = 1;
            }
        }
	}
    
    if ($results_found == 0) {
        // Reset search result description - no results.
        $page_search_result_description = "";
        // TODO: Gøre så det er muligt at bruge editor, samt sprog.
        $page_content .= "<table width='100%'>";
        $page_content .= "<tr><td><h1>Beklager !</h1><span style=\"font-size:16px;\">Musiksøgemaskinen fandt ingen match på din søgning.</span><br/><br/><h3>Du har måske stavet navn/titel forkert</h3><br/>(bemærk! Kunstnernavn starter måske med \"The\" som eks. The Rolling Stones, eller indeholder tegn i navnet som eks. L.O.C. og D-A-D)<br/><br/>eller<br/><br/><h3>Søgte kunstner, album eller sang mangler i musiksøgemaskinen</h3><br/><br/>eller<br/><br/><h3>Så er det \"bare\" teknikken der driller...</h3><br/><br/>Uanset årsag beklager vi naturligvis din ulejlighed.<br/><br/>Hvis fejl/mangler hører vi gerne fra dig - <a href=\"/contact\">kontakt os</a></td></tr>";
        $page_content .= "</table>";
    }
    
	return $page_search_result_description . $page_content;
}



function ap_music_search_autocomplete_artist( $sSearch ) 
{   
    $_SESSION['search_for'] = 'artist';
	if ( strlen($sSearch) < 3 )	{
		$aEmtyMatches = array();
		print drupal_json_encode($aEmtyMatches);
		exit();
		return;
	}
	db_set_active('airplay_music');
	
	$sQueryArtists = "
		SELECT artist_name, genre_name FROM {artist} 
		LEFT JOIN {genre} ON artist.genre_id = genre.genre_id 
	 	WHERE artist_name LIKE '{$sSearch}%%'";
	
	$aMatches = array();
	$iMaxResults = 200;
	$result = db_query_range( $sQueryArtists, 0, $iMaxResults, array() );
	while ($o = $result->fetchObject()) {
		$aMatches[ $o->artist_name ] = $o->artist_name . ' (<b>' . $o->genre_name . '</b>)';
	}
	
	db_set_active('default');
	
	print drupal_json_encode($aMatches);
}


function ap_music_search_autocomplete_album( $sSearch ) 
{
    $_SESSION['search_for'] = 'album';
//  	dfb ( "ap_music_search_autocomplete_album");
	if ( strlen($sSearch) < 3 )	{
		$aEmtyMatches = array();
		print drupal_json_encode($aEmtyMatches);
		exit();
		return;
	}
	db_set_active('airplay_music');
	
	$sQueryAlbums = "SELECT DISTINCT( album_simple_name ) FROM {album_simple} WHERE album_simple_name LIKE '{$sSearch}%%'";
	
	$aMatches = array();
	$iMaxResults = 15;
	$result = db_query_range( $sQueryAlbums, 0, $iMaxResults, array() );
	while ($o = $result->fetchObject()) {
		$aMatches[ $o->album_simple_name ] = $o->album_simple_name;
	}
	db_set_active('default');
	
	print drupal_json_encode($aMatches);
}


function ap_music_search_autocomplete_song( $sSearch ) 
{
    $_SESSION['search_for'] = 'song';
 // 	dfb ( "ap_music_search_autocomplete_song");
	if ( strlen($sSearch) < 3 )	{
		$aEmtyMatches = array();
		print drupal_json_encode($aEmtyMatches);
		exit();
		return;
	}
// 	dfb ( "ap_music_search_autocomplete_song");
	db_set_active('airplay_music');
	
	$sQuerySongs = "SELECT DISTINCT( song_simple_name ) FROM {song_simple} WHERE song_simple_name LIKE '{$sSearch}%%'";
	
	$aMatches = array();
	$iMaxResults = 15;
	$result = db_query_range( $sQuerySongs, 0, $iMaxResults, array() );
	while ($o = $result->fetchObject()) {
		$aMatches[ $o->song_simple_name ] = $o->song_simple_name;
	}
	db_set_active('default');
	
	print drupal_json_encode($aMatches);
// 	exit();
}


function apms_autocomplete_set_search_for(  ) 
{
	//$search_for = $_GET['search_for'];
/*	if ( $search_for != "" ) 	$_SESSION['apms_autocomplete__search_for'] = $search_for;
	else						$_SESSION['apms_autocomplete__search_for'] = null;*/
}






function ap_music_search_autocomplete_all( $sSearch ) 
{
// 	dfb("ap_music_search_autocomplete_all");
	if ( strlen($sSearch) < 2 )	{
		$aEmtyMatches = array();
		print drupal_json_encode($aEmtyMatches);
		exit();
		return;
	}
	db_set_active('airplay_music_v1');
	

	$sQueryArtists = "SELECT DISTINCT Res.artist_name FROM ( SELECT artist_name, item_price_count FROM artist WHERE artist_name LIKE :artist_name_like UNION SELECT artist_synonym_name, item_price_count FROM artist_synonym INNER JOIN artist ON artist.artist_id = artist_synonym.artist_id WHERE artist_synonym_name = :artist_name ) AS Res ORDER BY Res.item_price_count DESC";
	$sQueryAlbums  = "SELECT DISTINCT ( item_base_name ) FROM item_base WHERE item_type = 1 AND item_base_name LIKE :item_base_name";
	$sQuerySongs   = "SELECT DISTINCT ( item_base_name ) FROM item_base WHERE item_type = 2 AND item_base_name LIKE :item_base_name";

    /* Full text search Artist */
    //$sQueryArtists = "SELECT DISTINCT artist_name , name_score FROM (SELECT DISTINCT artist_name, MATCH (artist_name, artist_name_alternative_spelling) AGAINST (:artist_name) AS rel_score, CASE WHEN artist_name = :artist_name THEN 1 ELSE 0 END AS name_score FROM artist_lookup WHERE MATCH (artist_name, artist_name_alternative_spelling) AGAINST (:artist_name)";
    /* Lookup exact match like "D-A-D" */
    //if(!preg_match('/^[a-z]+$/i',$sSearch)) {
    //$sQueryArtists .= " UNION SELECT artist_name, 0, 1 FROM artist WHERE artist_name = :artist_name";
    //}
    //$sQueryArtists .= ") AS Res ORDER BY name_score DESC, rel_score DESC";
    
    /* Full text search Album */
    //$sQueryAlbums  = "SELECT DISTINCT item_base_name , name_score FROM (SELECT DISTINCT item_base_name, MATCH (item_base_name, item_base_name_alternative_spelling) AGAINST (:item_base_name) AS rel_score, CASE WHEN item_base_name = :item_base_name THEN 1 ELSE 0 END AS name_score FROM item_base_lookup WHERE item_type = 1 AND MATCH (item_base_name, item_base_name_alternative_spelling) AGAINST (:item_base_name)";
	/* Lookup exact match like "D-A-D" */
    //if(!preg_match('/^[a-z]+$/i',$sSearch)) {
    //$sQueryAlbums .= " UNION SELECT item_base_name, 0, 1 FROM item_base WHERE item_base_name = :item_base_name";
    //}
    //$sQueryAlbums .= ") AS Res ORDER BY name_score DESC, rel_score DESC";
    
    /* Full text search Album */
    //$sQuerySongs  = "SELECT DISTINCT item_base_name , name_score FROM (SELECT DISTINCT item_base_name, MATCH (item_base_name, item_base_name_alternative_spelling) AGAINST (:item_base_name) AS rel_score, CASE WHEN item_base_name = :item_base_name THEN 1 ELSE 0 END AS name_score FROM item_base_lookup WHERE item_type = 2 AND MATCH (item_base_name, item_base_name_alternative_spelling) AGAINST (:item_base_name)";
	/* Lookup exact match like "D-A-D" */
    //if(!preg_match('/^[a-z]+$/i',$sSearch)) {
    //$sQuerySongs .= " UNION SELECT item_base_name, 0, 1 FROM item_base WHERE item_base_name = :item_base_name";
    //}
    //$sQuerySongs .= ") AS Res ORDER BY name_score DESC, rel_score DESC";
    
	$aMatches = array();
	$aArtists = array();
	$aAlbums = array();
	$aSongs = array();
	$iMaxResults = 10;
   
	$result = db_query_range( $sQueryArtists, 0, $iMaxResults, array(':artist_name_like' => $sSearch ."%", ':artist_name' => $sSearch) );

    //$result = db_query_range( $sQueryArtists, 0, $iMaxResults, array(':artist_name' => "" . $sSearch . ""));
    $count = 0;
    while ($o = $result->fetchObject()) {
        if ($count == 0) {
            $aArtists[] = "_arti<b>" . t('Artist') . "</b>";
        }
		$aArtists[] = "_arti" . $o->artist_name;
        $count++;
	}
    
	$result = db_query_range( $sQueryAlbums, 0, $iMaxResults, array(':item_base_name' => "". $sSearch . "%"));
	//$result = db_query_range( $sQueryAlbums, 0, $iMaxResults, array(':item_base_name' => "" . $sSearch . ""));

    $count = 0;
	while ($o = $result->fetchObject()) {
        if ($count == 0) {
            $aAlbums[] = "_albu<b>" . t('Album') . "</b>";
        }
		$aAlbums[] = "_albu" . $o->item_base_name;
        $count++;
	}
    
	$result = db_query_range( $sQuerySongs, 0, $iMaxResults, array(':item_base_name' => "". $sSearch ."%"));
	//$result = db_query_range( $sQuerySongs, 0, $iMaxResults, array(':item_base_name' => "" . $sSearch . ""));

    $count = 0;
	while ($o = $result->fetchObject()) {
        if ($count == 0) {
            $aSongs[] = "_song<b>" . t('Song') . "</b>";
        }
		$aSongs[] = "_song" . $o->item_base_name;
        $count++;
	}
    
	db_set_active('default');

	for ( $i = 0; $i < count ($aArtists); $i++ )
	{
		$aMatches[ $aArtists[$i] ] = $aArtists[$i]; // . ' (<b>' . t('Artist') . '</b>)';
	}
	for ( $i = 0; $i < count ($aAlbums); $i++ )
	{
		$aMatches[ $aAlbums[$i] ] = $aAlbums[$i]; // . ' (<b>' . t('Album') . '</b>)';
	}
	for ( $i = 0; $i < count ($aSongs); $i++ )
	{
		$aMatches[ $aSongs[$i] ] = $aSongs[$i]; // . ' (<b>' . t('Song') . '</b>)';	
	}
	print drupal_json_encode($aMatches);
}



function ap_music_search_autocomplete_title( $sSearch ) 
{
//      dfb ( "ap_music_search_autocomplete_title");
    if ( strlen($sSearch) < 3 ) {
        $aEmtyMatches = array();
        print drupal_json_encode($aEmtyMatches);
        exit();
        return;
    }
    db_set_active('airplay_music');
    
    $sQueryAlbums = "SELECT DISTINCT( album_name ) FROM {album} WHERE album_name LIKE '%s%%'";
    $sQuerySongs = "SELECT DISTINCT( song_name ) FROM {song} WHERE song_name LIKE '%s%%'";
    
    $aMatches = array();
    $aAlbums = array();
    $aSongs = array();
    $iMaxResults = 200;
    $result = db_query_range( $sQueryAlbums, 0, $iMaxResults, $sSearch);
    while ($o = $result->fetchObject()) {
        $aAlbums[] = $o->album_name;
    }
    
    $result = db_query_range( $sQuerySongs, 0, $iMaxResults, $sSearch);
    while ($o = $result->fetchObject()) {
        $aSongs[] = $o->song_name;
    }
    db_set_active('default');
    
    $bStillMatches = true;
    $i = 0;
    while ( $bStillMatches )
    {
        $bStillAlbums = count ($aAlbums) > $i;  
        $bStillSongs = count ($aSongs) > $i;    
        $bStillMatches = $bStillAlbums || $bStillSongs;
        
        if ( $bStillAlbums ) {
            $aMatches[ $aAlbums[$i] ] =  $aAlbums[$i];
        }
        if ( $bStillSongs ) {
            $aMatches[ $aSongs[$i] ] = $aSongs[$i];
        }
        $i++;   
    }   
    print drupal_json_encode($aMatches);
//  exit();
}



// 
// '(<b>' . t('Ar') . '</b>) ' . 

// -------------------
// --- FORM: small --- 
// -------------------

//  

/*
function ap_music_search_form_small(&$form_state) 
{
	$form['search_for'] = array (
	'#type' => 'radios',
	'#default_value' => 'artist',
	'#options' => array (
		'artist'   => t('Artist'),
		'album'   => t('Album'),
		'song' => t('Song'),
		'all'   => t('All'),)
	);	
	$form['lookup'] = array(
		'#type' => 'textfield',
		'#size' => 20,
		'#maxlength' => 64,
		'#autocomplete_path' => 'music-search/autocomplete_all',
		'#description' => t('Lookup artists, albums, songs')
	);
	$form['submit'] = array('#type' => 'submit', '#value' => t('Search music'));
	return $form;
}

function ap_music_search_form_small_alter(&$form, &$form_state)
{
	dfb( $form_state['search_for']['#options'] ,"ap_music_search_form_small");
}

function ap_music_search_form_small_validate($form, &$form_state) {
 	dfb ("ap_music_search_form_small_validate");
}

function ap_music_search_form_small_submit($form, &$form_state) 
{
 	dfb ("ap_music_search_form_small_submit");
	$lookup = $form_state['values']['lookup'];
 	$form_state['redirect'] = array("music-search/results", array("lookup" => "$lookup" ) ); 
}
*/
// -------------------
// --- FORM: large ---
// -------------------
/*
function ap_music_search_form_large(&$form_state) 
{
	// Access log settings:
	$form['lookup'] = array(
		'#type' => 'textfield',
		'#size' => 40,
		'#maxlength' => 64,
		'#autocomplete_path' => 'music-search/autocomplete_all',
		'#description' => t('Lookup artists, albums, songs')
	);
	$form['submit'] = array('#type' => 'submit', '#value' => t('Search music'));
	return $form;
}

function ap_music_search_form_large_validate($form, &$form_state) {
//  	dfb ("ap_music_search_form_large_validate");
}

function ap_music_search_form_large_submit($form, &$form_state) 
{
// 	dfb("ap_music_search_form_large_submit");
	$lookup = $form_state['values']['lookup'];
 	$form_state['redirect'] = array("music-search/results", array("lookup" => "$lookup" ) ); 
}

function ap_music_search_form_large_alter(&$form, &$form_state)
{
// 	dfb("ap_music_search_form_large_alter");
}

*/
