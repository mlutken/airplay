<?php 
// $Id$

// // function apdb_last_insert_id($table, $field) {
// //   return db_result(db_query('SELECT LAST_INSERT_ID()'));
// // } 

// // function apdb_last_insert_id($table, $field) {
// // 	$queryResult = db_query('SELECT LAST_INSERT_ID()');
// // 	$iLastID = db_result($queryResult);
// // 	$queryResult = null;
// // 	return $iLastID;
// // } 

// ---------------------------
// --- CLASS: AirplayTable ---
// ---------------------------

/** Class that can handle a single table. */
class AirplayTable
{
	/** Construct a new AirplayTable object.
	\arg $sTableName Name of table to use ( eg. 'genre' )
	\arg $aOpts['name_name'] Name of 'name' to lookup id for (eg. 'genre_name')
	\arg $aOpts['id_name'] Name of 'id' to lookup ( eg. 'genre_id' )
	*/
	function __construct( $sTableName, $aOpts = array( 'name_name' => '',  'id_name' => '', 'db_name' => 'airplay_music' )  )
	{
		$this->m_bEnsureCorrectDB = false;
		$this->m_sTableName = $sTableName;
		$this->m_aOpts = $aOpts;
		
		$this->m_aOpts['name_name'] 		= $aOpts['name_name'] 	== "" ? $sTableName . '_name' 	: $aOpts['name_name'];
		$this->m_aOpts['id_name'] 			= $aOpts['id_name'] 	== "" ? $sTableName . '_id' 	: $aOpts['id_name'];
		
		// --- Assign default assign to names ---
		$this->m_aOpts['assign_to_names'] 	= $aOpts['assign_to_names'];
		if ( $this->m_aOpts['assign_to_names'] == '' ) {
		 	$this->m_aOpts['assign_to_names'] = array ( $this->m_aOpts['id_name'] );
		} 	
		
		// --- Assign default lookup WHERE clause ---
		$this->m_aOpts['lookup_where'] 	= $aOpts['lookup_where'];
		if ( $this->m_aOpts['lookup_where'] == '' ) {
		 	$this->m_aOpts['lookup_where'] = "WHERE " . $this->m_aOpts['name_name'] . " = '%s'";
		} 	
		
		// --- Assign default lookup fields array ---
		$this->m_aOpts['lookup_fields'] 	= $aOpts['lookup_fields'];
		if ( $this->m_aOpts['lookup_fields'] == '' ) {
		 	$this->m_aOpts['lookup_fields'] = array( $this->m_aOpts['name_name'] );
		} 	
		
		if ( array_key_exists( 'null_val', $aOpts  ) )	$this->m_aOpts['null_val'] = $aOpts['null_val'];
		else											$this->m_aOpts['null_val'] = 0;
		$this->m_aIDs = null;
	}


	function __destruct()
	{
		$this->m_sTableName 		= null;		
		$this->m_aOpts 				= null;			
		$this->m_aIDs 				= null;
		$this->m_bEnsureCorrectDB 	= null;
	}

	public function 	insertOrUpdateSimple ( &$aTitles )
	{
		foreach ( $aTitles as &$aTitle ) {
			$this->lookupTitleId( $aTitle );
			$this->insertNewTitle( $aTitle );
			$this->updateTitleByIndex( $aTitle );
		}
	}

	public function 	insertOrUpdateRecord ( &$aTitle )
	{
		$this->lookupTitleId( $aTitle );
		$this->insertNewTitle( $aTitle );
		$this->updateTitleByIndex( $aTitle );
	}
	
