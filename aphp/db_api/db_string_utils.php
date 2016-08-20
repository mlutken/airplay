<?php
require_once ('utils/string_utils.php');


function dbValueIsEmpty( $val, $dbFieldName = '' )
{
    
    if  ( '' == $val ) return true;
    if ( is_numeric($val) ) {
        return $val == 0;    
    }
    else if ( '0000-00-00' == $val && endsWith ( $dbFieldName, '_date' ) ){
        return true;
    }
    return false;
}

/** Filter empty values from an associative array (map). */
function dbFilterEmptyAssoc( $aData )
{
    $a = array();
    foreach( $aData as $k => $v ) {
        if ( !dbValueIsEmpty($v, $k ) ) {
            $a[$k] = $v;
        }
    }
    return $a;
}

/** Filter empty values from a plain array of associative arrays (array of maps). */
function dbFilterEmptyAssocArrays( $aDataRows )
{
    $aDR = array();
    foreach( $aDataRows as $aData ) {
        $aDR[] = dbFilterEmptyAssoc($aData);
    }
    return $aDR;
}


/**
If the artist name is written 'Surname, FirstName'. Like eg. (Turner, Tina), 
we try reversing the name to 'Tina Turner'
\todo ML:TODO: Describe/find out which types of artist names where we need the '/' split stuff this 
function also does. I suspect it is for classical artist we might need this.
\sa DbInserter_artist_data
*/
function reverseArtistNameWithComma( $sArtistNameOrig )
{
    $sArtistNameReversed = $sArtistNameOrig;
    $a = explode( ',', $sArtistNameOrig );
    if ( count($a) == 2 ) {
        $first  = trim ( $a[1] );
        $last   = trim ( $a[0] );
        $a = explode( '/', $first );
        if ( count($a) == 1 ) {
            $sArtistNameReversed = $first . " " . $last;    
        }
        else {
            $sArtistNameReversed = trim( $a[0] ) . " " . $last;
            for ( $i = 1 ; $i < count($a); $i++ ) {
                $sArtistNameReversed .= " / " . trim( $a[$i] );
            }
        }
    }
    return $sArtistNameReversed; 
}



/**
If the artist name is written 'Surname FirstName'. Like eg. (Turner Tina), 
we try reversing the name to 'Tina Turner'. This function reverses a name that 
consists of excactly 2 words, whereas tha reverseArtistNameWithComma only does so
if the name has excactly two words seperated by a comma. 
\note So far the only site we have seen writin e.g. 'Michael Jackson' as 'Jackson Michael' 
without a comma is CDON(DK). All other use the more normal reversed form 'Jackson, Michael' - 
i.e. with a comma.
So to try this reverse should be a last attemt at finding the artist before actually just creating 
him as a new when inserting data.
\sa DbInserter_artist_data
*/
function reverseArtistName( $sArtistNameOrig )
{
    $sArtistNameReversed = $sArtistNameOrig;
    $a = explode( ' ', $sArtistNameOrig );
    if ( count($a) == 2 ) {
        $first  = trim ( $a[1] );
        $last   = trim ( $a[0] );
        $a = explode( '/', $first );
        $sArtistNameReversed = $first . " " . $last;    
    }
    return $sArtistNameReversed; 
}



// ---------------------------------
// --- Clean item name functions ---
// ---------------------------------



/** Remove possible leading tracknumber like ' 03. - Name of track' 
' 03. Name of track', ' 03 - Name of track', but NOT ' 03 Name of track'
since the last form could be actual item name which begins with number.

To get a suitable array for this function you can do:
$aItemName = utf8StringToArray( simplifyWhiteSpace($sItemName) );
\param $aItemName The item name as an array of chars. 
\return
*/
function cleanTrackNumbering ( $aItemName )
{
    $iCount = count( $aItemName ); 
    if ( $iCount < 5 ) return 0;
    if ( !is_digit($aItemName[0]) || !is_digit($aItemName[1]) ) return 0;
    
    $i = 0;
    $bIsTracNumber = false;
    if ( $aItemName[2] == '.' && $aItemName[3] == ' ' ) $i = 4;
    if ( $aItemName[2] == '.' && $aItemName[3] == '-' ) $i = 4;
    if ( $aItemName[2] == ' ' && $aItemName[3] == '-' ) $i = 4;
    
    while ( $aItemName[$i] == ' ' || $aItemName[$i] == '-') $i++;
    return $i;
}


