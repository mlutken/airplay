<?php

require_once ("db_api/SimpleTableDataMySql.php");


class FriendsDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'friends'
        , array(  'user_id', 'friend_user_id' 
                )
        , $dbPDO );
    }
}

?>