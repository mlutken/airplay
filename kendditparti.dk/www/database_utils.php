<?php
global $g_db;
global $g_survey_table;
global $g_result_remove_prefix;
global $g_survey_groups;


$g_db                       = new PDO('mysql:host=localhost;dbname=kendditparti;charset=utf8', 'root', 'Ospekos#27');
//$g_survey_table             = 'survey_527654';
$g_survey_table             = 'survey_266595';



/** 
Get the raw DB results from one survey. 
Looks like this:
{
    "id": "1",
    "token": null,
    "submitdate": "2015-05-27 15:47:15",
    "lastpage": "27",
    "startlanguage": "da",
    "startdate": "2015-05-27 15:46:34",
    "datestamp": "2015-05-27 15:47:15",
    "ipaddr": "87.55.213.77",
    "refurl": "http://airplaymusic.dk:81/limesurvey-2.0.5/index.php?r=admin/survey/sa/view/surveyid/527654",
    "527654X50X1730DAGP1A": "",
    "527654X50X1730DAGP1B": "Y",
    "527654X50X1730DAGP1C": "",
    "527654X50X1730DAGP1F": "",
    "527654X50X1730DAGP1I": "",
....
}
*/
function getSurveyRawResults($id) 
{
    global $g_db, $g_survey_table;
    $stmt = $g_db->query("SELECT * FROM ${g_survey_table} WHERE id=${id}");
    return $stmt->fetchAll(PDO::FETCH_ASSOC)[0];
}

/** Parse one question line from the raw results and fills in group, question, and party. 
So for example:
"527654X50X1730DAGP1A" => group = 1, question = DAGP, party = A
*/
function parseQuestion($question_raw, &$group, &$question, &$party) 
{
    $group = '';
    $question = ''; 
    $party = '';
    $p_party_start = -1;
    $p_group_start  = -1;
    $p_question_start  = -1;
    $i=strlen($question_raw)-1;
    while ($i>-1 && !ctype_digit ( $question_raw[$i])) $i--;
    if($i>-1) $p_party_start = $i+1;
    while ($i>-1 && ctype_digit ( $question_raw[$i])) $i--;
    if($i>-1) $p_group_start = $i+1;
    while ($i>-1 && !ctype_digit ( $question_raw[$i])) $i--;
    if($i>-1) $p_question_start = $i+1;
    if($p_party_start>-1) $party = substr($question_raw, $p_party_start);
    if($p_group_start>-1) $group = substr($question_raw, $p_group_start, $p_party_start - $p_group_start);
    if($p_question_start>-1) $question = substr($question_raw, $p_question_start, $p_group_start - $p_question_start);
//         print "p_answer_start: $p_answer_start, p_group_start: $p_group_start, p_question_start: $p_question_start<br>";
}

/**
Parses the raw database results from one survey into a format like this example:
{
    "answers_checked": {
        "1": {
            "DAGP": {
                "B": 1,
                "V": 1,
                "OE": 1
            }
        },
        "2": {
            "ALME": {
                "A": 1
            },
            "LEJE": {
                "OE": 1
            }
        },
        "21": {
            "FASO": {
                "B": 1,
                "K": 1
            }
        },
        "49": {
            "FOAE": {
                "C": 1,
                "V": 1
            }
        }
    },
    "questions_all": {
        "1": {
            "DAGP": 1,
            "FAGB": 1,
            "EFTE": 1,
            "PENS": 1,
            "UDEN": 1
        },
        "2": {
            "ALME": 1,
            "LEJE": 1,
            "ANDE": 1,
            "UDSA": 1
        },
....
}

This can then be processed by calculateSurveyResults()
*/

function parseSurveyResults($raw_results) 
{
    global $g_db;
    $answers_checked = [];
    $questions_all = [];
    
    foreach($raw_results as $question_raw => $answer) {
        $group = '';
        $question = '';
        $party  = '';
        parseQuestion($question_raw, $group, $question, $party);
        if ("77" == $group) continue; // Afslutning - Ikke politik spm
        if ($group != '' && $question != '' && $party != '') {
            $questions_all[$group][$question] = 1;
            if ('Y' == $answer) {
                $question_text =  getQuestionText($group, $question, $party);
                $answers_checked[$group][$question][$party] = $question_text;
            }
        }
    }
    $res = [ 'answers_checked' => $answers_checked, 'questions_all' => $questions_all ];
    
    return $res;
}


/**
Sort results helper function
*/
function sortResultsDescending($lhs, $rhs) {
    return ($lhs < $rhs);
}


