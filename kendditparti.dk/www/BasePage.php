<?php
require_once ('www/string_utils.php');
require_once ('www/database_utils.php');

class BasePage
{
    // ------------------------
    // --- Constructor init ---
    // ------------------------
    public function __construct($browser_type = '')
    {
        $this->m_browser_type = $browser_type;
        if ( '' == $this->m_browser_type) $this->m_browser_type = 'desktop';
    }

    // -----------------------------------------------------------
    // --- These functions you will typically want to override ---
    // -----------------------------------------------------------
    public function doGetPageTitle()
    {
        return 'PAGE TITLE OVERRIDE ME';
    }

    public function doGetJsCodeInlinedTop($aPageData)
    {
        return '';
    }
    
    public function doGetCssInline($aPageData)
    {
        return '';
    }
    
    public function doGetCommonPageTop($aPageData)
    {
        return '';
    }



    public function doGetPageContent($aPageData)
    {
        return '';
    }

    public function doGetJsCodeInlinedButtom($aPageData)
    {
        return '';
    }


    // --------------------------------------------------------------------
    // --- Functions that only in rare cases will need to be overridden ---
    // --------------------------------------------------------------------
    public function getHtmlForPage($aPageData)
    {
        $s = '';
        $s .= $this->htmlHeaderStart($aPageData);
        $s .= $this->cssIncludes($aPageData);
        $s .= $this->cssInline($aPageData);
        $s .= $this->jsIncludes($aPageData);
        $s .= $this->htmlBodyStart($aPageData);
        $s .= $this->jsCodeInlinedTop($aPageData);
        $s .= $this->doGetCommonPageTop($aPageData);
        $s .= $this->doGetPageContent($aPageData);
        $s .= $this->jsCodeInlinedButtom($aPageData);
        $s .= $this->htmlBodyEnd($aPageData);
        return $s;
    }

    /** Stylesheet includes. */
    public function cssIncludes($aPageData)
    {
        $browser_type = $this->m_browser_type;
    return <<<HTML
<link rel="stylesheet" type="text/css" href="/css/{$browser_type}/default.css">\n
HTML;
    }

    /** Get inlined css for the top of this page.*/
    public function cssInline ($aPageData)
    {
        $css = $this->doGetCssInline($aPageData);
    return <<<HTML
<style>\n
{$css}
</style>\n
HTML;
    }
    
    /** Javascript includes. */
    public function jsIncludes($aPageData)
    {
    return <<<HTML
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>\n
<script type="text/javascript">var switchTo5x=true;</script>
<script type="text/javascript" src="http://w.sharethis.com/button/buttons.js"></script>
<script type="text/javascript">stLight.options({publisher: "aea12d8a-0659-4adf-b331-2a8f7f3227af", doNotHash: false, doNotCopy: false, hashAddressBar: false});</script>    
HTML;
    }

    /** Get inlined javascript for the top of this page. Typically this will be some functions only used
    on this type of page.*/
    public function jsCodeInlinedTop ($aPageData)
    {
        $jsCode = $this->doGetJsCodeInlinedTop($aPageData);
    return <<<HTML
<script>\n
{$jsCode}
</script>\n
HTML;
    }

    public function doGetBodyAttributes($aPageData)
    {
        return '';
    }
    
    /** Get inlined javascript for the buttom of this page. Typically this will be some initialization function.*/
    public function jsCodeInlinedButtom ($aPageData)
    {
        $jsCode = $this->doGetJsCodeInlinedButtom($aPageData);
    return <<<HTML
<script>\n
{$jsCode}
</script>\n
HTML;
    }


    /** Default HTML header start block.
    \note Most likely you will not need to override this.*/
    public function htmlHeaderStart($aPageData)
    {
        $title = $this->doGetPageTitle();
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">\n
    <title>$title</title>\n
HTML;
    }


    /** Default HTML body start block.
    \note Most likely you will not need to override this. */
    public function htmlBodyStart($aPageData)
    {
        $bodyAttributes = $this->doGetBodyAttributes($aPageData);
    return <<<HTML
</head>\n
<body $bodyAttributes >\n
HTML;
    }


    /** Default HTML body end block.
    \note Most likely you will not need to override this. */
    public function htmlBodyEnd($aPageData)
    {
    return <<<HTML
</body>\n
</html>\n
HTML;
    }

    // -----------------------
    // --- Final functions ---
    // -----------------------


    // -------------------------------------------------------------------------------
    // --- These functions will normally not be overridden (but you can if needed) ---
    // -------------------------------------------------------------------------------


    // ---------------------
    // --- PRIVATE: Data ---
    // ---------------------

    protected $m_browser_type = 'desktop';
}
