<?php


function getArtistIdFromURL($dbArtist = null )
{
    $id = intval($_GET['artist_id']);
    if ( 0 == $id && null != $dbArtist ) {
        // Try lookit up
    }
    
    return $id;
}

function getQuizThemeIdFromURL($dbQuizTheme = null )
{
    $id = intval($_GET['quiz_theme_id']);
    if ( 0 == $id && null != $dbQuizTheme ) {
        // Try lookit up
    }
    
    return $id;
}



function getItemBaseIdFromURL($dbItemBase = null )
{
    $id = intval($_GET['item_base_id']);
    if ( 0 == $id && null != $dbItemBase ) {
        // Try lookit up
    }
    
    return $id;
}

function getCurrentArticleLanguage()
{
    $languageCode  = $_SESSION['articleLanguageCode'];
    if ( '' == $languageCode ) $languageCode = 'da';
    return $languageCode;
}

?>