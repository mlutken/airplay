<?php
// $Id: music_search.drush.inc,v 1.2 2008/12/07 03:29:15 weitzman Exp $
 
/**
 * @file
 * Music search drush command.
 *
 * Shows how to make your own drush command.
 *
 * You can copy this file either into a .drush folder in your HOME folder,
 * along with one of your existing modules or in a folder specified with
 * the include option.
 */
 
/**
 * Implementation of hook_drush_command().
 *
 * In this hook, you specify which commands your
 * drush module makes available, what it does and
 * description.
 *
 * Notice how this structure closely resembles how
 * you define menu hooks.
 *
 * @See drush_parse_command() for a list of recognized keys.
 *
 * @return
 * An associative array describing your command(s).
 */
function music_search_drush_command() {
	$items = array();
	
	// the key in the $items array is the name of the command.
	$items['ap_read_excel_data'] = array(
		// the name of the function implementing your command.
		'callback' => 'ap_read_excel_data',
		// a short description of your command
		'description' => "Reads in data from a exported exel file. Those from Ulrik",
	);
	$items['ap_read_xml_data'] = array(
		'callback' => 'ap_read_xml_data',
		'description' => "Reads in data from an xml file.",
	);
	$items['ap_read_freedb_file'] = array(
		'callback' => 'ap_read_freedb_file',
		'description' => "Reads data from one freedb file.",
	);
	$items['ap_read_freedb_dir'] = array(
		'callback' => 'ap_read_freedb_dir',
		'description' => "Reads data from one freedb directory.",
	);
	$items['ap_upd_simple_album_names'] = array(
		'callback' => 'ap_upd_simple_album_names',
		'description' => "Updates simple album names. Make sure to delete album_simple table if you have changed in chars to replace/delete. See ap_simplify_item_name() in airplay_base/airplay_base_utils.inc",
	);
    $items['ap_upd_simple_song_names'] = array(
        'callback' => 'ap_upd_simple_song_names',
        'description' => "Updates simple song names. Make sure to delete song_simple table if you have changed in chars to replace/delete. See ap_simplify_item_name() in airplay_base/airplay_base_utils.inc",
    );
	$items['ap_read_upload_cron'] = array(
		'callback' => 'ap_read_upload_cron',
		'description' => "Reads data from the upload directory. I.e. the xml files uploaded from miners"
	);
    $items['ap_upd_sitemap_cron'] = array(
        'callback' => 'ap_upd_sitemap_cron',
        'description' => "Updates sitemaps"
    );
    $items['ap_upd_wordfiles_cron'] = array(
        'callback' => 'ap_upd_wordfiles_cron',
        'description' => "Updates word files for use for miners"
    );
	$items['ap_upd_rates_cron'] = array(
		'callback' => 'ap_upd_rates_cron',
		'description' => "Updates exchange rates table ."
	);
    $items['ap_upd_misc_cron'] = array(
        'callback' => 'ap_upd_misc_cron',
        'description' => "Updates misc data."
    );
    $items['ap_adhoc_test'] = array(
        'callback' => 'ap_adhoc_test',
        'description' => "Ad hoc test playground."
    );
	$items['ap_delete_old_album_prices_cron'] = array(
        'callback' => 'ap_delete_old_album_prices_cron',
        'description' => "Delete old album prices."
    );
	$items['ap_delete_old_song_prices_cron'] = array(
        'callback' => 'ap_delete_old_song_prices_cron',
        'description' => "Delete old song prices."
    );
	
	// more commands here ...
	
	return $items;
}
 
/**
 * Implementation of hook_drush_help().
 *
 * This function is called whenever a drush user calls
 * 'drush help <name-of-your-command>'
 *
 * @param
 * A string with the help section (prepend with 'drush:')
 *
 * @return
 * A string with the help text for your command.
 */
