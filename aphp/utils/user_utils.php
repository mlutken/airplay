<?php

require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');

/**  Logout user and redirect to url. 
\return user_id of logged out user if successfull, 0 otherwise. */
function userLogout ( $redirect_url = '' )
{
    session_start();
    if ( session_destroy() )
    {
        if ( '' != $redirect_url ) header("Location: {$redirect_url}");
        return (int)$_SESSION["user_id"];
    }
    return 0;
}


/**  Login user or try to autocreate if not found. 
\return Array with user data if user could be found or created, empty array otherwise. */
function userLogin ( $aData )
{
    session_start();
    $aUserData = array();
    $fac = new MusicDatabaseFactory();
    $dbUserData = $fac->createDbInterface('UserData'); 
    $user_id = $dbUserData->lookupAutoCreate($aData);
    
    // If we could find or create the user,then store user_id in session 
    // Also lookup the user data 
    if ( 0 != $user_id ) {
        $_SESSION["user_id"] = $user_id;
        $aUserData = $dbUserData->getBaseData($user_id);
    }
    return $aUserData;
}

/**  Check if a user is logged in. 
\return Array with user data if user is logged in, empty array otherwise. */
function userCheckLoggedIn ()
{
    session_start();
    $aUserData = array();
    $user_id = (int)$_SESSION["user_id"];
    
    if ( 0 != $user_id ) {
        $fac = new MusicDatabaseFactory();
        $dbUserData = $fac->createDbInterface('UserData'); 
        $aUserData = $dbUserData->getBaseData($user_id);
    }
    return $aUserData;
}



/** User login ajax handler. Use for example like:
\code
echo userLoginHandler($_POST);
\endcode
*/
function userLoginHandler($aData)
{
    $aUserData = userLogin($aData);
    dbgWritePostGetSessionData($aData, "/tmp/dbgWritePost_userLoginHandler.txt"); // For debugging help look here!
    return json_encode( $aUserData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}


/** Check if a user is logged in ajax handler. Use for example like:
\code
echo userCheckLoggedInHandler();
\endcode
*/
function userCheckLoggedInHandler()
{
    $aUserData = userCheckLoggedIn();
    dbgWritePostGetSessionData("/tmp/dbgWritePost_userCheckLoggedInHandler.txt"); // For debugging help look here!    
    return json_encode( $aUserData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}


/** DEBUG User login ajax handler. Use for example like:
\code
echo userLoginHandler_DEBUG($_POST);
\endcode
\todo Implement me!
*/
function userLoginHandler_DEBUG($aData)
{
    session_start();
    $aUserData = array();
    $user_id = (int)$_SESSION["user_id"];

    if ( 0 == $user_id ) {
        $user_id = 11;
    }

    if ( 0 != $user_id ) {
        $_SESSION["user_id"] = $user_id;
    }
    
    $aUserData = array(
          "user_id"     => $user_id
        , "user_name"   => "Anders And"
        , "email"       => "aa@andeby.dk"
        , "fb_id"       => 12345678
    );
    
    return json_encode( $aUserData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
}



/** User logout ajax handler. Use for example like:
\code
echo userLogoutHandler();
\endcode
*/
function userLogoutHandler()
{
    dbgWritePostGetSession("/tmp/dbgWritePost_userLogoutHandler.txt"); // For debugging help look here!
    return userLogout( '' );    // No redirect (thus the empty string '') since this is to be used from ajax call.
}

?>