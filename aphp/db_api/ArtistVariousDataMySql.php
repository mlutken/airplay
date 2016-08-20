<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ArtistVariousDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'artist_various'
        , array( 'artist_various_name' ) 
        , $dbPDO );
    }

    /** Get all base data rows from table, obeying the limits given. */
    public function getVariousArtist ( $artist_name )
    {
        $q = "SELECT * FROM {$this->m_baseTableName} WHERE artist_various_name = ?";
        return pdoQueryAssocFirstRow($this->m_dbPDO, $q, array($artist_name) ); 
    }
}


?>