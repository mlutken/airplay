<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');

echo "ItemBaseData test\n";

$fac = new MusicDatabaseFactory();
$ib = $fac->createDbInterface('ItemBaseData');


$aData = array(
      'item_base_id'    => 163513
    , 'item_master'     => 1
);

$ib->updateBaseData($aData);

?>