/**
"1": {
    "B": 3,
    "V": 1,
    "OE": 1,
    "TOTAL": 5
    },
"2": {
    "B": 1,
    "V": 1,
    "TOTAL": 2
    },
    


*/

function calculateSurveyResults($parsed_results) 
{
    global $g_db;
    $answers_checked = $parsed_results['answers_checked'];

    $results_per_group = [];
    $results_total_per_party = [];
    foreach (getAllPartyIds() as $partyId) $results_total_per_party[$partyId] = 0;
    $results_total = [];
    
    $questions_total_answered = 0;
    
    foreach($answers_checked as $group => $question) {
        $questions_per_goup_answered = 0;
        $results_per_group_per_party = [];
        foreach ($question as $question => $parties_checked) {
            $questions_per_goup_answered++;
            $questions_total_answered++;
            foreach ($parties_checked as $party => $val_not_used_always_1) {
                
                // Update per group per party
                $val = @$results_per_group_per_party[$party];
                if ('' == $val) $val = (int)1;
                else            $val++;
                $results_per_group_per_party[$party] = $val;

                // Update total per party
                $val = @$results_total_per_party[$party];
                if ('' == $val) $val = (int)1;
                else            $val++;
                $results_total_per_party[$party] = $val;
            }
        }
        uasort($results_per_group_per_party, 'sortResultsDescending');
        $results_per_group[$group]['per_party'] = $results_per_group_per_party;
        $results_per_group[$group]['questions_per_goup_answered'] = $questions_per_goup_answered;
    }
    // 
    uasort($results_total_per_party, 'sortResultsDescending');
    $results_total['per_party'] = $results_total_per_party;
    $results_total['questions_total_answered'] = $questions_total_answered;
    $res = [ 'results_total' => $results_total, 
             'results_per_group' => $results_per_group,
             'answers_checked' => $answers_checked ];
    
    return $res;
}


function getSurveyResults($saveid)
{
    $raw_results = getSurveyRawResults($saveid);

    $parsed_results = parseSurveyResults($raw_results);
    $results = calculateSurveyResults($parsed_results);
    
    return $results;
}


/** Get associative array connecting policy group IDs with theier associated names.
{
"1": "Arbejdsmarked",
"2": "Bolig",
...
},

*/
function getPolicyGroupIdToNameArray()
{
    global $g_db, $g_survey_table;
    $stmt = $g_db->query("SELECT gid, group_name FROM groups");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $group_to_name = [];
    foreach ($all as $pair){
        $group_to_name[$pair['gid']] = $pair['group_name'];
    }
    
    return $group_to_name;
}

function policyGroupIdToName($policyGroupId)
{
    static $lookup = null;
    if (!$lookup) {
        $lookup = getPolicyGroupIdToNameArray();
    }
    return $lookup[$policyGroupId];
}


/** Get associative array connecting party letter (ID) to party name.
{
"A": "Socialdemokratiet",
"": "Bolig",
...
"AA": "Alternativet",
},

*/
function getPartyIdToPartyNames()
{
    global $g_db;
    $stmt = $g_db->query("SELECT code, title FROM labels");
    $all = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $letter_to_name = [];
    foreach ($all as $pair){
        $letter_to_name[$pair['code']] = $pair['title'];
    }
    
    return $letter_to_name;
}

function partyIdToPartyName($party_id)
{
    static $lookup = null;
    if (!$lookup) {
        $lookup = getPartyIdToPartyNames();
    }
    return $lookup[$party_id];
}



/** Get array URLs to each partys webpage.*/
function getPartyIdToPartyWeb()
{
return [
    'C' => 'http://www.konservative.dk/',
    'I' => 'https://www.liberalalliance.dk/',
    'V' => 'http://www.venstre.dk/',
    'O' => 'http://www.danskfolkeparti.dk/',
    'B' => 'https://www.radikale.dk/',
    'K' => 'http://kd.dk/',
    'A' => 'http://socialdemokraterne.dk/',
    'F' => 'http://sf.dk/',
    'OE' => 'http://enhedslisten.dk/',
    'AA' => 'http://alternativet.dk/' 
    ];
}

function partyIdToPartyWeb($party_id)
{
    static $lookup = null;
    if (!$lookup) {
        $lookup = getPartyIdToPartyWeb();
    }
    return $lookup[$party_id];
}