/** This function cleans up the main parts of an item name (typically album,song). 
The following things are done:
 - It tries to remove prepended tracknumbering ( see helper function cleanTrackNumbering ).
 - Everything inside parentheses and including the parentheses ( ... ) is removed
 - Everything inside brackets and including the brackes [ ... ] is removed
 - Chars: , ; : / + - are replaced by a space
 - Chars: " ' . ! ^ are removed
 \todo Perhaps we should remove rest of string if we encounter this sequence:
       ' - ' (I.e. SPACE DASH SPACE)
*/
function cleanItemNameLevel1( $sItemName )
{
    $sItemNamePreprocessed = simplifyWhiteSpace($sItemName);
    
    if ( strlen($sItemNamePreprocessed) <= 3 ) return $sItemName;
    
    $s = "";
    $aItemName = utf8StringToArray($sItemNamePreprocessed);
    $iCount = count( $aItemName ); 
    $iStartPos = cleanTrackNumbering( $aItemName );
//    var_dump($aItemName);
    $aStateStack = array(0);        // 0: normal, 1: Skip chars
    for(  $i = $iStartPos; $i < $iCount; $i++ ) {
        
        $c = $aItemName[$i];
        $chAdd = '';
        switch ( $c ) {
/*          case '-' :
                $aStateStack[] = 1;
                break;*/
            case '(' :
                $aStateStack[] = 1;
                break;
            case ')' :
                array_pop($aStateStack); 
                break;
            case '[' :
                $aStateStack[] = 1;
                break;
            case ']' :
                array_pop($aStateStack); 
                break;
            case '+' :
            case ':' :
            case ';' :
            case ',' :
            case '/' :
            case '-' :
                $chAdd = ' ';
                break;
            case '"' :
            case '.' :
            case "'" :
            case '!' :
            case '^' :
                $chAdd = ''; 
                break;
/*          case '/' :
                $chAdd = ' / '; 
                break;*/
            default:
                $chAdd = $c;
        }
        $iStackLen = count($aStateStack);
        if ( $iStackLen >= 1 ) {
            if ( $aStateStack[$iStackLen -1] == 0 ) {
                $s .= $chAdd;
            }
        }
    }   
    return simplifyWhiteSpace($s); // Simplify spaces
}



/** This functions tries to clear up an item name by goig further than 
the '1'-function. Note that you must have called the cleanItemNameLevel1 
function prior to calling this. 
The following things are done:
  - Remove words like: 'dvd', 'cd', 'vinyl', 'blu-ray'
  - Remove strings like: 'extended edition', 'gold edition', ...
  - Remove any string that resembles 'remastered' by taking the soundex 
    and see if it matches soundex('remastered') == R523.
\return String with special song/album words removed.
*/
function cleanItemNameLevel2( $sItemName )
{
    static $aRemoveWords = array  (
          'dvd', 'cd', 'vinyl', 'casette', 'blu-ray'
     );

    static $aRemoveStrings = array  (
          'extended edition', 'extended version' 
        , 'gold edition', 'special edition'
        , 'special version'
    );

    // --------------------
    // --- Remove words ---
    // --------------------
    $aWords         = explode(' ', $sItemName );
    $aWordsLower    = explode(' ', mb_strtolower($sItemName, 'UTF-8') );
    
    $sName = '';
    $sSoundex = '';
    $iCountWords = count($aWords);
    for ( $i = 0; $i < $iCountWords; $i++ ) {
        $sWordLower = $aWordsLower[$i];
        if ( in_array( $sWordLower, $aRemoveWords)) continue;
        $sndx = soundex($aWords[$i]);
        
        if ( $sndx == 'R523' )  continue;   // R523: Is the soundex of (remast, remaster, remastered, ... etc.)
        if ( $i > 0 ) { 
            $sName      .= ' ';
        }
        $sName      .= $aWords[$i];
    }
    // ----------------------
    // --- Remove strings ---
    // ----------------------
    foreach ( $aRemoveStrings as $sRemove ) {
        $name = str_ireplace($sRemove, '', $sName);
        if ( strlen($name) > 3 ) $sName = $name;
    }
    return simplifyWhiteSpace($sName);
}



