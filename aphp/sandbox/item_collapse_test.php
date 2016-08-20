<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('utils/string_utils.php');
require_once ('db_api/db_string_utils.php');
require_once ('db_api/ItemDataMySql.php');
require_once ('db_api/ItemBaseCorrectionDataMySql.php');


$g_argv = $GLOBALS['argv'];
$namesFile = $g_argv[1];

$dbItemBaseCorrection = new ItemBaseCorrectionDataMySql;

$sFile = file_get_contents($namesFile);
$aNames = explode ("\n", $sFile );

////printf("Hello\n$sFile\n");

foreach ( $aNames as $sName ) {
    $sName = trim($sName);
    if ( $sName == '' ) continue;
    
    $sSimplified = simplifyWhiteSpace($sName);
    
//    $soundex = soundex($sName);
//    $metaphone = metaphone($sName);

    $sSoundex;
    $sMetaphone;
// //     $simplifiedName = cleanItemNameLevel1( $sName );
// //     $simplifiedName = cleanItemNameLevel2( $simplifiedName );
    $simplifiedName = cleanItemNameFull($sName, $dbItemBaseCorrection );
////    $simplifiedName = cleanItemNameLevel3( $simplifiedName );
    $similarityPct = 0;
//    similar_text ('Black Celebration' , $sName , $similarityPct );


    $sSoundex   = calcSoundex($simplifiedName);
    $sMetaphone = calcMetaphone($simplifiedName);
    printf( "%s : %s ; %s => %s\n", $simplifiedName, $sSoundex, $sMetaphone, $sName );

//    printf( "'%s' => '%s'\n", $sSimplified, $sName );
//    printf( "%s : %s => %s\n", $simplifiedName, $sSoundex, $sName );
//    printf( "%s, %s, %f : %s => %s\n", $soundex, $metaphone, $similarityPct, $simplifiedName, $sName );
}

//printf( "%s : master\n", soundex('master' ) );

// printf( "%s : Black\n", soundex('Black' ) );
// printf( "%s : Celebration\n", soundex('Celebration' ) );
// printf( "%s : Celebratio\n", soundex('Celebratio' ) );
// printf( "%s : black [CD+DVD]\n", soundex('black [CD+DVD]' ) );
$s = 'Dark Words, Gentle Sounds';
printf( "%s => %s\n", $s, calcSoundex($s)  );

// mb_regex_encoding('UTF-8');
// mb_internal_encoding("UTF-8");
// $v = mb_split('、',"日、に、本、ほん、語、ご");

//var_dump( mb_str_split("abcæø") );
//var_dump( mbStringToArray("abcæø") );

//printf( "is digit: %s\n", is_digit(9) );



$ib = new ItemDataMySql;

$artist_id = 3;
$aCandidates = $ib->getItemsForArtist($artist_id, 1 );

// $aItem = findItemFromSoundex( $aCandidates, 'item_base', 'Dark Words Gentle Sounds' );
// $bestMatchSimilarityFactor;
// $aItem = findBestMatchItemFromSoundex( $aCandidates, 'item_base', 'Dark Words Gentle Souns', $bestMatchSimilarityFactor );
// printf("bestMatchSimilarityFactor: $bestMatchSimilarityFactor\n");

// var_dump($aItem);
//$item_base_id = findIdFromSoundex( $aCandidates, 'item_base', 'Dark Words Gentle Sounds' );
$item_base_id = fuzzyFindIdFromSoundex( $aCandidates, 'item_base', 'Dark Words Gele Sounds', 0.84 );



printf("item_base_id: $item_base_id\n");

//var_dump($aItems);


?>
