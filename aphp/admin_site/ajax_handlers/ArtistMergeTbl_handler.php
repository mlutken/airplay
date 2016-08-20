<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('admin_site/classes/ArtistMergeTblUI.php');

$mainName       = 'ArtistMergeTbl';
$tblBaseName    = 'artist';
$fac = new MusicDatabaseFactory();
$db = $fac->createDbInterface("ArtistData");
$ui = new ArtistMergeTblUI( $mainName, $tblBaseName );
$ui->dbInterfaceSet($db);

print $ui->ajaxHandler();

?>