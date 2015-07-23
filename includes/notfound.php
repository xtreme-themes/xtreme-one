<?php
$el = 'div';
if(xtreme_is_html5()) {
	$el = 'article';
}
echo '<' . $el . ' class="post">';
?>
	<h2><?php _e('Sorry, Page Not Found', XF_TEXTDOMAIN ) ?></h2>
	<p><?php _e('Apologies, but the page you requested could not be found.', XF_TEXTDOMAIN ) ?></p>
<?php echo '</' . $el . '>'; ?>
