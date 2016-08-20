<?php
	$intro_text = "";
	$is_default_text = 0;
	$is_text_found = 0;
	require_once("include_db_functions.php");
	$dbconnection = open_db();
	
	/* Update song text */
	if (isset($_POST['song_simple_id']) && isset($_POST['language_code']) && isset($_POST['intro_text']) && isset($_POST["action"]) && $_POST["action"] == "update") {
		$language_code = $_POST["language_code"];
		$song_simple_id = $_POST["song_simple_id"];
		$intro_text = $_POST["intro_text"];
		$result = sql_non_return_query($dbconnection, "UPDATE intro_text_song SET intro_text = :intro_text WHERE song_simple_id = :song_simple_id AND language_code = :language_code", array(':intro_text' => $intro_text, ':song_simple_id' => $song_simple_id, ':language_code' => $language_code));
		if ( $result == 0) {
			sql_non_return_query($dbconnection, "INSERT INTO intro_text_song (song_simple_id, language_code, intro_text) VALUES (:song_simple_id, :language_code, :intro_text)", array(':intro_text' => $intro_text, ':song_simple_id' => $song_simple_id, ':language_code' => $language_code));
		}
		$redirect_url = "Location: /edit_intro_song.php?song_simple_id=" . $song_simple_id . "&language_code=" . $language_code;
		header($redirect_url);
	/* Delete song text */
	} else if (isset($_POST['song_simple_id']) && isset($_POST['language_code']) && isset($_POST["action"]) && $_POST["action"] == "delete") {
		$language_code = $_POST["language_code"];
		$song_simple_id = $_POST["song_simple_id"];
		sql_non_return_query($dbconnection, "DELETE FROM intro_text_song WHERE song_simple_id = :song_simple_id AND language_code = :language_code", array(':song_simple_id' => $song_simple_id, ':language_code' => $language_code));
		$redirect_url = "Location: /edit_intro_song.php?song_simple_id=" . $song_simple_id . "&language_code=" . $language_code;
		header($redirect_url);
	/* Get Data */
	} else if (isset($_GET['song_simple_id']) && isset($_GET['language_code'])) {
		$language_code = $_GET["language_code"];
		$song_simple_id = $_GET["song_simple_id"];
		$array = sql_return_query($dbconnection, "SELECT intro_text FROM intro_text_song WHERE song_simple_id = :song_simple_id AND language_code = :language_code", array(':song_simple_id' => $song_simple_id, ':language_code' => $language_code));
		if (sizeof($array) > 0) {
			for ($i = 0; $i < sizeof($array); $i++) {
				extract($array[$i]);
				$intro_text = $intro_text;
				$is_default_text = 0;
				$is_text_found = 1;
			}
		} else {
			$array = sql_return_query($dbconnection, "SELECT intro_text FROM intro_text_song WHERE is_default = 1 AND language_code = :language_code", array(':language_code' => $language_code));
			if (sizeof($array) > 0) {
				for ($i = 0; $i < sizeof($array); $i++) {
					extract($array[$i]);
					$intro_text = $intro_text;
					$is_default_text = 1;
					$is_text_found = 1;
				}
			} else {
				$is_text_found = 0;
			}
		}
	}
	
	close_db($dbconnection);
?>
<?php
	if (isset($_GET['song_simple_id']) && isset($_GET['language_code'])) {
?>
<!DOCTYPE html>
<html>
<head>
	<title>Redigere sang intro</title>
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
	{song_name} = sangens navn
	{song_url}  = kunsterens url
	<br>
	{artist_name} = kunsterens navn
	{artist_url} = kunsterens url
	
	</div>
	
	<br/>
	<div>
	<?php if ($is_text_found == 1) { ?>
		<form action="edit_intro_song.php" method="POST">
			<input type="hidden" name="action" id="action" value="update">
			<input type="hidden" name="song_simple_id" id="song_simple_id" value="<?php echo $_GET['song_simple_id']; ?>">
			<input type="hidden" name="language_code" id="language_code" value="<?php echo $_GET['language_code']; ?>">
			<textarea cols="100" rows="10" id="intro_text" name="intro_text"><?php echo $intro_text; ?></textarea><br/>
			<input type="submit" name="submit" id="submit" value="Gem">
		</form>
		<form action="edit_intro_song.php" method="POST">
			<input type="hidden" name="action" id="action" value="delete">
			<input type="hidden" name="song_simple_id" id="song_simple_id" value="<?php echo $_GET['song_simple_id']; ?>">
			<input type="hidden" name="language_code" id="language_code" value="<?php echo $_GET['language_code']; ?>">
			<input type="submit" name="submit" id="submit" value="Slet">
		</form>
	<?php } else { print "<b>Tekster ikke fundet</b>"; } ?>
	</div>
</body>
<?php } ?>