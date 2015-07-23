<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Low_Barrier_Widget"; return $classes;'));

function xtreme_low_barrier_classes($classes) {
	if (isset($_COOKIE['xfont']) && in_array($_COOKIE['xfont'], array('medium','maximum'))) {
		$classes[] = $_COOKIE['xfont'];
	}
	if (isset($_COOKIE['xcontrast']) && in_array($_COOKIE['xcontrast'], array('highcontrast'))) {
		$classes[] = $_COOKIE['xcontrast'];
	}
	return $classes;
}
add_filter('xtreme_html_tag_classes', 'xtreme_low_barrier_classes');

class Xtreme_Low_Barrier_Widget extends Xtreme_Widget_Base {
	function __construct() {
		$widget_ops = array( 'classname' => 'xtreme_low_barrier', 'description' => __( 'Low Barrier Font and Constract Selectors', XF_TEXTDOMAIN ) );
		parent::__construct(__FILE__, 'xtreme-low-barrier', __( 'Xtreme Low Barrier', XF_TEXTDOMAIN ), $widget_ops );
	}

	function ensure_widget_scripts($instance) {
		global $xtreme_script_manager;
		$xtreme_script_manager->ensure_low_barrier();
		$xtreme_script_manager->add_widget_data('xtreme-low-barrier', $this->id, array(
			'automatic' => XF_LOW_BARRIER_CSS_EXISTS ? false : true,
			'medium' => $instance['font_size_medium'],
			'maximum' => $instance['font_size_maximum']
		));		
	}
	
	function font_selector($show_selector, $show_font_switcher_desc) {
		if ($show_selector) {
			$current = 'original';
			if(isset($_COOKIE['xfont']) && in_array($_COOKIE['xfont'], array('medium','maximum'))) {
				$current = $_COOKIE['xfont'];
			}
			?><li class="barrier-font"><?php
			?><?php if ($show_font_switcher_desc) : ?><span class="barrier-label"><?php _e('Fontsize:', XF_TEXTDOMAIN) ?></span><?php endif;
			?><a class="barrier-font original<?php echo ($current == 'original' ? ' current' : '') ?>" href="#" rel="original" title="<?php _e('Normal Fontsize', XF_TEXTDOMAIN);?>"><span>A</span></a><?php
			?><a class="barrier-font medium<?php echo ($current == 'medium' ? ' current' : '') ?>" href="#" rel="medium" title="<?php _e('Medium Fontsize', XF_TEXTDOMAIN);?>"><span>A</span></a><?php
			?><a class="barrier-font maximum<?php echo ($current == 'maximum' ? ' current' : '') ?>" href="#" rel="maximum" title="<?php _e('Maximum Fontsize', XF_TEXTDOMAIN);?>"><span>A</span></a><?php
			?></li><?php
		}
	}
	
	function contrast_selector($show_selector, $show_contrast_switcher_desc) {
		if ($show_selector) {
			$current = 'themecontrast';
			if (isset($_COOKIE['xcontrast']) && in_array($_COOKIE['xcontrast'], array('highcontrast'))) {
				$current = $_COOKIE['xcontrast'];
			}
			?><li class="barrier-contrast"><?php
			?><?php if ($show_contrast_switcher_desc) : ?><span class="barrier-label"><?php _e('Contrast:', XF_TEXTDOMAIN) ?></span><?php endif; 
			?><a class="barrier-contrast themecontrast<?php echo ($current == 'themecontrast' ? ' current' : '') ?>" href="#" rel="themecontrast" title="<?php _e('Normal Contrast', XF_TEXTDOMAIN);?>"><span>&times;</span></a><?php
			?><a class="barrier-contrast highcontrast<?php echo ($current == 'highcontrast' ? ' current' : '') ?>" href="#" rel="highcontrast" title="<?php _e('High Contrast', XF_TEXTDOMAIN);?>"><span>&equiv;</span></a><?php
			?></li><?php
		}
	}

