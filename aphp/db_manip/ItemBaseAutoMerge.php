<?php
require_once ('utils/string_utils.php');
require_once ('utils/string_special_words.php');
require_once ('utils/auto_merge_utils.php');

/*
--------------------------------------
--- item_base auto merge functions ---
--------------------------------------
*/

// --- Compare functors ---
/** Returns true if item_base_name of \p $aPrimary is a substring of 
item_base_name in \p $aCandidate */
function ibCompare1($aPrimary, $aCandidate)
{
	$bDoMerge = true;
	$pos = strpos($aCandidate['item_base_name_NORMALIZED'], "{$aPrimary['item_base_name_NORMALIZED']} " );
	if ( $pos === false ) $bDoMerge = false;
	else $bDoMerge = $pos == (int)0;
	
	// Special case if they are both exactly the same name (normalized versions)
	if ( $aCandidate['item_base_name_NORMALIZED'] == $aPrimary['item_base_name_NORMALIZED'] ) {
        $bDoMerge = true;
	}
	
	if ( $aPrimary['item_base_name_SPECIAL_WORDS'] != $aCandidate['item_base_name_SPECIAL_WORDS'] ) {
		$bDoMerge = false;
	}
	
	return $bDoMerge;
}

/** Returns true if item_base_soundex of \p $aPrimary is a substring of 
item_base_soundex in \p $aCandidate */
function ibCompare2($aPrimary, $aCandidate)
{
// // 	if ( false !== strpos($aCandidate['item_base_name_SIMPLIFIED'], "rise fall" )) {
// // 		printf("{$aCandidate['item_base_name_SIMPLIFIED']}\n");
// // 	}
	////rise and fall of ziggy stardust and the spiders from mars 	
//	$pos = strpos($aCandidate['item_base_name_SIMPLIFIED'], $aPrimary['item_base_name_SIMPLIFIED'] );
	$pos = strpos($aCandidate['item_base_soundex_SIMPLIFIED'], $aPrimary['item_base_soundex_SIMPLIFIED'] );
	if ( $pos === false ) return false;
	else return $pos == (int)0;
}


function sortGreaterItemBaseName( $aDataLHS, $aDataRHS)
{
	return strlen($aDataLHS['item_base_name_NORMALIZED']) <  strlen($aDataRHS['item_base_name_NORMALIZED']);
}

/** Class for assigning auto merge candidates for one artist. */
class ItemBaseAutoMerge
{
    // --------------------------
    // --- Constructor / init --- 
    // --------------------------
    public function __construct( $iIsPrimaryThreshold, $iMergeCandidateThreshold )
    {
		$this->isPrimaryThresholdSet($iIsPrimaryThreshold);
		$this->mergeCandidateThresholdSet($iMergeCandidateThreshold);
    }

    
    public function isPrimary($aData)
	{
		$bIsPrimary = ((int)$aData['item_master'] == 1) || ((int)$aData['is_primary'] == 1);
		$aArtistWords = explodeTrim(' ', $aData['artist_name_SIMPLE'] );
		if ( isAnyInString( $aData['item_base_name_LOWER'], $aArtistWords) ) $bIsPrimary = false;
        if ( strlen($aData['item_base_name_LOWER']) < 3 ) $bIsPrimary = false;
        if ( $aData['item_base_name_LOWER'] == 'live' ) $bIsPrimary = false;
		
		return $bIsPrimary;
	}

	public function isMergeCandidate($aData)
	{
		$bIsCandidate = (int)$aData['nCount'] < (int)$this->mergeCandidateThreshold();
		return $bIsCandidate;
	}


	/** Assign primary flag to item_base items that has more than @a iIsPrimaryThreshold 
	prices. */
	public function assignPrimaries( &$aItemBases )
	{
		foreach ( $aItemBases as &$aData ) {
            $aData['is_primary'] = (int)0;
			if ( (int)$aData['nCount'] > $this->isPrimaryThreshold() ) {
				$aData['is_primary'] = (int)1;
				//printf("Primary[%d]: '%s'\n", (int)$aData['nCount'], $aData['item_base_name'] );
			}
		}
	}