	/** If title, that has a zero in it's (unique) index a new record is 
		inserted using the data from the title.  */
	public function 	insertNewTitle ( &$aTitle, $sRecordExistsField = '', $sAssignIDField = '' )
	{
		$sRecordExistsField = $sRecordExistsField 	=== '' ? $this->m_aOpts['id_name'] : $sRecordExistsField;
		$sAssignIDField 	= $sAssignIDField 		=== '' ? $this->m_aOpts['id_name'] : $sAssignIDField;
		
		if ( $aTitle[$sRecordExistsField] == true )	return;
		if ( !in_array ( $this->m_sTableName, $aTitle['do_insert'] ) )	return;
		
		$aFields = $this->getFields();
		$aInsertFields = ap_db_get_valid_fields( $aTitle, $aFields );
		
		if ( count($aInsertFields) == 0 )	return;
		
		
		$sQuery = "INSERT INTO {{$this->m_sTableName}} (";
		$i = 0;
		$sVALUES = '';
		$aFieldNames = array();
		foreach ( $aFields as $sField => $sFieldFmtStr )
		{
			if ( in_array( $sField, $aInsertFields) ) {
				if 	( stripos( $sField , "timestamp_updated" ) !== false ) {
					$sFieldFmtStr = "NOW()";
				}
				else {
					$aFieldNames[] = $sField;
				}
				
				$sQuery .= ' ' . $sField;
				$sVALUES .= ' ' . $sFieldFmtStr;
				if ( ++$i < count($aInsertFields) ) {	
					$sVALUES .= ', ';
					$sQuery  .= ', ';
				}
			}
		}	
		$sQuery .= ' ) VALUES ( ' . $sVALUES . ' ) ';
		
		
		$aArgs = array();
		foreach ( $aFieldNames as $sField )
		{
			$aArgs[] = $aTitle[$sField];
		}
		
// //         if ( "buy_album" == $this->m_sTableName ) {
// //             printf ( "INSERT sQuery : %s\n", $sQuery  );
// //             var_dump($aArgs);
// //             return; // TODO: TEMPORARY
// //         }
		
		$this->setCorrectDB();
		$queryResult =  db_query($sQuery, $aArgs );
		$aTitle[$sAssignIDField] = db_last_insert_id();
		$aTitle['newly_added_to_db'] = true;
		$queryResult = null;
		$aArgs = null;
		$aFieldNames = null;
		$aFields = null;
		$this->restoreDefaultDB();
	}



	/** For the title a (unique) index is used to lookup the corresponding 
		DB-entry, which is then updated. The 'do_update' entry in the title is 
		used. If this is empty no update is made. */
	public function 	updateTitleByIndex ( &$aTitle, $sSkipUpdateTestField = "" )
	{
// // 		$sSkipUpdateTestField = $sSkipUpdateTestField === '' ? 'newly_added_to_db' : $sSkipUpdateTestField;
		
		if ( $aTitle[$sSkipUpdateTestField] == true )	return;
		if ( $aTitle['do_update'] == '' 	)	return;
		if ( !array_key_exists( $this->m_sTableName, $aTitle['do_update']) ) return;
		
		$aFields = $this->getFields();
		
		$this->setCorrectDB();
		
		
		$aRet = ap_db_create_update_string( $aTitle, $this->m_sTableName, $aFields, $aTitle['do_update'][$this->m_sTableName] );	
		if ( $aRet == null )	 return;
		$sQuery = $aRet['query'] . ' WHERE ' . $this->optionGet('id_name') . ' = %d';
		//printf ( "UPDATE sQuery : %s\n", $sQuery  );
		//var_dump ( $aTitle );
		
		$aArgs = $aRet['args'];
		$aArgs[] = $aTitle[ $this->optionGet('id_name') ];
		
// //         if ( "buy_album" == $this->m_sTableName ) {
// //             var_dump ($aInsertFields);
// //            printf ( "UPDATE sQuery : %s\n", $sQuery  );
// //            var_dump($aArgs);
// //            return; // TODO: TEMPORARY
// //         }
		
		
		$queryResult = db_query($sQuery, $aArgs );
// 		var_dump ( $aArgs );
		
		$queryResult = null;
		$this->restoreDefaultDB();
	}
	

