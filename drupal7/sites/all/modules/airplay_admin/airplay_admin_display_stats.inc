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
    $sHeadLine  = t('Latest added data');
    $sHtml      = "<h1>$sHeadLine</h1>";

    $sHtml .= airplay_admin_display_latest_added_albums_songs();
    return $sHtml;
}

function airplay_admin_display_latest_added_albums_songs() {
    
    $sHtmlAlbums = "";
    $sHtmlSongs = "";
    $sHtmlMerchandise = "";
    $sHtmlConcert = "";
    
    $sHeadLineAlbums      = t('Albums');
    $sHeadLineSongs       = t('Songs');
    $sHeadLineMerchandise = t('Merchandise');
    $sHeadLineConcert     = t('Concert');
	$sHeadLineItems = "Items";
    $saveDB = db_set_active('airplay_music_v1');

    $aArgs = array();
    $sQuery = ap_admin_view_album_song_merchandise_updated();
	$aAllRowsItems = ap_admin_items_count();
    $query_result_items = db_query($aAllRowsItems, $aArgs );
    $query_result = db_query($sQuery, $aArgs );

    $aAllRowsAlbums = array();
    $aAllRowsSongs = array();
    $aAllRowsMerchandise = array();
    $aAllRowsConcert = array();
    while ( $o = $query_result->fetchAssoc() ) {
        if ($o["item_type"] == 1) {
            $aAllRowsAlbums[] = $o;
        } else if ($o["item_type"] == 2) {
            $aAllRowsSongs[] = $o;
        } else if ($o["item_type"] == 3) {
            $aAllRowsMerchandise[] = $o;
        } else if ($o["item_type"] == 4) {
            $aAllRowsConcert[] = $o;
        }
    } 
	
	$aAllRowsItems = array();
    while ( $o = $query_result_items->fetchAssoc() ) {
        if ($o["item_type"] == 1) {
            $aAllRowsItems[] = $o;
        } else if ($o["item_type"] == 2) {
            $aAllRowsItems[] = $o;
        } else if ($o["item_type"] == 3) {
            $aAllRowsItems[] = $o;
        } else if ($o["item_type"] == 4) {
            $aAllRowsItems[] = $o;
        }
    } 
    db_set_active($saveDB);
    
    /* Albums table */
    $header = array( 
        t('Record store'), 
        t('Last updated'), 
        t('Total album prices')
        );
    
    // --- Table content ---
    $iTotalPrices = 0;
    foreach ($aAllRowsAlbums as $o ) {
        $rowAlbums[]  = array( 
              $o["record_store_name"]
            , $o["last_updated"]
            , number_format( $o["item_count"], 0, "", " " )
            );
        $iTotalPrices += $o["item_count"];
    }
    // Add row with total number of prices    
    $rowAlbums[]  = array("","",""); 
    $rowAlbums[]  = array( 
            t("Total") . " " . t("album") . " " . t("prices")
        , ""
        , number_format( $iTotalPrices, 0, "", " " )
        );
    

    // --- Render Table ---
    $sHtmlAlbums .= "<h2>$sHeadLineAlbums</h2>";
	$sHtmlAlbums .= theme('table', array(  'header' => $header,  'rows' => $rowAlbums, 'attributes' => array('class' => array(''),  'width' => '100%')));
    
    
    /* Songs table */
    // --- Table header ---
    $header = array( 
        t('Record store'), 
        t('Last updated'), 
        t('Total song prices')
        );
    
    // --- Table content ---
    $iTotalPrices = 0;
    foreach ($aAllRowsSongs as $o ) {
        $rowSongs[]  = array( 
              $o["record_store_name"]
            , $o["last_updated"]
            , number_format( $o["item_count"], 0, "", " " )
            );
        $iTotalPrices += $o["item_count"];
    }
    // Add row with total number of prices    
    $rowSongs[]  = array("","",""); 
    $rowSongs[]  = array( 
            t("Total") . " " . t("song") . " " . t("prices")
        , ""
        , number_format( $iTotalPrices, 0, "", " " )
        );

    // --- Render Table ---
    $sHtmlSongs .= "<h2>$sHeadLineSongs</h2>";
	$sHtmlSongs .= theme('table', array(  'header' => $header,  'rows' => $rowSongs,  'attributes' => array('class' => array(''),  'width' => '100%')));
    
    /* Merchandise table */
    // --- Table header ---
    $header = array( 
        t('Record store'), 
        t('Last updated'), 
        t('Total merchandise prices')
        );
    
    // --- Table content ---
    $iTotalPrices = 0;
    foreach ($aAllRowsMerchandise as $o ) {
        $rowMerchandise[]  = array( 
              $o["record_store_name"]
            , $o["last_updated"]
            , number_format( $o["item_count"], 0, "", " " )
            );
        $iTotalPrices += $o["item_count"];
    }
    // Add row with total number of prices    
    $rowMerchandise[]  = array("","",""); 
    $rowMerchandise[]  = array( 
            t("Total") . " " . t("merchandise") . " " . t("prices")
        , ""
        , number_format( $iTotalPrices, 0, "", " " )
        );

    // --- Render Table ---
    $sHtmlMerchandise .= "<h2>$sHeadLineMerchandise</h2>";
	$sHtmlMerchandise .= theme('table', array(  'header' => $header,  'rows' => $rowMerchandise,  'attributes' => array('class' => array(''),  'width' => '100%')));
    
    
    /* Concert table */
    // --- Table header ---
    $header = array( 
        t('Record store'), 
        t('Last updated'), 
        t('Total concert prices')
        );
    
    // --- Table content ---
    $iTotalPrices = 0;
    foreach ($aAllRowsConcert as $o ) {
        $rowConcert[]  = array( 
              $o["record_store_name"]
            , $o["last_updated"]
            , number_format( $o["item_count"], 0, "", " " )
            );
        $iTotalPrices += $o["item_count"];
    }
    // Add row with total number of prices    
    $rowConcert[]  = array("","",""); 
    $rowConcert[]  = array( 
            t("Total") . " " . t("concert") . " " . t("prices")
        , ""
        , number_format( $iTotalPrices, 0, "", " " )
        );

    // --- Render Table ---
    $sHtmlConcert .= "<h2>$sHeadLineConcert</h2>";
	$sHtmlConcert .= theme('table', array(  'header' => $header,  'rows' => $rowConcert,  'attributes' => array('class' => array(''),  'width' => '100%')));
    
	
	 /* items type content - like total number of items - not with prices */
    // --- Table header ---
    $header = array( 
        t('Type') . " (Albums, Songs, Merchandise, Concerts)", 
        "", 
        t('Total items')
        );
    
    // --- Table content ---
    $iTotalPrices = 0;
    foreach ($aAllRowsItems as $o ) {
		if ($o["item_type"] == 1) {
			$item_text = "Album";
		} else if ($o["item_type"] == 2) {
			$item_text = "Song";
		} else if ($o["item_type"] == 3) {
			$item_text = "Merchandise";
		} else if ($o["item_type"] == 4) {
			$item_text = "Concert";
		}
        $rowItems[]  = array( 
              $item_text
            , ""
            , number_format( $o["item_count"], 0, "", " " )
            );
        $iTotalPrices += $o["item_count"];
    }
    // Add row with total number of prices    
    $rowItems[]  = array("","",""); 
    $rowItems[]  = array( 
            t("Total") . " items"
        , ""
        , number_format( $iTotalPrices, 0, "", " " )
        );

    // --- Render Table ---
    $sHtmlItems .= "<h2>$sHeadLineItems</h2>";
	$sHtmlItems .= theme('table', array(  'header' => $header,  'rows' => $rowItems,  'attributes' => array('class' => array(''),  'width' => '100%')));
	
	
    
	$style = "<style> h2 { margin:10px 0px 10px 0px; font-size:130%; }  td { width:33%; } th { font-weight:bold; }</style>";
	
    return $style . $sHtmlAlbums . $sHtmlSongs . $sHtmlMerchandise . $sHtmlConcert . $sHtmlItems;
    
}


