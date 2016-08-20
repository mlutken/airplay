<?php
require_once ('quiz/quiz_utils.php');


/** Retrieves partner settings (associative array) from _GET paramters */
function getPartnerSettingsFrom_GET()
{
    $partner_name   = "airplaymusic";
    $page_title     = "Music Quiz";
    
    // Set some default values
    $partner_id = 0;
    $css_path = $partner_name;
    $img_path = "/img";
    

    
    // Osv. tilføj flere ting hen af vejen.
    // Vi kan f.eks. sige at hvis der findes et partner_id i parametrene så bruger man den
    // Noget i stil med nedenstående ... :
//     if ( (int)$_GET['partner_id'] != 0 ) {
//         $fac = new MusicDatabaseFactory();
//         $dbQuizPartnerData = $fac->createDbInterface('QuizPartnerData'); 
//         $partner_name = $dbQuizPartnerData->IDToName($partner_id);
//         ...
//         Get more stuff from DB, like img_path, css_path (if different from default) etc.
//     
//     }
    
    return array (
          'partner_name'    => $partner_name
        , 'partner_id'      => $partner_id
        , 'css_path'        => $css_path
        , 'img_path'        => $img_path
        , 'page_title'      => $page_title
    );
}


/** Convert partner settings (associative array) javascript object string. */
function partnerSettingsToJavascript($aPartnerSettings)
{
    $s = '';
    $s .= '{';
    
    $i = 0;
    foreach ( $aPartnerSettings as $var => $value ) {
        if ( $i > 0 ) $s .= ", ";
        $s .= "{$var}: '{$value}' ";
        $i++;
    }
    $s .= '}';
    return $s;
}


?>