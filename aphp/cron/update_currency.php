<?php

    require "../../filesupload/public_html/include_db_functions.php";
    
    $currency_base   = "EUR";
    $currency_list   = array('EUR','DKK','SEK','GBP','USD','NOK');
    $currency_values = array();
	
	// TODO mail try catch error.
	
	$xml = simplexml_load_file('http://themoneyconverter.com/rss-feed/EUR/rss.xml');

	$p_cnt = count($xml->channel->item);

	for($i = 0; $i < $p_cnt; $i++) {
		$cur_code = (string)$xml->channel->item[$i]->title;
		$cur_code = substr($cur_code, 0, 3);
		$cur_rate = (string)$xml->channel->item[$i]->description;
		$cur_rate_split = explode(" ", $cur_rate);
		$cur_rate = $cur_rate_split[3];
		if (in_array($cur_code, $currency_list) && $cur_rate != "" && $cur_rate != 0 && $cur_rate != "0") {
			$currency_values[] = array('from' => $currency_base, 'to' => $cur_code, 'value' => number_format($cur_rate, 6));
		}
	}
    
	
	$dbconnection = open_db_v1();
    /* Update currency */
    foreach ($currency_values as $currency => $key) {
		$sql = "UPDATE currency SET from_euro = :currency_value WHERE currency_name = :currency_name";
		sql_non_return_query($dbconnection, $sql, array(':currency_value' => $key["value"], ':currency_name' => $key["to"]));
    }
	

	// Reset array
	$currency_values = array();
	
	for($i = 0; $i < $p_cnt; $i++) {
		$cur_code = (string)$xml->channel->item[$i]->title;
		$cur_code = substr($cur_code, 0, 3);
		$cur_rate = (string)$xml->channel->item[$i]->description;
		$cur_rate_split = explode(" ", $cur_rate);
		$cur_rate = $cur_rate_split[3];
		
		if (in_array($cur_code, $currency_list) && $cur_rate != "" && $cur_rate != 0 && $cur_rate != "0") {
			$currency_values[] = array('from' => $cur_code, 'to' => $currency_base, 'value' => number_format((1/$cur_rate), 6));
		}
	}

    /* Update currency */
	// ONLY update - no need to add new ones - we need only these currencies
    foreach ($currency_values as $currency => $key) {
        $sql = "UPDATE currency SET to_euro = :currency_value WHERE currency_name = :currency_name";
        sql_non_return_query($dbconnection, $sql, array(':currency_value' => $key["value"], ':currency_name' => $key["from"]));
        $sql = "UPDATE currency_to_euro SET to_euro = :currency_value WHERE currency_name = :currency_name";
        sql_non_return_query($dbconnection, $sql, array(':currency_value' => $key["value"], ':currency_name' => $key["from"]));
    }
    close_db($dbconnection);


	/*
	OLD	//$p_cnt = count($xml->Cube->Cube->Cube);
	    //$xml = simplexml_load_file('http://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml');
	*/
	
	
	/* OLD Currency  TO
	for($i = 0; $i < $p_cnt; $i++) {
		$cur_code = (string)$xml->Cube->Cube->Cube[$i]['currency'];
		$cur_rate = (string)$xml->Cube->Cube->Cube[$i]['rate'];
		
		if (in_array($cur_code, $currency_list) && $cur_rate != "" && $cur_rate != 0 && $cur_rate != "0") {
			$currency_values[] = array('from' => $currency_base, 'to' => $cur_code, 'value' => number_format($cur_rate, 4));
		}
	}
    $currency_values[] = array('from' => $currency_base, 'to' => $currency_base, 'value' => number_format(1, 4));
	
	
		for($i = 0; $i < $p_cnt; $i++) {
		$cur_code = (string)$xml->Cube->Cube->Cube[$i]['currency'];
		$cur_rate = (string)$xml->Cube->Cube->Cube[$i]['rate'];
		
		if (in_array($cur_code, $currency_list) && $cur_rate != "" && $cur_rate != 0 && $cur_rate != "0") {
			$currency_values[] = array('from' => $cur_code, 'to' => $currency_base, 'value' => number_format((1/$cur_rate), 6));
		}
	}
    $currency_values[] = array('from' => $currency_base, 'to' => $currency_base, 'value' => number_format(1, 6));	
	
	*/

?>