function music_search_drush_help($section) {
	switch ($section) 
	{
		case 'drush:ap_read_excel_data':
			return dt("Reads in data from a exported exel file. Those from Ulrik.");
		case 'drush:ap_read_xml_data':
			return dt("Reads in data from an xml file.");
		case 'drush:ap_read_freedb_file':
			return dt("Reads data from one freedb file.");
		case 'drush:ap_read_freedb_dir':
			return dt("Reads data from one freedb directory.");
        case 'drush:ap_upd_simple_album_names':
            return dt("Updates simple album names.");
        case 'drush:ap_upd_simple_song_names':
            return dt("Updates simple song names.");
//		case 'drush:ap_upd_simple_album_names':
//			return dt("Reads data from one freedb directory.");
		case 'drush:ap_read_upload_cron':
			return dt("Reads data from the upload directory. I.e. the xml files uploaded from miners.");
        case 'drush:ap_upd_sitemap_cron':
            return dt("Updates site maps.");
        case 'drush:ap_upd_wordfiles_cron':
            return dt("Updates word files used by miners.");
		case 'drush:ap_upd_rates_cron':
			return dt("Updates exchange rates table.");
        case 'drush:ap_upd_misc_cron':
            return dt("Updates misc data.");
        case 'drush:ap_adhoc_test':
            return dt("Ad hoc test/playground.");
		case 'drush:ap_delete_old_album_prices_cron':
            return dt("Delete old album prices.");
		case 'drush:ap_delete_old_song_prices_cron':
            return dt("Delete old song prices.");
			
	}
}
 
 
 
 
 
 
function ap_read_xml_data() {
	$commands = func_get_args();
	if ( count ($commands) < 1 ) {
		drush_print ( "Error: Please specify a filename to read" );
		return;
	}
	$bInsUpdDefault = false;
	if ( count ($commands) > 1 ) {
		if ( $commands[1] == '1' )  $bInsUpdDefault = true;
	}
	$sFileName = $commands[0];
	if ( !file_exists($sFileName) ) {
		$sFileName = $_SERVER['PWD'] . '/' . $sFileName;
	}
	
	
	$xmlRead = new AirplayXMLReader( true, $bInsUpdDefault );
	$xmlRead->readXMLData( $sFileName );
// 	$xmlRead->dbgPrintTitles();
} 
 
 
 
 
function ap_read_freedb_file() {
	$commands = func_get_args();
	if ( count ($commands) < 1 ) {
		drush_print ( "Error: Please specify a filename to read" );
		return;
	}
	$bDoUpdateFields = true;
	$sDefaultFreeDBGenre = "";
	if ( count ($commands) > 1 ) {
		$sDefaultFreeDBGenre = $commands[1];
		if ( count ($commands) > 2 ) {
			if ( $commands[2] == '0' )  $bDoUpdateFields = false;
		}
	}
	
	$sFileName = $commands[0];
	
	$freedbRead = new FreeDbReader( $sDefaultFreeDBGenre );
	$freedbRead->updateFieldsSet($bDoUpdateFields);
	$freedbRead->parseOneFile( $sFileName );
	$freedbRead->dbgPrintTitle();
	$freedbRead->addToDB();
	$iSkippedRecs = $freedbRead->countSkippedGet();
	drush_print ( "Number of skipped records: $iSkippedRecs");
} 
 
 
function ap_read_freedb_dir() {
	$commands = func_get_args();
	if ( count ($commands) < 1 ) {
		drush_print ( "Error: Please specify a directory to read" );
		return;
	}
	$bDoUpdateFields = true;
	$sDefaultFreeDBGenre = "";
	if ( count ($commands) > 1 ) {
		$sDefaultFreeDBGenre = $commands[1];
		if ( count ($commands) > 2 ) {
			if ( $commands[2] == '0' )  $bDoUpdateFields = false;
		}
	}
 	$sDirName = $commands[0];
	drush_print ( "ap_read_freedb_dir: $sDirName" );
	$freedbRead = new FreeDbReader( $sDefaultFreeDBGenre );
	$freedbRead->updateFieldsSet($bDoUpdateFields);
	$freedbRead->parseDirectory( $sDirName );
	
	$iSkippedRecs = $freedbRead->countSkippedGet();
	$iTotalRecs = $freedbRead->countTotalGet();
	drush_print ( "Number of skipped records: $iSkippedRecs");
	drush_print ( "Number of total records: $iTotalRecs");
} 
 
 
 
