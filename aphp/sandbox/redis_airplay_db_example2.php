<?php
require_once ( __DIR__ . '/../aphp_fix_include_path.php' );
require_once ('airplay_globals.php');

global $g_idToGenre;
$g_idToGenre = array("Unknown", "Pop/Rock", "Soul/R&B");

global $r;

$r = new Redis();

$r->connect("127.0.0.1");

// ----------------
// --- Currency ---
// ----------------
$r->set("EUR:to_EUR", 1);
$r->set("EUR:to_DKK", 7.4305);
$r->set("EUR:to_SEK", 8.9834);
$r->set("EUR:to_GBP", 0.799);
$r->set("EUR:to_USD", 1.2515);

$r->set("DKK:to_EUR", 1.0 / $r->get("EUR:to_DKK"));
$r->set("SEK:to_EUR", 1.0 / $r->get("EUR:to_SEK"));
$r->set("GBP:to_EUR", 1.0 / $r->get("EUR:to_GPB"));
$r->set("USD:to_EUR", 1.0 / $r->get("EUR:to_USD"));

function toCurrency( $val, $fromCur, $toCur ) 
{
    global $r;
    return $val * $r->get("$fromCur:to_EUR") * $r->get("EUR:to_$toCur");
}

function toLookUpId($sLookUpName)
{
    $lookup = mb_strtolower( $sLookUpName, 'UTF-8' );
    $lookup = str_replace  ( " " , "_" , $lookup );
    return $lookup;
}

// --- Artist: Kim Larsen ---
$r->set("1000:type", "artist");
$r->set("1000:name", "Kim Larsen");
$r->set("1000:genre", 1);
$r->set("1000:year_born", "1945");
$r->sadd("all_artists", 1000 );   // Tilføj Kim Larsens ID til globalt set med alle kunstnere 
$r->sadd("kim_larsen:ids", 1000); // Opslag af "Kim Larsen" (kim_larsen) til ID. Vi bruger et set, da der kan være flere "ting" der hedder "kim Larsen"

// Album: Midt om natten
$r->set("1020:type", "album");
$r->set("1020:name", "Midt om natten");
$r->set("1020:artist", 1000);
$r->set("1020:year", 1984);
$r->sadd("1000:all_albums", "1020");    // Tilføjer til listen af all Kim Larsen album
$r->sadd("all_albums", "1020");    // Tilføjer til global liste af alle albums
$r->sadd("midt_om_natten:ids", "1020"); // Bruger set da forskellige kunstnere kan have lavet albums med identiske navne
$r->sadd("kim_larsen:midt_om_natten:item_ids", "1020"); // Bruger set da kunstnere ofte har både et album og en sang med samme navn


// Song: Midt om natten
$r->set("5022:type", "song");
$r->set("5022:name", "Midt om natten");
$r->set("5022:artist", "1000");
$r->set("5022:album", "1020");
$r->set("5022:year", "1984");
$r->set("5022:time", "224");
$r->sadd("1000:all_songs", "5022");
$r->sadd("1020:all_songs", "5022");
$r->sadd("all_songs", "5022");
$r->sadd("midt_om_natten:ids", "5022");
$r->sadd("kim_larsen:midt_om_natten:item_ids", "1022"); // Bruger set da kunstnere ofte har både et album og en sang med samme navn


// Album prices: Midt om natten
$r->set("1030:type", "price");
$r->set("1030:item", "1020");   // item = song OR album
$r->set("1030:price", "59");
$r->set("1030:currency", "DKK");
$r->set("1030:media", "CD");
$r->set("1030:url", "http://gaffa.dk/shop/item123456");
$r->set("1030:name", "Midt om natten - remastered");
$r->set("1030:year", "2003");
$r->sadd("1020:all_prices", "1030");
$r->sadd("all_prices", "1030");
$r->set("1020:price_min", "1030");  // Vi kan nemt tjekke dette når vi indsætter en pris i Redis

// Album prices: Midt om natten
$r->set("1031:type", "price");
$r->set("1031:item", "1020");
$r->set("1031:price", "79");
$r->set("1031:currency", "DKK");
$r->set("1031:media", "CD");
$r->set("1031:url", "http://cdon.dk/shop/item45545");
$r->set("1031:name", "Midt om natten");
$r->set("1031:year", "1984");
$r->sadd("1020:all_prices", "1031");
$r->sadd("all_prices", "1031");
$r->set("1020:price_max", "1031"); // Vi kan nemt tjekke dette når vi indsætter en pris i Redis


// Album: Forklædt som voksen
$r->set("1021:type", "album");
$r->set("1021:name", "Forklædt som voksen");
$r->set("1021:artist", "1000");
$r->set("1021:year", "1985");
$r->sadd("1000:all_albums", "1021");
$r->sadd("all_albums", "1021");
$r->sadd("forklædt_som_voksen:ids", "1021");
$r->sadd("kim_larsen:forklædt_som_voksen:item_ids", "1021"); // Bruger set da kunstnere ofte har både et album og en sang med samme navn


