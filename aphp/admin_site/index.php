<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('admin_site/classes/PagesCommon.php');

$name = 'Welcome';
$pc = new PagesCommon();

echo $pc->pageStart("Admin: {$name}");

//echo $pc->pageTopLogin();
//echo $pc->pageTopMenu();
?>

<h1>Airplay music admin interface</h1>
<p>
Welcome to the adminstration pages for Airplay Music. If you are not already logged in please do so by entering username and password 
in the fields above.
<p>
I you need to access the phpMyAdmin interface you can find it here: <a href=./phpMyAdmin-4.0.0-rc1-all-languages >PhpMyAdmin</a>
<?php
echo $pc->pageEnd();

?>