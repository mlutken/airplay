<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_api/db_string_utils.php');
require_once ('db_manip/AllDbTables.php');


// --- Reources ---
// PHP large integer support: http://www.php.net/manual/de/function.gmp-init.php
// PHP trick to take modulo of hash: http://stackoverflow.com/questions/3379471/php-number-only-hash
// PHP different hashing algorithms: http://www.php.net/manual/en/function.hash.php


global $argv;
$baseDir	= $argv[1];
$start 		= $argv[2];
$count 		= $argv[3];
printf ("baseDir: '%s'\n"   , $baseDir );
printf ("Start ID: '$start'\n" );
printf ("Count   : '$count'\n");

// $g_redis = new Redis();
// $g_redis->connect("127.0.0.1");


function getArtistIdsFromMySql( $start, $count )
{
	global $g_MySqlPDO;
	$q = "SELECT artist_id FROM artist ORDER BY artist_id ASC LIMIT $start, $count";
	$aArtistNames = pdoQueryAllRowsFirstElem($g_MySqlPDO, $q, array() );
	return $aArtistNames;
}



function writeJsonToRedis( $filePath, $aData, $redis  )
{
    $s = json_encode( $aData );
    $redis->set( $filePath, $s );
}



function writeSolrXmlFile( $filePath, $aAllItemBasesAssoc, $aArtistBaseData  )
{
//    printf("filePath: %s\n", $filePath);
    $s = "<add>\n";
    $s .= arrayToSolrXmlString($aArtistBaseData);
    
    foreach ($aAllItemBasesAssoc as $aData ) {
        $s .= arrayToSolrXmlString($aData);
    }
    $s .= "</add>\n";
    file_put_contents( $filePath, $s);
}

function valueIsEmpty( $val, $dbFieldName = '' )
{
    if  ( 'item_used' == $dbFieldName ) return false;
    if  ( '' == $val ) return true;
    if  ( 'info_artist_reliability' == $dbFieldName ) return true;
    if  ( 'artist_id'       == $dbFieldName ) return true;
    if  ( 'item_base_id'    == $dbFieldName ) return true;
    if  ( 'info_artist_id'	== $dbFieldName ) return true;
//     if  ( 'item_price_id'   == $dbFieldName ) return true;
    if  ( 'record_store_id' == $dbFieldName ) return true;
    
    
    if ( is_numeric($val) ) {
        return $val == 0;    
    }
    else if ( '0000-00-00' == $val && endsWith ( $dbFieldName, '_date' ) ){
        return true;
    }
    return false;
}


/** Filter empty values from an associative array (map). */
function filterEmptyAssoc( $aData )
{
    $a = array();
    foreach( $aData as $k => $v ) {
        if ( !valueIsEmpty($v, $k ) ) {
            $a[$k] = $v;
        }
    }
    return $a;
}

/** Filter empty values from a plain array of associative arrays (array of maps). */
function filterEmptyAssocArrays( $aDataRows )
{
    $aDR = array();
    foreach( $aDataRows as $aData ) {
        $aDR[] = filterEmptyAssoc($aData);
    }
    return $aDR;
}



function getArtistFromDB($dba, $artist_id, &$aArtistBaseData, &$aAllItemBasesAssoc )
{
    $aArtistBaseData        = $dba->m_dbArtistData->getBaseData($artist_id);
// //     $aArtistBaseData['name'] = $aArtistBaseData['artist_name'];
    $aArtistBaseData['id']  = $aArtistBaseData['artist_id'] . 'ar';
    $aAllIBs                = $dba->m_dbItemBaseData->getItemsForArtist($artist_id, 0);
    
    $aAllItemBasesAssoc   = array();
    foreach ($aAllIBs as $aItemBaseData ) {
        $aItemData['id'] = $aItemBaseData['item_base_id'] . 'ib';
        $parent_item = $aItemBaseData['parent_item'];
        if ( 0 != (int)$parent_item ) {
            $aParentData = $dba->m_dbItemBaseData->getBaseData($parent_item);
            $aItemBaseData['parent_item_name'] = $aParentData['item_base_name'];
            $aItemBaseData['parent_item_type'] = $aParentData['item_type'];
        }
        $item_type = (int)$aItemBaseData['item_type'];
        if ( 0 == $item_type ) continue;
        
        ////$aItemData['item_base_id'] = $aItemBaseData['item_base_id'];
        $aItemData['artist_name'] = $aArtistBaseData['artist_name'];
        $aItemData['item_base_name'] = $aItemBaseData['item_base_name'];
// //         $aItemData['name'] = $aItemBaseData['item_base_name'];
        $aItemData['item_type'] = $aItemBaseData['item_type'];
        $aItemData['item_year'] = $aItemBaseData['item_year'];
        $aAllItemBasesAssoc[] = $aItemData;
    }
}



function writeArtist( $dba, $baseDir, $artist_id )
{
    global $g_redis;
    $aArtistBaseData = array();
    $aAllItemBases   = array();
    getArtistFromDB( $dba, $artist_id, $aArtistBaseData, $aAllItemBasesAssoc );
    
// //     var_dump ($aAllItemBasesAssoc);
// //     var_dump ($aArtistBaseData);
    
    $artistDir        = "{$baseDir}";
//    @mkdir( $artistDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
    $filePath = "{$artistDir}" . $aArtistBaseData['id'] . '.xml';
    
    writeSolrXmlFile($filePath, $aAllItemBasesAssoc, $aArtistBaseData );
    $artist_name = $aArtistBaseData['artist_name'];
    return $artist_name;
}





if ($argv[4] != '' ) {
	$g_fileDbPrettyJson    	= (bool)$argv[4];
}


$dba = new AllDbTables();
@mkdir( $argv[1], 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating

$aArtistIds = getArtistIdsFromMySql( $start, $count );
$countArtists = count($aArtistIds);
printf("getArtistIdsFromMySql retreived %d number of IDs\n", $countArtists );
printf("Export artists\n");


for ( $i = 0; $i < $countArtists; $i++) {
	$artist_id = $aArtistIds[$i];
    $artist_name = writeArtist($dba, $baseDir, $artist_id );
    if ( $i % 100 == 0 ) printf("$i: $artist_name\n");
	
}
// for ( $i = $argv[2]; $i <= $argv[3]; $i++ ) {
//     $artist_name = writeArtist($dba, $argv[1], $i );
//     if ( $i % 10 == 0 ) printf("$i: $artist_name\n");
// }


?>