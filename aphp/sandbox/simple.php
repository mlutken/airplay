<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('db_api/ItemDataMySql.php');
require_once ('db_api/ItemBaseCorrectionDataMySql.php');

$ad = new ArtistDataMySql;
$id = new ItemDataMySql;

$ibc = new ItemBaseCorrectionDataMySql;


//$artist_id = $ad->createNew("Kim Larsen");
$artist_id = $ad->lookupID("Kim Larsen");
$item_base_id = $id->nameToID($artist_id, "231045-0637", 0);
//$item_base_id = $id->createNew($artist_id, "Fru Sauterne", "0");

//$item_base_correction_id = $ibc->createNew ( $artist_id, "231045 0637", "231045-0637" );

printf("artist_id: $artist_id,  item_base_id: $item_base_id,  item_base_correction_id: $item_base_correction_id\n");

$item_base_correction_name = "231045 0637";
$item_base_name = $ibc->correctionNameToBaseName ( $artist_id, $item_base_correction_name );
printf("'$item_base_correction_name' => '$item_base_name' \n");

$item_base_correction_name = "7-9-13";
$item_base_name = $ibc->correctionNameToBaseName ( $artist_id, $item_base_correction_name );
printf("'$item_base_correction_name' => '$item_base_name' \n");

?>
