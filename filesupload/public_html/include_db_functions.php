<?php
	// Function used to open a connection to our database server.
 	function open_db() {
		try {
			$dbconnection = new PDO('mysql:host=localhost;dbname=airplay_music;charset=utf8', 'airplay_user', 'Deeyl1819');
			return $dbconnection;
		} catch (PDOException $e) {
			print_errs($e->getMessage());
			die();
		}
	}

    // Function used to open a connection to our database server.
 	function open_db_v1() {
		try {
			$dbconnection = new PDO('mysql:host=localhost;dbname=airplay_music_v1;charset=utf8', 'airplay_user', 'Deeyl1819');
			return $dbconnection;
		} catch (PDOException $e) {
			print_errs($e->getMessage());
			die();
		}
	}
    
	// Function used to close our database connection.
	function close_db($dbconnection) {
		$dbconnection = null;
	}

	// INSERT, UPDATE & DELETE. - Returns a number of affected rows.
	function sql_non_return_query($dbconnection, $query, $array) {
		try {
			$statement = $dbconnection->prepare($query);
			$statement->execute($array);
			$affected_rows = $statement->rowCount();
			return $affected_rows;
		} catch (PDOException $e) {
			print_errs($e->getMessage());
			die();
			return $e->getMessage();
		}
	}

	// Function used for return some queries.
	function sql_return_query($dbconnection, $query, $array) {
		try {
			$statement = $dbconnection->prepare($query);
			$statement->execute($array);
			$results = $statement->fetchAll(PDO::FETCH_ASSOC);
			return $results;
		} catch (PDOException $e) {
			print_errs($e->getMessage());
			die();
		}
	}

	function print_errs($error_msg) {
  		print "<p>Error is: <em>$error_msg</em></p>";
	}

	/** Convert simple (one level) associative array to an xml string. One level 
		meaning the array values cnnot be arrays but only strings. */
	function array_1lvl_to_xml_string(array $arr)
	{
		$s = '<?xml version="1.0" encoding="utf-8"?>';
		$s .= "\n<root>\n";
		$iLvl = 1;
		foreach ($arr as $k => $v) {
			for ( $i = $iLvl; $i > 0; $i-- )	   $s .= "\t";
			$s .= "<{$k}>{$v}</{$k}>\n";
		}
		$s .= "</root>";
		return $s;
	}
	
	
?>
