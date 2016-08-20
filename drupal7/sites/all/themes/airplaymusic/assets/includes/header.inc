<div class="container">
<?php
	if (!drupal_is_front_page()) {
?>
    <div class="logo">
        <?php if ($logo): ?>
            <a href="<?php print $front_page; ?>" title="<?php print t('Home'); ?>" rel="home" id="logo">logo</a>
        <?php endif; ?>
    </div>
    <!--logo-->
<?php
	}
?>
    <div class="top_menu">
		<?php 
			if (!drupal_is_front_page()) {
				$sForm = drupal_get_form('apms_large_form');
				$sMenuIcons = ap_get_top_menu_icons();
				print $sMenuIcons;
				print "<div class='search-music'>";
				print drupal_render($sForm);
				print "</div>";
			} else {
				$sMenuIcons = ap_get_top_menu_icons();
				print $sMenuIcons;
			}
		?>
    </div>
    <!--search-->
</div>
<div class="clear"></div>