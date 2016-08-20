<?php 
// $Id$




/** Singleton calls for accessing AirplayTable objects in their 
default representations. */
class DefaultTables
{

	// ----------------------------
	// --- PUBLIC: Functions ---	
	// ----------------------------
	/// Gets default list of tables that we are allowed to insert 
	/// into if current title is a record_store.  
	public function getInsertList__record_store()
	{
		return array ( 'record_store' );
		// artist, artist_synonym
	}

	/// Gets default list of tables,fields that we are allowed to update 
	/// into if current title is an record_store.  
	public function getUpdateList__record_store()
	{
		return array ( );  
	}

	/// Gets default list of tables in which we are allowed to update 
	/// any empty fields in, if current title is a record_store.  
	public function getUpdateOnEmptyList__record_store()
	{
		return array
			( 
				'record_store' => array()
			);  
		// artist
	}
	
	/// Gets default list of tables that we are allowed to insert 
	/// into if current title is an artist.  
	public function getInsertList__artist()
	{
		return array ( 'artist', 'artist_synonym', 'subgenre' );
		// artist, artist_synonym,subgenre
	}

	/// Gets default list of tables,fields that we are allowed to update 
	/// into if current title is an artist.  
	public function getUpdateList__artist()
	{
		return array ( );  
	}

	/// Gets default list of tables in which we are allowed to update 
	/// any empty fields in, if current title is an artist.  
	public function getUpdateOnEmptyList__artist()
	{
		return array
			( 
				'artist' => array()
			);  
		// artist
	}

	/// Gets default list of tables that we are allowed to insert 
	/// into if current title is an album.  
	public function getInsertList__album()
	{
		return array ( 	'record_store',
						'artist', 'artist_synonym', 'subgenre', 
						'album', 'buy_album', 'buy_album_history'
						);
		// artist,artist_synonym,subgenre,album,buy_album,buy_album_history
	}
	
	/// Gets default list of tables,fields that we are allowed to update 
	/// into if current title is an album.  
	public function getUpdateList__album()
	{
		return array
			( 
				'buy_album' => array( 'price_local', 'currency_id', 'buy_at_url', 'timestamp_updated' ),
				'buy_album_history' => array( 'price_local', 'currency_id', 'timestamp_updated' )
			);  
		// buy_album:price_local,currency_id,buy_at_url,timestamp_updated;buy_album_history:price_local,currency_id,timestamp_updated
	}
	
	/// Gets default list of tables in which we are allowed to update 
	/// any empty fields in, if current title is an album.  
	public function getUpdateOnEmptyList__album()
	{
		return array
			( 
				'album' => array()
			);  
		// artist;album
	}
	
	/// Gets default list of tables that we are allowed to insert 
	/// into if current title is an song.  
	public function getInsertList__song()
	{
		return array ( 	'record_store',
						'artist', 'artist_synonym', 'subgenre',
						'album', 'album_song_rel',
						'song', 'buy_song', 'buy_song_history'
						);
		// artist,artist_synonym,subgenre,song,buy_song,buy_song_history
	}
	
	/// Gets default list of tables,fields that we are allowed to update 
	/// into if current title is a song.  
	public function getUpdateList__song()
	{
		return array
			( 
				'buy_song' => array( 'price_local', 'currency_id', 'buy_at_url', 'timestamp_updated' ),
				'buy_song_history' => array( 'price_local', 'currency_id', 'timestamp_updated' )
			);
		// buy_song:price_local,currency_id,buy_at_url,timestamp_updated;buy_song_history:price_local,currency_id,timestamp_updated
	}
	
	/// Gets default list of tables in which we are allowed to update 
	/// any empty fields in, if current title is an album.  
	public function getUpdateOnEmptyList__song()
	{
		return array
			( 
				'song' => array()
			);  
		// artist;song
	}
	
	
	/** Get a default AirplayTable object. */
	public function tbl( $sTableName )
	{
		if ( ! array_key_exists( $sTableName, $this->m_aTables) ) {
			$this->m_aTables[$sTableName] = new AirplayTable( $sTableName );
		}
		return $this->m_aTables[$sTableName];
	}
	
	
	
	/** Instance function. */
	static public function I()
	{
		if ( DefaultTables::$m_pInstance === null ) {
			DefaultTables::$m_pInstance = new DefaultTables();
		}
		return DefaultTables::$m_pInstance;
	} 
	// ----------------------------
	// --- PRIVATE: Functions ---	
	// ----------------------------
	private function __construct()
	{
		$this->m_aTables = array();
	}
	
	
	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
	static private 	$m_pInstance = null;		///< Instance pointer.
	private	$m_aTables	= array();
};


/** Global function for getting a default AirplayTable object. */
function ap_tbl( $sTableName )
{
	return DefaultTables::I()->tbl( $sTableName );
}