function ap_upd_simple_item_names($item, $commands) {
    // --- Read command line params ---
    $bDoUpdateExistingSimpleNames = 0;
    $bDbg = 0;
    $iResStart = "continue"; // continue from where we last got to
    if ( count ($commands) > 0 ) {
        $iResStart = $commands[0];
        printf("Setting : 'iResStart'  to '$iResStart' \n" );
        if ( count ($commands) > 1 ) {
            $bDoUpdateExistingSimpleNames = $commands[1];
            if ( count ($commands) > 2 ) {
                $bDbg = $commands[2];
            }
        }
    }
    printf("1 ap_upd_simple_{$item}_names : '$iResStart'  bDoUpdateExistingSimpleNames: '$bDoUpdateExistingSimpleNames' \n" );
    if ($iResStart == "continue" ) {
        $iResStart = ap_read_setting( "simple_{$item}_names_upd__cur_index" );
        printf("ResStart from settings: $iResStart\n");
        if ( $iResStart === false ) {
            printf("NO setting 'simple_{$item}_names_upd__cur_index' found using 0\n");
            $iResStart = 0;
        }
    }
    
    printf("2 ap_upd_simple_{$item}_names : '$iResStart'  bDoUpdateExistingSimpleNames: '$bDoUpdateExistingSimpleNames' \n" );
    if ($bDbg) printf("\n");
    $prev_active = db_set_active( 'airplay_music' );
    
    $bEndOfTable    = false;
    $iResPerPage    = 1000;
    $iCount         = $iResStart;
    while ( !$bEndOfTable ) {
        $sQuery = "SELECT artist.artist_id AS artist_id, artist_name, {$item}_id, {$item}_name FROM `{$item}` INNER JOIN artist ON artist.artist_id = {$item}.artist_id";
        
        if ($bDbg) printf("<1 lookup");
        $res = db_query_range($sQuery, $iResStart, $iResPerPage, array() );


        $a = $res->fetchAssoc();
        if ( !$a ) { 
            $bEndOfTable = true;
        }
        $aAllOrigNames = null;
        
        while ( $a ) {
            $a["item_simple_name"] = ap_simplify_item_name( $a["{$item}_name"], $a["artist_name"] );
            $aAllOrigNames[] = $a;
            $a = $res->fetchAssoc();
            $iCount++;
        }
        $res = null;
        if ($bDbg) printf(">\n");

        
        if ($bDbg) printf("<2 find item_simple_id");
        foreach ( $aAllOrigNames as &$a ) {
            $artist_id  = $a[ "artist_id" ];
            $item_id    = $a[ "{$item}_id" ];
            $item_simple_name =  $a[ "item_simple_name" ] ;
            $sQueryLookup   = "SELECT * FROM `{$item}_simple` WHERE artist_id = :artist_id AND {$item}_simple_name = :item_id";
            $resLookup      = db_query($sQueryLookup, array( ':artist_id' => $artist_id,  ':item_id' => $item_simple_name));
            $aLookup        = $resLookup->fetchAssoc();
            if ( $aLookup ) $a["item_simple_id"] = $aLookup[ "{$item}_simple_id" ];
            else            $a["item_simple_id"] = 0;
            $resLookup = null;
        }
        if ($bDbg) printf(">\n");

        //print_r($aAllOrigNames);

        // --- Update simple_name table with new simple name ---
        if ( $bDoUpdateExistingSimpleNames ) {
            if ($bDbg) printf("<3 update table item_simple");
            foreach ( $aAllOrigNames as $a ) {
                $item_simple_id = $a[ "item_simple_id" ] ;
                
                if ( $item_simple_id != 0 ) {
                    $item_simple_name =  $a[ "item_simple_name" ] ;
                    $sQueryUpdate= "UPDATE `{$item}_simple` 
                    SET item_simple_name = '$item_simple_name'
                    WHERE item_simple_id = '$item_simple_id' 
                    ";
                    $resUpdate = db_query($sQueryUpdate, array());
                }
            }
            if ($bDbg) printf(">\n");
        }

        // --- Insert into simple_name table the new simple name ---
        if ($bDbg) printf("<4 insert table item_simple");
        foreach ( $aAllOrigNames as &$a ) {
            $item_simple_name =  $a[ "item_simple_name" ] ;
            $item_simple_id = $a[ "item_simple_id" ] ;
            if ($bDbg == 2) printf("artist_name, item_name: '%s', '%s' => '%s'\n",$a["artist_name"],  $a["{$item}_name"], $item_simple_name);
            if ( $item_simple_id == 0 ) {
                $artist_id = $a[ "artist_id" ] ;
                $sQueryInsert= "INSERT INTO `{$item}_simple` ( artist_id, {$item}_simple_name ) 
                VALUES (:artist_id,:item_simple_name) 
                ";
                $resInsert = db_query($sQueryInsert, array( ':artist_id' => $artist_id, ':item_simple_name' => $item_simple_name));
                $a[ "item_simple_id" ] = db_last_insert_id();
            }
        }
        if ($bDbg) printf(">\n");
        
        if ($bDbg) printf("<5 update item table");
        foreach ( $aAllOrigNames as $a ) {
            $item_id        = $a[ "{$item}_id" ] ;
            $item_simple_id = $a[ "item_simple_id" ] ;
            $sQueryUpdate1= "UPDATE `{$item}` 
                SET {$item}_simple_id = :item_simple_id 
                WHERE {$item}_id = :item_id  
            ";
            $resUpdate1 = db_query($sQueryUpdate1, array( ':item_simple_id' => $item_simple_id, ':item_id' => $item_id ));
        } 
        if ($bDbg) printf(">\n");
        
        if ($bDbg) printf("<6 update buy_item table");
        foreach ( $aAllOrigNames as $a ) {
            $item_id        = $a[ "{$item}_id" ] ;
            $item_simple_id = $a[ "item_simple_id" ] ;
            $sQueryUpdate2= "UPDATE `buy_{$item}` 
                SET {$item}_simple_id = :item_simple_id 
                WHERE {$item}_id = :item_id  
            ";
            $resUpdate2 = db_query($sQueryUpdate2, array( ':item_simple_id' => $item_simple_id, ':item_id' => $item_id ));
        } 
        if ($bDbg) printf(">\n");



        if ( $iCount % $iResPerPage == 0 ) {
            printf("Name[%d]: '%s', '%s'\n",$iCount, $item_simple_name, $a["{$item}_name"] );
        }

        //$bEndOfTable = true; // TODO: temporary
        if ( $bEndOfTable ) {
            printf("DONE[%d]: End of table reached!\n", $iCount );
            $iResStart = 0;
        }
        else {
            $iResStart = $iResStart + $iResPerPage;
            $res = null;
        }
        ap_write_setting( "simple_{$item}_names_upd__cur_index", "$iResStart");

    }
    db_set_active($prev_active);
} 
 
 
function ap_upd_simple_album_names() {
    $commands = func_get_args();
    ap_upd_simple_item_names("album", $commands);
} 
 
 
function ap_upd_simple_song_names() {
    $commands = func_get_args();
    ap_upd_simple_item_names("song", $commands);
} 
 
 
 
 
 
