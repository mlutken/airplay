<?php

require_once ("utils/string_utils.php");
require_once ('db_manip/MusicDatabaseFactory.php');


class BaseInserterFileDb
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $fileDbBaseDir, $dbAll, $openParents )
    {
		global $g_fileDbBaseDir;

		$this->m_fileDbBaseDir	= $fileDbBaseDir;
        if ( '' == $this->m_fileDbBaseDir ) {
			$this->m_fileDbBaseDir = $g_fileDbBaseDir;
        }
        $this->m_dbAll = $dbAll;
        $this->m_openParents	= $openParents;
        $this->m_tsNow = time();
    }
    
    public function moduloBaseSet	( $iModuloBase )	{	$this->m_iModuloBase = $iModuloBase;  }
    public function moduloMatchSet	( $iModuloMatch )	{	$this->m_iModuloMatch = $iModuloMatch;  }

    
    public function initBaseInserter()
    {
    }
    
    public function init()
    {
		$this->initBaseInserter();
    }
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    protected       $m_fileDbBaseDir;
    protected       $m_dbAll;
    protected		$m_openParents;
    
    // --- Modulo stuff. Default values are set so we handle all - that is same as no modulo splitting of the data handling
    protected		$m_iModuloBase 	= 1;	// Main modulo number which we divide with. For example: 4
    protected		$m_iModuloMatch = 0;	// The 'remainder' that we match after dividing with m_iModuloBase. If m_iModuloBase = 4 then m_iModuloMatch is one of 0,1,2,3 
    protected		$m_tsNow;	// "Current" time. I.e. time of construction of this inserter, which is fine for timestamping prices

    
}


?>