	function widget( $args, $instance ) {
		extract( $args );
		$title = ( empty( $instance['title'] ) ) ? '' : apply_filters( 'widget_title', $instance['title'] );
		$show_font_switcher = isset( $instance['show_font_switcher'] ) ? $instance['show_font_switcher'] : 0;
		$show_font_switcher_desc = isset( $instance['show_font_switcher_desc'] ) ? $instance['show_font_switcher_desc'] : 0;
		$font_size_medium = isset( $instance['font_size_medium'] ) ? $instance['font_size_medium'] : 150;
		$font_size_maximum = isset( $instance['font_size_maximum'] ) ? $instance['font_size_maximum'] : 200;
		$show_contrast_switcher = isset( $instance['show_contrast_switcher'] ) ? $instance['show_contrast_switcher'] : 0;
		$show_contrast_switcher_desc = isset( $instance['show_contrast_switcher_desc'] ) ? $instance['show_contrast_switcher_desc'] : 0;
		$show_horizontal = isset( $instance['show_horizontal'] ) ? $instance['show_horizontal'] : 0;
		$flip_order = isset( $instance['flip_order'] ) ? $instance['flip_order'] : 0;

		if ($show_font_switcher == 0 && $show_contrast_switcher == 0) return;
		
		echo $before_widget;
		if ( !empty( $title ) ) { echo $before_title . $title . $after_title; };
		
		?><ul class="ym-contain-dt<?php echo $show_horizontal ? ' horizontal' : ' vertical';?>"><?php
		
		if ($flip_order) {
			$this->contrast_selector($show_contrast_switcher, $show_contrast_switcher_desc);
			$this->font_selector($show_font_switcher, $show_font_switcher_desc);		
		}else{
			$this->font_selector($show_font_switcher, $show_font_switcher_desc);		
			$this->contrast_selector($show_contrast_switcher, $show_contrast_switcher_desc);
		}
		?></ul><?php
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['show_font_switcher'] = $new_instance['show_font_switcher'] ? 1 : 0;
		$instance['show_font_switcher_desc'] = $new_instance['show_font_switcher_desc'] ? 1 : 0;
		$instance['font_size_medium'] = intval( $new_instance['font_size_medium'] );
		$instance['font_size_maximum'] = intval( $new_instance['font_size_maximum'] );		
		$instance['show_contrast_switcher'] = $new_instance['show_contrast_switcher'] ? 1 : 0;
		$instance['show_contrast_switcher_desc'] = $new_instance['show_contrast_switcher_desc'] ? 1 : 0;
		$instance['show_horizontal'] = $new_instance['show_horizontal'] ? 1 : 0;
		$instance['flip_order']  = $new_instance['flip_order'] ? 1 : 0;
		return $instance;
	}
	
	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$show_font_switcher = isset( $instance['show_font_switcher'] ) ? $instance['show_font_switcher'] : 0;
		$show_font_switcher_desc = isset( $instance['show_font_switcher_desc'] ) ? $instance['show_font_switcher_desc'] : 0;
		$font_size_medium = isset( $instance['font_size_medium'] ) ? $instance['font_size_medium'] : 150;
		$font_size_maximum = isset( $instance['font_size_maximum'] ) ? $instance['font_size_maximum'] : 200;
		$show_contrast_switcher = isset( $instance['show_contrast_switcher'] ) ? $instance['show_contrast_switcher'] : 0;
		$show_contrast_switcher_desc = isset( $instance['show_contrast_switcher_desc'] ) ? $instance['show_contrast_switcher_desc'] : 0;
		$show_horizontal = isset( $instance['show_horizontal'] ) ? $instance['show_horizontal'] : 0;
		$flip_order  = isset( $instance['flip_order'] ) ? $instance['flip_order'] : 0;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php  _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title ) ?>" />
		</p>
		<h3><?php _e('Font Switcher Options', XF_TEXTDOMAIN) ?></h3>
		<p>
			<input class="x-thumbnail" id="<?php echo $this->get_field_id( 'show_font_switcher' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_font_switcher' ) ?>" value="1" <?php checked( 1, $show_font_switcher ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_font_switcher') ?>"><?php _e( 'Enable Fontsize Selector', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id( 'show_font_switcher' ) ?>">
			<p>
				<input id="<?php echo $this->get_field_id( 'show_font_switcher_desc' ) ?>" name="<?php echo $this->get_field_name('show_font_switcher_desc') ?>" type="checkbox" value="1" <?php checked( 1, $show_font_switcher_desc ) ?>/>
				<label for="<?php echo $this->get_field_id( 'show_font_switcher_desc' ) ?>"><?php _e( 'Show Fontsize Label', XF_TEXTDOMAIN ); ?></label>
			</p>
			<p><small><?php _e('Your Framework defined <em>body fontsize</em> will be enlarged to the given percentage values, if the visitor clicks one of this 2 options.', XF_TEXTDOMAIN) ?></small></p>
			<p>
				<label for="<?php echo $this->get_field_id( 'font_size_medium' ) ?>"><?php _e( 'Font Size (medium):', XF_TEXTDOMAIN ); ?></label>
				<input id="<?php echo $this->get_field_id( 'font_size_medium' ) ?>" name="<?php echo $this->get_field_name('font_size_medium') ?>" type="text" value="<?php echo $font_size_medium; ?>" size="3" /> %
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'font_size_maximum' ) ?>"><?php _e( 'Font Size (maximum):', XF_TEXTDOMAIN ); ?></label>
				<input id="<?php echo $this->get_field_id( 'font_size_maximum' ) ?>" name="<?php echo $this->get_field_name('font_size_maximum') ?>" type="text" value="<?php echo $font_size_maximum; ?>" size="3" /> %
			</p>
		</div>
		<h3><?php _e('Contrast Switcher Options', XF_TEXTDOMAIN) ?></h3>
		<p>
			<input class="x-thumbnail" id="<?php echo $this->get_field_id( 'show_contrast_switcher' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_contrast_switcher' ) ?>" value="1" <?php checked( 1, $show_contrast_switcher ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_contrast_switcher') ?>"><?php _e( 'Enable Contrast Selector', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id( 'show_contrast_switcher' ) ?>">
			<p>
				<input id="<?php echo $this->get_field_id( 'show_contrast_switcher_desc' ) ?>" name="<?php echo $this->get_field_name('show_contrast_switcher_desc') ?>" type="checkbox" value="1" <?php checked( 1, $show_contrast_switcher_desc ) ?>/>
				<label for="<?php echo $this->get_field_id( 'show_contrast_switcher_desc' ) ?>"><?php _e( 'Show Contrast Label', XF_TEXTDOMAIN ); ?></label>
			</p>
		</div>
		<h3><?php _e('Appearance Options', XF_TEXTDOMAIN) ?></h3>
		<p>
			<input class="x-thumbnail" id="<?php echo $this->get_field_id( 'show_horizontal' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_horizontal' ) ?>" value="1" <?php checked( 1, $show_horizontal ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_horizontal') ?>"><?php _e( 'Alignment Horizontal', XF_TEXTDOMAIN ) ?></label>
		</p>
		<p>
			<input class="x-thumbnail" id="<?php echo $this->get_field_id( 'flip_order' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'flip_order' ) ?>" value="1" <?php checked( 1, $flip_order ) ?>/>
			<label for="<?php echo $this->get_field_id( 'flip_order') ?>"><?php _e( 'Flip Selector Order', XF_TEXTDOMAIN ) ?></label>
		</p>
		<p>
			<?php if(XF_LOW_BARRIER_CSS_EXISTS) : ?>
			<small class="x-remark"><b><?php _e('Remark:', XF_TEXTDOMAIN);?></b> <?php _e('You are currently using a "low-barrier.css" file at your current child theme css folder. All appearance changes requested by selectors will depend at the CSS definitions there.',XF_TEXTDOMAIN); ?> <?php _e( 'You also have to regenerate your theme if you\'re using a "low-barrier.css" file.', XF_TEXTDOMAIN ) ?></small>
			<?php else : ?>
			<small class="x-remark"><b><?php _e('Remark:', XF_TEXTDOMAIN);?></b> <?php _e('You can also define more complex style switching, if you create a "low-barrier.css" file at your current child theme css folder beside the "theme.css" file. It will be added automatically and will replace the given automatic defaults.',XF_TEXTDOMAIN); ?></small>
			<?php endif; ?>
		</p>
		<?php
	}
}