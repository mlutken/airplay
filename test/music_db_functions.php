<?php

$host       =    "localhost";
$user       =    "airplay_user";
$pass       =    "Deeyl1819";
$db         =    "airplay_music";


$link = mysql_connect($host, $user, $pass );
if (!$link) {
    die('Could not connect: ' . mysql_error());
}
echo "Connected successfully\n";
mysql_select_db($db);

// ------------------------
// --- Sorting functors ---
// ------------------------

function year_DESC ( $item1,$item2 )
{
    if ($item1['year'] == $item2['year']) return 0;
    return ($item1['year'] < $item2['year']) ? 1 : -1;
}

function year_ASC ( $item1,$item2 )
{
    if ( (int)$item1['year'] == (int)$item2['year']) return 0;
    return ( (int)$item1['year'] > (int)$item2['year']) ? 1 : -1;
}


function name_DESC ( $item1,$item2 )
{
    if ($item1['name'] == $item2['name']) return 0;
    return ($item1['name'] < $item2['name']) ? 1 : -1;
}

function name_ASC ( $item1,$item2 )
{
    if ($item1['name'] == $item2['name']) return 0;
    return ($item1['name'] > $item2['name']) ? 1 : -1;
}


function album_simple_name_DESC ( $item1,$item2 )
{
    if ($item1['album_simple_name'] == $item2['album_simple_name']) return 0;
    return ($item1['album_simple_name'] < $item2['album_simple_name']) ? 1 : -1;
}

function album_simple_name_ASC ( $item1,$item2 )
{
    if ($item1['album_simple_name'] == $item2['album_simple_name']) return 0;
    return ($item1['album_simple_name'] > $item2['album_simple_name']) ? 1 : -1;
}


// -----------------------------
// --- Get DB info functions ---
// -----------------------------

function getArtistAlbums($artist_name, $currency, $media_format)
{
    $query = 
    "SELECT artist.artist_id, artist.artist_name, album.album_name, album.album_year as year, album_simple_name as name, 
    currency_to_euro.to_euro,  
    COUNT(*) as album_prices_count, 
    MIN(price_local * currency_to_euro.to_euro * currency.from_euro) * 0.01 as price_MIN,   
    MAX(price_local * currency_to_euro.to_euro * currency.from_euro) * 0.01 as price_MAX
    FROM artist
    INNER JOIN album ON album.artist_id=artist.artist_id 
    INNER JOIN buy_album ON album.album_id=buy_album.album_id 
    INNER JOIN album_simple ON album_simple.album_simple_id=album.album_simple_id 
    INNER JOIN media_format ON media_format.media_format_id=buy_album.media_format_id
    INNER JOIN currency_to_euro ON currency_to_euro.currency_id=buy_album.currency_id   
    INNER JOIN currency ON currency.currency_name='%s'
    WHERE artist.artist_name = '%s'
    ";
    if ( $media_format != "" && $media_format != 'ALL' ) {
        $query .= " AND media_format_name = '" . mysql_real_escape_string($media_format) . "'"; 
    }
    $query .= " GROUP BY album_simple.album_simple_name";
    
    $query = sprintf( $query   
        ,   mysql_real_escape_string($currency)
        ,   mysql_real_escape_string($artist_name)
        );

//    echo "$query\n";
    $result = mysql_query($query);
    $aRes = array();
    while ($row = mysql_fetch_assoc($result)) {
        $aRes[] = $row;
    }
    return $aRes;
}

$artist_name    = "Kim Larsen";
$currency       = "DKK";
$media_format   = "ALL";

$aRes = getArtistAlbums($artist_name, $currency, $media_format);

usort($aRes,'year_DESC');


print_r ($aRes);


mysql_close($link);


?>
