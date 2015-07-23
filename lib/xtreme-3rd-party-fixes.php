<?php 

//because of bad programming in woocommerce, we have to reset
//the global sidebars var, to be repopulated by first real sidebar call
function xtreme_fix_woocommerce() {
	if(isset($GLOBALS['woocommerce'])) {
		global $_wp_sidebars_widgets;
		$_wp_sidebars_widgets = null;
	}
}
add_action( 'after_setup_theme', 'xtreme_fix_woocommerce');