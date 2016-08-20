<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('admin_site/classes/PagesCommon.php');
require_once ('admin_site/classes/SimpleTableUI.php');
require_once ('admin_site/classes/ArtistMergeTblUI.php');
require_once ('admin_site/classes/SingleArtistAliasTblUI.php');
require_once ('admin_site/classes/QuizThemeEditPageUI.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('db_api/GenreLookup.php');
require_once ('db_api/CountryLookup.php');
require_once ('admin_site/utils/admin_site_utils.php');
require_once ('utils/general_utils.php');
require_once ('utils/language_data.php');

$pc                 = new PagesCommon();
$mainName           = 'QuizThemeEditPage';
$ui = new QuizThemeEditPageUI( $pc );
print $pc->pageStart($mainName);
print $pc->pageIncrementalSearchBoxMusicDB();
print $ui->pageContents();
print $pc->pageEnd();



?>