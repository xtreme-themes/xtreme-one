<?php
xtreme_sidebar_widget_start_tag( array( 'id' => 'xtreme_sidebar_one' ) );

if ( is_active_sidebar( 'sidebar-one' ) ) {
	dynamic_sidebar( 'sidebar-one' );
} else {
	do_action( 'xtreme_sidebar_one' );
}

xtreme_sidebar_widget_end_tag();