/** Cleans item name as much as possible calling 
cleanItemNameLevel1, cleanItemNameLevel2 in succession. 
If \a $dbItemBaseCorrection is present we use that to lookup the final cleaned 
item name for a correction. For example 'Kim Larsen' album '231045-0637' will 
get cleaned to '231045 0637', since we replace '-'s with space. So the 
\a $dbItemBaseCorrection class can fix this back to '231045-0637'.

\param $dbItemBaseCorrection Should be a ItemBaseCorrection instance (or any class 
        that implements correctionNameToBaseName function). In case it is null we don't use it.
\param $artist_id Artist ID to use when doing the correction lookup. If zero we match all names in 
        the item_base_correction table in DB that has this $itemName and not just the one for the 
        given artist. In most cases this is probably not a problem. But if you have the artist_id 
        you shoul supply it.
\deprecated When new filedb is implemented this function should not be used any more. See cleanItemName
*/
function cleanItemNameFull($itemName, $dbItemBaseCorrection = null, $artist_id = 0 )
{
    $itemName = cleanItemNameLevel1($itemName);
    $itemName = cleanItemNameLevel2($itemName);
    if ( $dbItemBaseCorrection != null ) {
        $itemName = $dbItemBaseCorrection->correctionNameToBaseName ( $artist_id, $itemName );
    }
    
    return $itemName;
}


// // /** Cleans item name as much as possible calling 
// // cleanItemNameLevel1, cleanItemNameLevel2 in succession. 
// // If \a $dbItemBaseCorrection is present we use that to lookup the final cleaned 
// // item name for a correction. For example 'Kim Larsen' album '231045-0637' will 
// // get cleaned to '231045 0637', since we replace '-'s with space. So the 
// // \a $dbItemBaseCorrection class can fix this back to '231045-0637'.
// // 
// // \param $dbItemBaseCorrection Should be a ItemBaseCorrection instance (or any class 
// //         that implements correctionNameToBaseName function). In case it is null we don't use it.
// // \param $artist_id Artist ID to use when doing the correction lookup. If zero we match all names in 
// //         the item_base_correction table in DB that has this $itemName and not just the one for the 
// //         given artist. In most cases this is probably not a problem. But if you have the artist_id 
// //         you shoul supply it.
// // */
// // function cleanItemName( $itemName, $dbItemBaseAliasLookup = null, $artist_name_lower_case = '' )
// // {
// //     $itemName = cleanItemNameLevel1($itemName);
// //     $itemName = cleanItemNameLevel2($itemName);
// //     if ( $dbItemBaseAliasLookup != null ) {
// // 		$alias_name_lower_case 		= mb_strtolower( $itemName, 'UTF-8' );
// // 		$itemName = $dbItemBaseAliasLookup->aliasNameToItemBaseName( $artist_name_lower_case, $alias_name_lower_case, $itemName );
// //     }
// //     return $itemName;
// // }


// TODO: Do we need this and if ye then we should implement it :-) !
function cleanArtistName($artistName)
{
    return $artistName;
}




// --------------------------------------
// --- MySQL Soundex helper functions ---
// --------------------------------------
// TODO: These should be removed when filedb is fully implemented
/** 

*/
function findIdFromSoundex($aCandidates, $baseName, $nameToFind )
{
    $idName         = $baseName . '_id';
    $aItem = findItemFromSoundex( $aCandidates, $baseName, $nameToFind );
    $id = $aItem[$idName];
    if ( $id != '' )    return (int)$id;
    else                return 0;
}

/**
*/
function fuzzyFindIdFromSoundex($aCandidates, $baseName, $nameToFind, $minimumSimilarityFactor )
{
    $bestMatchSimilarityFactor;
    $idName         = $baseName . '_id';
    $aItem = findBestMatchItemFromSoundex( $aCandidates, $baseName, $nameToFind, $bestMatchSimilarityFactor );
////    printf("bestMatchSimilarityFactor: $bestMatchSimilarityFactor > $minimumSimilarityFactor \n");

    if ( $bestMatchSimilarityFactor < $minimumSimilarityFactor ) return 0;
    $id = $aItem[$idName];
    if ( $id != '' )    return (int)$id;
    else                return 0;
}

