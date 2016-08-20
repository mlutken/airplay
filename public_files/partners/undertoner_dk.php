<?php
    if (isset($_GET["skey"]) && $_GET["skey"] == "cd9479a2664bb4d4551e654b22af90f0") {
        include "../../drupal7/sites/all/modules/airplay_base/airplay_base_url_functions.inc";
        include "../../filesupload/public_html/include_db_functions.php";
        
        header('Content-Type: text/json');
        
        $dbconnection = open_db_v1();
        $undertoner_links = array();

        $sql = "SELECT artist_name, item_base_name, undertoner_review_url FROM item_base INNER JOIN artist ON artist.artist_id = item_base.artist_id WHERE undertoner_review_url <> ''";
        
        $undertoner_data = sql_return_query($dbconnection, $sql, array());
        
        if (is_array($undertoner_data)) {
            $item_array_size = sizeof($undertoner_data);
            if ($item_array_size > 0) {
                for ($i = 0; $i < $item_array_size; $i++)
                {
                    extract($undertoner_data[$i]);
                    $ap_link_dk = "http://www.airplaymusic.dk/kunstner/" . airplay_name_to_url($artist_name) . "/album/" . airplay_name_to_url($item_base_name);
                    
                    $array = array('ap_artist_name' => $artist_name, 'ap_album_name' => $item_base_name, 'ap_link_dk' => $ap_link_dk, 'ut_link' => $undertoner_review_url);
                    $undertoner_links[] = $array;
                }
            }
        }
        close_db($dbconnection);
        
        print json_encode($undertoner_links, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    } else {
        print "<h1>Airplay Music</h1>";
        print "<p>Incorrect key</p>";
    }
?>
