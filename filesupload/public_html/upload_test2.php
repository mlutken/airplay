<?php

    $usernameOrEmail = $_POST['usernameOrEmail'];
    $PasswordMd5     = $_POST['PasswordMd5'];
    $myfield1   = $_POST['myfield1'];
    $myfield2   = $_POST['myfield2'];
//    if ($usernameOrEmail == '' ) $usernameOrEmail = $_GET['usernameOrEmail'];

    echo "Hey '$usernameOrEmail' with hashed password: '$PasswordMd5'<br>\n";
    echo "Test fields: myfield1 => '$myfield1', myfield2 => '$myfield2'<br>\n";
    $target = "uploads/";
    $target = $target . basename( $_FILES['uploadedfile']['name']) ;
    $ok=1;

    //This is our size condition
    if ($uploaded_size > 10000000)
    {
        echo "Your file is too large.<br>";
        $ok=0;
    }

    //This is our limit file type condition
    if ($uploaded_type =="text/php")
    {
        echo "No PHP files<br>";
        $ok=0;
    }

    //Here we check that $ok was not set to 0 by an error
    if ($ok==0)
    {
        echo "Sorry your file was not uploaded";
    }

    //If everything is ok we try to upload it
    else
    {
        if(move_uploaded_file($_FILES['uploadedfile']['tmp_name'], $target))
        {
            echo "Hello '$usernameOrEmail'. The file '". basename( $_FILES['uploadedfile']['name']). "' has been uploaded successfully!\n<br>";
        }
        else
        {
            echo "Sorry '$usernameOrEmail', there was a problem uploading your file.";
        }
    }
?>