// Song: Forklædt som voksen
$r->set("1022:type", "song");
$r->set("1022:name", "Forklædt som voksen");
$r->set("1022:artist", "1000");
$r->set("1022:album", "1021");
$r->set("1022:year", "1985");
$r->set("1022:time", "204");
$r->sadd("1000:all_songs", "1022");
$r->sadd("1021:all_songs", "1022");
$r->sadd("all_songs", "1022");
$r->sadd("forklædt_som_voksen:ids", "1022");
$r->sadd("kim_larsen:forklædt_som_voksen:item_ids", "1022"); // Bruger set da kunstnere ofte har både et album og en sang med samme navn

// Song: Fru Sauterne
$r->set("1022:type", "song");
$r->set("1022:name", "Fru Sauterne");
$r->set("1022:artist", "1000");
$r->set("1022:album", "1021");
$r->set("1022:year", "1985");
$r->set("1022:time", "204");
$r->sadd("1000:all_songs", "1022");
$r->sadd("1021:all_songs", "1022");
$r->sadd("fru_sauterne:ids", "1022");
$r->sadd("kim_larsen:fru_sauterne:item_ids", "1022"); // Bruger set da kunstnere ofte har både et album og en sang med samme navn


$r->set("artist:id:1000:song_id:1023:name", "Fru Sauterne");
$r->set("artist:id:1000:song_id:1023:time", "183");
$r->sadd("artist:id:1000:all_songs", "1023");
$r->sadd("artist:id:1000:album_id:1021:all_songs", "1023");


// ---------------------------
// --- Lookup 'Kim Larsen' ---
// ---------------------------
$sViewCurrency = "DKK";
$sLookUpName = 'Kim Larsen';
$lookup = toLookUpId( $sLookUpName );

printf( "\n\n" );
printf ("LOOKUP: '%s' using => '%s'\n", $sLookUpName, $lookup );
$ids = $r->sMembers("$lookup:ids");

foreach ( $ids as $id ) {
    if ( $r->get("$id:type") == "artist" ) {
        $artist_id = $id;
        printf( "--------------------------------------------\n" );
        printf( "%s  (%s)\n", $r->get("$artist_id:name"), $g_idToGenre[ $r->get("$artist_id:genre") ] );
        printf( "--------------------------------------------\n" );
        printf( "Født: %d\n", $r->get("$artist_id:year_born") );
        
        $aAlbumIds = $r->sMembers("$id:all_albums");
        printf( "\nAlbum list:\n" );
        foreach( $aAlbumIds as $album_id ) {
            $album_name = $r->get("$album_id:name");
            $price_min_id = $r->get("$album_id:price_min");
            $price_max_id = $r->get("$album_id:price_max");
            $linkVievPrices = "/kunstner/$lookup/album/" . toLookUpId($album_name);
            
            printf ("%s,   %s   Price range (%.2f -> %.2f) %s   View Prices: %s\n", 
              $album_name
            , $r->get("$album_id:year")
            , toCurrency( $r->get("$price_min_id:price"), $r->get("$price_min_id:currency"), $sViewCurrency ) 
            , toCurrency( $r->get("$price_max_id:price"), $r->get("$price_max_id:currency"), $sViewCurrency ) 
            , $sViewCurrency
            , $linkVievPrices
            );
        }
    
        $aSongIds = $r->sMembers("$id:all_songs");
        printf( "\nSong list:\n" );
        foreach( $aSongIds as $song_id ) {
            printf ("%s,   %s\n", $r->get("$song_id:name"), $r->get("$song_id:year" ) );
        }
    }
}


// ------------------------------------------------------------------------------------
// --- Print album priser fra link '/kunstner/kim_larsen/album/midt_om_natten' ---
// ------------------------------------------------------------------------------------
printf("\n\n");
$artistLookUpId = "kim_larsen";
$albumLookUpId = "midt_om_natten";

$item_ids = $r->sMembers("$artistLookUpId:$albumLookUpId:item_ids");
//var_dump($item_ids);

foreach ( $item_ids as $id ) {
    if ( $r->get("$id:type") == "album" ) {
        $album_id = $id;
        $artist_id = $r->get("$album_id:artist");
        $artist_name = $r->get("$artist_id:name");
        $album_name = $r->get("$album_id:name");
        $album_year = $r->get("$album_id:year");
        printf( "--------------------------------------------\n" );
        printf( "ALBUM: %s  (%s, %d)\n", $album_name, $artist_name, $album_year);
        printf( "--------------------------------------------\n" );
        
        $aAlbumPriceIds = $r->sMembers("$album_id:all_prices");
        printf( "Prices:\n" );
        foreach( $aAlbumPriceIds as $price_id ) {
            $album_release_name = $r->get("$price_id:name");
            $album_release_year = $r->get("$price_id:year");
            $linkBuy = "/kunstner/$lookup/album/" . toLookUpId($album_name);
            
            printf ("%s,   Year: %d   %.2f %s   Buy: %s\n", 
              $album_release_name != "" ? $album_release_name : $album_name
            , $album_release_year != "" ? $album_release_year : $album_year
            , toCurrency( $r->get("$price_id:price"), $r->get("$price_id:currency"), $sViewCurrency ) 
            , $sViewCurrency
            , $r->get("$price_id:url")
            );
        }
    }
}



?>
