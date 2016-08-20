<?php
require_once ('utils/string_utils.php');


function simplifyArtistName($artistName)
{
	static $aRemove = array(
		  'the', 'and', '&'
	);
	return stringRemoveAll($aRemove, $artistName);
}


function findItemBaseSpecialWords($itemBaseName)
{
	static $aSpecialWords = array(
		  'live', 'anniversary', 'tour' 
	);
	return findAllInString($itemBaseName, $aSpecialWords);
}

function normalizeItemBaseName($itemBaseName)
{
	static $aSearchReplace = array(
		  'and' => '&'
		, 'og' => '&'
		, 'und' => '&'
	);
	return stringReplaceAll($aSearchReplace, $itemBaseName);
}

function simplifyItemBaseName($itemBaseName)
{
	if ( strlen($itemBaseName) < 30 ) return $itemBaseName;
	static $aSearchReplace = array(
		  '&' => ' '
		, 'the ' => ' '
		, 'in ' => ' '
		, '*' => ''
		, "'" => ''
	);
	$itemBaseName = trim(stringReplaceAll($aSearchReplace, $itemBaseName));
	return $itemBaseName;
}

// -----------------------------------
// --- Formatting helper functions ---
// -----------------------------------

/** To short list string representation */
function ibamSimpleString($aItemBases, $sHeadLine)
{
	$s = $sHeadLine . "\n";
	foreach ( $aItemBases as $aData ) {
		$s .= "[{$aData['item_base_name']}] ({$aData['nCount']}) : ";
		if (array_key_exists('aToMerge', $aData ) ) {
			$i = 0;
			foreach ($aData['aToMerge'] as $aMergeItem ) {
				$i++;
				if ( $i > 1 ) $s .= ", ";
				$s .= "{$aMergeItem['item_base_name']} ({$aMergeItem['nCount']})";
			}
		}
		$s .= "\n";
	}
	$s .= "\n";
	return $s;
}


function ibamArrayToHtmlTable($aItemBases, $sHeadLine)
{
	if ( 0 == count($aItemBases) ) return "<h3>NOTE: Table empty</h3>";
	$s = "<h1>$sHeadLine</h1>\n";
	$s .= "<table border=1 >\n";
	$s .= "<tr><th>item_master</th><th>Name</th><th># prices</th>";
	if (array_key_exists('aToMerge', $aItemBases[0] ) ) {
		$s .= "<th>Names merged</th><th># merged</th>";
	}
	$s .= "</tr>\n";
	
	// item_base_name_SIMPLIFIED, item_base_name
	
	$iTotalMerged = 0;
	foreach ( $aItemBases as $aData ) {
		$s .= "<tr>\n";
        $s .= "<th>\n{$aData['item_master']}\n</th>\n";
        $s .= "<th>\n{$aData['item_base_name']}\n</th>\n";
		$s .= "<td>\n{$aData['nCount']}\n</td>\n";
		if (array_key_exists('aToMerge', $aData ) ) {
			$i = 0;
			$s .= "<td>\n";
			foreach ($aData['aToMerge'] as $aMergeItem ) {
				$i++;
				if ( $i > 1 ) $s .= ", ";
				if ( $i % 2 == 0 ) $s .= "<b>";
				$s .= "{$aMergeItem['item_base_name']} ({$aMergeItem['nCount']})";
				if ( $i % 2 == 0 ) $s .= "</b>";
			}
			$iCountToMerge = count($aData['aToMerge']) ;
			$iTotalMerged += $iCountToMerge;
			$s .= "<td>{$iCountToMerge}</td>\n";
			$s .= "</tr>\n";
		}
		$s .= "\n</td>\n";
	}
	$iTotalItemBases = count($aItemBases);
	$s .= "<tr><th>Titles count</th><td><b>{$iTotalItemBases}</b></td>\n";
	if (array_key_exists('aToMerge', $aItemBases[0] ) ) {
		$s .= "<td></td><th></th><td><b>{$iTotalMerged}</b></td>";
	}
	$s .= "</tr>\n";

	$s .= "\n</table>\n";
	return $s;
}

?>