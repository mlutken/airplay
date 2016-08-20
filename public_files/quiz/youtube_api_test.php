<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>MusicQuiz: Youtube simple player test</title>
    <link href="/js/jquery-ui-1.10.2.custom/css/ui-lightness/jquery-ui-1.10.2.custom.css" rel="stylesheet" type="text/css" />
    <link href="/js/jtable.2.3.0/themes/lightcolor/blue/jtable.css" rel="stylesheet" type="text/css" />
    <link href="/css/public_files.css" rel="stylesheet" type="text/css" />
    <script src="/js/jquery-1.9.1.min.js"></script>
    <script src="/js/ckeditor/ckeditor.js" type="text/javascript" ></script>   
    <script src="/js/jquery-ui-1.10.2.custom/js/jquery-ui-1.10.2.custom.min.js"></script>
    <script src="/js/jtable.2.3.0/jquery.jtable.js" type="text/javascript"></script>
    <script src="/js/jeditable.js" type="text/javascript"></script>
    <script src="/js/jquery.autocomplete.js" type="text/javascript"></script>
    <script src="/js/public_files.js" type="text/javascript"></script></head>
<body>
<span >Menu: <a href=/quiztest/index.php />Home</a> </span> 
<select name="select" onchange="selectGotoPage(this)" size="1">
<option value="">--- Menu ---</option>
<option value="youtube_api_test.php">Youtube simple test</option>
<option value="text_quiz_test.php">Text quiz test</option>
</select>

<br /><script>


// Once the api loads call enable the search box.
function handleAPILoaded() {
  $('#search-button').attr('disabled', false);
}

// Search for a given string.
function search() {
  var q = $('#query').val();
  var request = gapi.client.youtube.search.list({
    q: q,
    part: 'snippet'
  });

  request.execute(function(response) {
    var str = JSON.stringify(response.result);
    $('#search-container').html('<pre>' + str + '</pre>');
  });
}


</script>
    <div id="buttons">
      <label> <input id="query" value='cats' type="text"/><button id="search-button"  onclick="search()">Search</button></label>
    </div>
    <div id="search-container">
    </div>
    <script src="https://apis.google.com/js/client.js?onload=googleApiClientReady"></script>


  </body>
</html>