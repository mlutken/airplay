<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('db_manip/MusicDatabaseManip.php');

$query = $_GET['query'];

$minLen = 3;
if ( startsWith ( $query, 'the', false ) ) $minLen = 6;

$ok = strlen($query) >= $minLen; 
if ( $query == 'U2' || $query == 'u2' ) $ok = true;

if ( $ok ) {
    $fac                = new MusicDatabaseFactory();
    $dbItemTypeConvert  = $fac->createDbInterface('ItemTypeConvert');
    $dbArtist           = $fac->createDbInterface('ArtistData');

    $m = new MusicDatabaseManip();

    $aArtists   = array();
    $aItemBases = array();

    $m->lookupSimilarNamesOut( $query, $aArtists, $aItemBases );
    $aSuggestions = array();

    $i = 0;
    foreach( $aArtists as $aArtist ) {
        $i++;
        $aSuggestions[] = array( 'value' => $aArtist['artist_name'] . "&nbsp;<i><b>(artist)</b</i>", 'data' => array($aArtist['artist_id'], 0 ) );
        if ( $i > 12 ) break;
    }

    $i = 0;
    foreach( $aItemBases as $aItemBase ) {
        $i++;
        $artistName     = $dbArtist->IDToName($aItemBase['artist_id']);
        $itemTypeName   = $dbItemTypeConvert->IDToName($aItemBase['item_type']);
        $aSuggestions[] = array( 'value' => $aItemBase['item_base_name'] . "&nbsp;<i><b>($itemTypeName - $artistName)</b</i>", 'data' => array( $aItemBase['artist_id'], $aItemBase['item_base_id'] ) );
        if ( $i > 12 ) break;
    }

    $a = array( 'query' => $query, 'suggestions' => $aSuggestions );

//     $sj = pretty_json (json_encode($_POST) );
//     $sj .= pretty_json (json_encode($_GET) );
//     $sj .= "\n";
//     $sj .= pretty_json( json_encode($aArtists) );
//     $sj .= "\n";
//     $sj .= pretty_json( json_encode($aItemBases) );
//     $sj .= "\n";
//     $sj .= pretty_json( json_encode($a) );
//     file_put_contents("/tmp/MusicDatabaseAutocomplete_ajax_dbg.txt", $sj );


    print json_encode($a);
}
else {
    print json_encode( array( 'query' => $query, 'suggestions' =>array() ) );
}
?>