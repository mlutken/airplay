<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" 
           xml:lang="en" lang="en">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Google image search artist sandbox 2</title>
    <script src="https://www.google.com/jsapi"></script>
<!--     <script src="youtube_utils.js"></script> -->
    <script type="text/javascript">

        
    </script>

</head>

<body id="page">
    <div>
        <input name='test' type='submit' value='Next image' onclick='showNextArtistImage()' />
        <br />
        <img id='artistImageID' src='' width=200  onclick='showNextArtistImage()' ></img>
        <br />
    </div>
    <?php  
    echo addArtistImage( 'artistImageID', 'lady gaga' );
    ?>
    
    
</body>
        
</html>


<?php

function addArtistImage($artistImageID, $sSearchString )
{
$s=
<<<SCRIPT
<script type="text/javascript">
    var g_allArtistImages           = [];
    var g_currentArtistImageIndex   = -1;
        
    
    function showNextArtistImage()
    {
        if ( g_currentArtistImageIndex == -1) return;
        var iNumVideos = g_allArtistImages.length;
        g_currentArtistImageIndex = (g_currentArtistImageIndex + 1) % iNumVideos
        console.log('showNextArtistImage: ' + g_currentArtistImageIndex);

        var artistImage = document.getElementById('$artistImageID');
        artistImage.src = g_allArtistImages[g_currentArtistImageIndex];
    }
    
 
    function parseGoogleImageDataAndLoadFirst(data) 
    {
        if (data.results && data.results.length > 0) {
            var results = data.results;
            var result = results[0];

            var artistImage = document.getElementById('$artistImageID');
            artistImage.src = result.tbUrl;
            
            // TODO: If we need to have more images so that users can help us find one that is good 
            g_currentArtistImageIndex = 0;
            for (var i = 0; i < results.length; i++) {
                var result = results[i];
                g_allArtistImages[i] = result.tbUrl;
            }
        }
    }
        
        
    google.load('search', '1');

    function OnLoad() {
        var imageSearch = new google.search.ImageSearch();

        // Restrict image size: IMAGESIZE_SMALL , IMAGESIZE_MEDIUM, IMAGESIZE_LARGE, IMAGESIZE_EXTRA_LARGE
        imageSearch.setRestriction(google.search.ImageSearch.RESTRICT_IMAGESIZE,
                                    google.search.ImageSearch.IMAGESIZE_LARGE); 

        imageSearch.setSearchCompleteCallback(this, parseGoogleImageDataAndLoadFirst, [imageSearch]);
        imageSearch.execute('$sSearchString');
    }
    google.setOnLoadCallback(OnLoad);    
</script>
SCRIPT;
    return $s;
}


?>