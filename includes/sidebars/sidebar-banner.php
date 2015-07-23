<?php xtreme_sidebar_widget_start_tag( array( 'id' => 'xtreme_sidebar_banner' ) );
$t = xtreme_get_sidebar_tag();
if ( !xtreme_is_html5() && $t === 'ul' ) echo '<li>';
?>
<h5>Our Sponsors</h5>
<ul class="advertise">
	<li><a href="#" title="Advertise here"><img src="<?php echo get_stylesheet_directory_uri() ?>/images/ads.png" alt="Advertise here!" /></a></li>
	<li><a href="#" title="Advertise here"><img src="<?php echo get_stylesheet_directory_uri() ?>/images/ads.png" alt="Advertise here!" /></a></li>
	<li><a href="#" title="Advertise here"><img src="<?php echo get_stylesheet_directory_uri() ?>/images/ads.png" alt="Advertise here!" /></a></li>
	<li><a href="#" title="Advertise here"><img src="<?php echo get_stylesheet_directory_uri() ?>/images/ads.png" alt="Advertise here!" /></a></li>
	<li><a href="#" title="Advertise here"><img src="<?php echo get_stylesheet_directory_uri() ?>/images/ads.png" alt="Advertise here!" /></a></li>
	<li><a href="#" title="Advertise here"><img src="<?php echo get_stylesheet_directory_uri() ?>/images/ads.png" alt="Advertise here!" /></a></li>
</ul>
<?php
if ( !xtreme_is_html5() && $t === 'ul' ) echo '</li>';
echo xtreme_sidebar_widget_end_tag();