function airplay_admin_display_latest_added_albums()
{
    $sHtml = "";
    $sHeadLineAlbums    = t('Albums');
    $saveDB = db_set_active('airplay_music_v1');

    $aArgs = array();
    $sQuery = ap_admin_view_buy_album_updated();
    $query_result = db_query($sQuery, $aArgs );
 
    $aAllRows = array();
    while ( $o = $query_result->fetchObject() ) {
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
	$sHtml = theme('table', array(  'header' => $header,  'rows' => $row,  'attributes' => array('class' => array(''))));
    
    return $sHtml;
}


function airplay_admin_display_latest_added_songs()
{
    $sHtml = "";
    $sHeadLineSongs     = t('Songs');
    $saveDB = db_set_active('airplay_music_v1');

    $aArgs = array();
    $sQuery = ap_admin_view_buy_song_updated();
    $query_result = db_query($sQuery, $aArgs );
 
    $aAllRows = array();
    while ( $o = $query_result->fetchObject() ) {
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
    //$sHtml .= theme('table', $header, $row);
	$sHtml = theme('table', array(  'header' => $header,  'rows' => $row,  'attributes' => array('class' => array(''))));
    return $sHtml;
}




function airplay_admin_display_latest_upload()
{
    $sHeadLine = t('Latest uploaded data ');
    $sHtml .= "<h1>$sHeadLine</h1>";

	return $sHtml;
}

