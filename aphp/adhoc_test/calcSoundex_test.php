<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

require_once ('utils/string_utils.php');

echo calcSoundex('Midt om natten') . "\n";
echo calcSoundex('Midt om naten') . "\n";

?>