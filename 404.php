<?php
/*
 * Xtreme Name: 404
 */
get_header();
if ( !xtreme_is_html5() ) : ?>
<div class="post">
    <h2><?php _e( 'Sorry, Page Not Found', XF_TEXTDOMAIN ) ?></h2>
<?php else : ?>
    <article class="post">
        <header><h2><?php _e( 'Sorry, Page Not Found', XF_TEXTDOMAIN ) ?></h2></header>
<?php endif ?>
	<div class="entry-content">
		<p><?php _e( 'Apologies, but the page you requested could not be found.', XF_TEXTDOMAIN ) ?></p>
	</div>
<?php if ( !xtreme_is_html5() ) echo '</div>'; else echo '</article>';
get_footer();