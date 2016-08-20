<?php
    require "../../filesupload/public_html/include_db_functions.php";

    
    $dbconnection = open_db_v1();
    $sql = "SELECT DISTINCT artist_name
            FROM (
                SELECT COUNT(*) AS nCount, item_price.artist_id, item_base_name
                FROM item_price
                INNER JOIN item_base ON item_base.item_base_id = item_price.item_base_id
                WHERE item_price.item_type = 1
                GROUP BY item_price.artist_id, item_base.item_base_id
            ) AS InnerRes, artist
            WHERE InnerRes.nCount > 5 AND artist.artist_id = InnerRes.artist_id AND artist_name NOT IN ('V/a', 'Various Artists', 'Various')
            ORDER BY InnerRes.nCount desc";
    $artists = sql_return_query($dbconnection, $sql, array());
    close_db($dbconnection);
    
    ArtistsToFile($artists);

    function ArtistsToFile($artists) {
        $artist_names_10 = "";
        $artist_names_100 = "";
        $artist_names_1000 = "";
        $artist_names_5000 = "";
        $artist_names_10000 = "";
        $artist_names_20000 = "";
        $artist_names_50000 = "";
        
        $word_file_name = "artist_names_with_prices_pr_album_";
        $file_path = __DIR__;

        $file_path = str_replace("aphp/cron", "drupal7/public_files/miners/files/", $file_path);
        $word_file_name = $file_path . $word_file_name;

        
        if (count($artists) > 0) {
            for ($i=0; $i < count($artists); $i++) {
                if ($i<=10) {
                    $artist_names_10 .= $artists[$i]["artist_name"] . "\n";
                } 
                if ($i<=100) {
                    $artist_names_100 .= $artists[$i]["artist_name"] . "\n";
                } 
                if ($i<=1000) {
                    $artist_names_1000 .= $artists[$i]["artist_name"] . "\n";
                } 
                if ($i<=5000) {
                    $artist_names_5000 .= $artists[$i]["artist_name"] . "\n";
                } 
                if ($i<=10000) {
                    $artist_names_10000 .= $artists[$i]["artist_name"] . "\n";
                } 
                if ($i<=20000) {
                    $artist_names_20000 .= $artists[$i]["artist_name"] . "\n";
                } 
                if ($i<=50000) {
                    $artist_names_50000 .= $artists[$i]["artist_name"] . "\n";
                }
            }
        }
        print "Saving to files....";
        file_put_contents($word_file_name . "10.txt"   , $artist_names_10);
        file_put_contents($word_file_name . "100.txt"  , $artist_names_100);
        file_put_contents($word_file_name . "1000.txt" , $artist_names_1000);
        file_put_contents($word_file_name . "5000.txt" , $artist_names_5000);
        file_put_contents($word_file_name . "10000.txt", $artist_names_10000);
        file_put_contents($word_file_name . "20000.txt", $artist_names_20000);
        file_put_contents($word_file_name . "50000.txt", $artist_names_50000);
    }
?>