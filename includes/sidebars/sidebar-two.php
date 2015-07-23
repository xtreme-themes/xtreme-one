<?php
xtreme_sidebar_widget_start_tag( array( 'id' => 'xtreme_sidebar_two' ));

if ( is_active_sidebar( 'sidebar-two' ) ) {
	dynamic_sidebar( 'sidebar-two' );
} else {
	do_action( 'xtreme_sidebar_two' );
}

xtreme_sidebar_widget_end_tag();