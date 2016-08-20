<?php 
// $Id$

// ------------------------------------
// --- CLASS: AirplaySimpleIdAssign ---
// ------------------------------------

/** Class that can handle assignments of ID's from a 
single simple table. Simple tables are table that are relatively short 
and which we do not (normally) insert in to automatically. It is tables 
like 'genre', 'currency', 'media_format'. */
class AirplaySimpleIdAssign
{
	/** Construct a new SimpleIdAssign object.
	\arg $sTableName Name of table to use ( eg. 'genre' )
	\arg $aOpts['name_name'] Name of 'name' to lookup id for (eg. 'genre_name')
	\arg $aOpts['id_name'] Name of 'id' to lookup ( eg. 'genre_id' )
	*/
	function __construct( $sTableName, $aOpts = array( 'name_name' => '',  'id_name' => '', 'db_name' => 'airplay_music' )  )
	{
		$this->m_sTableName = $sTableName;
		$this->m_aOpts = $aOpts;
		$this->m_sNameName = $aOpts['name_name'] == "" ? $sTableName . '_name' : $aOpts['name_name'];
		$this->m_sIDName = $aOpts['id_name'] == "" ? $sTableName . '_id' : $aOpts['id_name'];
		$this->m_aIDs = null;
	}

	function __destruct( )
	{
		////printf("__destruct\n");
		$this->m_sTableName = null;
		$this->m_aOpts = null;
		$this->m_sNameName = null;
		$this->m_sIDName = null;
		$this->m_aIDs = null;
	}

	/** Assign the id's */
	public function 	assign ( &$aTitles )
	{
		$aNameToID = $this->getIdArray();
		foreach ( $aTitles as &$aTitle ) {
			$sLookupName = $aTitle[ $this->m_sNameName ];
			if ( $sLookupName != "" )	$aTitle[ $this->m_sIDName ] = $aNameToID[$sLookupName];
			else						$aTitle[ $this->m_sIDName ] = 0;
		}
	}


	/** Get a php array that can translate a name to an id. */
	public function &getIdArray() 
	{
		if ( $this->m_aIDs == null ) {
			$this->m_aIDs = array();
			db_set_active( $this->m_aOpts['db_name'] );
			$query = "SELECT {$this->m_sNameName}, {$this->m_sIDName} FROM {{$this->m_sTableName}}";
			$queryResult =  db_query($query);
			while ( $a = db_fetch_array($queryResult)) {
 				$this->m_aIDs[$a[$this->m_sNameName]]= $a[$this->m_sIDName];
			}
			$queryResult = null;
			db_set_active('default');
		}
		return $this->m_aIDs;
	}

	// -------------------
	// --- Member data ---
	// -------------------
	private		$m_sTableName;		///< Name of table to use ( eg. 'genre' )
	private		$m_sNameName;		///< Name of 'name' to lookup id for (eg. 'genre_name')
	private		$m_sIDName;			///< Name of 'id' to lookup ( eg. 'genre_id' )
	private		$m_aOpts;			
	private		$m_aIDs = null;
};

// ----------------------
// --- Free functions ---
// ----------------------

function airplay_assign_simple_ids ( &$aTitles )
{
	$country = new AirplaySimpleIdAssign('country');
	$country__ToId = $country->getIdArray();
	$media_format = new AirplaySimpleIdAssign('media_format');
	$media_format__ToId = $media_format->getIdArray();
	$distribution_form = new AirplaySimpleIdAssign('distribution_form');
	$distribution_form__ToId = $distribution_form->getIdArray();
	$genre = new AirplaySimpleIdAssign('genre');
	$genre__ToId = $genre->getIdArray();
	$currency = new AirplaySimpleIdAssign('currency');
	$currency__ToId = $currency->getIdArray();
	
	
	foreach ( $aTitles as &$aTitle ) {
		$sLookupName = $aTitle[ 'country_name' ];
		if ( $sLookupName != "" )	$aTitle[ 'country_id' ] = $country__ToId[$sLookupName];
		else						$aTitle[ 'country_id' ] = 0;
		
		$sLookupName = $aTitle[ 'media_format_name' ];
		if ( $sLookupName != "" )	$aTitle[ 'media_format_id' ] = $media_format__ToId[$sLookupName];
		else						$aTitle[ 'media_format_id' ] = 0;
		
		$sLookupName = $aTitle[ 'distribution_form_name' ];
		if ( $sLookupName != "" )	$aTitle[ 'distribution_form_id' ] = $distribution_form__ToId[$sLookupName];
		else						$aTitle[ 'distribution_form_id' ] = 0;
		
		$sLookupName = $aTitle[ 'currency_name' ];
		if ( $sLookupName != "" )	$aTitle[ 'currency_id' ] = $currency__ToId[$sLookupName];
		else						$aTitle[ 'currency_id' ] = 0;
		
		$sLookupName = $aTitle[ 'genre_name' ];
		if ( $sLookupName != "" )	$aTitle[ 'genre_id' ] = $genre__ToId[$sLookupName];
		else						$aTitle[ 'genre_id' ] = 0;
	}

	$country = null;
	$country__ToId = null;
	$media_format = null;
	$media_format__ToId = null;
	$distribution_form = null;
	$distribution_form__ToId = null;
	$genre = null;
	$genre__ToId = null;
	$currency = null;
	$currency__ToId = null;

}


