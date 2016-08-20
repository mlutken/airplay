<?php 
// $Id$


function airplay_admin_main ()
{
    $sHeadLine = t('Airplay administration/statistics pages');
    $sHtml .= "<h1>$sHeadLine</h1>";
    $sHtml .= "<ul>";
    $sHtml .= "<li><a href='airplay_admin/display_latest_added'>" . t('Latest added data') . "</a>";
    $sHtml .= "</ul>";
    return $sHtml;
}

function airplay_admin_display_latest_added()
{
    $sHeadLine          = t('Latest added data');
    $sHtml = "<h1>$sHeadLine</h1>";
    
    $sHtml .= airplay_admin_display_latest_added_albums();
    $sHtml .= airplay_admin_display_latest_added_songs();
    return $sHtml;
}

function airplay_admin_display_latest_added_albums()
{
    $sHtml = "";
    $sHeadLineAlbums    = t('Albums');
    $saveDB = db_set_active('airplay_music');

    $aArgs = array();
    $sQuery = ap_admin_view_buy_album_updated();
    $query_result = db_query($sQuery, $aArgs );
 
    $aAllRows = array();
    while ( $o = db_fetch_object($query_result) ) {
        $aAllRows[] = $o;
    } 
    db_set_active($saveDB);
    
    // --- Table header ---
    $header = array( 
        t('Record store'), 
        t('Last updated'), 
        t('Total album prices')
        );
    
    // --- Table content ---
    $iTotalPrices = 0;
    foreach ($aAllRows as $o ) {
        $row[]  = array( 
              $o->record_store_name
            , $o->last_updated
            , number_format( $o->album_count, 0, "", " " )
            );
        $iTotalPrices += $o->album_count;
    }
    // Add row with total number of prices    
    $row[]  = array("","",""); 
    $row[]  = array( 
            t("Total") . " " . t("album") . " " . t("prices")
        , ""
        , number_format( $iTotalPrices, 0, "", " " )
        );
    

    // --- Render Table ---
    $sHtml .= "<h2>$sHeadLineAlbums</h2>";
    $sHtml .= theme('table', $header, $row);
    return $sHtml;
}


function airplay_admin_display_latest_added_songs()
{
    $sHtml = "";
    $sHeadLineSongs     = t('Songs');
    $saveDB = db_set_active('airplay_music');

    $aArgs = array();
    $sQuery = ap_admin_view_buy_song_updated();
    $query_result = db_query($sQuery, $aArgs );
 
    $aAllRows = array();
    while ( $o = db_fetch_object($query_result) ) {
        $aAllRows[] = $o;
    } 
    db_set_active($saveDB);
    
    // --- Table header ---
    $header = array( 
        t('Record store'), 
        t('Last updated'), 
        t('Total song prices')
        );
    
    // --- Table content ---
    $iTotalPrices = 0;
    foreach ($aAllRows as $o ) {
        $row[]  = array( 
              $o->record_store_name
            , $o->last_updated
            , number_format( $o->song_count, 0, "", " " )
            );
        $iTotalPrices += $o->song_count;
    }
    // Add row with total number of prices    
    $row[]  = array("","",""); 
    $row[]  = array( 
            t("Total") . " " . t("song") . " " . t("prices")
        , ""
        , number_format( $iTotalPrices, 0, "", " " )
        );

    // --- Render Table ---
    $sHtml .= "<h2>$sHeadLineSongs</h2>";
    $sHtml .= theme('table', $header, $row);
    return $sHtml;
}




function airplay_admin_display_latest_upload()
{
    $sHeadLine = t('Latest uploaded data ');
    $sHtml .= "<h1>$sHeadLine</h1>";

	return $sHtml;
}


/*
Kunstner (60 360)
Album (118 801)
Sang (126 667)

 
Kunstner (60 356)
Album (118 448)
Sang (121 649)

*/
