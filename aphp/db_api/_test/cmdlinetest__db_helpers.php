<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('db_api/db_helpers.php');

// $iMaxRecordsToRead = -1;
// $g_argv = $GLOBALS['argv'];
// 
// $xmlFile            = $g_argv[1];
// if ( count($g_argv) > 2 ) {
//     $iMaxRecordsToRead  = $g_argv[2];
// }

printf ("cmdlinetest__db_helpers\n" );

$aData = array( 'record_store_name' => 'CdOn', 'record_store_url' => 'www.cdon.dk' );
$aTblFields = array( 'record_store_name', 'record_store_url', 'country_id', 'use_affiliate', 'affiliate_link', 'affiliate_encode_times', 'record_store_reliability' );

$aUpd = pdoGetInsert( $aData, $aTblFields );
// var_dump($aUpd);

$aData = array(     'item_base_id' => 12
                ,   'item_price_name' => 'Midt om natten'
                ,   'item_year' => 1984 );
                
$aTblFields = array(  'item_base_id', 'item_price_name', 'record_store_id', 'media_format_id'
                    , 'media_type_id', 'item_genre_id', 'price_local', 'currency_id'
                    , 'buy_at_url', 'cover_image_url', 'release_date', 'track_number'
                    , 'item_time', 'item_year', 'timestamp_updated', 'parent_item'
                    , 'cover_image_url', 'item_used' );

$aUpd = pdoGetInsert( $aData, $aTblFields );
var_dump($aUpd);

$aUpd = pdoGetUpdate( $aData, $aTblFields );
var_dump($aUpd);

?>