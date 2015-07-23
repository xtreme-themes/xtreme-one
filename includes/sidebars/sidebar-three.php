<?php xtreme_sidebar_widget_start_tag( array( 'id' => 'xtreme_sidebar_three', 'class' => 'sidebar ym-grid linearize-level-1' ) ); ?>
	<?php
	if ( is_active_sidebar('sidebar-three-top') ) :
	    dynamic_sidebar( 'sidebar-three-top' );
	else:
	    do_action('sidebar-three-top');
	endif;
	?>
	<div class="ym-g50 ym-gl">
		<div class="ym-gbox-left">
			<?php
			if ( is_active_sidebar('sidebar-three-bottom-left') ) :
			    dynamic_sidebar( 'sidebar-three-bottom-left' );
			else:
			    do_action('sidebar-three-bottom-left');
			endif;
			?>
		</div>
	</div>
	<div class="ym-g50 ym-gr">
		<div class="ym-gbox-right">
			<?php
			if ( is_active_sidebar('sidebar-three-bottom-right') ) :
			    dynamic_sidebar( 'sidebar-three-bottom-right' );
			else:
			    do_action('sidebar-three-bottom-right');
			endif;
			?>
		</div>
	</div>
<?php xtreme_sidebar_widget_end_tag(); ?>
