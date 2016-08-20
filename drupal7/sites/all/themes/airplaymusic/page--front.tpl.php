<?php
/**
 * @file
 * Default theme implementation to display a single Drupal page.
 *
 * Available variables:
 *
 * General utility variables:
 * - $base_path: The base URL path of the Drupal installation. At the very
 *   least, this will always default to /.
 * - $directory: The directory the template is located in, e.g. modules/system
 *   or themes/bartik.
 * - $is_front: TRUE if the current page is the front page.
 * - $logged_in: TRUE if the user is registered and signed in.
 * - $is_admin: TRUE if the user has permission to access administration pages.
 *
 * Site identity:
 * - $front_page: The URL of the front page. Use this instead of $base_path,
 *   when linking to the front page. This includes the language domain or
 *   prefix.
 * - $logo: The path to the logo image, as defined in theme configuration.
 * - $site_name: The name of the site, empty when display has been disabled
 *   in theme settings.
 * - $site_slogan: The slogan of the site, empty when display has been disabled
 *   in theme settings.
 *
 * Navigation:
 * - $main_menu (array): An array containing the Main menu links for the
 *   site, if they have been configured.
 * - $secondary_menu (array): An array containing the Secondary menu links for
 *   the site, if they have been configured.
 * - $breadcrumb: The breadcrumb trail for the current page.
 *
 * Page content (in order of occurrence in the default page.tpl.php):
 * - $title_prefix (array): An array containing additional output populated by
 *   modules, intended to be displayed in front of the main title tag that
 *   appears in the template.
 * - $title: The page title, for use in the actual HTML content.
 * - $title_suffix (array): An array containing additional output populated by
 *   modules, intended to be displayed after the main title tag that appears in
 *   the template.
 * - $messages: HTML for status and error messages. Should be displayed
 *   prominently.
 * - $tabs (array): Tabs linking to any sub-pages beneath the current page
 *   (e.g., the view and edit tabs when displaying a node).
 * - $action_links (array): Actions local to the page, such as 'Add menu' on the
 *   menu administration interface.
 * - $feed_icons: A string of all feed icons for the current page.
 * - $node: The node object, if there is an automatically-loaded node
 *   associated with the page, and the node ID is the second argument
 *   in the page's path (e.g. node/12345 and node/12345/revisions, but not
 *   comment/reply/12345).
 *
 * Regions:
 * - $page['help']: Dynamic help text, mostly for admin pages.
 *
 * @see template_preprocess()
 * @see template_preprocess_page()
 * @see template_process()
 */
