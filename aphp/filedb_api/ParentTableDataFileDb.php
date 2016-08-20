<?php

require_once ("db_api/db_helpers.php");
require_once ("utils/string_utils.php");
require_once ("utils/general_utils.php");
require_once ('filedb_api/TableDataFileDbIF.php');


class ParentTableDataFileDb extends TableDataFileDbIF
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $baseTableName, $fileDbBaseDir )
    {
        parent::__construct($baseTableName, $fileDbBaseDir );
    }

    // -------------------------------------------------
    // --- FileDb functions to override/re-implement --- 
    // -------------------------------------------------
    public function createNewChild		( $childElem, $aBaseData, $item_base_id ) 	{ printf("Error: createNewChild Reimplement\n"); exit(1); }
    protected function onChildChanged	( $childElem ) 	{ printf("Error: onChildChanged Reimplement\n"); exit(1); }
    protected function onChildErase		( $childElem ) 	{ printf("Error: onChildErase Reimplement\n"); exit(1); }

    // --------------------------
    // --- Children functions --- 
    // --------------------------
    
    public function openChildForWrite( $child_id, &$childElem )
    {
		$bExists = false;
		$childElem = $this->m_openChilds[$child_id];
		if ( '' != $childElem ) $bExists = true;
		else {
			$childElem = new ItemBaseDataFileDb($this);
			$bExists = $childElem->openForWriteFromID ($child_id, null);
			$this->m_openChilds[$child_id] = $childElem;
		}
		return $bExists;
    }

    public function openChildForRead( $child_id, &$childElem )
    {
		$bExists = false;
		$childElem = $this->m_openChilds[$child_id];
		if ( '' != $childElem ) $bExists = true;
		else {
			$childElem = new ItemBaseDataFileDb($this);
			$bExists = $childElem->openForReadFromID ($child_id, null );	
			$this->m_openChilds[$child_id] = $childElem;
		}
		return $bExists;
    }
    

    public function openAllChildrenForWrite()
    {
		$childElemUnused;
		$aAllItemBaseIds = array();
		if ( !isset( $this->m_aAllData) || !array_key_exists('children', $this->m_aAllData ) ) return array();
		foreach( $this->m_aAllData['children'] as $child_id => $aItemBase ) {
			$aAllItemBaseIds[] = $child_id;
			$this->openChildForWrite( $child_id, $childElemUnused );
		}
		return $aAllItemBaseIds;
    }

    public function openAllChildrenForRead()
    {
		$childElemUnused;
		$aAllItemBaseIds = array();
		if ( !isset( $this->m_aAllData) || !array_key_exists('children', $this->m_aAllData ) ) return array();
		foreach( $this->m_aAllData['children'] as $child_id => $aItemBase ) {
			$aAllItemBaseIds[] = $child_id;
			$this->openChildForRead( $child_id, $childElemUnused );
		}
		return $aAllItemBaseIds;
    }

    // ---------------------------------
    // --- FileDb specific functions --- 
    // ---------------------------------
 
 
    /** Open record for writing from ID. 
    \return True if record existed and could be opened. */
	public function openForWriteFromID ($id, $idHash32 )
    {
		$this->m_openChilds = array();
		$bExists = $this->openForReadFromID ($id, $idHash32);
		$this->m_openForWrite 	= true;

		return $bExists;
    }

	public function writeCurrent ()
    {
		if ( $this->m_openForWrite ) {
			foreach( $this->m_openChilds as $ib ) {
				if ( $ib->openForWrite() ) { 
					$this->onChildChanged($ib);
					$ib->writeCurrent();	
				}
			}
			writeFileDbFile( $this->m_fullFilePath, $this->m_aAllData );  
		}
    }
    
