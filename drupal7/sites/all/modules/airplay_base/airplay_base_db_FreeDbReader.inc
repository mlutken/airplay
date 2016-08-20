<?php


class FreeDbReader 
{

	public 	function	__construct( $sDefaultFreeDBGenre = "" )
	{
		$this->m_sDefaultFreeDBGenre = $sDefaultFreeDBGenre;
		$this->m_bDoUpdateFields = true;
		$this->m_iCountSkipped = 0;
		$this->m_iCountTotal = 0;
		$this->m_aTitles  = null;
	}

	public 	function	updateFieldsSet	( $bDoUpdateFields )
	{
		$this->m_bDoUpdateFields = $bDoUpdateFields;
	} 	
	
	public 	function 	countSkippedGet () 	{	return $this->m_iCountSkipped; }
	public 	function 	countTotalGet 	() 	{	return $this->m_iCountTotal; }

	public	function	parseDirectory($sDirName)
	{
		$aTitles = array();
		$this->m_iCountTotal = (int)0;
		if ($handle = opendir($sDirName)) {
			/* This is the correct way to loop over the directory. */
			while (false !== ($file = readdir($handle))) {
				$this->m_iCountTotal++;
				$sFullFilePath = $sDirName . "/" . $file;
				$this->parseOneFile($sFullFilePath);
				$a = $this->getTitlesToAdd();
				$aTitles = array_merge( $aTitles, $a );
// // 				printf("i : $i  ; res: $res\n");
				if ( $this->m_iCountTotal % 500 == 0 ) { 
					//printf ("WWWWWWWWWWWWWWWWWWW  i: $i\n");
					airplay_insert_update_data( $aTitles );
					$aTitles = array();
				}
// 				//$this->addToDB();
			}
			closedir($handle);
		}
		// Add the rest to DB
		airplay_insert_update_data( $aTitles );
	}

	public	function	parseOneFile( $sFileName )
	{
		$this->resetData();
		$this->m_sState  = "";
		$iTitleNum = 0;
		$iTitlesLength = 0;
		$fTitlesLength = 0;
		
		$fp = fopen ( $sFileName, "r" );
		while ( !feof($fp) ) {
			$sOldState = $this->m_sState;
			$sLine = $this->getNextLine( $fp );
			
			////print "State: '{$this->m_sState}'\n";
			//print $sLine;
			if ( $this->m_sState == 'ReadOffsets' ) 
			{
				if ( $sOldState == '' )	{
					$iTitleNum = 0;
					$iTitlesLength = 0;
					$fTitlesLength = 0;
				}
				$a = explode ( '#', $sLine );
				$iFrameOffset = trim ( $a[1] );
				if ( $iFrameOffset != '' ) {
					$this->m_iTitlesCount++;
					$this->m_aFrameOffsets[$iTitleNum] = $iFrameOffset;
					//print "XX Offset[$iTitleNum] = $iFrameOffset\n";
					if ( $iTitleNum > 0 ) {
						$fTitleLength = ($this->m_aFrameOffsets[$iTitleNum] - $this->m_aFrameOffsets[$iTitleNum-1]) / 75;
						$this->m_aTitlesLength[$iTitleNum-1] = floor($fTitleLength);
						$fTitlesLength += $fTitleLength;
						$iTitlesLength += $this->m_aTitlesLength[$iTitleNum-1];
					}
				}
				else {
					$this->m_sState  = "";
				}
				$iTitleNum++;				
			}
			else if ( $this->m_sState == 'ReadTitles' ) {
				if ( $sOldState == '' )	$iTitleNum = 0;
				////printf("ReadTitles: $iTitleNum\n");
				$sString = $this->getSimpleVal( $sLine );
				$sArtist = "";
				$sTitle = "";
				$this->getTrackArtistTitle( $sArtist, $sTitle, $sString );
				$this->m_aTitlesName[$iTitleNum]		= $sTitle;	
				$this->m_aTitlesArtistName[$iTitleNum]	= $sArtist;	
				if ( $sTitle == "" ) $this->m_bFoundEmptyTitle = true;
				$iTitleNum++;
			}
			else if ( strpos($sLine, 'Disc length:' ) !== false  ) {
				$a = explode ( ':', $sLine);
				$this->m_iCDLenght = trim ( $a[1] );
				$this->m_aTitlesLength[$this->m_iTitlesCount-1] = floor($this->m_iCDLenght - $fTitlesLength);
			}			
			else if ( strpos($sLine, 'DISCID' ) !== false  ) {
				$this->m_sCDId = $this->getSimpleVal( $sLine);
			}			
			else if ( strpos($sLine, 'DTITLE' ) !== false  ) {
				$sString = $this->getSimpleVal( $sLine );
				$this->getCDArtistTitle( $this->m_sArtistName, $this->m_sCDTitleName, $sString );
			}			
			else if ( strpos($sLine, 'DYEAR' ) !== false  ) {
				$this->m_iCDYear = $this->getSimpleVal( $sLine);
			}			
			else if ( strpos($sLine, 'DGENRE' ) !== false  ) {
				$this->m_sCDSubGenre = $this->getSimpleVal( $sLine);
				if ( $this->m_sCDSubGenre == "" ) $this->m_sCDSubGenre = $this->m_sDefaultFreeDBGenre;
				////if ( $this->m_sCDSubGenre == "" ) $this->m_sCDSubGenre = "Unknown";
				$this->m_sCDGenre = FreeDbReader::FreeDbGenreToAirplay( $this->m_sCDSubGenre );
				if ( $this->m_sCDGenre == "" && $this->m_sDefaultFreeDBGenre != "" ) {
					$this->m_sCDGenre = FreeDbReader::FreeDbGenreToAirplay( $this->m_sDefaultFreeDBGenre );
				}
			}			
						
						
		}			
		fclose($fp);
	}

