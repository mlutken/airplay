<?php

require_once ("admin_site/classes/SimpleTableUI.php");
require_once ("db_manip/MusicDatabaseManip.php");

class ItemBaseTblUI extends SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        parent::__construct( $mainName, $baseTableName ); 
        $this->m_musicDbManip = new MusicDatabaseManip();
    }
    
    // Make sure we delete everything related to this ItemBase
    protected function dbDelete($id)
    {
        return $this->m_musicDbManip->eraseItemBase($id);
    }

    // ---------------------
    // --- PRIVATE: Data ---
    // ---------------------
    private     $m_musicDbManip;
    
}


?>