<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('db_api/ArtistDataMySql.php');
// require_once ('db_api/MediaTypeLookup.php');

// $artist_name = 'V.A.L. 10 Survivors';
// //$artist_name = 'Hej matematik';
// 
// $ad = new ArtistDataMySql;
// $artist_id      = $ad->lookupID( $artist_name );
// //$artist_id      = $ad->nameToID( $artist_name );
// if ( $artist_id <= 0 )  {
//     logDbInsertWarning("ML: Could not find '$artist_name', id returned '$artist_id'");
// }
// else {
//     printf("FOUND: '$artist_name' => $artist_id\n");
// }
// $g_argv = $GLOBALS['argv'];
// 
// $xmlFile = $g_argv[1];
// printf ("read xml file: $xmlFile\n" );
// $r = new XmlDataReader;
// $r->readXMLData($xmlFile);



/*
SELECT COUNT(*) AS nCount, item_base.artist_id, item_base.item_base_name, item_base.item_type
FROM item_base
LEFT JOIN item_price ON item_base.item_base_id = item_price.item_base_id
WHERE item_base.item_type = 1 AND item_base.artist_id = 472
GROUP BY item_base.item_base_id 
ORDER BY nCount DESC
             
*/



?>