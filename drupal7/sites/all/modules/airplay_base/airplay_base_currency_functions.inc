<?php 
// $Id$




class ExchangeRatesUpdater 
{

	public 	function	__construct( )
	{
		$this->m_sLockFile	= "ExchangeRatesUpdater_alock";
		
		
	}




	function cron()
	{
		if ( ! $this->okToRun($this->m_sLockFile) ) {
			drush_print( "Semaphore '{$this->m_sLockFile}' locked\n" );
			return;
		}
		
		$hFile = fopen( $this->m_sLockFile, "w+");	
		writeTimeStamp($hFile);
		printf("Running exchange rates update cron ... \n");
		$this->updateRates();
		
		unlink( $this->m_sLockFile );	// Delete the file on disk.	

	}


	
	function updateRates ( ) 
	{
		// Table currency
		$tbl_currency = new AirplayTable( 'currency' );
		$tbl_currency->optionSet ( 'lookup_where', "WHERE currency_id = :currency_id" );
		$tbl_currency->optionSet ( 'lookup_fields', array( "currency_id" ) );
// 		$tbl_currency->optionSet ( 'assign_to_names', array("currency_id", 'currency_name', 'to_euro', 'from_euro' ) );
		$tbl_currency->optionSet ( 'assign_to_names', array("currency_id", 'currency_name') );
		
		// Table currency_to_euro
		$tbl_currency_to_euro = new AirplayTable( 'currency_to_euro' );
		$tbl_currency_to_euro->optionSet ( 'lookup_where', "WHERE currency_id = :currency_id" );
		$tbl_currency_to_euro->optionSet ( 'lookup_fields', array( "currency_id" ) );
		
		$iCountRecords = (int)$tbl_currency->countRecords();
		
		$aTitles = array();
		for ( $i = 1; $i <= $iCountRecords; $i++ ) {
			$aTitle = array( 'do_update' => array  ( "currency" => array( 'to_euro', 'from_euro' ) ) );
			$aTitle["currency_id"] = $i;
			$tbl_currency->lookupTitleId ( $aTitle );
 			//$aTitle['to_euro'] = ap_convert_price( 1, $aTitle['currency_name'], 'EUR' );// Seems not to work for all currencies
 			$aTitle['from_euro'] = ap_convert_price( 1, 'EUR', $aTitle['currency_name'] );
 			$aTitle['to_euro'] = 1.0 / $aTitle['from_euro'];
            printf("({$i}) %s:  to_euro = %f , from_euro = %f\n", $aTitle['currency_name'], $aTitle['to_euro'], $aTitle['from_euro'] );
			$aTitles[] = $aTitle;
		}
		foreach ( $aTitles as $aTitle ) {
 			$tbl_currency->updateTitleByIndex( $aTitle );
 		}
		
		foreach ( $aTitles as $aTitle ) {
 			$aTitle['do_update'] = array  ( "currency_to_euro" => array( 'currency_id', 'currency_name', 'to_euro' ) );
 			$aTitle['do_insert'] = array ( 'currency_to_euro' );
			$tbl_currency_to_euro->insertOrUpdateRecord ( $aTitle );
 		}
		
		//print_r($aTitles);
		
// 		2010-03-01 16:42:20
		$now = date("Y-m-d G:i:s");
		ap_write_setting ( 'exchange_rates_cron__last_upd_time', $now );
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
}