	/// Get artist, title
	public function		getCDArtistTitle( &$sArtist, &$sTitle, $sString ) 
	{
		$a = explode ( ' / ', $sString);
		if ( count($a) == 2 ) {
			$sArtist = $this->cleanUpString( trim($a[0]) );
			$sTitle = $this->cleanUpString( trim($a[1]) );
		}
		else {
			$sArtist = $this->cleanUpString( trim($a[0]) );
			$sTitle = $this->cleanUpString( trim($a[0]) );
		}
	}	

	/// Get artist, title
	public function		getTrackArtistTitle( &$sArtist, &$sTitle, $sString ) 
	{
		$a = explode ( ' / ', $sString);
		if ( count($a) == 2 ) {
			$sArtist = $this->cleanUpString( trim($a[0]) );
			$sTitle = $this->cleanUpString( trim($a[1]) );
// // 			printf( "A: '$sArtist' , A: '{$this->m_sArtistName}'\n");
			if ( $sArtist != $this->m_sArtistName ) $this->m_bIsVariousArtists = true;
		}
		else {
			$sArtist = $this->m_sArtistName;
			$sTitle = $this->cleanUpString( trim($a[0]) );
		}
	}	

	/// Get value (SomeValue) from lines of type SOMENAME=SomeValue
	public function		getSimpleVal( $sLine ) 
	{
		$a = explode ( '=', $sLine);
		return $this->cleanUpString( trim( $a[1] ) );
	}	
	
	/// Get cd type 'album' or 'single'
	public function		getCdType() 
	{
		if ( (count($this->m_aTitlesName) <= 3) && ($this->m_iCDLenght < 60*25) )	return 'song';
		else 																		return 'album';
	}	
	
	public function		skipInsertInDB() 
	{
		$bSkipInsertInDB = false;
		if ( $this->getCdType() == 'song' ) $bSkipInsertInDB = true;
		$sSubGenre = mb_strtolower( $this->m_sCDSubGenre, 'UTF-8' );
		if ( in_array( $sSubGenre, FreeDbReader::$M_aDbGenresToSkip ) ) {
			$bSkipInsertInDB = true;
		}
		if ( $this->m_bFoundIllegalChars 	)	$bSkipInsertInDB = true;
		if ( $this->m_bFoundEmptyTitle		)	$bSkipInsertInDB = true;
		if ( $this->m_bIsVariousArtists		)	$bSkipInsertInDB = true;
		return $bSkipInsertInDB;
	}	
	