function getQuestionIdToNames()
{
    return [
        "DAGP" => "Dagpenge  (dagpengeperiodens varighed, genoptjening dagpengeret, dagpengesats)",
        "DARA" => "Rådighedsregler",
        "FAGB" => "Fagbevægelsen  (eksklusivaftaler)",
        "EFTE" => "Efterløn",
        "PENS" => "Pensionsalder",
        "UDEN" => "Udenlandsk arbejdskraft  (adgang til det danske arbejdsmarked, social dumpning)",
        "ALME" => "Almennyttige boliger  (investering/udvikling almennyttige boliger, privat ejerskab)",
        "LEJE" => "Lejeloven",
        "ANDE" => "Andelsboliger",
        "UDSA" => "Udsatte boligområder",
        "DAGI" => "Daginstitutioner  (normeringer, tilskud pasning egne børn)",
        "ABOR" => "Abort  (abortgrænse på 12 uger)",
        "BOER" => "Børnecheck  (indkomstbetinget, adgang for EU-borgere)",
        "BARN" => "Barnløshed  (kunstig befrugtning)",
        "LAND" => "Landbrug  (husdyrproduktion, dyretransport)",
        "ENAF" => "Energiafgifter",
        "VEDV" => "Vedvarende energi  (grøn omstilling, vindmøller, solvarme)",
        "BANK" => "Bankunion",
        "EUPO" => "Europol",
        "PAEU" => "Patentdomstol",
        "EURF" => "Euro  (forbehold)",
        "FORF" => "Forsvarsforbehold ",
        "RETF" => "Retsforbehold ",
        "SOEU" => "Sommerhusregel",
        "UDVI" => "Udvidelse EU-medlemslande",
        "EUSF" => "EUŽs fremtid",
        "UDER" => "Udenlandsk arbejdskraft",
        "VAEE" => "Vækst/udvikling erhverslivet (prioritering brancher, globalisering) ",
        "RAME" => "Rammevilkår  (konkurrenceevne, administrative byrder, skatter og afgifter, erhversstøtte)",
        "IVAE" => "Iværksætteri / selvstændighedskultur",
        "UDFE" => "Udflytning arbejdspladser",
        "FARF" => "Farlige stoffer og kemikalier",
        "MAEF" => "Mærkning forbrugsvarer",
        "ALLI" => "Alliancer  (NATO, EU-hær)",
        "DANF" => "Dansk krigsdeltagelse  (Afghanistan, Irak)",
        "HJEF" => "Hjemmeværnet",
        "FOST" => "Forsvarets størrelse/ressourcer",
        "VAEF" => "Værnepligt",
        "INDH" => "Indsats over for handicappede (offentlig støtte, arbejde/uddannelse)",
        "AFFL" => "Afviste asylansøgere",
        "ASYA" => "Asyl ansøgningsproces",
        "ASAR" => "Asylansøgere arbejde/uddannelse",
        "BAEF" => "Bådflygtninge Middelhavet",
        "FNFL" => "FNŽs flygtningekonvention  (kvoteflygtninge)",
        "ANFL" => "Antal flygtninge/indvandrere i Danmark  (vs. hjælp i flytninges nærområder)",
        "FAMF" => "Familiesammenføring  (24-års reglen, tilknytningskrav, pointsystem)",
        "HJEF" => "Hjemsendelse / udvisning  (mislykket integration, kriminelle udlændinge, illegale indvandrere)",
        "GETF" => "Ghettoer / paralelsamfund",
        "VELF" => "Vellykket integration",
        "OPHF" => "Opholdstilladelse  (flygtninge, greencard/jobkortordning, visum)",
        "INFF" => "Indfødsret (statsborgerskab)",
        "SOCF" => "Sociale ydelser  (starthjælp/integrationsydelse, udlændiges adgang til dagpenge/kontanthjælp...)",
        "COKL" => "CO2-/drivhusgasser / fossile brændstoffer reduktion",
        "DAKL" => "Dansk / EU enegang vs. øvrige verden",
        "NAKL" => "Natur",
        "BIKU" => "Biblioteker",
        "DAKU" => "Dansk kultur",
        "IDKU" => "Idræt",
        "STKU" => "Statslig kulturstøtte  (kunststøtte, teatre, museer, orkestre, kulturarvsinstitutioner, fortidsminder, private sponsorater)",
        "MEKU" => "Medielicens",
        "OPKU" => "Ophavsrettigheder og pirateri",
        "PUKU" => "Public service  (Danmarks Radio, TV2) ",
        "MILA" => "Miljø  (gødskning, sprøjtemidler)",
        "LALA" => "Landbrugsstøtte",
        "OELA" => "Økologi",
        "BALI" => "Barselsorlov (øremærket barsel)",
        "KOLI" => "Kønskvoter (kvinder i bestyrelser, ledelse...)",
        "SELI" => "Seksuel ligestilling  (registrerede partnerskaber, Hate Crimes)",
        "RELO" => "Retssikkerhed  (borgernes retsstilling, retsafgifter, retshjælp)",
        "UDLO" => "Ungdomskriminalitet  (kriminelle lavalder, SSP-samarbejde)",
        "OVLO" => "Overvågning",
        "POLO" => "Politi  (ressourcer, prioritering...)",
        "FRLO" => "Fri hash",
        "FALO" => "Fængsler",
        "STLO" => "Strafferammer",
        "PRLO" => "Prostitution",
        "KOOF" => "Kommuner / regioner  (kommunalreform, kommunal selvstyre)",
        "VAOF" => "Vækst/størrelse offentlige sektor",
        "LOPO" => "Politikeres løn / pension ",
        "GRPO" => "Grundloven",
        "OFPO" => "Offentlighedsloven",
        "PAPO" => "Partistøtte",
        "STPO" => "Stemmeret / valgret",
        "FORE" => "Folkekirke  (kirke og stat)",
        "RERE" => "Religionsfrihed",
        "RLRE" => "Ret til at bære religiøse symboler",
        "FISK" => "Finansskat  (Tobin-skat, transaktionsskat)",
        "BOSK" => "Boligjobordning (håndværkerfradrag)",
        "SESK" => "Selskabsskat",
        "BLSK" => "Boligskat",
        "BUSK" => "Bundskat  (beskæftgelsesfradrag)",
        "FOSK" => "Formueskat",
        "RESK" => "Registreringsafgift biler",
        "SKSK" => "Skattetryk",
        "TOSK" => "Topskat",
        "KOSO" => "Kontanthjælp  (ydelse, kontanthjælpsloft, gensidig forsørgerpligt)",
        "FASO" => "Fattige i Danmark  (fattigdomsydelse, fattigdomsgrænse)",
        "MISO" => "Misbrugere  (narkomaner, alkoholikere)",
        "BRSU" => "Brugerbetaling",
        "BESU" => "Behandlingsgaranti",
        "FRSU" => "Frit sygehusvalg  (privathospitaler)",
        "AKSU" => "Aktiv dødshjælp",
        "NASU" => "Narkomaner  (behandling, fixerum, lægeordineret heroin)",
        "ORSU" => "Organdonation",
        "PSSU" => "Psykiatri",
        "RYSU" => "Rygning",
        "KOTR" => "Kollektiv trafik",
        "TOTR" => "Tog",
        "VETR" => "Vejnet  (trængsel, roadpricing, vejskat)",
        "DIUD" => "Differentieret undervisning  (inklusion)",
        "HEUD" => "Helhedsskole",
        "SKUD" => "Skolereform",
        "FOUD" => "Forskning  (investering / prioritering, basismidler fri forskning)",
        "GYUD" => "Gymnasielle adgangskrav ",
        "PRUD" => "Praktikpladser",
        "FRUD" => "Friskole / privatskole  /efterskole",
        "SUUD" => "SU",
        "FNUD" => "FN",
        "PAUD" => "Palæstina",
        "PRUD" => "Prioritering udviklingsbistand",
        "STUD" => "Størrelse udviklingsbistand",
        "FOAE" => "Folkepension  (ældrecheck)"
    ];
}