?>
<?php
    drupal_set_title(t('Music Search Engine | Compare prices Vinyl, CD, MP3, Streaming, Concert'));
	
	// Frontpage tokens.
	if (ap_language_code() == "da") {
		$ap_token_frontpage_teaser_search_header = "Musiksøgemaskine";
		$ap_token_frontpage_teaser_search_text = "Airplay Music er musiksøgemaskinen til Best buy &amp; Hard to find music.<br/>Du finder og sammenligner udvalg og priser på tværs af musikmarkedet, blandt 200+ musik websites...";
		$ap_token_frontpage_teaser_search_1st = "<h2 class='header'>Søg</h2><div class='text'><strong>Navn/titel</strong><br/>Kunstner<br/>Album<br/>Sang</div>";
		$ap_token_frontpage_teaser_search_2nd = "<h2 class='header'>Find</h2><div class='text'><strong>Musikprodukter</strong><br/>CD, Vinyl, MP3...<br/>Streaming, merchandise<br/>Koncert, musikfestival</div>";
		$ap_token_frontpage_teaser_search_3rd = "<h2 class='header'>Sammenlign</h2><div class='text'><strong>200+ shops/tjenester</strong><br/>Pladeforretninger<br/>Streamingtjenester<br/>Koncertarrangører</div>";
		$ap_token_frontpage_teaser_search_4th = "<h2 class='header'>Køb</h2><div class='text'><strong>Bedste tilbud</strong><br/>Vælg tilbud<br/>Link til Shop<br/>Køb</div>";
		
		$ap_token_frontpage_teaser_guide_competition_header = "Konkurrence";
		$ap_token_frontpage_teaser_guide_competition_text = "Vind musikfestival billetter.<br/>Deltag i konkurrencen om 1 x 2 billetter til Frigg Festival, Give Open Air,<br/>New Note Festival eller Wonderfestiwall<br/>- Held og lykke!";
		$ap_token_frontpage_teaser_guide_competition_link = "/konkurrence";
		
		$ap_token_frontpage_teaser_guide_shop_guide_header = "Shop Guide";
		$ap_token_frontpage_teaser_guide_shop_guide_text = "Guide til 500+ pladeforretninger, musiktjenester - både webshops og fysiske butikker, i Danmark og udland. Find shop/tjeneste via musikformat, musikgenre, land.";
		$ap_token_frontpage_teaser_guide_shop_guide_link = "/pladeforretninger";
		
		$ap_token_frontpage_teaser_guide_concert_guide_header = "Koncert Guide";
		$ap_token_frontpage_teaser_guide_concert_guide_text = "Guide til 3.000+ koncerter i Danmark og udland. Find koncertkalender for dine favorit-kunstnere, aktuelle koncerter i din by / på dit spillested.";
		$ap_token_frontpage_teaser_guide_concert_guide_link = "/koncert-guide";
		
		$ap_token_frontpage_teaser_guide_festival_guide_header = "Musikfestival Guide";
		$ap_token_frontpage_teaser_guide_festival_guide_text = "Guide til 60+ musikfestivaler i Danmark. Se opdaterede lister med optrædende kunstnere, og find musikfestivaler med dine favorit-kunstnere.";
		$ap_token_frontpage_teaser_guide_festival_guide_link = "/musikfestival-guide";
		
		$ap_token_frontpage_searches_record_stores_text = "Airplay Music søger blandt 200+ musik websites";
	} else {
		$ap_token_frontpage_teaser_search_header = "Music Search Engine";
		$ap_token_frontpage_teaser_search_text = "Airplay Music is the Music Search Engine for Best buy & Hard to find music. You can find and compare products and prices across the music market, among 200+ music websites...";
		$ap_token_frontpage_teaser_search_1st = "<h2 class='header'>Search</h2><div class='text'><strong>Name/Title</strong><br/>Artist<br/>Album<br/>Song</div>";
		$ap_token_frontpage_teaser_search_2nd = "<h2 class='header'>Find</h2><div class='text'><strong>Music products</strong><br/>CD, Vinyl, MP3...<br/>Streaming, merchandise<br/>Concert, music festival</div>";
		$ap_token_frontpage_teaser_search_3rd = "<h2 class='header'>Compare</h2><div class='text'><strong>200+ shops/services</strong><br/>Record stores<br/>Streaming services<br/>Venues</div>";
		$ap_token_frontpage_teaser_search_4th = "<h2 class='header'>Buy</h2><div class='text'><strong>Best buy</strong><br/>Choose offer<br/>Link to shop<br/>Buy</div>";
		
		$ap_token_frontpage_searches_record_stores_text = "Airplay Music searches more then 200+ music websites";
	}
