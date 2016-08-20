<?php

require_once ("admin_site/classes/SimpleTableUI.php");
require_once ("db_manip/MusicDatabaseManip.php");

class ArtistTblUI extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        parent::__construct( $mainName, $baseTableName ); 
        $this->m_musicDbManip = new MusicDatabaseManip();
    }
    
    // Make sure we delete everything related to this Artist
    protected function dbDelete($id)
    {
        return $this->m_musicDbManip->eraseArtist($id);
    }

    // ---------------------
    // --- PRIVATE: Data ---
    // ---------------------
    private     $m_musicDbManip;
    

}


?>