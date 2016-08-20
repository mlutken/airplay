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


function writeJsonFile( $filePath, $aData, $redis  )
{
	writeFileDbFile( $filePath, $aData );
// 	return;
//      return writeJsonToRedis( $filePath, $aData, $redis  );
//     return;
//     $s = json_encode( $aData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
//    $s = json_encode( $aData );
//    $s = pretty_json($s);
//    file_put_contents( $filePath, $s);
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



// // function getArtistFromDB($dba, $artist_id, &$aArtistBaseData, &$aAllItemBasesPerType )
function getArtistFromDB($dba, $artist_id, &$aArtistBaseData, &$aAllItemBasesAssoc )
{
    $aArtistBaseData    = $dba->m_dbArtistData->getBaseData($artist_id);
    $aAllIBs            = $dba->m_dbItemBaseData->getItemsForArtist($artist_id, 0);

// //    $aAllItemBasesPerType   = array();
    $aAllItemBasesAssoc   = array();
    foreach ($aAllIBs as $aItemBaseData ) {
        $aItemBaseData['item_base_id'] = nameToID( $aItemBaseData['item_base_name'] . '^' . $aItemBaseData['item_type'] ); 
        $parent_item = $aItemBaseData['parent_item'];
        if ( 0 != (int)$parent_item ) {
            $aParentData = $dba->m_dbItemBaseData->getBaseData($parent_item);
            $aItemBaseData['parent_item_id'] = nameToID( $aParentData['item_base_name'] . '^' . $aParentData['item_type'] ); 
            $aItemBaseData['parent_item_name'] = $aParentData['item_base_name'];
            $aItemBaseData['parent_item_type'] = $aParentData['item_type'];
			unset($aItemBaseData['parent_item']);
        }
        $item_type = (int)$aItemBaseData['item_type'];
        if ( 0 == $item_type ) continue;
        
        ////$aItemData['item_base_id'] = $aItemBaseData['item_base_id'];
        $aItemData['item_base_name'] = $aItemBaseData['item_base_name'];
        $aItemData['item_type'] = $aItemBaseData['item_type'];
        $aItemData['item_year'] = $aItemBaseData['item_year'];
        $aItemData['item_base_soundex'] = calcSoundex( $aItemBaseData['item_base_name'] );
//        $aAllItemBasesPerType[$item_type][] = $aItemData;
        $aAllItemBasesAssoc[ $aItemBaseData['item_base_id'] ] = $aItemData;
        
    }
}

function addToItemPrice( $dba, $aItemBaseData, &$aItemPrice  )
{
    
    $currency_id = $dba->m_dbCurrencyData->IDToName($aItemPrice['currency_id']);
    
    $item_base_name = $aItemBaseData['item_base_name'];
    
    $aItemPrice['currency_id'] = $dba->m_dbCurrencyData->IDToName($aItemPrice['currency_id']);
    $aItemPrice['record_store_name'] = $dba->m_dbRecordStore->IDToName($aItemPrice['record_store_id']);
    if ( 0 != (int)$aItemPrice['parent_item'] ) {
        $aParentItemPrice   = $dba->m_dbItemPriceData->getBaseData($aItemPrice['parent_item']);
        $item_price_name    = $aParentItemPrice['item_price_name'];
        $media_format_id    = (int)$aParentItemPrice['media_format_id'];
        $record_store_name  = $dba->m_dbRecordStore->IDToName($aParentItemPrice['record_store_id']);
        $item_used          = (int)$aParentItemPrice['item_used'];
        $item_type          = (int)$aParentItemPrice['item_type'];
        
        $parent = "{$item_price_name}^{$media_format_id}^{$record_store_name}^{$item_used}^{$item_type}";
// //         $aItemPrice['parent_item_path'] = $parent;
        $aItemPrice['parent_price_id'] = nameToID($parent);
		unset($aItemPrice['parent_item']);
    }
    $aItemPrice = filterEmptyAssoc($aItemPrice);

    $item_price_name    = $aItemPrice['item_price_name'];
    $media_format_id    = (int)$aItemPrice['media_format_id'];
    $record_store_name  = $aItemPrice['record_store_name'];
    $item_used          = (int)$aItemPrice['item_used'];
    $item_type          = (int)$aItemPrice['item_type'];
    $id = "{$item_price_name}^{$media_format_id}^{$record_store_name}^{$item_used}^{$item_type}";
    
// //     $aItemPrice['item_price_id'] = nameToID($id);
    return nameToID($id);	// item_price_id
    
    
}
// lookupItemPriceID ($artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type)


function writeArtist( $dba, $baseDir, $artist_id )
{
    global $g_redis;
    $aArtistBaseData        = array();
// //     $aAllItemBasesPerType   = array();
    $aAllItemBases   = array();
// //     getArtistFromDB( $dba, $artist_id, $aArtistBaseData, $aAllItemBasesPerType );
    getArtistFromDB( $dba, $artist_id, $aArtistBaseData, $aAllItemBasesAssoc );
    
    $artist_name    	= $aArtistBaseData['artist_name'];
    $artistID      		= nameToID($artist_name);
    $artistModuloDir    = moduloDirFromID($artistID);
    $artistDir      	= "{$baseDir}/artist/{$artistModuloDir}/{$artistID}";
    @mkdir( $artistDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
    
    $aAllItemBases          = $dba->m_dbItemBaseData->getItemsForArtist($artist_id, 0);
    foreach( $aAllItemBases as $aItemBase ) {
        $item_base_id   = $aItemBase['item_base_id'];
        
        $aItemPrices    = $dba->m_dbItemPriceData->getItemPrices( $item_base_id );

        $item_base_name = $aItemBase['item_base_name'];
        $itemBaseFile   = nameToID( $aItemBase['item_base_name'] . '^' .  $aItemBase['item_type'] ); 

        $itemBaseFile   = "{$artistDir}/$itemBaseFile.json";
        
        $aItemPricesAssoc = array();
        foreach ( $aItemPrices as &$aItemPrice ) {
            $item_price_id = addToItemPrice( $dba, $aItemBase, $aItemPrice );
            $aItemPricesAssoc[$item_price_id] = $aItemPrice;
        }
        
        
        $aItemBaseTexts = $dba->m_dbItemBaseData->textDataGetAllForJson($item_base_id);
        
        
        $aItemBaseFiltered = filterEmptyAssoc($aItemBase);
        $aItemBaseFiltered['item_base_id'] = nameToID( $aItemBase['item_base_name'] . '^' . $aItemBase['item_type'] ); 

        ///////////
        $parent_item = $aItemBase['parent_item'];
        if ( 0 != (int)$parent_item ) {
            $aParentData = $dba->m_dbItemBaseData->getBaseData($parent_item);
            $aItemBaseFiltered['parent_item_id'] = nameToID( $aParentData['item_base_name'] . '^' . $aParentData['item_type'] ); 
            $aItemBaseFiltered['parent_item_name'] = $aParentData['item_base_name'];
            $aItemBaseFiltered['parent_item_type'] = $aParentData['item_type'];
            unset($aItemBaseFiltered['parent_item']);
        }
        
        //////////
        
		$aItemBase['item_base_id'] = nameToID( $aItemBase['item_base_name'] . '^' . $aItemBase['item_type'] ); 
        
        $aAll = array( 'base_data' => $aItemBaseFiltered, 'item_prices' => $aItemPricesAssoc, 'text' => $aItemBaseTexts );
        
        writeJsonFile( $itemBaseFile, $aAll, $g_redis );
        
    }
    $aArtistBaseData    = filterEmptyAssoc( $aArtistBaseData );
    $aArtistBaseData['artist_id']    = nameToID($artist_name);
    $aArtistTexts       = $dba->m_dbArtistData->textDataGetAllForJson($artist_id);
    $aAll = array('base_data' => $aArtistBaseData, 'children' => $aAllItemBasesAssoc, 'text' => $aArtistTexts );
    writeJsonFile("{$artistDir}/artist.json", $aAll, $g_redis );
    
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
    if ( $i % 10 == 0 ) printf("$i: $artist_name\n");
	
}
// for ( $i = $argv[2]; $i <= $argv[3]; $i++ ) {
//     $artist_name = writeArtist($dba, $argv[1], $i );
//     if ( $i % 10 == 0 ) printf("$i: $artist_name\n");
// }


?>