<?php
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php print $language->language ?>" xml:lang="<?php print $language->language ?>" dir="<?php print $language->dir ?>">
<head>
<meta name="msvalidate.01" content="D60DEF0DA54385B05443CC9ED70222BB" />
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

<?php global $base_url;?>
<title><?php print $head_title ?></title>
<?php print $head ?>
<?php print $styles ?>
<?php print $scripts; ?>

</head>
<body>

<div id="page">
	<!--header-top-->
	<div id="header-top">
		<div id="header-top-inside" class="clearfix">
			<div id="header-top-inside-left"><div id="header-top-inside-left-content"><?php print $header; ?> </div></div>
			<div id="header-top-inside-left-feed"><?php print $feed_icons ?></div>
			<div id="header-top-inside-right"><?php print $search_box; ?></div>  
		</div>
	</div>
	<!--/header-top-->
	
	<div id="header" class="clearfix">
		<div id="logo-frontpage"> 
			<?php
			// Prepare header
			$site_fields = array();
			if ($site_name) {
				$site_fields[] = check_plain($site_name);
			}
			if ($site_slogan) {
				$site_fields[] = check_plain($site_slogan);
			}
			$site_title = implode(' ', $site_fields);
			if ($site_fields) {
				$site_fields[0] = '<span>'. $site_fields[0] .'</span>';
			}
			$site_html = implode(' ', $site_fields);
			
			if ($logo || $site_title) {
				print '<a href="'. check_url($front_page) .'" title="'. $site_title .'">';
			if ($logo) {
				print '<img src="'. check_url($logo) .'" alt="'. $site_title .'" id="logo-image" />';
			}
			print '<div style="display:none">'.$site_html .'</div></a>';
			}
			?>
		</div> <!--logo-->
		
		<div id="navigation">
			<?php if (isset($primary_links)) { ?><?php print theme('links', $primary_links, array('class' =>'links', 'id' => 'primary-links')) ?><?php } ?>
			<?php print menu_tree($menu_name = 'primary-links'); ?>
		</div><!--navigation-->
	
	</div><!--header-->
	
	<div id="wrapper-front">

		<div id="intro" class="clearfix">
			
			<div class="left_view">
				<?php print $intro_right;?>
			</div>
			<div class="main_view">
				<?php print $content; ?>
			</div>

		</div>
		
		<div id="banner" class="clearfix">
			<div class="left">
				<?php print $left_banner;?>
			</div>
		</div>
		
		<div id="home-blocks-area" class="clearfix">
			<div id="home-block-1" class="home-block">
				<?php print $home_area_1;?> 		
			</div>
			<div id="home-block-2" class="home-block">
				<?php print $home_area_2;?> 
			</div>
			<div id="home-block-3" class="home-block">
				<?php print $home_area_3;?> 
			</div>
		</div>    
	
		<?php 
		// uncomment this to get news feed in your home page
		// you have to take care about the look'n'feel 
		// print $content;
		?> 
		 
	</div><!-- /wrapper-->

	<div id="footer">
		<div id="footer-inside" class="clearfix">
			<div id="footer-left">
				<div id="footer-left">
					<?php if ($footer_left != '') { print $footer_left; } else { print "&nbsp;"; }?>
				</div>
			</div>
			<div id="footer-center">
				<?php if ($footer_center != '') { print $footer_center; } else { print "&nbsp;"; }?>
			</div>
			<div id="footer-right">
				<?php if ($footer_right != '') { print $footer_right; } else { print "&nbsp;"; }?>
			</div>
		</div>
	</div>

	<?php print $closure ?>


	</div><!-- /page-->

</body>
</html>