	/** Looks up a row in the table using \a $sWHERE_clause and assigns it's associated 
		\a $sAssignToName value to the corresponding field the title, typically 
		the id - ie. primary index. The \a $aLookupFields should be an array of the title/table
		fields to match against in the query.
		The \a $aAssignToNames is an array of field values to read from the table and assign back to the \a $aTitle.  
		Most fields have default values, whic just looks up the TABLE_NAME_id of the table.
		For example:
		lookupTitleId( $aTitle, array('album_id', 'record_store_id', 'media_format_id'), 
					  "WHERE album_id = %d, record_store_id = %d, media_format_id = %d", 'buy_album_id', 0 );
		Will, for each title, lookup the value of : 
		$aLookupFields 	: array of names in the \a $aTitles whose values to use for looking up. Eg. array( "buy_album_id" )
		$sWHERE_clause 	: WHERE clause to use in query eg: "WHERE buy_album_name = '%s'";
		$aAssignToNames : array of names whose corresponding values from the table gets assigned to \a $aTitle from. Eg: array("price_local")
		*/
	public function 	lookupTitleId ( &$aTitle, $aLookupFields="", $sWHERE_clause="", $aAssignToNames=null, $nullVal=null )
	{
		$aLookupFields 	= $aLookupFields	=== "" 		? $this->m_aOpts['lookup_fields']	: $aLookupFields;
		$sWHERE_clause 	= $sWHERE_clause	=== "" 		? $this->m_aOpts['lookup_where']	: $sWHERE_clause;
		$aAssignToNames = $aAssignToNames 	=== null 	? $this->m_aOpts['assign_to_names'] : $aAssignToNames;
		$nullVal 		= $nullVal 			=== null 	? $this->m_aOpts['null_val'] 		: $nullVal;
		
		$aFields = $this->getFields() ;
		
		$sQuery = "SELECT *";
// // 		$sQuery = "SELECT";
// // 		$i = 0;
// // 		$iCount = count ($aAssignToNames);
// // 		foreach ( $aAssignToNames as $sAssignToName ) {
// // 			$sQuery .= " $sAssignToName";
// // 			if ( ++$i < $iCount ) $sQuery .= ",";
// // 		}
		$sQuery .= " FROM {{$this->m_sTableName}} $sWHERE_clause";
		
//  		dfb ($sQuery, "lookupTitleId" );
// //  		printf ( "sQuery: $sQuery\n" );
		
		$this->setCorrectDB();
		$aArgs = array();
		foreach ($aLookupFields as $sField ) $aArgs[] = $aTitle[$sField]; 
		$queryResult =  db_query($sQuery, $aArgs );
		if ( $a = db_fetch_array($queryResult)) {
			foreach ( $aAssignToNames as $sAssignToName ) {
				$aTitle[$sAssignToName] = $a[$sAssignToName];
			}
			$this->getEmptyFields( $aTitle, $a );
			$this->addUpdateOnEmptyFields( $aTitle ); 
		}
		else {
			foreach ( $aAssignToNames as $sAssignToName ) {
				$aTitle[$sAssignToName] = $nullVal;
			}
		}
		
		$queryResult = null;
		$this->restoreDefaultDB();
	}
	
	
	public function 	getEmptyFields ( &$aTitle, $aRow )
	{
		$aEmpty = array();
		foreach ( $aRow as $field => $val ) {
			if ( $field == "{$this->m_sTableName}_id" )	continue;
			if ( is_numeric($val) ) {
				if ( $val == 0 ) 	$aEmpty[] = $field;
			}
			else if ( is_string($val) ) {
				if 	( $val == "" ) 	$aEmpty[] = $field;
			}
		}
 		$aTitle['empty_fields'][$this->m_sTableName] = $aEmpty;
	}
	
	public function 	addUpdateOnEmptyFields ( &$aTitle )
	{
		$aUpd = array();
		$aEmpty = $aTitle['empty_fields'][$this->m_sTableName];
		$aThisUpdOnEmpty = $aTitle['do_update_on_empty'][$this->m_sTableName];
		if ( is_array($aThisUpdOnEmpty) ) {
			if ( $aThisUpdOnEmpty[0] != '' ) { 
				$aUpd = array_intersect ( $aEmpty, $aThisUpdOnEmpty );
 			}
 			else {
				$aUpd = $aEmpty;
 			}
 			if ( count($aUpd) > 0 ) {
 				$aDoUpd = $aTitle['do_update'][$this->m_sTableName];
 				if ( is_array($aDoUpd) ) {
 					$aTitle['do_update'][$this->m_sTableName] = ap_array_union ( $aDoUpd, $aUpd );
 				}
 				else {
 					$aTitle['do_update'][$this->m_sTableName] = $aUpd;
 				}
 			}
			
		}
	}
	
// <do_insert>record_store,artist,artist_synonym,album,buy_album,buy_album_history</do_insert>
// <do_update>buy_album:price_local,currency_id,buy_at_url,timestamp_updated;buy_album_history:price_local,currency_id,timestamp_updated</do_update>
// <do_update_on_empty>artist;album</do_update_on_empty>
	// ---------------------
	
	
	public function 	lookupRaw ( $aTitle, $aLookupFields="", $sWHERE_clause="", $aAssignToNames=null )
	{
		$aLookupFields 	= $aLookupFields	=== "" 		? $this->m_aOpts['lookup_fields']	: $aLookupFields;
		$sWHERE_clause 	= $sWHERE_clause	=== "" 		? $this->m_aOpts['lookup_where']	: $sWHERE_clause;
		$aAssignToNames = $aAssignToNames 	=== null 	? $this->m_aOpts['assign_to_names'] : $aAssignToNames;
		
		$aFields = $this->getFields() ;
		
		$sQuery = "SELECT *";
// // 		$sQuery = "SELECT";
// // 		$i = 0;
// // 		$iCount = count ($aAssignToNames);
// // 		foreach ( $aAssignToNames as $sAssignToName ) {
// // 			$sQuery .= " $sAssignToName";
// // 			if ( ++$i < $iCount ) $sQuery .= ",";
// // 		}
		$sQuery .= " FROM {{$this->m_sTableName}} $sWHERE_clause";

		$this->setCorrectDB();
		$aArgs = array();
		foreach ($aLookupFields as $sField ) $aArgs[] = $aTitle[$sField]; 
 		
		$queryResult =  db_query($sQuery, $aArgs );
		$this->restoreDefaultDB();
		return $queryResult;
	}
	