function ap_read_upload_cron() {
	$commands = func_get_args();
	if ( count ($commands) < 1 ) {
		drush_print ( "Error: Please specify a directory to read where the uploaded files are." );
		return;
	}
	$sDirName 		= $commands[0];
	$iTitlesPerPart	= 200;
	$bInsUpdDefault	= false;
	$iFilesToReadPerCronInvocation = 1;
	if ( count ($commands) > 1 ) {
		$iTitlesPerPart = $commands[1];
		if ( count ($commands) > 2 ) {
			if ( $commands[2] == '1' )  $bInsUpdDefault = true;
			if ( count ($commands) > 3 ) {
				$iFilesToReadPerCronInvocation = $commands[3];
			}
		}
	}
	
	$uploadRead = new UploadReader( $sDirName );
	$uploadRead->titlesPerPartSet		( $iTitlesPerPart )	;
	$uploadRead->insertUpdateDefaultSet	( $bInsUpdDefault )	;
	$uploadRead->filesToReadPerCronInvocationSet	( $iFilesToReadPerCronInvocation);
	
	$uploadRead->cron();
	
// 	drush_print_r ( $_SERVER );
} 
 
 
 
function ap_upd_sitemap_cron() {
    $commands = func_get_args();
    
    $sCmd       = "update"; // Commands: update, rewrite, delete
    $sDomain    = "dk";
    $sBaseDir   = "";    
    if ( count ($commands) > 0 ) {
        $sCmd = $commands[0];
        if ( count ($commands) > 1 ) {
            $sDomain = $commands[1];
            if ( count ($commands) > 2 ) {
                $sBaseDir = $commands[2];
            }
        }
    }
    printf("ap_upd_sitemap_cron ( '$sCmd', '$sDomain', '$sBaseDir' )\n");
    $siteMap = new SiteMapWriter( $sCmd, $sDomain, $sBaseDir );
    $siteMap->cron();
} 
 
 
function ap_upd_wordfiles_cron() {
    $commands = func_get_args();
    
    $sCmd       = "artist"; // Commands: artist, album, song, all
    $sBaseDir   = "";    
    if ( count ($commands) > 0 ) {
        $sCmd = $commands[0];
        if ( count ($commands) > 1 ) {
            $sBaseDir = $commands[1];
        }
    }
    printf("ap_upd_wordfiles_cron ( '$sCmd', '$sBaseDir' )\n");
    $WordFile = new WordFilesWriter( $sCmd, $sBaseDir );
    $WordFile->cron();
} 
 
 
 
 
function ap_upd_rates_cron() {
	printf("ap_upd_rates_cron\n");
	$ratesUpdater = new ExchangeRatesUpdater();
	$ratesUpdater->cron();
} 
 
