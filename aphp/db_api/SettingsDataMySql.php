<?php

require_once ("db_api/SimpleTableDataMySql.php");

/**
Simple key/value table, that we can use for any kind of various data we need to save. 
For example we can use for cronjobs that need to save data across each invocation.
You can find a full example simple example in aphp/adhoc_test/settings_test.php

Here is the interresting parts of the example:
\code
$fac = new MusicDatabaseFactory();
$set = $fac->createDbInterface('SettingsData');

$set->setValue('ArtistCron:limit', 2000 );

printf( "AS STRING => ArtistCron:limit: '%s'\n", $set->getValueStr('ArtistCron:limit') );
printf( "AS INT    => ArtistCron:limit:  %d\n", $set->getValueInt('ArtistCron:limit') );
printf( "AS FLOAT  => ArtistCron:limit:  %f\n", $set->getValueFloat('ArtistCron:limit') );
\endcode

*/
class SettingsDataMySql extends SimpleTableDataMySql
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $dbPDO = null )
    {
        parent::__construct( 
        'settings'
        , array(  'settings_name', 'settings_value' 
                )
        , $dbPDO );
    }
	// --------------------------
	// --- Set data functions ---
	// --------------------------
	/** Set/create value of settings_name. 
	\return ID (settings_id) of the setting. */
    public function setValue ($settings_name, $settings_value)
    {
		$id = $this->nameToIDSimple($settings_name);
		if ($id == 0) {
			$id = $this->newItemFromName($settings_name);
		}
		$aData = array(
			  'settings_id'		=> $id
			, 'settings_name'	=> $settings_name
			, 'settings_value' 	=> "{$settings_value}"
			);
		$this->updateBaseData($aData);
        return $id;
    }
    
	// --------------------------
	// --- Get data functions ---
	// --------------------------

	/** Get string value of settings_name. */
    public function getValueStr ($settings_name)
    {
        $q = "SELECT settings_value FROM {$this->m_baseTableName} WHERE {$this->m_baseTableName}_name = ?";
        $val = pdoLookupSingleStringQuery($this->m_dbPDO, $q, array($settings_name) ); 
        return $val;
    }

	/** Get int/integer value of settings_name. */
    public function getValueInt ($settings_name)
    {
        $val = $this->getValueStr($settings_name);
        return $val == '' ? (int)0 : (int)$val;
    }
    
	/** Get float value of settings_name. */
    public function getValueFloat ($settings_name)
    {
        $val = $this->getValueStr($settings_name);
        return $val == '' ? (float)0 : (float)$val;
    }
}

?>