	/** Split an array of item_base'es into primaries and candidates. */
	public function getPrimariesAndCandidates($aItemBases, &$aPrimaries, &$aCandidates, &$aRest)
	{
		$aPrimaries = array();
		$aCandidates = array();
		foreach ( $aItemBases as $aData ) {
			$aData['item_base_name_LOWER'] = mb_strtolower( $aData['item_base_name'], 'UTF-8' );
			$aData['item_base_name_NORMALIZED'] = normalizeItemBaseName($aData['item_base_name_LOWER']);
			$aData['item_base_name_SIMPLIFIED'] = simplifyItemBaseName($aData['item_base_name_NORMALIZED']);
			$aData['item_base_soundex_SIMPLIFIED'] = calcSoundex($aData['item_base_name_SIMPLIFIED']);
			$aData['item_base_name_SPECIAL_WORDS'] = findItemBaseSpecialWords($aData['item_base_name_LOWER']);
			
			$aData['artist_name_LOWER'] = mb_strtolower( $aData['artist_name'], 'UTF-8' );
			$aData['artist_name_SIMPLE'] = simplifyArtistName($aData['artist_name_LOWER']);
// // 			if ( $aData['item_base_soundex'] == '' ) {
// // 				$aData['item_base_soundex'] = calcSoundex( $aData['item_base_name_LOWER'] );
// // 			}
			if ( $this->isPrimary($aData) ) {
				$aData['aToMerge'] = array();
				$aPrimaries[] = $aData;
				//printf("Primary[%d]: '%s'\n", (int)$aData['nCount'], $aData['item_base_name'] );
			}
			else if ( $this->isMergeCandidate($aData) ) {
				$aCandidates[] = $aData;
			}
			else $aRest[] = $aData;
		}
		usort($aPrimaries, 'sortGreaterItemBaseName');
	}
	
	/////////////////////////// 
	
	/** Given a list of primaries and a single candidate we try to find a primary 
	to which the candidate belongs/can be merged with. 
	\return true if the candidate was assigned otherwise false. */
	public function assignMergeCandidate( &$aPrimaries, $aCandidate, $compareFn )
	{
		$bAssigned = false; 
		foreach ( $aPrimaries as &$aPrimary ) {
			if ( $compareFn($aPrimary, $aCandidate) ) {
				$aPrimary['aToMerge'][] = $aCandidate;
				$bAssigned = true;
				break;
			}
		}
		return $bAssigned;
	}

	
	/** Given a list of primaries and a list of candidates we try to find a primary 
	to which each candidate belongs/can be merged with. 
	This function only does a single 'pass' - ie. only uses a single compare function.
	\see assignMergeCandidates for a function that takes several compare functions.
	\return List of candidates that could not be assigned. */
	public function assignMergeCandidatesSinglePass( &$aPrimaries, $aCandidates, $compareFn )
	{
		$aNotAssigned = array(); 
		foreach ( $aCandidates as $aCandidate ) {
			if ( !$this->assignMergeCandidate( $aPrimaries, $aCandidate, $compareFn ))  {
				$aNotAssigned[] = $aCandidate;
			}
		}
		return $aNotAssigned;
	}

	/** Given a list of primaries and a list of candidates we try to find a primary 
	to which each candidate belongs/can be merged with. 
	this function expects a comma separated lst of compare operators to be applied. 
	Each pass/iteration of all primaries will use the next compare function adding to the list 
	of items to merge.
	\return List of candidates that could not be assigned. */
	public function assignMergeCandidates( &$aPrimaries, $aCandidates, $compareFunctions )
	{
		$aNotAssigned = array(); 
		$aCompareFunctions = explode( ',', $compareFunctions );
		foreach ( $aCompareFunctions as $compareFn ) {
			$compareFn = trim($compareFn);
			$aNotAssigned = $this->assignMergeCandidatesSinglePass( $aPrimaries, $aCandidates, $compareFn );
			$aCandidates = $aNotAssigned;
		}
		return $aNotAssigned;
	}
	
	//////////////////////////
	
	public function isPrimaryThreshold			() 				{ return (int)$this->m_iIsPrimaryThreshold; }
	public function isPrimaryThresholdSet		($iNumPrices) 	{ $this->m_iIsPrimaryThreshold = (int)$iNumPrices; }
	public function mergeCandidateThreshold		() 				{ return (int)$this->m_iMergeCandidateThreshold; }
	public function mergeCandidateThresholdSet	($iNumPrices) 	{ $this->m_iMergeCandidateThreshold = (int)$iNumPrices; }

	
	
	private		$m_iIsPrimaryThreshold 		= 10;
	private		$m_iMergeCandidateThreshold = 3;

}



?>