	public function 	lookup ( $aTitle, $aTitleFields="", $sWHERE_clause="", $aAssignToNames=null )
	{
		$this->setCorrectDB();
		$aRet = null;
		$queryResult = $this->lookupRaw ( $aTitle, $aTitleFields, $sWHERE_clause, $aAssignToNames );
		
		while ( $a = db_fetch_array($queryResult)) {
			$aRet[] = $a;
		}
		
		$queryResult = null;
		$this->restoreDefaultDB();
		return $aRet;
	}
	// -----------------------------------
	// -----------------------------------

	// -----------------------------
	// --- Basic table functions ---
	// -----------------------------
	public function 	deleteById ( $id )
	{
		$this->setCorrectDB();
		$sQuery = "DELETE FROM {$this->m_sTableName} WHERE {$this->m_sTableName}_id = %d";
		$queryResult = db_query( $sQuery, array($id) );
		
		$queryResult = null;
		$this->restoreDefaultDB();
		return $id;
	}


	public function 	Records ( )
	{
		$iCount = (int)0;
		$this->setCorrectDB();
		$sQuery = "SELECT COUNT(*) FROM {{$this->m_sTableName}}";
		$queryResult = db_query( $sQuery, array() );
		if ( $a = db_fetch_array($queryResult)) {
			$iCount = (int)$a['COUNT(*)'];
		}
		$queryResult = null;
		$this->restoreDefaultDB();
		return $iCount;
	}


    public function countRecords ( )
    {
        $iCount = (int)0;
        $this->setCorrectDB();
        $sQuery = "SELECT COUNT(*) FROM {{$this->m_sTableName}}";
        $queryResult = db_query( $sQuery, array() );
        if ( $a = db_fetch_array($queryResult)) {
            $iCount = (int)$a['COUNT(*)'];
        }
        $queryResult = null;
        $this->restoreDefaultDB();
        return $iCount;
    }

	public function 	setCorrectDB ( )
	{
		if ( $this->m_bEnsureCorrectDB === true )	return;
		$this->m_bEnsureCorrectDB = true;
		db_set_active( $this->m_aOpts['db_name'] );
	}

	public function 	restoreDefaultDB ( )
	{
		if ( $this->m_bEnsureCorrectDB === false )	return;
		$this->m_bEnsureCorrectDB = false;
		db_set_active('default');
	}


	/** Add or overwrite options with those incoming. Options 
		not specified in incoming array are not affected. */
	public function 	optionsAdd ( $aOpts )
	{
		foreach ($aOpts as $key => $val ) {
			$this->m_aOpts[$key] = $val;
		}
	}


	/** Overwrite all options with those incoming.
	\note 	Remember that event the default 'db_name' (=> 'airplay_music') is overwritten 
			to the new value coming from here. Normally You'd want to use optionsAdd instead. */
	public function 	optionsSet ( $aOpts )
	{
		$this->m_aOpts = $aOpts;
	}

	public function 	&optionsGet ( )
	{
		return $this->m_aOpts;
	}

