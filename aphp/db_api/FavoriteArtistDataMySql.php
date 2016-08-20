<?php

require_once ("db_api/SimpleTableDataMySql.php");


class FavoriteArtistDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'favorite_artist'
        , array(  'user_id', 'artist_id', 'artist_score'
                )
        , $dbPDO );
    }
}

?>