<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('redis_api/RecordStoreDataRedis.php');
require_once ('redis_api/ArtistDataRedis.php');
require_once ('redis_api/ItemBaseDataRedis.php');
require_once ('redis_api/ItemPriceDataRedis.php');

// // $iMaxRecordsToRead = -1;
// // $g_argv = $GLOBALS['argv'];
// // 
// // $xmlFile            = $g_argv[1];
// // if ( count($g_argv) > 2 ) {
// //     $iMaxRecordsToRead  = $g_argv[2];
// // }

$ad = new ArtistDataRedis();
$id = new ItemBaseDataRedis();
$ip = new ItemPriceDataRedis();

printf("Airplay Redis ItemPrice test\n");

$artist_name = "Hej Matematik";
$item_base_name = "Party i provinsen";

$item_base_id = 3;
$item_price_name = 'Party i provinsen [maxi]';
$record_store_id = 1;
$media_format_id = 3;
$media_type_id = 1;
$price_local = 10;
$currency_id = 'DKK';
$buy_at_url = 'http://cdon.dk/shop/item123456';
$artist_id = 2;
$item_used = 0;
$item_type = 1;



// $item_price_id = $ip->createNew (   $item_base_id, $item_price_name, $record_store_id, $media_format_id
//                                 , $media_type_id, $price_local, $currency_id, $buy_at_url
//                                 , $artist_id, $item_used, $item_type);
                                
$item_price_id = $ip->lookupItemPriceID ( $artist_id, $item_price_name, $media_format_id, $record_store_id, $item_used, $item_type );
printf("item_price_id: %d\n", $item_price_id );

//var_dump($a);


$aData = array(     "artist_id"         => $artist_id 
                  , "item_price_id"     => $item_price_id
                  , "item_type"         => $item_type
                  , "item_genre_id"     => 3
                  , "item_year"         => 2007
                  , "release_date"      => "2007-04-22"
                  , "parent_item"       => 120
                  , "item_time"         => 195
                  , "track_number"      => 8
                  );

$ip->updateBaseData($aData);
$a = $ip->getBaseData($item_price_id);
pp_array($a, "BaseData");

?>