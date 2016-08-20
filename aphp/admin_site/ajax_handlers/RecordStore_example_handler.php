<?php
require_once ( __DIR__ . '/../../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('utils/general_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('admin_site/classes/SimpleTableUI.php');

$name = 'RecordStore';
$fac = new MusicDatabaseFactory();
$db = $fac->createDbInterface("{$name}Data");
$nameViewMode       = "{$name}_viewMode";
$nameSearchString   = "{$name}_searchString";


try
{
    if ( $_POST['viewMode'] != ''       ) $_SESSION[$nameViewMode]      = $_POST['viewMode'];
    if ( $_POST['searchString'] != ''   ) $_SESSION[$nameSearchString]  = $_POST['searchString'];
    
    $viewMode       = $_SESSION[$nameViewMode];
    $searchString   = $_SESSION[$nameSearchString];
    
    if ( $viewMode == '' ) $viewMode = 'listAll';

    $sj = pretty_json (json_encode($_POST) );
    $sj .= pretty_json (json_encode($_GET) );
    $sj .= "\nviewMode: '$viewMode', searchString: '$searchString'\n";
    file_put_contents("/tmp/_ajax_dbg.txt", $sj );
    
	//Getting records (listAction)
	if($_GET['action'] == 'list')
	{
		//Return result to jTable
		$jTableResult = array();
		$jTableResult['Result'] = "OK";
        $rows = array();
        if ( 'listAll' == $viewMode ) {
            $start  = (int)($_GET['jtStartIndex']);
            $count  = (int)($_GET['jtPageSize']);
            $rows   = $db->getBaseDataRows($start, $count);
        }
        else if ( 'incrSearch' == $viewMode  ) {
            $minLen = 3;
            if ( startsWith ( $searchString, 'the', false ) ) $minLen = 7;
            if ( strlen($searchString) >= $minLen ) {
                $rows = $db->lookupSimilarBaseData($searchString);
            }
        }
		$jTableResult['Records'] = $rows;
		print json_encode($jTableResult);
	}
	//Creating a new record (createAction)
	else if($_GET["action"] == "create")
	{
        $aData = $_POST;
        $id = 0;
        $jTableResult = array();
        $jTableResult['Result'] = 'ERROR';
        $name = trim($_POST["record_store_name"]);
        if ( $name == '' ) $jTableResult['Message'] = 'Empty name given';
        else if ( $db->nameToID($name) != 0 ) $jTableResult['Message'] = 'Record already exists';
        else {
            // Create the records and call updateBaseData
            $id = $db->createNew($name);
            if ( $id == 0 ) $jTableResult['Message'] = 'Error creating new record';
            else {
                $aData["record_store_id"] = $id;
                $result = $db->updateBaseData($aData);
                if ( $result < 0 ) $jTableResult['Message'] = 'Error updating fields in new record';
                else $jTableResult['Result'] = 'OK';
            }
        }
        $row = $db->getBaseData($id);
        $jTableResult['Record'] = $row;
		print json_encode($jTableResult);
	}
	//Updating a record (updateAction)
	else if($_GET["action"] == "update")
	{
        $aData = $_POST;
        $jTableResult = array();
        $jTableResult['Result'] = 'ERROR';
        $id = $aData["record_store_id"];
        if ( $id == 0 ) $jTableResult['Message'] = 'Error updating due to id = 0';
        else {
            $result = $db->updateBaseData($aData);
            if ( $result < 0 ) $jTableResult['Message'] = 'Error updating fields in new record';
            else $jTableResult['Result'] = 'OK';
        }
		//Return result to jTable
		$jTableResult['Result'] = "OK";
		print json_encode($jTableResult);
	}
	//Deleting a record (deleteAction)
	else if($_GET["action"] == "delete")
	{
        $aData = $_POST;
        $jTableResult = array();
        $jTableResult['Result'] = 'ERROR';
        $id = $aData["record_store_id"];
        if ( $id == 0 ) $jTableResult['Message'] = 'Error deleting due to id = 0';
        else {
            $result = $db->erase($id);
            if ( $result == 0 ) $jTableResult['Message'] = 'Error deleting record';
            else $jTableResult['Result'] = 'OK';
        }
        //Return result to jTable
        $jTableResult['Result'] = "OK";
        print json_encode($jTableResult);
	}

// // 	//Close database connection
// // 	mysql_close($con);

}
catch(Exception $ex)
{
    //Return error message
	$jTableResult = array();
	$jTableResult['Result'] = "ERROR";
	$jTableResult['Message'] = $ex->getMessage();
	print json_encode($jTableResult);
}
	
?>