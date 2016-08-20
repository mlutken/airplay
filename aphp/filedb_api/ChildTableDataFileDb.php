<?php

require_once ("db_api/db_helpers.php");
require_once ("utils/string_utils.php");
require_once ('filedb_api/TableDataFileDbIF.php');



class ChildTableDataFileDb extends TableDataFileDbIF
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $parentTableDataFileDb )
    {
        parent::__construct( $parentTableDataFileDb->baseTableName(), $parentTableDataFileDb->fileDbBaseDir() );
        $this->m_parent = $parentTableDataFileDb;
    }

    // -------------------------------------------------
    // --- FileDb functions to override/re-implement --- 
    // -------------------------------------------------
    /** Get full path to directory on disk.
    \return Path directory on disk. 
    \note MUST implement this in derived class! */
    public function relativeFileDir ()
    {
		return $this->m_parent->relativeFileDir();

    }
    
    // ---------------------------------
    // --- FileDb specific functions --- 
    // ---------------------------------
    
 
    /** Open record for writing from ID. 
    \return True if record existed and could be opened. */
	public function openForWriteFromID ($id, $idHash32)
    {
		$bExists = $this->openForReadFromID ($id, $idHash32);
		$this->m_openForWrite 	= true;
		return $bExists;
    }
    
	public function writeCurrent ()
    {
// // 		if ($this->m_openForWrite) {
// // 			writeFileDbFile( $this->m_fullFilePath, $this->m_aAllData );    
// //  		}
		// NOTE: No need to check $this->m_openForWrite since it is now done in ParentTableDataFileDb::writeCurrent()
		writeFileDbFile( $this->m_fullFilePath, $this->m_aAllData );
    }
    
	public function writeCurrentToBaseDir ($fileDbBaseDir)
    {
		$fullWritePath = str_replace ( $this->m_fileDbBaseDir , $fileDbBaseDir , $this->m_fullFilePath  );
		writeFileDbFile( $fullWritePath, $this->m_aAllData );    
    }


    // ------------------------------------------------------------------------------------
    // --- PARENT FREIND Functions: Do never call other than from a parent owner class ----
    // ------------------------------------------------------------------------------------
    

    /** Permanently delete a child.
    \note ONLY call from parent's eraseChild function or similar. */
    public function erase ()
    {
// // 		printf( "ChildTableDataFileDb::erase(): '%s'\n", $this->fullFilePath() );
		unlink( $this->fullFilePath() );
		$this->m_openForWrite = false;	// make sure that if we later call writeCurrent() from parent then nothing happens.
    }
    
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    protected       $m_parent;
    
}


?>