function ap_upd_misc_cron ()
{
	printf("ap_upd_misc_cron\n");
	$ratesUpdater = new MiscDataUpdater();
	$ratesUpdater->cron();
}

function ap_delete_old_album_prices_cron ()
{
	printf("ap_delete_old_album_prices_cron\n");
	$ratesUpdater = new MiscDataUpdater();
	$ratesUpdater->cronDeleteOldAlbumPrices();
}

function ap_delete_old_song_prices_cron ()
{
	printf("ap_delete_old_song_prices_cron\n");
	$ratesUpdater = new MiscDataUpdater();
	$ratesUpdater->cronDeleteOldSongPrices();
}
// --------------------------------------------
// --------------------------------------------
// --------------------------------------------
 
 
 
 
/**
 * Music search drush command callback.
 *
 * This is where the action takes place.
 *
 * In this function, all of Drupals API is (usually) available, including
 * any functions you have added in your own modules/themes.
 *
 * To print something to the terminal window, use drush_print().
 *
 */
function ap_read_excel_data() {
	$commands = func_get_args();
	if ( count ($commands) < 2 ) {
		drush_print ( "Error: Please specify a filename to read and a genre name" );
		drush_print("Possible genre args:\n");
		
		
		db_set_active('airplay_music');
		
		$query_result =  db_query( "SELECT * FROM `genre`", array() );
		while ( $a = $query_result->fetchAssoc()) {
			drush_print( "{$a['genre_name']}" );
		}
		db_set_active('default');

		return;
	}
	$sFileName = $commands[0];
	$genre_name = trim($commands[1]);
	
	drush_print ( "Reading file $sFileName" );	
	$fp = fopen( $sFileName, 'r');
	$iCountArtists = 0;
	$iCountLines = 0;
	
	while ( !feof($fp) ) {
		$sLine = trim(fgets( $fp ));	// Read next line from the file
// 		if ( ++$iCountLines > 50 )	continue;
		
// 		drush_print ( "LINE: $sLine" );
// 		continue;
		
		$aArtistCells = explode ('|', $sLine );
		foreach ( $aArtistCells as $sArtistCell )
		{ 		
			trim_value( $sArtistCell );				
			
			$bDoAddArtist = true;
// 			$country_name = 'Denmark'; 
			$artist_name = '';
			$artist_url = '';
			$artist_status = 1;
			
			$a = explode (';', $sArtistCell );
			array_walk( $a, 'trim_value');	
	
			if ( count ($a) == 0 ) {
				$bDoAddArtist = false;
				drush_print ( "SKIPPING: '$sArtistCell'" );
				continue;
			}	
			else if ( count ($a) > 0 ) {
				$artist_name = $a[0];
			}	
			
			if ( count ($a) == 2 ) {
				if 	( stripos( $a[1] , "http://" ) !== false ) {
					$artist_url = $a[1];
				}
				else if ( stripos( $a[1] , "http//" ) !== false ) {
					$artist_url = str_replace  (  "http//" , "http://" ,  $a[1] );
				}
			}	
			if ( count ($a) > 2 ) {
				drush_print ( "ERROR: '$sArtistCell'" );
				$bDoAddArtist = false;
			}
				
			if 	( stripos( $artist_name , ' - ' ) !== false ) {
				$aArtistName = explode (' - ', $artist_name );
				$artist_name = $aArtistName[0];
// 				drush_print ( "INFO: Skipping description ($artist_name): '" . $aArtistName[1] . "'" );
			}
				
			// Rearange multiple artists artist names.	
			if 	( stripos( $artist_name , "/" ) !== false ) {
				$aArtistName = explode ('/', $artist_name );
				$artist_name_OLD = $artist_name;
				$artist_name = "";
				$i = 0;
				foreach ( $aArtistName as $artist_name_ADD ) {
					$i++;
					$artist_name_ADD = trim( $artist_name_ADD );
					if 	( stripos( $artist_name_ADD , "," ) !== false ) {
						$aArtist = explode (',', $artist_name_ADD );
						array_walk( $aArtist, 'trim_value');
						if ( count($aArtist) == 2 ) {
							$artist_name_ADD = $aArtist[1] . " " . $aArtist[0];
						}	
						else  {
// 							drush_print ( "INFO: Not rearranging name ADD: '$artist_name_ADD'" );
							$artist_status = 2;
						}
					}
					$artist_name .= $artist_name_ADD;
					if ( $i < count($aArtistName) )	$artist_name .= " / ";
				}
// 	   			drush_print ( "ARTIST: '$artist_name'; OLD: '$artist_name_OLD'" );
			}
			
			
				
			if ( $artist_name == "" ) {
// 	 			drush_print ( "SKIPPING: Empty artist name: '$sArtistCell'" );
				continue; 
			}
			
			if ( $bDoAddArtist )
			{
				$iCountArtists++;
// 				if ( $iCountArtists < 2 ) {
					drush_print ( "ARTIST: '$artist_name'; URL: '$artist_url'" );
					$aTitle = array();
					$aTitle['data_record_type'] = 'artist';
					$aTitle['do_insert'] = array('artist', 'artist_synonym'); 
					
					
					$aTitle['do_update'] = array
					( 
// 						'artist' => array( 'artist_url', 'genre_id' ),
						'DUMMY' => array( )
					); 
		
					$aTitle['do_update_on_empty'] = array
					( 
						'artist' => array( 'artist_url', 'genre_id' ),
						'DUMMY' => array( )
					); 
					
					$aTitle['country_name'] = $country_name; 
					$aTitle['genre_name'] = $genre_name;
					$aTitle['artist_name'] = $artist_name;
					$aTitle['artist_url'] = $artist_url;
					$aTitle['artist_status'] = $artist_status;
// 					$aAll = array ($aTitles);
// 					airplay_insert_update_data( $aAll );	

					$aTitles[] = $aTitle;
// 				}				
			}
			
		}		
	}
	fclose($fp);
	
// 	drush_print_r( $aTitles );
	drush_print ( "ADDING $iCountArtists artists" );
	airplay_insert_update_data($aTitles);	
	$aTitles = null;
	drush_print ( "DONE" );
	

}


