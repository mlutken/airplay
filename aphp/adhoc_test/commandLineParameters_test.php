<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('utils/CmdLineArgs.php');

echo "Command line parameters test\n";


// Create commandline parser giving default values for parameters.
$cmd = new CmdLineArgs("input-file=mydata.xml;verbose=NOT_SET;extra=;start=1;end=-1");

// OPTIONAL: Create help text
$cmd->helpSet(array(
		'INTRO' 		=> "Demo command line parser program"
	,	'input-file'	=> "Input file to use."
    ,   'verbose'       => "Use verbose output."
    ,   'extra'         => "Some optional extra data."
    ,   'start'         => "Start ID."
    ,   'end'           => "End ID."
));



var_dump($cmd->parsedArgs() );
var_dump($cmd->defaultArgs() );



printf ("Use verbose       : '%d'\n", $cmd->argumentIsSet("verbose") );
printf ("start             : '%d'\n", $cmd->getValueInt("start") );
printf ("end               : '%d'\n", $cmd->getValueInt("end") );
printf ("input-file: '%s'\n", $cmd->getValueStr("input-file") );

$cmd->checkPrintHelp();

// TRY RUNNING LIKE THIS FOR EXAMPLE:
// php adhoc_test/commandLineParameters_test.php --start=23 --end=56 --verbose



?>