	public function 	&optionGet ( $sOpt )
	{
		return $this->m_aOpts[$sOpt];
	}

	public function 	optionSet ( $sOpt, $val )
	{
		return $this->m_aOpts[$sOpt] = $val;
	}

	/** Get a php array that can translate a name to an id. */
	public function &getIdArray() 
	{
		if ( $this->m_aIDs == null ) {
			$this->m_aIDs = array();
			$this->setCorrectDB();
			$sNameName = $this->m_aOpts['name_name'];
			$sIDName = $this->m_aOpts['id_name'];
			$query = "SELECT " . $this->m_aOpts['name_name'] . ", " . $this->m_aOpts['id_name'] . " FROM {{$this->m_sTableName}}";
			$queryResult =  db_query($query);
			while ( $a = db_fetch_array($queryResult)) {
				////dfb( $a, "ID" );
 				$this->m_aIDs[$a[$sNameName]]= $a[$sIDName];
			}
			$queryResult = null;
			$this->restoreDefaultDB();
		}
		return $this->m_aIDs;
	}


	/// Get table definition from DB. Table must be in DB.
	public function		getTableDefFromDB( $bFullDef = false, $bIncludePrimaryInFields = false )	
	{	
// 		drush_print ("getTableDefFromDB");
		$this->setCorrectDB();
		$aTbl = array( 'TABLE_NAME' => $this->m_sTableName );
		$aTbl['PRI'] = "" ;
		$aTbl['PRI_FMT_STR'] = "" ;
		$aTbl['FIELDS'] = array() ;
		
		$queryResult = db_query("SHOW COLUMNS FROM " . $this->m_sTableName );		
		while ( $row = db_fetch_array($queryResult) ) {
			// Find format string for field 
			$sFieldFmtStr = "'%s'";
			$sField = $row['Field'];
			$sFieldType = $row['Type'];
			if 		( stripos($sFieldType , "int" 		) !== false ) $sFieldFmtStr = "%d";
			else if ( stripos($sFieldType , "timestamp" ) !== false ) $sFieldFmtStr = "%d";
			else if ( stripos($sFieldType , "float" 	) !== false ) $sFieldFmtStr = "%f";
			else if ( stripos($sFieldType , "double" 	) !== false ) $sFieldFmtStr = "%f";
			
			if ( $bIncludePrimaryInFields )		$aTbl['FIELDS'][$sField] = $sFieldFmtStr;
			else if ( $row['Key'] != 'PRI' ) 	$aTbl['FIELDS'][$sField] = $sFieldFmtStr;
			
			if ( $row['Key'] == 'PRI' ) { 
				$aTbl['PRI'] = $sField;
				$aTbl['PRI_FMT_STR'] = $sFieldFmtStr;
			}
			if ( $bFullDef ) $aTbl[$sField] = $row;
		}		
		$queryResult = null;
		$this->restoreDefaultDB();
		return $aTbl;
	}

	/// Get table definition.
	public function		&getTableDef()	
	{	
		if ( !array_key_exists( 'table_def', $this->m_aOpts  ) ) {
			$this->m_aOpts['table_def'] = $this->getTableDefFromDB( $bFullDef, $bIncludePrimaryInFields );
		}
		return $this->m_aOpts['table_def'];
	}

	/// Get table primary key. Table must be in DB.
	public function		&getPrimaryKey()	
	{	
		$aTblDef = $this->getTableDef();
		return $aTblDef['PRI'];
	}

	/// Get table fields , except primary key. Table must be in DB.
	public function		&getFields()	
	{	
		$aTblDef = $this->getTableDef();
		return $aTblDef['FIELDS'];
	}
	// -------------------------------
	// --- PRIVATE: Misc Functions ---
	// -------------------------------


	// -----------------------
	// --- Debug functions ---
	// -----------------------
	public function dbgPrintOptions()
	{
		//dfb ( $this->m_aOpts );
	}


	// -------------------
	// --- Member data ---
	// -------------------
	private		$m_sTableName;		///< Name of table to use ( eg. 'genre' )
	private		$m_aOpts;			///< Array of (default) options used throughout the member functions.
	private		$m_aIDs = null;
	private		$m_bEnsureCorrectDB;
};

// ----------------------
// --- Free functions ---
// ----------------------


















// -------------------------------------------------------
// -------------------------------------------------------

