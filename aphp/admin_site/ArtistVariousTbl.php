<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('admin_site/classes/PagesCommon.php');
require_once ('admin_site/classes/SimpleTableUI.php');
require_once ('db_manip/MusicDatabaseFactory.php');

$mainName       = 'ArtistVariousTbl';
$tblBaseName    = 'artist_various';
$pc = new PagesCommon();
$fac = new MusicDatabaseFactory();
$db = $fac->createDbInterface("ArtistVariousData");
$ui = new SimpleTableUI( $mainName, $tblBaseName );
$ui->dbInterfaceSet($db);

echo $pc->pageStart($mainName);
echo $pc->pageIncrementalSearchBox($mainName);
echo $ui->pageContents();
echo $pc->pageEnd();

?>