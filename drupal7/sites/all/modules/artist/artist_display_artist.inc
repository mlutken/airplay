<?php 
// $Id$

function setTitleAndMeta($title = null, $metaK = null, $metaD = null){
	drupal_set_title($title);   //Set page title
	$metaDescription = "";
	$metaKeywords = "";
	$meta = $metaKeywords . $metaDescription;
	drupal_set_html_head($meta);
}


function artist_all() {
	$sPageContent = '';
	
    $sArtistSubUrl      = '';
    $sArtistName        = '';
	$sArtistItemType 	= '';
	$sItemTitle			= '';
	
	artist_decode_url( $sArtistSubUrl, $sArtistName, $sArtistItemType, $sItemTitle );
    
////    dfb ( "sArtistName: '{$sArtistName}', sArtistItemType: '{$sArtistItemType}',  sItemTitle: '{$sItemTitle}'" );


    // If artist suburl does not match current language, then return not found and suggest correct link
    if ( ap_artist_suburl() != $sArtistSubUrl ) {
        $sNotFound  = "<h2>" . t("Sorry artist") . " '{$sArtistName}' " . t("not found") . "</h2>";
        $sNotFound .= "<span>" . t("Perhaps") . " " . t("try") . ":</span>";
        $sNotFound .= "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($sArtistName) . "\">{$sArtistName}</a>";
        return $sNotFound;
    }
    else { 
        if ( $sArtistItemType == ap_album_suburl() ) {
            if ( $sItemTitle == "" ) {
                
            }
            else {
                return prices_display_album( $sItemTitle, $sArtistName );
            }
        }
        else if ( $sArtistItemType == ap_song_suburl() ) {
            if ( $sItemTitle == "" ) {
                
            }
            else {
                return prices_display_song( $sItemTitle, $sArtistName );
            }
            
        }
        else {
            return prices_display_artist( $sArtistName, false );
        }
    }
	
	

// 	$artistInfo = airplay_base_lookup_artist_exact	( $sArtistName);
// 	if ( $artistInfo === false ) {
// 		$sPageContent = "<h2>" . t("Sorry artist") . " '{$sArtistName}' " . t("not found") . "</h2>";
// 		return $sPageContent;
// 	}
// 	if 		( $sSubPage == '' )				return artist_main 			( $sArtistName );
// 	else if ( $sSubPage == 'discography' )	return artist_discography 	( $sArtistName );
// 	else if ( $sSubPage == 'album' )		return artist_album 		( $sArtistName, $sTitle );
// 	else if ( $sSubPage == 'song' )			return artist_song 			( $sArtistName, $sTitle );
	
}


function artist_main( $sArtistName ) {
	$artistInfo = airplay_base_lookup_artist_exact	( $sArtistName );
	drupal_set_title( check_plain($artistInfo->artist_name) );
// 	drupal_set_html_head("<title>drupal_set_html_head - Google-søgning</title>");
// 	setTitleAndMeta("motorbikes | daipratt.co.uk", "motorbikes, gsxr, donors", "I really hope that motorcyclist has signed his organ donor card. If he keeps riding like that and he won't need them anymore.");

	
	$sPageContent = "";
	$sPageContent .= "<table><tr>";
//  	$sPageContent .= "<td><h2>" . $artistInfo->artist_name . "</h2><td>";
//  	$sPageContent .= "<td>&nbsp;<td>";
 	
 	$sPageContent .= "<td>( {$artistInfo->genre_name} )<td>";
	$sHomePage 	= "<a target='_blank' href=\"" . $artistInfo->artist_url . "\">" . $artistInfo->artist_url . "</a>";
 	$sPageContent .= "<td>$sHomePage<td>";
	$sPageContent .= "</table></tr>";
	
	$sDiscography 	= "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($sArtistName) . "/discography\">" . t('Discography') . "</a>";
	$sPageContent .= $sDiscography;
		
// // 	$iDKK = 100;
// // 	$iSEK = $iDKK;	
// //  	$iSEK = currency_api_convert("DKK", "SEK", $iDKK );
// //  	dfb ( $iSEK, "$iSEK" );
// //  	$sPageContent .= "<div>Fra DKK til SEK: $iDKK => $iSEK</div>";
	
	return $sPageContent;
}