?>
<div id="page-wrapper">
    <div class="row-fluid header-wrapper"><?php include 'assets/includes/header.inc'; ?></div>
    <!--/headerwrapper-->
    <div class="container" style="position:relative;">
        
        <div class="row-fluid content">
            <div class="span12 content-wrapper">
                <div class="well">
                    <?php if ($messages): ?>
                        <div id="console" class="clearfix">
                            <?php print $messages; ?>
                        </div>
                    <?php endif; ?>
                    <?php if ($tabs): ?><?php print render($tabs); ?><?php endif; ?>
                    <?php 
                        unset($page['content']['system_main']['default_message']);
                        //print render($page['content']); 
                    ?>
                    <div class="logo">&nbsp;</div>
                    <div class="search-music">
					<?php
						$sForm = drupal_get_form("apms_large_form");
						print render($sForm);
					?>
					</div>
					
					<div id="music_search_desc">
						<div class="desc"><h1><?php print $ap_token_frontpage_teaser_search_header; ?></h1><?php print $ap_token_frontpage_teaser_search_text; ?></div>
						<div class="icons">
							<div class="icon"><div class="search"><div class="icon">&nbsp;</div></div><div class="bottom"><?php print $ap_token_frontpage_teaser_search_1st; ?></div></div>
							<div class="icon"><div class="find"><div class="icon">&nbsp;</div></div><div class="bottom"><?php print $ap_token_frontpage_teaser_search_2nd; ?></div></div>
							<div class="icon"><div class="compare"><div class="icon">&nbsp;</div></div><div class="bottom"><?php print $ap_token_frontpage_teaser_search_3rd; ?></div></div>
							<div class="icon"><div class="buy"><div class="icon">&nbsp;</div></div><div class="bottom"><?php print $ap_token_frontpage_teaser_search_4th; ?></div></div>
						</div>
					</div>
					
					<?php if (ap_language_code() == "da") { ?>
					
						<div id="guide_sections">
							<div class="guide_section_wrapper">
								<div class="guide_section">
									<div class="first"><div class="icon">&nbsp;</div><h2><?php print $ap_token_frontpage_teaser_guide_shop_guide_header; ?></h2></div>
									<div class="image"><img src="http://www.airplaymusic.dk/images/site/frontpage_teaser_pladeforretning.jpg" width="230" height="60" border="0" alt="" title="" /></div>
									<div class="text"><?php print $ap_token_frontpage_teaser_guide_shop_guide_text; ?></div>
									<div class="link"><a href="<?php print $ap_token_frontpage_teaser_guide_shop_guide_link; ?>"><?php print t("Click here"); ?></a></div>
								</div>
							</div>
							<div class="guide_section_wrapper">
								<div class="guide_section">
									<div class="second"><div class="icon">&nbsp;</div><h2><?php print $ap_token_frontpage_teaser_guide_concert_guide_header; ?></h2></div>
									<div class="image"><img src="http://www.airplaymusic.dk/images/site/frontpage_teaser_koncert.jpg" width="230" height="60" border="0" alt="" title="" /></div>
									<div class="text"><?php print $ap_token_frontpage_teaser_guide_concert_guide_text; ?></div>
									<div class="link"><a href="<?php print $ap_token_frontpage_teaser_guide_concert_guide_link; ?>"><?php print t("Click here"); ?></a></div>
								</div>
							</div>
							<div class="guide_section_wrapper">
								<div class="guide_section">
									<div class="third"><div class="icon">&nbsp;</div><h2><?php print $ap_token_frontpage_teaser_guide_festival_guide_header; ?></h2></div>
									<div class="image"><img src="http://www.airplaymusic.dk/images/site/frontpage_teaser_musikfestival.jpg" width="230" height="60" border="0" alt="" title="" /></div>
									<div class="text"><?php print $ap_token_frontpage_teaser_guide_festival_guide_text; ?></div>
									<div class="link"><a href="<?php print $ap_token_frontpage_teaser_guide_festival_guide_link; ?>"><?php print t("Click here"); ?></a></div>
								</div>
							</div>
						</div>
					<?php } ?>
					
					<div id="record_store_icon_section">
						<h2><?php print $ap_token_frontpage_searches_record_stores_text; ?></h2>
						<div class="icons_list">&nbsp;</div>
						<?php /*
						<div class="icons_list">
							<div class="itunes icon">&nbsp;</div>
							<div class="spotify icon">&nbsp;</div>
							<div class="smukfest icon">&nbsp;</div>
							<div class="cdon icon">&nbsp;</div>
							<div class="wimp icon">&nbsp;</div>
							<div class="northside icon">&nbsp;</div>
							
							<div class="amazon icon">&nbsp;</div>
							<div class="billetlugen icon">&nbsp;</div>
							<div class="deezer icon">&nbsp;</div>
							<div class="rdio icon">&nbsp;</div>
							<div class="billetnet icon">&nbsp;</div>
							
							<div class="sevendigital icon">&nbsp;</div>
							<div class="napster icon">&nbsp;</div>
							<div class="jelling icon">&nbsp;</div>
							<div class="cdbaby icon">&nbsp;</div>
							<div class="cduniverse icon">&nbsp;</div>
							<div class="gronkoncert icon">&nbsp;</div>
						</div>*/
						?>
					</div>
                </div>
            </div>
            <!--contentwrapper-->
            <?php if ($page['topbanner']): ?>
                <div class="span12 top-banner">
                    <?php print render($page['topbanner']); ?>
                </div><!--topbanner--><?php endif; ?>
            <div class="row-fluid txtfield_wrapper">
                <div class="span4 txtfield txtfield_left">
                    <?php 
                        //$ap_release_frontpage_release = airplay_release_frontpage_release();
                        //print render($ap_release_frontpage_release);
                        print render($page['txtfield_left']);
                    ?>
                </div>
                <div class="span4 txtfield txtfield_center">
                    <?php 
                        //$ap_release_frontpage_prerelease = airplay_release_frontpage_prerelease();
                        //print render($ap_release_frontpage_prerelease);
                        print render($page['txtfield_center']);
                    ?>
                </div>
                <div class="span4 txtfield txtfield_right"><?php print render($page['txtfield_right']); ?></div>
            </div>
        </div>
        <!--/content-->

    </div><!--/pagewrapper-->
            <div class="row-fluid footer-wrapper"><?php if ($action_links): ?>
                <ul class="action-links"><?php print render($action_links); ?></ul><?php endif; ?><?php include 'assets/includes/footer.inc'; ?>
        </div>
        <!--/footerwrapper-->
</div>
