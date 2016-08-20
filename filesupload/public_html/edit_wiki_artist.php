<?php
	$wiki_text = "";
	$is_default_text = 0;
	require_once("include_db_functions.php");
	$dbconnection = open_db();
	
	/* Get Data */
	if (isset($_GET['artist_id']) && isset($_GET['language_code'])) {
		$array = sql_return_query($dbconnection, "SELECT wiki_text FROM wiki_text_artist WHERE artist_id = :artist_id AND language_code = :language_code", array(':artist_id' => $_GET['artist_id'], ':language_code' => $_GET['language_code']));

		if (sizeof($array) > 0) {
			for ($i = 0; $i < sizeof($array); $i++) {
				extract($array[$i]);
				$wiki_text = $wiki_text;
				$is_default_text = 0;
			}
		}
	}
	if (isset($_POST['artist_id']) && isset($_POST['language_code']) && isset($_POST['wiki_text'])) {
		$language_code = $_POST["language_code"];
		$artist_id = $_POST["artist_id"];
		$wiki_text = $_POST["wiki_text"];
		$array = sql_return_query(($dbconnection, "UPDATE wiki_text_artist SET wiki_text = :wiki_text WHERE artist_id = :artist_id AND language_code = :language_code;", array(':wiki_text' => $wiki_text, ':artist_id' => $artist_id, ':language_code' => $language_code));        
		if (sizeof($array) > 0) {
			$array = sql_return_query(($dbconnection, "INSERT INTO wiki_text_artist (artist_id, language_code, wiki_text) VALUES (:artist_id, :language_code, :wiki_text);", array(':artist_id' => $artist_id,':language_code' => $language_code,':wiki_text' => $wiki_text));
		}
	}
	close_db($dbconnection);
?>
<?php
	if (isset($_GET['artist_id']) && isset($_GET['language_code'])) {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Redigere artist WIKI</title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<style>
		* {
			font-family:verdana;
			font-size:12px;
		}
	</style>
<head>

<body>
	<div><b><?php if ($is_default_text == 1) { echo "Denne bruger standard teksten"; } else { echo "Denne bruger ikke standard teksten"; } ?></b></div>
	<br/>
	<div><b>HTML Tags:</b><br/>
	&lt;b&gt;fed&lt;b&gt; , &lt;i&gt;kursiv&lt;i&gt; , &lt;u&gt;understreget&lt;u&gt; , &lt;BR/&gt; = linieskift<br/>
	&lt;a href="http://www.airplaymusic.dk/" title="en eller anden tekst" &gt;Tekst i linket&lt;/a&gt; (åbne i nyt vindue tilføjes target="_blank")
	</div>
	<br/>
	<div>
	<b>Erstatningstekster:</b><br/>
	{artist_name} = kunsterens navn
	</div>
	
	<br/>
	<div>
	<form action="edit_wiki_artist.php" method="POST">
		<input type="hidden" name="artist_id" id="artist_id" value="<?php echo $_GET['artist_id']; ?>">
		<input type="hidden" name="language_code" id="language_code" value="<?php echo $_GET['language_code']; ?>">
		<textarea cols="100" rows="10" id="wiki_text" name="wiki_text"><?php echo $wiki_text; ?></textarea><br/>
		<input type="submit" name="submit" id="submit" value="Gem">
	</form>
	</div>
</body>
<?php } ?>