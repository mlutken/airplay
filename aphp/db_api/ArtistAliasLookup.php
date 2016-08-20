<?php


/** Class to facilitate fast lookup of artist aliases by reading everything from DB and keeping it in memory. 
You cant add new aliases using this interface. For this you need to use ArtistAliasDataMySql. */
class ArtistAliasLookup
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbArtistAliasData )
    {
		if ( null != $dbArtistAliasData ) $this->initialiseFromTableDb( $dbArtistAliasData );
    }

    function 	initialiseFromTableDb( $dbArtistAliasData )
    {
		$this->m_dbArtistAliasData = $dbArtistAliasData;
		$aArtistAliases = $dbArtistAliasData->getBaseDataRows(0,0);	// Get all rows
		foreach ( $aArtistAliases as $aArtistAliasRow ) {
			$artist_name 			= $aArtistAliasRow['artist_name'];
			$alias_name_lower_case  = $aArtistAliasRow['artist_alias_name'];
			$this->m_aAliasNameToArtistName[$alias_name_lower_case] = $artist_name;
		}
    }

    function 	moduloInitialiseFromTableDb( $dbArtistAliasData, $iModuloBase, $iModuloMatch )
    {
		$this->m_dbArtistAliasData = $dbArtistAliasData;
		$aArtistAliases = $dbArtistAliasData->getBaseDataRows(0,0);	// Get all rows
		foreach ( $aArtistAliases as $aArtistAliasRow ) {
			$artist_name 			= $aArtistAliasRow['artist_name'];
			$artist_id 	= nameToID($artist_name);
			$hash32 	= hash32($artist_id);
			if ( $hash32 % $iModuloBase != $iModuloMatch ) {
				continue;	// Skip all that does not match our modulo parameters
			}

			$alias_name_lower_case  = $aArtistAliasRow['artist_alias_name'];
			$this->m_aAliasNameToArtistName[$alias_name_lower_case] = $artist_name;
		}
    }
    
    
    /** Alias lookup. Remember that alias to lookup should be in all lowercase letters. */
    function 	aliasNameToArtistName( $alias_name_lower_case )
    {
		return $this->m_aAliasNameToArtistName[$alias_name_lower_case];
    }
    

    /** Creates a new alias for an artist name. This function both makes a temporary alias in this lookup class and 
		inserts new alias to the DB using the m_db . */  
    public function createNewAlias( $artist_alias_name, $artist_name )
    {
		$alias_name_lower_case = mb_strtolower( $artist_alias_name, 'UTF-8' ); // NOTE: This is done also in the DB class, but that one is also meant to be called directly so we need it there
		$this->m_dbArtistAliasData->createNewAlias( $alias_name_lower_case, $artist_name ); // Insert new alias to DB
		$this->m_aAliasNameToArtistName[$alias_name_lower_case] = $artist_name; // Insert alias in lookup array for this process.
    }
    
    // ---------------------
    // --- PUBLIC: Data --- 
    // ---------------------
	// We have made these public on purpose!
    
    public     	$m_aAliasNameToArtistName 	= array ();
    
    private		$m_dbArtistAliasData;

    
}



?>