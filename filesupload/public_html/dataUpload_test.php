<html>
<head></head>

<?php 
//$_GET['PROD']
?>

<body>
<h4> File uploads TEST</h4>

<?php
require_once ('SystemSettings.php');
$PROD = "";
if ( array_key_exists ('PROD', $_GET) ) { 
	$val = $_GET['PROD'];
	$PROD = "?PROD=$val";
}
print "<form enctype=\"multipart/form-data\" action=\"upload_test.php$PROD\" method=\"post\">";
?>
	
	<p>
	Select File:
	<input type="file" size="35" name="uploadedfile" /> <br/>
	
	<input type="submit" name="Upload" value="Upload" />
	</p>
</form>
</body>

<?php 
if ( isCLI() ) {
	print "<h3>Is command line</h3>";
}
else {
	print "<h3>Is on webserver</h3>";
}

// print "<pre>\n";
// print_r ( $_SERVER );
// print "</pre>\n";


// <form method="POST" enctype='multipart/form-data' action="upload.cgi">
//    <input type=file name=upload>
//    <input type=submit name=press value="OK">
// </form>
// curl -F upload=@localfilename -F press=Upload http://jordenergiftig.dk.localhost/webservice/dataUpload.php

?>

</html>
