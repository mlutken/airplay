<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiz/classes/PagesCommon.php');

$name = 'Quiztest welcome';
$pc = new PagesCommon();

echo $pc->pageStart("{$name}");

?>

<h1>Music quiz prototype pages</h1>
<p>
Welcome to the music quiz prototype/playground pages for Airplay Music. Select a page from the emnu above.
<p>
<?php
echo $pc->pageEnd();

?>