	public	function	getAlbumDBData()
	{
		$a = array();
		
		$a['data_record_type'] 		= 'album';
		$a['artist_status']			= 2;
		$a['album_status']			= 2;
		
		$a['artist_name'] 			= $this->m_sArtistName;
		$a['album_name'] 			= $this->m_sCDTitleName;
		$a['genre_name'] 			= $this->m_sCDGenre;
		$a['subgenre_name'] 		= $this->m_sCDSubGenre;
		$a['album_year'] 			= $this->m_iCDYear;
		
		$a_artist_upd = array();
		if ( $a['genre_name'] != "" ) 		$a_artist_upd[] = 'genre_id';
		if ( $a['subgenre_name'] != "" ) 	$a_artist_upd[] = 'subgenre_id';
		$a_album_upd = $a_artist_upd;
		$a_album_upd[] = 'album_year';
		
	 	$a['do_insert'] = array( 'subgenre','artist', 'artist_synonym', 'album') ;
		$a['do_update'] = array
			( 
			);
		$a['do_update_on_empty'] = array
			( 
				'artist' => array('genre_id','subgenre_id' ),
				'album' => array('genre_id','subgenre_id','album_year' )
			);
		if ( $this->m_bDoUpdateFields == false ) 	$a['do_update'] = array();
		return $a;
	}
	
	
	public	function	getSongDBData( $iIndex )
	{
		if ( $iIndex >= count($this->m_aTitlesName) ) return false;

		$a = array();
		$a['data_record_type'] 		= 'song';
		
		$a['song_status']			= 2;
		$a['artist_status']			= 2;
		$a['track_number'] 			= $iIndex +1;
		$a['artist_name'] 			= $this->m_aTitlesArtistName[$iIndex];
		$a['song_name'] 			= $this->m_aTitlesName[$iIndex];
		$a['song_time'] 			= $this->m_aTitlesLength[$iIndex];
		$a['song_year'] 			= $this->m_iCDYear;
		$a['genre_name'] 			= $this->m_sCDGenre;
		$a['subgenre_name'] 		= $this->m_sCDSubGenre;
		
		$a['album_name'] 			= $this->m_sCDTitleName;
		$a['album_year'] 			= $this->m_iCDYear;
		
		if ($this->m_bIsVariousArtists) $a['album_artist_name'] 	= $this->m_sArtistName;
		
		$a_song_upd = array('song_time');
		if ( $a['genre_name'] != "" ) 		$a_song_upd[] = 'genre_id';
		if ( $a['subgenre_name'] != "" ) 	$a_song_upd[] = 'subgenre_id';
		if ( !$this->m_bIsVariousArtists ) 	$a_song_upd[] = 'song_year';
		
		
	 	$a['do_insert'] = array( 'subgenre', 'artist', 'artist_synonym', 'album', 'song', 'album_song_rel' ) ;
		$a['do_update'] = array
			( 
				'song' => array('song_time'),
	 			'album_song_rel' => array( 'track_number' )
			);
		$a['do_update_on_empty'] = array
			( 
				'song' => array('genre_id','subgenre_id','song_year')
			);
		if ( $this->m_bDoUpdateFields == false ) 	$a['do_update'] = array();
		return $a;
	}
	
	public	function	getTitlesToAdd()
	{
// 		if ( $this->m_bIsVariousArtists ) 	printf("\n *** m_bIsVariousArtists\n \n");
// 		else								printf("\n *** NOT m_bIsVariousArtists\n \n");
		
		$aTitles = array();
		if ( $this->skipInsertInDB() )	{
			$this->m_iCountSkipped++;
			return $aTitles;
		}
		$aTitle = $this->getAlbumDBData();
		if ( $aTitle['album_name'] != "" &&  $aTitle['artist_name'] != "" ) { 
			$aTitles[] = $aTitle;
		}
		for ( $iIndex = 0 ; $iIndex < $this->m_iTitlesCount; $iIndex++ ) {
			$aTitle = $this->getSongDBData($iIndex);
			if ( ($aTitle['song_name']) != "" &&  ($aTitle['artist_name'] != "") ) { 
				$aTitles[] = $aTitle;
			}
			////break; // /TODO: Debug only !!!
		}
		return $aTitles;
	}
	
	public	function	addToDB()
	{
		$aTitles = $this->getTitlesToAdd();
		airplay_insert_update_data( $aTitles );
		$aTitles = null;
	}
	
	// -----------------------
	// --- Debug functions ---
	// -----------------------
	public	function	dbgPrintTitle()
	{
// 		drush_print_r ($this->m_aFrameOffsets);
		drush_print_r ($this->m_aTitlesLength);
		drush_print_r ($this->m_aTitlesArtistName);
		drush_print_r ($this->m_aTitlesName);
		drush_print   ("m_sArtistName: {$this->m_sArtistName}");
		drush_print   ("m_sCDId: {$this->m_sCDId}");
		drush_print   ("m_iCDLenght: {$this->m_iCDLenght}");
		drush_print   ("m_iCDYear: {$this->m_iCDYear}");
		drush_print   ("m_sCDTitleName: {$this->m_sCDTitleName}");
		drush_print   ("m_sCDGenre: {$this->m_sCDGenre}");
		drush_print   ("m_sCDSubGenre: {$this->m_sCDSubGenre}");
		drush_print   ("m_iTitlesCount: {$this->m_iTitlesCount}");
 		//drush_print_r ( $this->getTitlesToAdd() );
	}


