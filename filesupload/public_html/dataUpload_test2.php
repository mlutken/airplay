<form enctype="multipart/form-data" action="upload_test2.php" method="POST">
    User: <input name="usernameOrEmail" type="text" /><br />
    Hashed password: <input name="PasswordMd5" type="text" /><br />
    Test field 1: <input name="myfield1" type="text" /><br />
    Test field 2: <input name="myfield2" type="text" /><br />
	Please choose a file: 
	<input name="uploadedfile" type="file" /><br />
	<input type="submit" value="Upload" />
</form>
