<?php

require_once ("new_db_inserter/BaseInserterFileDb.php");



class ItemBaseInserterFileDb extends BaseInserterFileDb
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $fileDbBaseDir, $dbAll, $openParents )
    {
        parent::__construct( $fileDbBaseDir, $dbAll, $openParents );
    }

    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    
}


?>