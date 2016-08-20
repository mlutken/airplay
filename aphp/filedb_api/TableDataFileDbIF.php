<?php

require_once ("db_api/db_helpers.php");
require_once ("utils/string_utils.php");


class TableDataFileDbIF
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $baseTableName, $fileDbBaseDir )
    {
		global $g_fileDbBaseDir;
        $this->m_baseTableName 	= $baseTableName;
        
        $this->m_fileDbBaseDir	= $fileDbBaseDir;
        if ( '' == $this->m_fileDbBaseDir ) {
			$this->m_fileDbBaseDir = $g_fileDbBaseDir;
        }
    }

    public	function	openForWrite	() { return $this->m_openForWrite; 	}
    public	function	fullFileDir 	() { return $this->m_fullFileDir; 	}
    public	function	fullFilePath 	() { return $this->m_fullFilePath; 	}
    public	function	baseTableName 	() { return $this->m_baseTableName; }
    public	function	fileDbBaseDir	() { return $this->m_fileDbBaseDir;	}
    public	function	id				() { return $this->m_id;			}
    public	function	idHash32		() { return $this->m_idHash32;		}
    
    // -------------------------------------------------
    // --- FileDb functions to override/re-implement --- 
    // -------------------------------------------------
    public function leafFileName			() 		{ printf("Error: leafFileName() Reimplement\n"); exit(1); }
    public function relativeFileDir			()		{ printf("Error: relativeFileDir() Reimplement\n"); exit(1); }
    public function erase					()		{ printf("Error: erase()  Reimplement\n"); exit(1); }
    public function openForWriteFromID 		($id, $idHash32)	{ printf("Error: openForWriteFromID 	($id, $idHash32) Reimplement\n"); exit(1); }
    public function writeCurrent			()		{ printf("Error: writeCurrent() Reimplement\n"); exit(1); }

    public function createNew				($aBaseData, $id, $parentTableDataFileDb)	{ printf("Error: createNew() Reimplement\n"); exit(1); }
    public function updateBaseData			($aBaseData)	{ printf("Error: updateBaseData() Reimplement\n"); exit(1); }
    public function updateBaseDataCheckOld	($aBaseData)	{ printf("Error: updateBaseDataCheckOld() Reimplement\n"); exit(1); }
    public function idExists				($id, $idHash32) { printf("Error: idExists($id) Reimplement\n"); exit(1); 	}
    
    // TODO: Do we need these ?
    public function toID					($aBaseData)	{ printf("Error: toID() Reimplement\n"); exit(1); }

    
    // ---------------------------------
    // --- FileDb specific functions --- 
    // ---------------------------------

    
    
    /** Open record for reading from ID. 
    \return True if record existed and could be opened. */
	public function openForReadFromID ($id, $idHash32)
    {
// // 		printf("openForReadFromID: $id\n");
		$this->m_openForWrite 	= false;
		$this->m_id				= $id;
		$this->m_idHash32		= $m_idHash32;
		$this->m_fullFileDir	= $this->m_fileDbBaseDir . '/' . $this->relativeFileDir();
		$this->m_leafFileName	= $this->leafFileName();
		$this->m_fullFilePath	= $this->m_fullFileDir . '/' . $this->m_leafFileName;
		$this->m_aAllData		= readFileDbFile( $this->m_fullFilePath );
		return !empty($this->m_aAllData);
    }
   
    
 

    // -----------------------------------
    // --- PROTECTED: Helper functions --- 
    // -----------------------------------
    protected function doUpdateBaseData ( $aBaseData, $aFields ) 
    {
		foreach( $aFields as $f ) {
			$v = $aBaseData[$f];
			if ( '' != $v ) {
				$this->m_aAllData['base_data'][$f] = $v;
			}
			else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
				$this->m_aAllData['base_data'][$f] = time(); // date("Y-m-d H:i:s", time();
			}
		}
    }

    
    protected function doUpdateBaseDataCheckReliability ( $aBaseData, $aFields, $reliabilityField  ) 
    {
		$reliabilityOld = (int)$this->m_aAllData['base_data'][$reliabilityField];
		$reliabilityNew = (int)$aBaseData[$reliabilityField];
		
		$bNewDataBetter = $reliabilityNew > $reliabilityOld;

		foreach( $aFields as $f ) {
			$valNew = $aBaseData[$f];
			$valOld = $this->m_aAllData['base_data'][$f];
			
			
			if ( is_numeric($valNew) ) {
				if ( $valNew != 0 ) {
					if ( $bNewDataBetter || $valOld == 0 ) {
						$this->m_aAllData['base_data'][$f] = $valNew;
					}
				}
			}
			else if  ( $valNew != '' ) {
				if ( $bNewDataBetter || $valOld == "" ) {
					$this->m_aAllData['base_data'][$f] = $valNew;
				}
			}
			else if  ( stripos( $sFieldName , "timestamp_updated" ) !== false ) {
				$this->m_aAllData['base_data'][$f] = time(); // date("Y-m-d H:i:s", time();
			}
		}
    }
    
    
    // --------------------
    // --- PUBLIC: Data --- 
    // --------------------
    public		$m_aAllData;
    
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    protected		$m_baseTableName;
    protected		$m_fileDbBaseDir;
    protected		$m_id;
    protected		$m_idHash32 = null;	// If different from null assume it's the hash32 of the ID (so we don't have to compute twice)
    protected		$m_fullFileDir;
    protected		$m_leafFileName;
    protected		$m_fullFilePath;
    protected		$m_openForWrite = false;
    
}


?>