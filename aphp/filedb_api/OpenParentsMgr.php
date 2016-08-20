<?php

require_once ("db_api/db_helpers.php");
require_once ("utils/string_utils.php");
require_once ('filedb_api/ArtistDataFileDb.php');


class OpenParentsMgr
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $fileDbBaseDir, $dbAll )
    {
		global $g_fileDbBaseDir;
        
        $this->m_fileDbBaseDir	= $fileDbBaseDir;
        $this->m_dbAll = $dbAll;
        if ( '' == $this->m_fileDbBaseDir ) {
			$this->m_fileDbBaseDir = $g_fileDbBaseDir;
        }

		$this->m_ad = new ArtistDataFileDb($this->m_fileDbBaseDir, $this->m_dbAll->m_CurrencyConvert );			
		$this->m_artistID = '';		// Current open artist ID
		
		$this->m_aNewArtistNames	= array();
		$this->m_aNewItemBaseNames	= array();
		
    }

   
	public function artistOpenForWrite( $artist_id, $hash32 )
	{
		if ( $this->m_artistID == $artist_id ) return true;	// Already open. Return true for artist exists!
		
		// Not same as previous artist. Write the old one to disk first
		$this->m_ad->writeCurrent();
		
		// Set ID of new current and open for writing.
		$this->m_artistID = $artist_id;
		return $this->m_ad->openForWriteFromID($artist_id, $hash32);
	}

	/** Add new artist name (call on create new artist) to be added to DB when current (cronjob) 'run' is done */
	public function artistAddNewName( $artist_name )
	{
		$this->m_aNewArtistNames[] = $artist_name;
	}

	/** Add new item_base name (call on create new item_base) to be added to DB when current (cronjob) 'run' is done */
	public function itemBaseAddNewName( $item_base_name, $item_type )
	{
		$this->m_aNewItemBaseNames[] = array( $item_base_name, $item_type );
	}
	

	/** Write all new artist names to DB. */
	public function writeNewArtistNamesToDB()
	{
		// TODO: Implement like this this:
		// Use the names from $this->m_aNewArtistNames.
		// Get the 'all_artists' MySQL db API ( AllArtistsDataMySql ) and use that for adding the names
		// perhaps add a special function to the interface to do it fast by creating a large 
		// insert string and do it in one SQL command
		//// var_dump($this->m_aNewArtistNames);
	}

	/** Write all new item_base names to DB. */
	public function writeNewItemBaseNamesToDB()
	{
		// TODO: Implement like this this:
		// Use the names from $this->m_aNewItemBaseNames.
		// Get the 'all_item_bases' MySQL db API ( AllItemBasesDataMySql ) and use that for adding the names
		// perhaps add a special function to the interface to do it fast by creating a large 
		// insert string and do it in one SQL command
		//// var_dump($this->m_aNewItemBaseNames);
	}
	
	
	/** Write all current parents to disk. */
	public function writeAll()
	{
		$this->m_ad->writeCurrent();
		$this->writeNewArtistNamesToDB();
		$this->writeNewItemBaseNamesToDB();
	}
	

	
	
    // --------------------
    // --- PUBLIC: Data --- 
    // --------------------
    public		$m_artistDB;
    public		$m_artistName;

    public		$m_aNewArtistNames;
    public		$m_aNewItemBaseNames;
    
    
    
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    private		$m_fileDbBaseDir;
    private		$m_dbAll;
    
}


?>