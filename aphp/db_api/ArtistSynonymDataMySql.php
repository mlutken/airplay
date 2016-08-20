<?php

require_once ("db_api/SimpleTableDataMySql.php");


class ArtistSynonymDataMySql extends SimpleTableDataMySql
{
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'artist_synonym'
        , array(  'artist_synonym_name', 'artist_id' ) 
        , $dbPDO );
    }

    /** Get all base data rows from table, obeying the limits given. */
    public function getAliasesDataForArtist ( $artist_id )
    {
        $q = "SELECT * FROM {$this->m_baseTableName} WHERE artist_id = ?";
        return pdoQueryAssocRows($this->m_dbPDO, $q, array($artist_id) ); 
    }
    
}


?>