// --------------------------------------------
// --------------------------------------------
// --------------------------------------------

function ap_adhoc_test ()
{
    printf("ap_adhoc_test\n");
    //ap_simplify_item_name
//     $name1 = " 02. - Navn på emne";
//     $name2 = " 02 - Navn på emne";
//     $name3 = "  - Navn på emne 02.";
//     $name4 = " Navn på emne 02. hallo";
//     $name5 = " Navn på emne 02. hallo";
/*
    $regex = array("/^\s*(-\s*)?/", "/^\s*\d+\.?(\s*-\s*)?/");
    $replace = array("","");
    $n1 = preg_replace( $regex, $replace,  $name1 ); 
    $n2 = preg_replace( $regex, $replace,  $name2 ); 
    $n3 = preg_replace( $regex, $replace,  $name3 ); 
    $n4 = preg_replace( $regex, $replace,  $name4 ); 

    printf("1: '$name1' => '$n1'\n");
    printf("2: '$name2' => '$n2'\n");
    printf("3: '$name3' => '$n3'\n");
    printf("4: '$name4' => '$n4'\n");*/
    $a = array(" 02. - Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" 02 - Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" 02 Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" 02. Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" 02.Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" Navn på emne 02. hallo", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" Navn på emne 02. hallo", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array("Aquarium", "Aqua"); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array("7-9-13", "Kim Larsen"); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );

    $a = array("- 02. - Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array("- 02 - Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array("- 02 Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" - 02. Navn på emne", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" - Navn på emne 02. hallo", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" - Navn på emne 02. hallo", ""); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array(" - Aquarium", "Aqua"); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );
    $a = array("-7-9-13", "Kim Larsen"); printf("{$a[0]}, {$a[1]} => '%s'\n", ap_simplify_item_name($a[0], $a[1]) );

    
    
}