	// --------------------------
	// --- PRIVATE: Functions ---	
	// --------------------------

	public	function	getNextLine( $fp )
	{
		$this->m_bStateChanged = false;
		$sOldState = $this->m_sState;
		$sLine = fgets ( $fp );
		if ( strpos($sLine, 'Track frame offsets' ) !== false ) {
			$this->m_sState = "ReadOffsets";
			$sLine = fgets ( $fp );
		}
		else	if ( strpos($sLine, 'TTITLE') !== false ) {
				$this->m_sState = "ReadTitles";
		}
		// Test for leaving of state 'ReadTitles'
		if ( $this->m_sState == "ReadTitles" && strpos($sLine, 'TTITLE') === false ) {
			$this->m_sState = "";
		}
		
		if ( $sOldState != $this->m_sState )	$this->m_bStateChanged = true;
		return $sLine;
	}
	
	
	public	function	resetData()
	{
		$this->m_aFrameOffsets 		= null;
		$this->m_aTitlesLength 		= null;
		$this->m_aTitlesName		= null;	
		$this->m_aTitlesArtistName	= null;	
		$this->m_sArtistName		= "";
		$this->m_sCDId				= "";
		$this->m_iCDLenght			= 0;
		$this->m_iCDYear			= 0;
		$this->m_sCDTitleName		= "";
		$this->m_sCDGenre			= "";
		$this->m_sCDSubGenre		= "";
		$this->m_sState				= "";
		$this->m_bStateChanged		= false;
		$this->m_iTitlesCount 		= 0;
		$this->m_bFoundIllegalChars	= false;
		$this->m_bFoundEmptyTitle	= false;
		$this->m_bIsVariousArtists	= false;
	}
	
	public static function	FreeDbGenreToAirplay( $sFreeDbGenre )
	{
		$sFreeDbGenre = mb_strtolower ( $sFreeDbGenre, 'UTF-8' );
		return FreeDbReader::$M_aFreeDbGenreToAirplay[$sFreeDbGenre];
	}
	
	public function		cleanUpString( $sString )
	{
		$sCleanString = iconv("UTF-8","UTF-8//IGNORE", $sString);
		////printf("\n\n cleanUpString\n '$sCleanString' != '$sString'\n\n");
		if ( $sCleanString != $sString ) {
// 			printf("\n\n ************** Illegal chars found! **************  \n\n");
			$this->m_bFoundIllegalChars = true;
		}
		return $sCleanString;
	}
	
	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
	private		$m_aFrameOffsets 		= null;
	private		$m_aTitlesLength 		= null;
	private		$m_aTitlesName			= null;	
	private		$m_aTitlesArtistName	= null;	
	private		$m_sArtistName			= "";
	private		$m_sCDId				= "";
	private		$m_iCDLenght			= 0;
	private		$m_iCDYear				= 0;
	private		$m_sCDTitleName			= "";
	private		$m_sCDGenre				= "";
	private		$m_sCDSubGenre			= "";
	private		$m_sState				= "";
	private		$m_bStateChanged		= false;
	private 	$m_iTitlesCount 		= 0;
	private		$m_sDefaultFreeDBGenre	= "";
	private		$m_bDoUpdateFields		= true;
	private		$m_bFoundIllegalChars	= false;
	private		$m_bFoundEmptyTitle		= false;
	private		$m_bIsVariousArtists	= false;
	private		$m_iCountSkipped		= 0;
	private		$m_iCountTotal			= 0;
	private		$m_aTitles				= 0;
	
	private static $M_aFreeDbGenreToAirplay = array(
		'blues' => 'Jazz/Blues', 
		'classical' => 'Classical', 
		'country' => 'Country/Folk',
		'data'	=> 'Other', 
		'folk' => 'Country/Folk', 
		'jazz' => 'Jazz/Blues', 
		'misc' => 'Other', 
		'new age' => 'New age', 
		'reggae' => 'World/Reggae', 
		'rock' => 'Pop/Rock', 
		'soundtrack' => 'Soundtrack',
		
		'dance' => 'Dance/Electronic', 
		'pop' => 'Pop/Rock' 
		
		);
	
	private static $M_aDbGenresToSkip = array(
		'Data', 'Book'
		);
	
	private static $M_aSubstInStrings = array(
		'�' => "'"
		);
	
}

?>







