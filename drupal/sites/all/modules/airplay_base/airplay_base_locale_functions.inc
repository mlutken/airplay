<?php 
// $Id$

/** Formats a price. */
function ap_convert_price( $fPrice, $sCurrencyFrom, $sCurrencyTo ) 
{
 	static $aRates = array();
 	$sConvertKey = $sCurrencyFrom . $sCurrencyTo;
 	if ( !array_key_exists($sConvertKey, $aRates ) ) {
		$aConv = currency_api_convert( $sCurrencyFrom, $sCurrencyTo, 1 );
		if ( $aConv['status'] == TRUE ) {
			$aRates[$sConvertKey] = $aConv['rate'];
		}
		if ( $aRates[$sConvertKey] == 0 || $aRates[$sConvertKey] == 1 ) {
            $aConv = currency_api_convert( $sCurrencyTo, $sCurrencyFrom, 1 );
            $aRates[$sConvertKey] = 1.0 / $aConv['rate'];
            //printf("Currency convert error: $fPrice, $sCurrencyFrom, $sCurrencyTo\n");
		}
		$rate = $aRates[$sConvertKey];
// 		dfb ( "Get rate for: $sConvertKey => $rate" );
 		
 	}
 	return (double) $fPrice * $aRates[$sConvertKey];
}


/** Formats a price. */
function airplay_format_price( $fPrice, $sCurrency = "" ) 
{
// 	if ( $sCurrency !== 'GBP' ) {
		$fPrice = round( $fPrice*20 ) * 0.05;
// 	}
	return number_format  ( $fPrice , 2 , ',', "."  );
}


/** Formats a price. */
function ap_convert_format_price( $fPrice, $sCurrencyFrom, $sCurrencyTo ) 
{
	$fPrice = ap_convert_price( $fPrice, $sCurrencyFrom, $sCurrencyTo );
	$sPrice = number_format  ( $fPrice , 2 , ',', "."  );
	return $sPrice;	
}


// ------------------------------
// --- User values / settings --- 
// ------------------------------




/// Gets the title year sort direction in use. 
function ap_user_title_name_year_sort() 
{
    global $user;
    // Prefer value specified in URL if any
    $title_name_year_sort = "name_ASC";
    if ( array_key_exists( 'title_name_year_sort', $_GET) ) {
        $title_name_year_sort = $_GET['title_name_year_sort'];
    }
    else if ( array_key_exists( 'title_name_year_sort', $_SESSION) ) {
        $title_name_year_sort = $_SESSION['title_name_year_sort'];
    }
    
    // --- Write value to session --- 
    $_SESSION['title_name_year_sort'] = $title_name_year_sort;

    return $title_name_year_sort;
	
}


/** Gets the 'search_for' currently in use. Eg.: 'artist', 'album' or 'song' */
function ap_user_search_for() 
{
    global $user;
    // Prefer value specified in URL if any
    $search_for = "artist";
    if ( array_key_exists( 'search_for', $_GET) ) {
        $search_for = $_GET['search_for'];
    }
    else if ( array_key_exists( 'search_for', $_SESSION) ) {
        $search_for = $_SESSION['search_for'];
    }
    
    // --- Write value to session --- 
    $_SESSION['search_for'] = $search_for;

    return $search_for;
}

/// Gets the currency currently in use. 
function ap_user_locale_currency( ) 
{
    global $user;
    // Prefer value specified in URL if any
    $currency = "DKK";
    if ( array_key_exists( 'currency', $_GET) ) {
        $currency = $_GET['currency'];
    }
    else if ( array_key_exists( 'currency', $_SESSION) ) {
        $currency = $_SESSION['currency'];
    }
    
    // --- Write value to session --- 
    $_SESSION['currency'] = $currency;

    return $currency;
}


/// Gets the media format in use.
function ap_user_media_format( ) 
{
    global $user;
    // Prefer value specified in URL if any
    $media_format = "ALL";
    if ( array_key_exists( 'media_format', $_GET) ) {
        $media_format = $_GET['media_format'];
    }
    else if ( array_key_exists( 'media_format', $_SESSION) ) {
        $media_format = $_SESSION['media_format'];
    }
    
    // --- Write value to session --- 
    $_SESSION['media_format'] = $media_format;

    return $media_format;
}

function ap_site_domain()
{
    global $conf;
    $language_default = $conf['language_default'];
    return $language_default->domain;
} 


function ap_site_top_domain($fullDomain_s="")
{
    
    if ( $fullDomain_s == "" ) {
        $fullDomain_s = ap_site_domain();
    }
    $fullDomain_s = str_replace(".localhost", "", $fullDomain_s );
    $parts_a = explode (".", $fullDomain_s );
    $topDomain = $parts_a[ count($parts_a)-1 ];

    return $topDomain;
} 


/** Convert a site (top) domain like (dk,se,no,uk,com) 
to a language code (en,da,se,no )*/
function domainToLang ($domain )
{
    static $domainsToLangs_a = array(
          'com'=> 'en'
        , 'de' => 'de'
        , 'dk' => 'da'
        , 'no' => 'no'
        , 'se' => 'se'
        , 'uk' => 'en'
    );
    return $domainsToLangs_a[$domain];
}

/** Convert a site (top) domain like (dk,se,no,uk,com) 
to a site URL e.g: 'http://www.airplaymusic.dk/', 'http://www.airplaymusic.co.uk/' */
function domainToSiteBaseUrl ($domain )
{
    static $domainsToLangs_a = array(
          'com'=> ''
        , 'de' => ''
        , 'dk' => 'http://www.airplaymusic.dk/'
        , 'no' => ''
        , 'se' => ''
        , 'uk' => 'http://www.airplaymusic.co.uk/'
    );
    return $domainsToLangs_a[$domain];
}


/*
/// Gets the currency currently in use. 
function ap_user_locale_currency( ) 
{
    global $user;
    // Prefer currency specified in URL if any
//  $bFound = false;
    $currency = "";
    if ( array_key_exists( 'currency', $_GET) ) {
        $currency = $_GET['currency'];
//      if ( $currency != "" ) return $currency;
    }
    
    if ( $currency == "" ) {
        $profile =  profile_load_profile($user);
        if ( $user->profile_currency ) {
            $currency = $user->profile_currency;
        }
    }
    if ( $currency == "" ) {
        if ( array_key_exists( 'currency', $_SESSION) ) {
            $currency = $_SESSION['currency'];
        }
    }
    
    // Default - Should change to a site specific one
    if ( $currency == "" ) {
        $currency = "DKK";
    }
    
    // --- Write value to session --- 
    $_SESSION['currency'] = $currency;

    return $currency;
}

/// Gets the media format in use.
function ap_user_media_format( ) 
{
    global $user;
    // Prefer currency specified in URL if any
    if ( array_key_exists( 'media_format', $_GET) ) {
        $media_format = $_GET['media_format'];
        if ( $media_format != "" ) return $media_format;
    }
    
    $profile =  profile_load_profile($user);
    $media_format = "ALL";
    if ( $user->profile_media_format ) {
        $media_format = $user->profile_media_format;
    }
    return $media_format;
}

*/