/**
Function to match an item against a list of candidates using soundex.
This function tries to find an exact soundex match. See XXX for more loose 
matching.
\param $aCandidates A simple array of associative arrays each representing a candidate item.
                    Fx. the ItemDataMySql::getItemsForArtist return in this format.
\param 
*/
function findItemFromSoundex($aCandidates, $baseName, $nameToFind )
{
    $soundexName    = $baseName . '_soundex';
    $nameName       = $baseName . '_name'; 
    
    $soundexToFind = calcSoundex($nameToFind);
    
    $iCount = count($aCandidates);
    for ( $i = 0; $i < $iCount; $i++ ) {
        $aCandidate = $aCandidates[$i];   // This ($aItem) is an associative array representing an item. Fx. item_base 
        $soundexMatch = @$aCandidate[$soundexName];
        if ( $soundexMatch == '' ) $soundexMatch = calcSoundex( $aCandidate[$nameName] );
//         printf("soundexMatch: $soundexToFind == $soundexMatch\n");
        if ( $soundexToFind == $soundexMatch ) break;
    }
    if ( $i < $iCount ) return $aCandidates[$i];
    else                return array();
}



/**
XXX TODO: Fix doc
\param $aCandidates A simple array of associative arrays each representing a condidate item.
                    Fx. the ItemDataMySql::getItemsForArtist return in this format.
\param 
*/
function findBestMatchItemFromSoundex( $aCandidates, $baseName, $nameToFind, &$bestMatchSimilarityFactor )
{
    $soundexName    = $baseName . '_soundex';
    $nameName       = $baseName . '_name'; 
    
    $soundexToFind      = calcSoundex($nameToFind);
    $iLenSoundexToFind  = strlen($soundexToFind);
    if ( $iLenSoundexToFind < 1 )   return 0;  
    
    $bestMatchFac = 1; 
    $iBestMatch = -1;
    $iCount = count($aCandidates);
    for ( $i = 0; $i < $iCount; $i++ ) {
        $aCandidate = $aCandidates[$i];   // This ($aItem) is an associative array representing an item. Fx. item_base 
        $soundexCandidate = @$aCandidate[$soundexName];
        if ( $soundexCandidate == '' ) $soundexCandidate = calcSoundex( $aCandidate[$nameName] );
        $levDistSoundex = levenshtein ( $soundexToFind , $soundexCandidate );
        $fac  = $levDistSoundex   / $iLenSoundexToFind;
        if ( $fac < $bestMatchFac ) {
            $bestMatchFac = $fac;
            $iBestMatch = $i;
        }
// //         if ( $iLenSoundexToFind == 0 ) {
// //             printf("BestMatch Sndx: [$levDistSoundex - $fac]: {$aCandidate[$nameName]} == $nameToFind  => $soundexToFind == $soundexCandidate \n");
// //         }
        if ( $bestMatchFac == 0 )   break;
    }
    $bestMatchSimilarityFactor = 1.0 - $bestMatchFac;
    if ( -1 < $iBestMatch ) return $aCandidates[$iBestMatch];
    else                    return array();
}


// TODO: Remove to here when filedb is fully implemented



// ------------------------------------------------------------
// --- OLD stuff, just in case we need something tlike this ---
// ------------------------------------------------------------

// --- cleanItemNameLevel1 ---

// If artist name is part of item_name then remove it

//    $sItemNamePreprocessed = str_replace( $artist_name, "", $sItemNamePreprocessed ); 


//    $regex = array("/^\s*\d\d\./", "/^\s*\d\d\s+-\s*/", "/^\s*\d\d\.\s+-\s*/" );
//    $replace = array( "","", "", "" );
//    $sItemNamePreprocessed = preg_replace( $regex, $replace, $sItemNamePreprocessed ); 

// function cleanItemNameLevel3($itemName)
// {
// 
//     static $aRemoveStrings = array  (
//         'extended edition', 'gold edition', 'special edition'
//     );
//     
//     foreach ( $aRemoveStrings as $sRemove ) {
//         $name = str_ireplace($sRemove, '', $itemName);
//         if ( strlen($name) > 3 ) $itemName = $name;
//     }
//     return simplifyWhiteSpace($itemName);
// }

?>