<?php

require_once ("airplay_globals.php");
require_once ("XmlDataReader.php");
require_once ('MediaFormatLookup.php');
require_once ('MediaTypeLookup.php');

$g_argv = $GLOBALS['argv'];

$xmlFile = $g_argv[1];
printf ("read xml file: $xmlFile\n" );
$r = new XmlDataReader;
$r->readXMLData($xmlFile);

//var_dump($v);

// // $mf = new MediaFormatLookup;
// // $mf->dbg();
// // 
// // $mf = new MediaTypeLookup;
// // $mf->dbg();

?>


