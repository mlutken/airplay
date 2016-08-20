<?php

class User
{

    /** Authenticate user againt his password.
    \return true if user could be logged in. */
    public function login( $user, $password )
    {
        $ok = false;
        switch( $user )
        {
            case 'root'     : $ok = $password == 'Ospekos#27'; break;
            case 'admin'    : $ok = $password == 'Ospekos#27'; break;
            case 'uh'       : $ok = $password == 'Ospekos#27'; break;
            case 'ml'       : $ok = $password == 'Deeyl1819'; break;
            default: $ok = false;
        }
        
        if ( $ok ) {
            $this->setSessionForUser($user, $this->getUserRole( $user ) );
        }
        print "user:  $user, password: $password,  ";
        return $ok;
    }

    public function logout()
    {
// //         session_start(); # NOTE THE SESSION START
        $_SESSION = array(); 
        session_unset();
        session_destroy();
    }
    

    /** Get users role.
    \todo This function should lookup in Drupal to get the role. */
    public function getUserRole( $user )
    {
        $user_role = 'music_admin';
        switch( $user )
        {
            case 'root'     : $user_role = 'db_admin'; break;
            case 'admin'    : $user_role = 'db_admin'; break;
            case 'uh'       : $user_role = 'music_admin'; break;
            default: $user_role = 'music_admin';
        }
        return $user_role;
    }
    
    private function setSessionForUser( $user, $user_role )
    {
        $_SESSION['logged_in']  = 1;
        $_SESSION['user']       = $user;
        $_SESSION['user_role']  = $user_role;
    }

}


?>