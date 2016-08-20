<?php
require_once ('www/BasePage.php');

class MainPage__desktop extends BasePage
{
    // ------------------------
    // --- Constructor init ---
    // ------------------------
    public function __construct($browser_type)
    {
        parent::__construct( $browser_type ); 
    
    }

    // -----------------------------------------------------------
    // --- These functions you will typically want to override ---
    // -----------------------------------------------------------
    public function doGetPageTitle()
    {
        return 'Test Dit Valg';
    }

    public function doGetJsCodeInlinedTop($aPageData)
    {
        return '';
    }
    
    public function doGetCssInline($aPageData)
    {
    return <<<CSS
    html {
        margin:0; 
        padding:0; 
        width:100%; 
        height:100%
    }

    body  {
        margin:0; 
        padding:0; 
        width:100%; 
        height:100%;
    }

CSS;
    }
    
    public function doGetCommonPageTop($aPageData)
    {
      
        $s = '';
        return $s;
     }


    public function doGetPageContent($aPageData)
    {
    return <<<HTML
    <div class=mainPage>\n
    <table style="height:100%;width:100%; position: absolute; top: 0; bottom: 0; left: 0; right: 0;" >\n
    <tr>\n
        <td style="width:16.67%;max-height:30%" >\n
            <div class="logos"><a href="http://socialdemokraterne.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_A.png" title="Socialdemokratiet" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%" >\n
            <div class="logos"><a href="https://www.radikale.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_B.png" title="Det Radikale Venstre" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%" >\n
            <div class="logos"><a href="http://sf.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_F.png" title="SF / Socialistisk Folkeparti" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%">\n
            <div class="logos"><a href="http://enhedslisten.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_OE.png" title="Enhedslisten" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%">\n
            <div class="logos"><a href="http://alternativet.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_AA.png" title="Alternativet" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%">\n
            <div class="logos"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_blank.png" /></div>
        </td>\n
    </tr>\n
    <tr>\n
        <td>\n
        <span  > 
            <div class="center" style="font-size: 3.5vh;" ><h4>Test Dit Valg</h4></div>
            <a style="font-size: 1.5vh;" href="nailto:uh@airplaymusic.dk" >Kontakt Test Dit Valg:  Ulrik Hermansen</a>  
        </span>
        </td>\n
        <td colspan=4  style="width:100%;max-height:30%;padding:10;margin:10" >\n
        <span style="font-size: 1.99vh;" >
            Her kan du sammenligne de politiske partiers holdninger til 100+ politiske emner, og hermed Teste Dit Valg til folketingsvalget torsdag den 18. juni.
            Idéen med Test Dit Valg er at give dig som vælger en objektiv og upartisk vejledning til
            hvilket politisk parti der bedst matcher dine holdninger og interesser.
            Test Dit Valg er derfor funderet på følgende:
            <ul>
            <li>Partiernes holdninger primært hentet direkte og uredigeret fra partiernes hjemmesider</li> 
            <li>Holdninger "anonymiseret" så du ikke kan se, hvilket parti mener hvad - før til sidst</li>
            <li> Test Dit valg dækker <i>alle</i> politikområder og mere end 100 konkrete politiske emner</li>
            <li>Vælg alle eller blot de politikområder som interesserer dig. Det er <b>Dit Valg</b></li>
            </ul>
            </span>
        </td>\n
        <td style="width:16.67%;max-height:30%" >\n
            <div class="logos"><a href="http://www.testditvalg.dk/limesurvey-2.0.5/index.php?r=survey/index/sid/266595/lang/da"><img style="width:100%" src="/css/{$this->m_browser_type}/start_button.png" title="Start Test Dit Valg" /></a></div>
        </td>\n
    </tr>\n
    <tr>\n
        <td style="width:16.67%;max-height:30%">\n
            <div class="logos"><a href="http://www.konservative.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_C.png" title="Det Konservative Folkeparti" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%" >\n
            <div class="logos"><a href="http://kd.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_K.png" title="Kristendemokraterne" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%" >\n
            <div class="logos"><a href="http://www.danskfolkeparti.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_O.png" title="Dansk Folkeparti" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%">\n
            <div class="logos"><a href="http://www.venstre.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_V.png" title="Venstre" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%" >\n
            <div class="logos"><a href="https://www.liberalalliance.dk/"><img style="width:100%" src="/css/{$this->m_browser_type}/logo_I.png" title="Liberal Alliance" /></a></div>
        </td>\n
        <td style="width:16.67%;max-height:30%" >\n
            <span class='st_sharethis_vcount' displayText='ShareThis'></span>
       </td>\n
    </tr>\n
    </table>\n
    </div>\n
HTML;
    }

/*
             <span class='st_facebook_vcount' displayText='Facebook'></span>
             <span class='st_twitter_vcount' displayText='Tweet'></span>
             <span class='st_linkedin_vcount' displayText='LinkedIn'></span>        


*/
    
    public function doGetBodyAttributes($aPageData)
    {
    return <<<ATTRIBUTES
class="redWhiteBlueGradientVertical"
ATTRIBUTES;
    }

    public function doGetJsCodeInlinedButtom($aPageData)
    {
        return '';
    }

}
