<?php 
// $Id$

//airplay_base_MiscDataUpdater.inc

class MiscDataUpdater 
{

	public 	function	__construct( )
	{
		$this->m_sLockFile	= "MiscDataUpdater_alock";
		
	}


	function cronDeleteOldAlbumPrices() {
		db_set_active( 'airplay_music' );
		$sQuery  = "DELETE FROM buy_album WHERE timestamp_updated < DATE_ADD(now(), INTERVAL -1 YEAR);";
		$queryResult =  db_query( $sQuery, array() );
        printf("cronDeleteOldAlbumPrices\n");
	}

	function cronDeleteOldSongPrices() {
		db_set_active( 'airplay_music' );
		$sQuery  = "DELETE FROM buy_song WHERE timestamp_updated < DATE_ADD(now(), INTERVAL -1 YEAR);";
		$queryResult =  db_query( $sQuery, array() );
        printf("cronDeleteOldSongPrices\n");
	}

	function cron()
	{
		if ( ! $this->okToRun($this->m_sLockFile) ) {
			drush_print( "Semaphore '{$this->m_sLockFile}' locked\n" );
			return;
		}
		
		$hFile = fopen( $this->m_sLockFile, "w+");	
		writeTimeStamp($hFile);
		drush_print("Running misc update cron ... ");
		$this->m_aSettings = ap_read_settings();
		
		$this->clearWatchDogAndSessions();
		$this->updateTablesCount();
		
		
		// Write time of this update to settings 
		$now = date("Y-m-d G:i:s");		// 		2010-03-01 16:42:20
		$this->m_aSettings ['misc_cron__last_run_time'] = $now ;
		
		ap_write_settings ( $this->m_aSettings );
		
		unlink( $this->m_sLockFile );	// Delete the file on disk.	

	}

    function clearWatchDogAndSessions()
    {
        $sQuery  = "TRUNCATE TABLE watchdog;";
        $queryResult =  db_query( $sQuery, array() );
        printf("clearWatchDogAndSessions watchdog: '$queryResult'\n");
        $sQuery  = "TRUNCATE TABLE sessions;";
        $queryResult =  db_query( $sQuery, array() );
        printf("clearWatchDogAndSessions sessions: '$queryResult'\n");
    }


	function updateTablesCount( ) 
	{
		db_set_active( 'airplay_music' );
		
		$this->m_aSettings['artist__count'] = 0;
		$this->m_aSettings['album__count'] = 0;
		$this->m_aSettings['song__count'] = 0;
		
		$sql__artist 	= "SELECT COUNT(*) FROM `artist`";
		$sql__album 	= "SELECT COUNT(*) FROM `album`";
		$sql__song 		= "SELECT COUNT(*) FROM `song`";
		
		$sqlRes = db_query( $sql__artist, array() );
		if ( $a = db_fetch_array($sqlRes) ) { 
			$this->m_aSettings['artist__count'] = $a['COUNT(*)']; 
		}
		$sqlRes = db_query( $sql__album, array() );
		if ( $a = db_fetch_array($sqlRes) ) { 
			$this->m_aSettings['album__count'] = $a['COUNT(*)']; 
		}
		$sqlRes = db_query( $sql__song, array() );
		if ( $a = db_fetch_array($sqlRes) ) { 
			$this->m_aSettings['song__count'] = $a['COUNT(*)']; 
		}
		
		db_set_active('default');
	}
		
		


	function okToRun ( $sLockFile )
	{
		if ( $sLockFile == "" ) return true;
		
		$bOkToRun = true;
		if ( file_exists($sLockFile) ) {
			$bOkToRun = false;
			$hFile = fopen( $sLockFile, "r");
			$iTimeStampRead = readTimeStamp( $hFile );
			if ( time() - readTimeStamp( $hFile ) > 3600 )	{	// 3600 = 1 hour, 90000 = 25 hours 
				printf ("OK to run since the old instance is hanging or exited without cleaning semaphore\n");
				// Too long time expired since the (apparently) running instance
				// wrote to the timestamp file, that we assume it's hanging or has exited
				// without removing the semaphore file.
				// We delete the file and allow this instance to start.
				// NOTE: We really should find a way to actually kill, the dead process. If it is in fact still running.  !!!!
				$bOkToRun = true;
				fclose ( $hFile);
				unlink( $sLockFile );	// Delete the file on disk.	
				if ( file_exists($sLockFile) ) {
					// This should not happen unless this dir has gotten wrong owner
					logErr('Could not delete semaphore lock file', __file__);
					print( "Error: Could not delete semaphore lock file\n");
				}
			}
			else {
				fclose ( $hFile);
			}
		}
		return $bOkToRun;
	}


	// Not used currently 
	function outsideExecutionTime()
	{
		return false;
		//00:00:00 < now && now < 23:00:00
		if (date("H:i:s", '82800') < date("H:i:s") && date("H:i:s") < date("H:i:s",
			'79200') || (isset($_SERVER['SERVER_NAME']) && ($_SERVER['SERVER_NAME'] ==
			'localhost'))) {
			return false;
		} else {
			return true;
		}
	}

	// ----------------------------
	// --- PRIVATE: Member data ---	
	// ----------------------------
	private		$m_sLockFile;
	private		$m_aSettings	= array();
}