/** Question ID to question name
\todo Implement me!*/
function questionIdToName($id)
{
    static $lookup = null;
    if (!$lookup) {
        $lookup = getQuestionIdToNames();
    }
    return $lookup[$id];
}


/** Get array URLs to each partys webpage.*/
function getPartyIdToPartyLetter()
{
return [
    'C' => 'C',
    'I' => 'I',
    'V' => 'V',
    'O' => 'O',
    'B' => 'B',
    'K' => 'K',
    'A' => 'A',
    'F' => 'F',
    'OE' => 'Ø',
    'AA' => 'Å' 
    ];
}

function partyIdToLetter($partyId)
{
    static $lookup = null;
    if (!$lookup) {
        $lookup = getPartyIdToPartyLetter();
    }
    return $lookup[$partyId];
}



/** Get array URLs to each partys webpage.*/
function getAllPartyIds()
{
return [
    'C',
    'I',
    'V',
    'O',
    'B',
    'K',
    'A',
    'F',
    'OE',
    'AA' 
    ];
}


/** Lookup question text.
*/
function getQuestionText($group_id, $question, $party)
{
    global $g_db;
    $title = "${question}${group_id}${party}";
    $stmt = $g_db->query("SELECT question FROM questions WHERE title = '{$title}'");
    $question_text = $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['question'];
    return $question_text;
}