function artist_discography( $sArtistName ) {
	$sQuery = "
		SELECT album_name, album_year
		FROM album
		WHERE album.artist_id = :artist_id 
		ORDER BY album_year DESC
		";

	$artistInfo = airplay_base_lookup_artist_exact	( $sArtistName );
	$sPageContent = "";
	$sPageContent .= "<table><tr>";
 	$sPageContent .= "<td><h2>" . $artistInfo->artist_name . "</h2><td>";
 	$sPageContent .= "<td>( discography )<td>";
	$sPageContent .= "</table></tr>";
	
	db_set_active('airplay_music');
	
	$query_result = db_query ( $sQuery, array( ':artist_id' => $artistInfo->artist_id) );
	
	// ---------------------
	// --- Table content ---
	// ---------------------
 	$rows = array();
 	$bEmptyQuery = true;
	while ($a = $query_result->fetchObject()) {
 		$bEmptyQuery = false;
		////$sViewPricesUrl ="/prices?search_for=album&lookup=" . urlencode($a->album_name) .  "&artist_name=" . urlencode($a->artist_name);
		$albumUrl 	= "<a href=\"/" . ap_artist_suburl() . "/" . airplay_name_to_url($sArtistName) . "/album/" . airplay_name_to_url($a->album_name) . "\">" . $a->album_name . "</a>";
// 		$albumUrl = $a->album_name;
		$rows[]  = array(  
			$albumUrl,
			$a->album_year
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		t('Album'), 
		t('Year')
		);
	
	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme( 'table', $header, $rows );
	
	$sPageContent .= $tableHTML;
	return $sPageContent;
}


function artist_album( $sArtistName, $sAlbumName ) 
{
// 	$sQuery = "
// 		SELECT song_name, song_time, track_number
// 		FROM album_song_rel
// 		INNER JOIN song ON song.song_id = album_song_rel.song_id 
// 		INNER JOIN album ON album.artist_id = song.artist_id 
// 		WHERE album.album_name = '%s' AND album.artist_id = %d 
// 		ORDER BY track_number ASC
// 		";
	
	$sQuery = "
		SELECT song_name, song_time, track_number
		FROM album_song_rel  
		INNER JOIN song ON song.song_id = album_song_rel.song_id  
		WHERE album_id = :album_id 
		ORDER BY track_number ASC
		";
	
	$artistInfo = airplay_base_lookup_artist_exact	( $sArtistName );
// 	dfb ( $sQuery, "sQuery" );
	$albumInfo = ap_lookup_album_exact	( $artistInfo->artist_id, $sAlbumName );
// 	dfb ( $albumInfo, "albumInfo" );
	
// 	dfb ( "XX ap_lookup_album_exact: {$artistInfo->artist_id}, $sAlbumName  ");
// 	dfb ( $sAlbumName, "sAlbumName" );
	$sPageContent = "";
	$sPageContent .= "<table><tr>";
 	$sPageContent .= "<td><h2>" . $artistInfo->artist_name . "</h2><td>";
 	$sPageContent .= "<td><h2> $sAlbumName </h2><td>";
 	$sPageContent .= "<td>[" . t('album') . "]<td>";
	$sPageContent .= "</table></tr>";
	
	db_set_active('airplay_music');
	
	$query_result = db_query ( $sQuery, array( ':album_id' => $albumInfo->album_id) );
	
	
	// ---------------------
	// --- Table content ---
	// ---------------------
 	$rows = array();
 	$bEmptyQuery = true;
	while ($a = $query_result->fetchObject()) {
 		$bEmptyQuery = false;
		////$sViewPricesUrl ="/prices?search_for=album&lookup=" . urlencode($a->album_name) .  "&artist_name=" . urlencode($a->artist_name);
// 		$time = sprintf( "%02.2d:%02.2d", floor( $a->song_time / 60 ), $a->song_time % 60 );;
		$time = sprintf( "%d:%02d", floor( $a->song_time / 60 ), $a->song_time % 60 );;
		$rows[]  = array(  
			$a->track_number,
			$a->song_name,
			$time
			);
	}
	db_set_active('default');
	
	// --------------------
	// --- Table header ---
	// --------------------
	$header = array( 
		'', 
		t('Name'), 
		t('Time')
		);
	
	// --- Render Table ---
	$tableHTML = "";
 	if ( !$bEmptyQuery) $tableHTML = theme( 'table', $header, $rows );
	
	$sPageContent .= $tableHTML;
	return $sPageContent;
}

function artist_song( $sArtistName, $sSongName ) {
	$artistInfo = airplay_base_lookup_artist_exact	( $sArtistName );
	$sPageContent = "";
	$sPageContent .= "<table><tr>";
 	$sPageContent .= "<td><h2>" . $artistInfo->artist_name . "</h2><td>";
 	$sPageContent .= "<td>( song XX $sSongName )<td>";
	$sPageContent .= "</table></tr>";
	
	return $sPageContent;
}


// // function ap_test_form($form_state) {
// //   // Access log settings:
// //   $options = array('1' => t('Enabled'), '0' => t('Disabled'));
// //   $form['access'] = array(
// //     '#type' => 'fieldset',
// //     '#title' => t('Access log settings'),
// //     '#tree' => TRUE,
// //   );
// //   $form['access']['log'] = array(
// //     '#type' => 'radios',
// //     '#title' => t('Log'),
// //     '#default_value' =>  variable_get('log', 0),
// //     '#options' => $options,
// //     '#description' => t('The log.'),
// //   );
// //   $period = drupal_map_assoc(array(3600, 10800, 21600, 32400, 43200, 86400, 172800, 259200, 604800, 1209600, 2419200, 4838400, 9676800), 'format_interval');
// //   $form['access']['timer'] = array(
// //     '#type' => 'select',
// //     '#title' => t('Discard logs older than'),
// //     '#default_value' => variable_get('timer', 259200),
// //     '#options' => $period,
// //     '#description' => t('The timer.'),
// //   );
// //   // Description
// //   $form['details'] = array(
// //     '#type' => 'fieldset',
// //     '#title' => t('Details'),
// //     '#collapsible' => TRUE,
// //     '#collapsed' => TRUE,
// //   );
// //   $form['details']['description'] = array(
// //     '#type' => 'textarea',
// //     '#title' => t('Describe it'),
// //     '#default_value' =>  variable_get('description', ''),
// //     '#cols' => 60,
// //     '#rows' => 5,
// //     '#description' => t('Log description.'),
// //   );
// //   $form['details']['admin'] = array(
// //     '#type' => 'checkbox',
// //     '#title' => t('Only admin can view'),
// //     '#default_value' => variable_get('admin', 0),
// //   );
// //   $form['name'] = array(
// //     '#type' => 'textfield',
// //     '#title' => t('Name'),
// //     '#size' => 30,
// //     '#maxlength' => 64,
// //     '#description' => t('Enter the name for this group of settings'),
// //   );
// //   $form['hidden'] = array('#type' => 'value', '#value' => 'is_it_here');
// //   $form['submit'] = array('#type' => 'submit', '#value' => t('Save'));
// //   return $form;
// // }