// // 	public function writeCurrent ()
// //     {
// // 		if ( $this->m_openForWrite ) {
// // 			foreach( $this->m_openChilds as $ib ) {
// // 				$this->onChildChanged($ib);
// // 				$ib->writeCurrent();
// // 			}
// // 			writeFileDbFile( $this->m_fullFilePath, $this->m_aAllData );  
// // 		}
// //     }

    /** Write current parent item to an alternate base dir. Cnn be used to backup an item or to implement recycle bin when deleting. */
	public function writeCurrentToBaseDir ($fileDbBaseDir)
    {
		$this->openAllChildrenForRead();	// Make sure all children are opened
		$fullWriteDir 	= str_replace ( $this->m_fileDbBaseDir , $fileDbBaseDir , $this->m_fullFileDir	);
		$fullWritePath 	= str_replace ( $this->m_fileDbBaseDir , $fileDbBaseDir , $this->m_fullFilePath	);
		
 		@mkdir( $fullWriteDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
		writeFileDbFile( $fullWritePath, $this->m_aAllData );  
		foreach( $this->m_openChilds as $ib ) {
			$ib->writeCurrentToBaseDir ($fileDbBaseDir);
		}
    }
    
    // -------------------------------
    // --- Erase/delete functions ----
    // -------------------------------
    /** Permanently delete.
    \note  */
    public function erase ()
    {
		deleteDir( $this->m_fullFileDir );
		$this->m_openForWrite = false;	// make sure that if we later call writeCurrent() then nothing happens.
    }
    

    /** Permanently delete a child.
    \note  */
    public function eraseChild ( $childElem )
    {
		$child_id = $childElem->id();
		$this->onChildErase($childElem);
		$childElem->erase();
		unset($this->m_openChilds[$child_id]);
		unset($this->m_aAllData['children'][$child_id]);
    }
    
    // ----------------------------------
    // --- Item ID lookup functions --- 
    // ----------------------------------

    
// //     /** Lookup ID from its name.
// //     \return One ID if found one and only one matching. Zero if not found any or more than one found. 
// //     \sa nameToID which is equivalent */
// //     public function nameToIDSimple ($name)
// //     {
// // 		return nameToID($name);
// //     }

    /** Lookup ID "base/real/official" name.
    \return one ID if found one and only one matching. Zero if not found any and -n if more than one (n) found.  
    \sa nameToIDSimple which is equivalent */
    public function nameToID ($aData)
    {
		return nameToID( $aData["{$this->m_baseTableName}_name"] );
    }

// //     /** Check if a given name exists.
// //     \return true if found.  */
// //     public function nameSimpleExists ($name)
// //     {
// //     }
    
    /** Lookup ID from $aData.
    \return One ID if found one and only one matching. Zero if not found any or more than one found.  */
    public function toID ($aData)
    {
    
    }

    // ------------------------------
    // --- Names lookup functions --- 
    // ------------------------------
    public function lookupSimilarBaseData ( $name )
    {
    }
   
    // --------------------------
    // --- Get data functions --- 
    // --------------------------
    /** Get base data for one item. */
    public function getBaseData ($id)
    {
    }
    
    /** Get all base data rows from table, obeying the limits given. */
    public function getBaseDataRows ( $start, $count )
    {
    }
    
    public function getSize() 
    {
    }

    /** Get official name from ID. 
    \return Official AP name. */
    public function IDToName ($id)
    {
        return $s;
    }
    
    // --------------------------
    // --- Set data functions --- 
    // --------------------------
    /**  Set base data of item. Creates new item if name not found. */
    public function setBaseData ($aData)
    {

    }
    
    
    /**  Create new item from name. 
    \return ID of new item. */
    public function newItemFromName ( $name )
    {
    }
    
    /**  Create new item. 
    \return ID of new item. */
    public function newItem ( $aData )
    {
    }
    
    
    /**  Update base data of existing item. */
    public function updateBaseData ($aData)
    {
    }
    
    /**  Update base data of existing item, but checking against the data already in DB and 
        only overwites non-empty values if new data has higher reliability (record_store_reliability). */
    public function updateBaseDataCheckOld ($aData)
    {
    }

    // ------------------------------
    // --- PUBLIC: Info Functions --- 
    // ------------------------------
    /** Get array with all table fields (including the primarry xx_id name ) */
    public function getBaseDataFields() 
    {
    }

    
    /** Get array with all table fields (including the primarry xx_id name ) */
    public function getAllDataFields() 
    {
    }

    // -----------------------------------
    // --- PROTECTED: Helper functions --- 
    // -----------------------------------
    /** Create directory for a parent item. */
    public function createDirectory() 
    {	
			@mkdir( $this->m_fullFileDir, 0755, true ); // The @ suppresses php warnings. Here we particularly want to disregard the dir exists warning when creating
    }
    
    // --------------------
    // --- PUBLIC: Data --- 
    // --------------------
    public		$m_openChilds = array();
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    
}


?>