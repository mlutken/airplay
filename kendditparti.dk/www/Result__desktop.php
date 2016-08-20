<?php
require_once ('www/BasePage.php');

class Result__desktop extends BasePage
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
        return 'Dit valg resultat';
    }

    public function doGetJsCodeInlinedTop($aPageData)
    {
    }
    
    public function doGetCommonPageTop($aPageData)
    {
    return <<<HTML
HTML;
    }


    public function doGetPageContent($aPageData)
    {
        global $g_rawResult;
        $sHtml = '';
        $sHtml .= '<a href="http://www.testditvalg.dk/">Prøv igen</a><br>';
        $sHtml .= $this->htmlMainResult($aPageData);
        $sHtml .= $this->htmlAnswersChecked($aPageData);
        $sHtml .= '<br><a href="http://www.testditvalg.dk/">Prøv igen</a>';
        
// DEBUG stuff. See page data in json format.         
//         $sHtml .= '<h1>Resultat</h1>';
//         $sHtml .= '<pre>';
//         $sHtml .= json_encode( $aPageData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
//         $sHtml .= '</pre>';
//         $sHtml .= '<h1>Party web</h1>';
//         $sHtml .= '<pre>';
//         $sHtml .= json_encode( getPartyIdToPartyWeb(), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
//         $sHtml .= '</pre>';
//         $sHtml .= '<h1>Parti bogstaver</h1>';
//         $sHtml .= '<pre>';
//         $sHtml .= json_encode( $g_rawResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
//         $sHtml .= '</pre>';
        
        return $sHtml;
    }
    
    public function doGetBodyAttributes($aPageData)
    {
    return <<<ATTRIBUTES
class="redWhiteBlueGradientHorizontal"
ATTRIBUTES;
    }

    public function doGetJsCodeInlinedButtom($aPageData)
    {
        return '';
    }

    
    // --- private helper functions ---
    private function htmlMainResult($aPageData)
    {
        $per_party = $aPageData['results_total']['per_party'];
        $questions_total_answered = $aPageData['results_total']['questions_total_answered'];
        
        $s = '';
        $s .= "<div class=mainResults>\n";
        $s .= "<h3>Her kan du se hvilke partier du er mest enig med</h3>\n";
        $s .= "<table>\n";
        foreach($per_party as $partyId => $answersCount){
            $partyLink = partyIdToPartyWeb($partyId); 
            $partyName = partyIdToPartyName($partyId); 
            $answersPct = round( ((float)$answersCount / (float)$questions_total_answered)*100, 0); 
            $img = "<div class=\"logos\"><img src=\"/css/{$this->m_browser_type}/logo_{$partyId}.png\" title=\"{$partyName}\" /></div>";

            $s .= "<tr>\n";
            $s .= "<td><span class=answersCount> $answersCount</span></td>\n";
            $s .= "<td><span class=answersPct>{$answersPct}%</span></td>\n";
            $s .= "<td>$img</td>\n";
            $s .= "<td><a href='{$partyLink}' class=partyName >{$partyName}</a></td>\n";
            $s .= "</tr>\n";
        }
        $s .= "<tr>\n";
        $s .= "<td colspan=3><span class=answersPct>Spørgsmål total</span></td>\n";
        $s .= "<td><span class=answersCount>{$questions_total_answered}</span></td>\n";
        $s .= "</tr>\n";
        $s .= "</table>\n";
        $s .= "</div>\n";
        
        return $s;
    }

    
    // --- private helper functions ---
    private function htmlAnswersChecked($aPageData)
    {
        $answers_checked = $aPageData['answers_checked'];
        
        $s = '';
        $s .= "<div class=answersChecked>\n";
        $s .= "<h3>Se dine svar fordelt på politikområde og underliggende emner</h3>\n";
        $s .= "<table>\n";
        foreach($answers_checked as $policyGroupId => $questions){
            $s .= "<tr>\n";
            $policyGroupName = policyGroupIdToName($policyGroupId);
            $s .= "<td><span class=policyGroupName> $policyGroupName</span></td>\n";
            $cell = '';
            foreach ($questions as $questionId => $answers) {
                $questionName = questionIdToName($questionId);
                $cell .= "<span class=questionName> $questionName</span><br>\n";
                $cell .= "<ul>\n";
                foreach ($answers as $partyId => $questionText) {
                    $li = '';
                    $partyLetter = partyIdToLetter($partyId);
                    $li .= "<span class=partyLetter>$partyLetter</span> - ";
                    $li .= "$questionText";
                    $cell .= "<li>{$li}</li>\n";
                }
                $cell .= "</ul>\n";
            }

            $s .= "<td>$cell</td>\n";
            $s .= "</tr>\n";
        }
        $s .= "</table>\n";
        $s .= "</div>\n";
        
        return $s;
    }
    
}
