<?php
require_once ('utils/general_utils.php');
require_once ('utils/string_utils.php');
require_once ('db_manip/MusicDatabaseFactory.php');
require_once ('db_manip/QuizManip.php');

// -------------------------------
// --- Quiz save/load handlers ---
// -------------------------------

/** Save handler function. Use for example like:
\code
echo quizSaveHandler($_POST);
\endcode
*/
function quizSaveHandler($aQuizParams)
{
	dbgWritePostGetSession("/tmp/dbgWritePost_quizSaveHandler.txt");  // For debugging help look here!
	$res = 0;
	
	$fac = new MusicDatabaseFactory();
	$dbQuizData = $fac->createDbInterface('QuizData'); 

	//DB aData
	$aData = array(
		  'quiz_name'		=> $aQuizParams['quiz_name']
		, 'quiz_keywords'	=> $aQuizParams['quiz_keywords']
		, 'quiz_json'		=> json_encode( $aQuizParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
		, 'author_user_id'	=> $aQuizParams['author_user_id']
		, 'author_email'	=> $aQuizParams['author_email']
		, 'author_fb_id'	=> $aQuizParams['author_fb_id']
	);
 	
 	$quiz_id = $dbQuizData->toID($aData);
 	if ( 0 == $quiz_id ) {
 		$quiz_id = $dbQuizData->newItem($aData);
 	}
 	$aData['quiz_id'] = $quiz_id;
 	$res = $dbQuizData->updateBaseData($aData);

	return $res;
}


/** Load/autocreate handler function. Use for example like:
\code
echo quizLoadHandler($_POST);
\endcode
*/
function quizLoadHandler($aQuizParams)
{
	$aQuiz = array();

	$quiz_json = quizNoQuiz();

	$fac = new MusicDatabaseFactory();
	$dbQuizData = $fac->createDbInterface('QuizData');

	$quiz_id 	= $aQuizParams['quiz_id'];
	$quiz_name	= $aQuizParams['quiz_name'];

	if ( $quiz_id < 1 ) $quiz_id = $dbQuizData->nameToIDSimple($quiz_name);

	if ( $quiz_id > 0 ) {
		$aBaseData = $dbQuizData->getBaseData($quiz_id);
		$quiz_json = $aBaseData['quiz_json'];
	}

	if ( 'GUESS_SONG_PLAYING' == $aQuizParams['auto_gen_quiz_type'] ) {
		$aQuiz = quizAutoGenerate_GUESS_SONG_PLAYING($aQuizParams);
		$dbQuizData->autoSaveNewQuiz($aQuiz);
		$quiz_json = json_encode( $aQuiz, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
	}

	dbgWritePostGetSessionData($aQuiz, "/tmp/dbgWritePost_quizLoadHandler.txt");  // For debugging help look here!

	//$quiz_json = quizGetTheme_TEST ();

	return $quiz_json;
}

// ------------------------------------
// --- QuizTheme save/load handlers ---
// ------------------------------------

/** Quiz theme Save handler function. Use for example like:
\code
echo quizThemeSaveHandler($_POST);
\endcode
*/
function quizThemeSaveHandler($aQuizThemeParams)
{
    dbgWritePostGetSession("/tmp/dbgWritePost_quizThemeSaveHandler.txt");  // For debugging help look here!
    $res = 0;
    
    $fac = new MusicDatabaseFactory();
    $dbQuizThemeData = $fac->createDbInterface('QuizThemeData'); 

    //DB aData
    $aData = array(
          'quiz_theme_name'         => $aQuizThemeParams['quiz_theme_name']
        , 'level1_kategory_name'    => $aQuizThemeParams['level1_kategory_name']
        , 'level2_kategory_name'    => $aQuizThemeParams['level2_kategory_name']
        , 'country_code'            => $aQuizThemeParams['country_code']
        , 'theme_keywords'          => $aQuizThemeParams['theme_keywords']
        , 'theme_json'              => json_encode( $aQuizThemeParams, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES )
    );
    
    $quiz_theme_id = $dbQuizThemeData->toID($aData);
    if ( 0 == $quiz_theme_id ) {
        $quiz_theme_id = $dbQuizThemeData->newItem($aData);
    }
    $aData['quiz_theme_id'] = $quiz_theme_id;
    $res = $dbQuizThemeData->updateBaseData($aData);

    return $res;
}



/** Quiz theme load handler function. Use for example like:
\code
echo quizThemeLoadHandler($_POST);
\endcode
*/
function quizThemeLoadHandler($aQuizThemeParams)
{
    $aQuizTheme = array();

    $theme_json = "{ \"quiz_theme_name\": \"_NO_QUIZ_THEME_\" }";

    $fac = new MusicDatabaseFactory();
    $dbQuizThemeData = $fac->createDbInterface('QuizThemeData');

    $quiz_theme_id    = $aQuizThemeParams['quiz_theme_id'];
    $quiz_theme_name  = $aQuizThemeParams['quiz_theme_name'];

    if ( $quiz_theme_id < 1 ) $quiz_theme_id = $dbQuizThemeData->nameToIDSimple($quiz_theme_name);

    if ( $quiz_theme_id > 0 ) {
        $aBaseData = $dbQuizThemeData->getBaseData($quiz_theme_id);
        $theme_json = $aBaseData['theme_json'];
    }

    dbgWritePostGetSessionData($aQuizTheme, "/tmp/dbgWritePost_quizThemeLoadHandler.txt");  // For debugging help look here!

    //$theme_json = quiz_themeGetTheme_TEST ();

    return $theme_json;
}

// ---------------------------------------------------------
// --- Quiz current quiz save/load from SESSION handlers ---
// ---------------------------------------------------------

/** Save current quiz (in SESSION) handler function. It is used to keep 
current/last completed quiz across browser reloads. Use for example like:
\code
echo quizSaveCurrentHandler($_POST);
\endcode
*/
function quizSaveCurrentHandler($aQuiz)
{
    $quiz_json = json_encode( $aQuiz, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
    dbgWritePostGetSessionString($quiz_json, "/tmp/dbgWritePost_quizSaveCurrentHandler.txt");  // For debugging help look here!

    session_start();
    $_SESSION["g_quiz"] = $quiz_json;
    return 1;
}

/** Load current quiz (in SESSION) handler function. It is used to keep 
current/last completed quiz across browser reloads. Use for example like:
\code
echo quizLoadCurrentHandler();
\endcode
*/
function quizLoadCurrentHandler()
{
    session_start();
    $quiz_json = $_SESSION["g_quiz"];
    dbgWritePostGetSessionString($quiz_json, "/tmp/dbgWritePost_quizLoadCurrentHandler.txt");  // For debugging help look here!

    return $quiz_json;
}

// -------------------------------
// --- Quiz highscore handlers ---
// -------------------------------

/** Highscore save handler function. Use for example like:
\code
echo quizHighScoreSaveHandler($_POST);
\endcode
\todo Implement me!
*/
function quizHighScoreSaveHandler($aHighScoreData)
{
    dbgWritePostGetSession("/tmp/dbgWritePost_quizHighScoreSaveHandler.txt");  // For debugging help look here!

    $res = 0;

    $fac = new MusicDatabaseFactory();
    //$dbQuizScoreData = $fac->createDbInterface('QuizScoreData'); 

    return $res;
}

/** Highscore list handler function. Get highscore list for a given (quiz,user_id). 
Also saves the current users score in case she is doing this quiz for the first time.
Use for example like:
\code
echo quizHighScoreListHandler($_POST);
\endcode
\todo Implement me!
*/
function quizHighScoreListHandler($aLookupScoreData)
{
    $aHighScore = array();

    $fac = new MusicDatabaseFactory();
    $dbScore = $fac->createDbInterface('QuizScoreData');
    $dbQuiz = $fac->createDbInterface('QuizData');
    $quizManip = new QuizManip();
    $quizManip->quizScoreSave($aLookupScoreData);
    
    $quiz_id = (int)$aLookupScoreData['quiz_id'];
    if ( 0 == $quiz_id ) {
        $quiz_id = (int)$dbQuiz->toID($aLookupScoreData);
    }
    
    $aHighScoreList = array();
    $quiz_name = "";
    if ( 0 != $quiz_id ) {
        $aHighScoreList = $dbScore->highScoreListFull($quiz_id); 
        $quiz_name = $dbQuiz->IDToName($quiz_id);
    }
    
    $aHighScore = array(
              'quiz_name'   => $quiz_name
            , 'user_id'     => $aLookupScoreData['user_id']
            , 'score'       => $aLookupScoreData['score']
            , 'list'        => $aHighScoreList
        );
    
 
    dbgWritePostGetSessionData($aHighScore, "/tmp/dbgWritePost_quizHighScoreGetHandler.txt"); // For debugging help look here!
    return json_encode( $aHighScore, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );;
}



///////////////////////////////
/** Save URL commands in current SESSION (handler function). Use for example like:
\code
echo quizSaveUrlCommandHandler($_POST);
\endcode
\todo Implement me!
*/
function quizSaveUrlCommandHandler($aPost)
{
    dbgWritePostGetSession("/tmp/dbgWritePost_quizSaveUrlCommandHandler.txt");  // For debugging help look here!
    return 1;
}



/** Clear URL commands saved in current SESSION (handler function). Use for example like:
\code
echo quizClearUrlCommandHandler($_POST);
\endcode
\todo Implement me!
*/
function quizClearUrlCommandHandler()
{
    dbgWritePostGetSession("/tmp/dbgWritePost_quizClearUrlCommandHandler.txt");  // For debugging help look here!
    return 1;
}

// ---------------------------
// --- Quiz misc functions ---
// ---------------------------

function songNameValid($item_base_name)
{
	static $aFilter = array(
              ' remaster'
            , ' extended'
            , ' remix'
            , ' club mix'
            , ' track'
            , ' promo'
            , ' interview'
            , ' live'
            , ' edit'
            , ' demo'
            , ' version'
            , ' issue'
            , ' digital'
            , ' single'
            , ' anniversary'
            , ' medley'
            , ' dance mix'
        );

	return !iIsAnyInString($item_base_name, $aFilter);
}


// -----------------------------------------
// --- Quiz Generate: GUESS_SONG_PLAYING ---
// -----------------------------------------

function quizAutoGenerate_GUESS_SONG_PLAYING($aCreatOptions)
{
    if ($aCreatOptions['artist_names'] != "")   return quizArtistAutoGenerate_GUESS_SONG_PLAYING($aCreatOptions);
    else                                        return quizThemeAutoGenerate_GUESS_SONG_PLAYING($aCreatOptions);
}

function quizThemeAutoGenerate_GUESS_SONG_PLAYING($aCreatOptions)
{
//    dbgWritePostGetSessionData($aCreatOptions, "/tmp/dbgWritePost_quizThemeAutoGenerate.txt");  // For debugging help look here!
    $fac = new MusicDatabaseFactory();
    $dbTheme = $fac->createDbInterface('QuizThemeData');

    // NOTE: The code here supports multiple themes although, we might never want to use it!
    $aQuiz = quizAutoGenerateQuizHeader($aCreatOptions);
    $aThemeNames = explodeTrim(';', $aCreatOptions['theme_names']);
    $sThemeNames = '';
    $i = 0;
    foreach($aThemeNames as $quiz_theme_name ) {
        $i++;
        $quiz_theme_id = $dbTheme->nameToIDSimple($quiz_theme_name);
        if ( $i > 1) $sThemeNames .= ", ";
        $sThemeNames .= $dbTheme->IDToName($quiz_theme_id);
        $aBaseData = $dbTheme->getBaseData( $quiz_theme_id);
        $theme_json = $aBaseData['theme_json'];
        $aTheme = json_decode($theme_json, true);
        $aSongs = $aTheme['songs'];
        quizAutoGenAddToSongsList( $aQuiz['songs'], $aSongs, $dbItemBase );
        dbgWritePostGetSessionData($aSongs, "/tmp/dbgWritePost_quizThemeAutoGenerate.txt");  // For debugging help look here!
    }
    $aQuiz['quiz_name'] = $sThemeNames;
    $aQuiz['intro_text'] = quizGetIntroTextTheme($aCreatOptions, $sThemeNames);
    return $aQuiz;
}

function quizArtistAutoGenerate_GUESS_SONG_PLAYING($aCreatOptions)
{
    $fac = new MusicDatabaseFactory();
    $dbArtist = $fac->createDbInterface('ArtistData');
    $dbItemBase = $fac->createDbInterface('ItemBaseData');

    $aQuiz = quizAutoGenerateQuizHeader($aCreatOptions);
    $aArtisNames = explodeTrim(';', $aCreatOptions['artist_names']);
    $sArtistNames = '';
    $i = 0;
    foreach($aArtisNames as $artist_name ) {
        $i++;
        $artist_id = $dbArtist->lookupID($artist_name);
        if ( $i > 1) $sArtistNames .= ", ";
        $sArtistNames .= $dbArtist->IDToName($artist_id);
        $aSongs = $dbArtist->getAllBaseItemsWithNumPrices( $artist_id, 2 );
        quizAutoGenAddToSongsList( $aQuiz['songs'], $aSongs, $dbItemBase );
    }
    $aQuiz['quiz_name'] = $sArtistNames;
    $aQuiz['intro_text'] = quizGetIntroTextArtist($aCreatOptions, $sArtistNames);
    return $aQuiz;
}


function quizAutoGenAddToSongsList( &$aToQuizSongList, $aFromSongList, $dbItemBase)
{
    foreach ( $aFromSongList as $aSong ) {
		if (!songNameValid($aSong['item_base_name'])) continue;
		
        $url = '/artist/' . nameToUrl($aSong['artist_name']) . '/song/' . nameToUrl($aSong['item_base_name']); 
        $url = 'http://www.airplaymusic.co.uk' . $url;
        $artist_name = stringRemoveAll(array('#'), $aSong['artist_name']);
        $item_base_name = stringRemoveAll(array('#'), $aSong['item_base_name']);
        $url_image = '';
        $parent_item = $aSong['parent_item'];
        if ( $parent_item != '' ) {
            $url_image = quizGetAlbumCoverUrl( $aSong['parent_item'], $dbItemBase );
        }
        $aToQuizSongList[] = array
        ( 
            'artist_name'     => $artist_name 
            , 'item_base_name'  => $item_base_name 
            , 'url'             => $url 
            , 'url_image'       => $url_image 
            , 'prices_count'    => $aSong['prices_count']
            , 'parent_item'     => $parent_item
        );
    }
}


/** 
\param $item_base_id ID album for which to get the cover image URL */
function quizGetAlbumCoverUrl( $item_base_id, $dbItemBase )
{
    $url = 'http://www.airplaymusic.co.uk';
    //$parent_item
    $aData = $dbItemBase->getBaseData($item_base_id);
    if ( 1 == $aData['image_processed'] ) {
        $url .= $aData['image_url'];
    }
    else {
        $url .= $aData['image_url']; // TODO: Add a default 80x80 AirplayMusic image/icon we can use!
    }
    return $url;
}


function quizAutoGenerateQuizHeader($aCreatOptions)
{
    $aQuiz = array 
    (
          'quiz_name' => $aCreatOptions['artist_names']
        , 'intro_text' => ''
        , 'intro_image' => $aCreatOptions['intro_image']
        , 'author_email' => $aCreatOptions['author_email']
        , 'author_name' => $aCreatOptions['author_name']
        , 'autogen_question_index' => -1 
        , 'type' => 'media'
        , 'difficulty' => $aCreatOptions['difficulty']
        , 'num_questions' => $aCreatOptions['num_questions']
        , 'num_choices' => $aCreatOptions['num_choices']
        , 'image_mode' => 'none'
        , 'start_pos_in_seconds' => -1
        , 'questions' => array()
        , 'songs' => array()
    );
    
    return $aQuiz;
}

function quizGetIntroTextTheme($aCreatOptions, $sThemeNames)
{
    $sIntro =
<<<TEXT
Auto generated quiz where you need to guess which song we are playing. 
<ul>
    <li><b>Theme:</b> {$sThemeNames}</li>
    <li><b>Number of questions:</b> {num_questions}</li>
    <li><b>Difficulty:</b>{$aCreatOptions['difficulty']}</li>
</ul>
TEXT;
    return $sIntro;
}


function quizGetIntroTextArtist($aCreatOptions, $sArtistNames)
{
    $sIntro =
<<<TEXT
Auto generated quiz where you need to guess which song we are playing. 
<ul>
    <li><b>Artists:</b> {$sArtistNames}</li>
    <li><b>Number of questions:</b> {num_questions}</li>
    <li><b>Difficulty:</b>{$aCreatOptions['difficulty']}</li>
</ul>
TEXT;
    return $sIntro;
}

function quizNoQuiz()
{
	$s =
<<<TEXT
{
	  "quiz_name": "_NO_QUIZ_"
}
TEXT;
	return $s;
}


// -------------------------------------
// --- Quiz generate debug functions ---
// -------------------------------------
function quizJsonAutoSongTEST()
{
    $s =
<<<TEXT
{
      "quiz_name": "TEST: Auto song quiz test 1"
    , "intro_text": "This is a test for auto generating a song quiz"
    , "intro_image": "/images/k/i/m/kim_larsen_1.png"
    , "author_email": "ml@airplaymusic.dk"
    , "author_name": "Martin Lütken"
    , "autogen_question_index": -1 
    , "type": "media" 
    , "difficulty": 1 
    , "num_questions": 3
    , "num_choices": 3
    , "image_mode" : "none" 
    , "questions" : [
    ]
    , "songs" : [
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Thriller"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Dirty Diana"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Another Part of Me"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Bad"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Dangerous"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Earth Song"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Billie Jean"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Black or White"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Blood on the Dance Floor"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Dont Stop Til You Get Enough"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "I Just Cant Stop Loving You"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Man in the Mirror"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Rock with You"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Smooth Criminal"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "The Way You Make Me Feel"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "They Dont Care About Us"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "You Are Not Alone"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Human Nature"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
        , 
        { 
                "artist_name"       : "Michael Jackson"
            ,   "item_base_name"    : "Remember the Time"
            ,   "prices_count"      : 30
            ,   "fixed_play_obj"    : { "youtube_id" : "" }  
            ,   "do_play_obj"       : null  
        }
    ]
}
TEXT;
    return $s;
}



function quizJsonMediaTEST()
{
	$s =
<<<TEXT
{
	  "quiz_name": "TEST: Michael Jackson quiz 1"
	, "intro_text": "TEST This is a simple text quiz featuring songs of Michael Jackson"
	, "intro_image": "/images/k/i/m/kim_larsen_1.png"
	, "author_email": "ml@airplaymusic.dk"
	, "author_name": "Martin Lütken"
	, "type": "media" 
	, "num_questions": 3
	, "num_choices": 3
	, "image_mode" : "none" 
	, "questions" : [
		{ 
				"question" 			: "Which song is playing?"
			, 	"c0" 				: "Thriller"
			, 	"c1" 				: "Bad"
			, 	"c2" 				: "Dirty Diana"
			, 	"answer" 			: "c2" 
			,	"fixed_play_obj"	: { "youtube_id" : "ShmLyskuK4o" }	
			,	"do_play_obj"		: null	
		}
		, 
		{ 
				"question" 			: "Which song is playing?"
			, 	"c0" 				: "Another Part of Me"
			, 	"c1" 				: "Thriller"
			, 	"c2" 				: "Baby Be Mine"
			, 	"answer" 			: "c0" 
			,	"fixed_play_obj"	: { "youtube_id" : "nouY3gPAwWM" }	
			,	"do_play_obj"		: null	
		}
 		, 
 		{ 
				"question" 			: "Which song is playing?"
			, 	"c0" 				: "Earth Song"
			, 	"c1" 				: "Euphoria"
			, 	"c2" 				: "Keep Your Head Up"
			, 	"answer" 			: "c0" 
			,	"fixed_play_obj"	: { "youtube_id" : "XAi3VTSdTxU" }	
			,	"do_play_obj"		: null	
		}
	]
}
TEXT;
	return $s;
}


function quizJsonTextTEST()
{
	$s =
<<<TEXT
{
	  "quiz_name": "TEST: Kim Larsen quiz 1"
	, "intro_text": "This is a simple text quiz featuring Kim Larsen questions"
	, "intro_image": "/images/k/i/m/kim_larsen_1.png"
	, "author_email": "ml@airplaymusic.dk"
	, "author_name": "Martin Lütken"
	, "type": "text" 
	, "num_questions": 5
	, "num_choices": 3
	, "image_mode" : "none" 
	, "questions" : [
		  { "question" : "What year was Kim Larsen born?", "c0" : "1944", "c1" : "1945", "c2" : "1946", "answer" : "c1" }
 		, { "question" : "Name of first album?", "c0" : "Sådan", "c1" : "Ja tak", "c2" : "Værsgo", "answer" : "c2" }
 		, { "question" : "Song about hospital ship in Korea?", "c0" : "Sealandia", "c1" : "Jutlandia", "c2" : "1949", "answer" : "c1" }
 		, { "question" : "Best selling album ever?", "c0" : "Midt om natten", "c1" : "Forklædt som voksen", "c2" : "Yummi Yummi", "answer" : "c0" }
 		, { "question" : "Kim Larsen's education?", "c0" : "School teacher", "c1" : "No education", "c2" : "Electrician", "answer" : "c0" }
	]
}
TEXT;
	return $s;
}


function quizGetTheme_TEST()
{
    $sTestTheme =
<<<TEXT
{
    "quiz_theme_name": "Dansk melodi grand prix klassikere TEST",
    "author_name": "Martin",
    "songs": [
        {
            "artist_name": "Gry",
            "item_base_name": "Kloden drejer"
        },
        {
            "artist_name": "Brixx",
            "item_base_name": "Video Video"
        },
        {
            "artist_name": "Fenders",
            "item_base_name": "Vild med eventyr"
        },
        {
            "artist_name": "Snapshot",
            "item_base_name": "Gir du et knus"
        },
        {
            "artist_name": "Kirsten og Søren",
            "item_base_name": "Sku du spørg fra noen"
        },
        {
            "artist_name": "Banjo",
            "item_base_name": "En lille Melodi"
        },
        {
            "artist_name": "Trine Dyrholm",
            "item_base_name": "Danse I Måneskin"
        }
    ]
}
TEXT;
    return $sTestTheme;
}



?>