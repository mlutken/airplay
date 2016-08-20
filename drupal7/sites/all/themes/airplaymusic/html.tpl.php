<?php
/**
 * @file
 * Default theme implementation to display the basic html structure of a single
 * Drupal page.
 *
 * Variables:
 * - $css: An array of CSS files for the current page.
 * - $language: (object) The language the site is being displayed in.
 *   $language->language contains its textual representation.
 *   $language->dir contains the language direction. It will either be 'ltr' or 'rtl'.
 * - $rdf_namespaces: All the RDF namespace prefixes used in the HTML document.
 * - $grddl_profile: A GRDDL profile allowing agents to extract the RDF data.
 * - $head_title: A modified version of the page title, for use in the TITLE
 *   tag.
 * - $head_title_array: (array) An associative array containing the string parts
 *   that were used to generate the $head_title variable, already prepared to be
 *   output as TITLE tag. The key/value pairs may contain one or more of the
 *   following, depending on conditions:
 *   - title: The title of the current page, if any.
 *   - name: The name of the site.
 *   - slogan: The slogan of the site, if any, and if there is no title.
 * - $head: Markup for the HEAD section (including meta tags, keyword tags, and
 *   so on).
 * - $styles: Style tags necessary to import all CSS files for the page.
 * - $scripts: Script tags necessary to load the JavaScript files and settings
 *   for the page.
 * - $page_top: Initial markup from any modules that have altered the
 *   page. This variable should always be output first, before all other dynamic
 *   content.
 * - $page: The rendered page content.
 * - $page_bottom: Final closing markup from any modules that have altered the
 *   page. This variable should always be output last, after all other dynamic
 *   content.
 * - $classes String of classes that can be used to style contextually through
 *   CSS.
 *
 * @see template_preprocess()
 * @see template_preprocess_html()
 * @see template_process()
 */
 
 /*
	HACK for not showing UserReport on first-time-visit. */
	/*
	session_start();
	// URPW - User Report Page Views
	if (isset($_SESSION['URPW'])) {
		$_SESSION['URPW'] = $_SESSION['URPW'] + 1;
	} else {
		$_SESSION['URPW'] = 1;
	}
	*/
 
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML+RDFa 1.0//EN"
  "http://www.w3.org/MarkUp/DTD/xhtml-rdfa-1.dtd">
<!DOCTYPE html>
<?php
		if (ap_language_code() == "da") {
			print '<html lang="da" xml:lang="da">';
		} else {
			print '<html lang="en" xml:lang="en">';
		}
?>
<head>
<?php
	// Canonical URLs for the entire site.
	if (isset($_SERVER['REQUEST_URI'])) {
		printf('<link rel="canonical" href="' . strtok($_SERVER['REQUEST_URI'],'?') . '" />');
	}
	//<link href='http://fonts.googleapis.com/css?family=Open+Sans' rel='stylesheet' type='text/css'>
?>

<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<meta http-equiv="x-ua-compatible" content="ie=edge, chrome=1" />
<meta property="og:image" content="/images/icons/ap_facebook_logo.png" />
<?php
	if (drupal_is_front_page()) {
        if (ap_language_code() == "da") {
			print '<meta name="description" content="Musiksøgemaskine sammenligner udvalg og priser fra 100+ pladeforretninger, streaming-tjenester,  koncertarrangører... Find det største udvalg og de billigste priser på mp3, streaming, cd, vinyl, merchandise, koncerter...">';
    } else {
			print '<meta name="description" content="Music Search Engine compares products and prices from 100+ Record stores, Streaming services... Find largest selection and best possible prices for mp3, streaming, cd, vinyl, merchandise...">';
		}
	}
?>
<title><?php print $head_title; ?></title>
<link rel="publisher" href="https://plus.google.com/102349067624794918412"/>
<?php
	print $styles;
	print $scripts;
?>
<!--[if IE 8]>  
<link rel="stylesheet" href="/sites/all/themes/airplaymusic/assets/css/ie8.css">  
<![endif]-->  
<!--[if IE 7]>  
<link rel="stylesheet" href="/sites/all/themes/airplaymusic/assets/css/ie8.css">  
<![endif]-->  
</head>
<body id="<?php print airplaymusic_section(); ?>" class="<?php print $classes; ?>">
<?php
	print $page_top;
	print $page;
	print $page_bottom;
	if (ap_language_code() == "da") {
		print '<script type="text/javascript" src="http://www.airplaymusic.dk/js/cookie_v2.js"></script>';
	}
?>
<?php
	// Remove $_SESSION['URPW'] in top of page when removing UserReport....
/*	if (ap_language_code() == "da" && $_SESSION['URPW'] > 2) {

<script type="text/javascript">
if (!is_mobile()) {
var _urq = _urq || [];
_urq.push(['initSite', '34bf35e0-5fc7-4848-b2fa-63915404eadd']);
(function() {
var ur = document.createElement('script'); ur.type = 'text/javascript'; ur.async = true;
ur.src = ('https:' == document.location.protocol ? 'https://cdn.userreport.com/userreport.js' : 'http://cdn.userreport.com/userreport.js');
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ur, s);
})();
}
</script> 

	}*/
	if (!drupal_is_front_page()) {
		print '<script type="text/javascript" src="http://s7.addthis.com/js/250/addthis_widget.js#pubid=ra-4e1345660edb6a27&async=1&domready=1"></script>';
		print '<script type="text/javascript">function initAddThis() { addthis.init() } initAddThis();</script>';
	}
?>
<script type="text/javascript">
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
  ga('create', '<?php echo variable_get('googleanalytics_account', ''); ?>', '<?php echo $_SERVER['HTTP_HOST']; ?>');
  ga('send', 'pageview');
</script>
</body>
</html>
