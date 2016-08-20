<?php
// To set include_path for use with all aphp files include this in you top level php file like this:
// require_once ( __DIR__ . '/../aphp_fix_include_path.php' ); 
// 
// or whatever the relative path to this file might be.
// Alternatively you can make sure to add the directory this file is located in to you include_path 
// in php.ini

set_include_path( get_include_path() . PATH_SEPARATOR . __DIR__ );


//printf("get_include_path():\n%s\n\n", get_include_path() );


?>