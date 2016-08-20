<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('admin_site/classes/PagesCommon.php');
require_once ('admin_site/classes/ArtistTblUI.php');
require_once ('db_manip/MusicDatabaseFactory.php');

$mainName       = 'ArtistTblCompact';
$tblBaseName    = 'artist';
$pc = new PagesCommon();
$fac = new MusicDatabaseFactory();
$db = $fac->createDbInterface("ArtistData");
$ui = new ArtistTblUI( $mainName, $tblBaseName );
$ui->dbInterfaceSet($db);
$ui->fieldsToListSet( array(  'artist_name', 'artist_real_name', 'artist_url', 'genre_id', 'country_id', 'country_id', 'url_artist_official' ) );

echo $pc->pageStart($mainName);
echo $pc->pageIncrementalSearchBox($mainName);
echo $ui->pageContents();
echo $pc->pageEnd();

?>