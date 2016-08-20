<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('admin_site/classes/User.php');

$u = new User();

var_dump($_POST);

if( $_POST["action"] == "login" ) {
    if ( $u->login( $_POST['user'], $_POST['password'] ) ) {
        print 'ok';
    }
    else print 'error';
    
}
else if( $_POST["action"] == "logout" ) {
    $u->logout();
    print 'ok';
}
else print 'error';


?>