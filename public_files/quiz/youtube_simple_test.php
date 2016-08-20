<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ( __DIR__ . '/../public_files_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('quiztest/classes/PagesCommon.php');

$name = 'Youtube simple player test';
$pc = new PagesCommon();

echo $pc->pageStart("{$name}");

?>
<script>


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


<?php
echo $pc->pageEnd();

?>