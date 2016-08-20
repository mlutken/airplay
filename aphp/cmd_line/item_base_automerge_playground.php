<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('db_api/ArtistDataMySql.php');
require_once ('db_manip/ItemBaseAutoMerge.php');
require_once ('utils/string_utils.php');


$g_iIsPrimaryThreshold = 10;
$g_isMergeCandidateThreshold = 3;

//////////////
function pageMainContentHtml($aItemBases)
{
	global $g_iIsPrimaryThreshold, $g_isMergeCandidateThreshold;

	$sHtml = "";
	$ibam = new ItemBaseAutoMerge($g_iIsPrimaryThreshold, $g_isMergeCandidateThreshold);

	$ibam->assignPrimaries($aItemBases);

	$aPrimaries = array();
	$aCandidates = array();
	$aRest = array();
	$ibam->getPrimariesAndCandidates($aItemBases, $aPrimaries, $aCandidates, $aRest );
	$aNotAssigned = $ibam->assignMergeCandidates($aPrimaries, $aCandidates, "ibCompare1,ibCompare2" );
	
	usort($aNotAssigned, 'sortGreaterItemBaseName');

//	foreach ()
	
	$sHtml .= ibamArrayToHtmlTable($aPrimaries, "Primaries" );
	$sHtml .= ibamArrayToHtmlTable($aNotAssigned, "Not Assigned" );
	$sHtml .= ibamArrayToHtmlTable($aRest, "Not Attempted" );
	$sHtmlPage = wrapStringAsHtmlPage($sHtml, "" );
	return $sHtmlPage;
}
	//$sHtml .= json_encode( $aPrimaries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );



////////////////
/**
------------------------------------------
--- item_base_automerge_playground.php ---
------------------------------------------
Simple dev/test program to try out different ways of doing auto merge of item_base names 
.
*/

global $argv, $g_MySqlPDO;

$dataDir = "test_data/auto_merge";

$filePath = $argv[1];
$item_type = 1;

// -------------
// --- Setup ---
// -------------
// -------------
// --- Do it ---
// -------------
printf("$filePath: {$filePath}\n");
$aItemBases = readFileDbFile( $filePath );
$sHtml = pageMainContentHtml($aItemBases);



file_put_contents ( "{$filePath}.html",  $sHtml);


?>