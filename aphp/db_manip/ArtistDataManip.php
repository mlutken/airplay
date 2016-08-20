<?php

require_once ("db_api/ArtistDataMySql.php");


class ArtistDataHelpers
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $artistData )
    {
        $this->m_artistData = $artistData;
    }

    // -------------------------------
    // --- Artist lookup functions --- 
    // -------------------------------

    
    /** .
    \return .  */
    public function tryFindArtist ( $artist_name )
    {
    }
    

    // --------------------------
    // --- PRIVATE: Functions --- 
    // --------------------------
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private         $m_artistData;
    
}


// ########################################
// ########################################
////        printf("query: %s\n", $q);

?>