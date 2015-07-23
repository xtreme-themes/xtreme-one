<?php

add_filter( 'xtreme-collect-widget-classes', create_function( '$classes', '$classes[] = "Xtreme_Flex_Post_Slider"; return $classes;' ) );

class Xtreme_Flex_Post_Slider extends Xtreme_Widget_Base {
	function __construct() {
		$widget_ops = array( 'classname' => 'xtreme_flex_slider', 'description' => __( 'A responsive Slider for your Posts', XF_TEXTDOMAIN ) );
		parent::__construct( __FILE__, 'xtreme-flex-slider', __( 'Xtreme Post FlexSlider', XF_TEXTDOMAIN ), $widget_ops );
	}
	
	function ensure_widget_scripts( $instance ) {
		global $xtreme_script_manager;
		$xtreme_script_manager->ensure_flexslider();
		$xtreme_script_manager->add_widget_data( 'xtreme-flexslider', $this->id, array(
			'animation' => $instance['animation'],
			'slideDirection' => $instance['slideDirection'],
			'controlsContainer' => '.flex-container',
			'mousewheel' => true,
			'pauseOnHover' => true,
			'slideshowSpeed' => ( int ) $instance['slideshowSpeed'],
			'animationDuration' => ( int ) $instance['animationDuration'] ,
			'randomize' => (isset( $instance['randomize'] ) && $instance['randomize'] === 1 ? true : false),
			'controlNav' => ( $instance['controlNav'] === 1 ? true : false ),
			'directionNav' => ( $instance['directionNav'] === 1 ? true : false ),
			'prevText' =>  __(	'previous', XF_TEXTDOMAIN ),
			'nextText' =>  __(	'next', XF_TEXTDOMAIN )
		) );
	}
	
	function widget($args, $instance) {
		global $wpdb;
		extract( $args );
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		$number_posts = absint( $instance['number_posts'] );
		$thumbnail_size = esc_attr( $instance['thumbnail_size'] );

		$r = new WP_Query(
			array(
				'cat' => esc_attr( $instance['category'] ),
				'showposts' => $number_posts,
				'offset' => ( int ) $instance['offset'],
				'nopaging' => 0,
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => esc_attr( $instance['orderby'] )
			)
		);
		if( $r->have_posts() ) :
			echo $before_widget;
			if ( $title ) echo $before_title . $title . $after_title;
			echo "<div id='x-" . $this->id ."' class='flex-container flex-post'><div class='flexslider'><ul class='slides'>\n";
			while ( $r->have_posts() ) : $r->the_post();
				echo '<li>'; 
				$html = '';
				if ( has_post_thumbnail() ):
					if ( $instance['content_type'] === 'nothing' ) {
						$html = '';
					} elseif ( $instance['content_type'] === 'title_only' ) {
						$html = '<h2>' . esc_attr( get_the_title() ) . '</h2>';
					} elseif ( $instance['content_type'] === 'xtreme_excerpt' ) {
						$html = xtreme_excerpt( $instance['excerpt_length'], esc_attr( $instance['excerpt_morelink_text'] ), esc_html( $instance['excerpt_more'] ), false );
					} elseif( $instance['content_type'] === 'both' ) {
						$html = '<h2>' . esc_attr( get_the_title() ) . '</h2>';
						$html .= xtreme_excerpt( $instance['excerpt_length'], esc_attr( $instance['excerpt_morelink_text'] ), esc_html( $instance['excerpt_more'] ), false );
					} ?>
					<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', XF_TEXTDOMAIN ); ?> <?php the_title_attribute(); ?>">
					<?php the_post_thumbnail( $thumbnail_size ) ?>
					</a>
				<?php else : ?>
					<img src="<?php echo XF_THEME_URI; ?>/images/white.gif" alt="" />
				<?php endif;
				if ( !empty( $html ) ) {
					echo '<div class="flex-caption">'.$html.'</div>';
				}
				echo '</li>';
			endwhile;
			echo "</ul></div></div>\n";
			echo $after_widget;
		endif;
		wp_reset_query();
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number_posts'] = absint( $new_instance['number_posts'] );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['offset'] = absint( strip_tags($new_instance['offset'] ) );
		$instance['thumbnail_size'] = strip_tags($new_instance['thumbnail_size']);
		$instance['content_type'] = strip_tags( $new_instance['content_type'] );
		$instance['excerpt_length'] = absint( strip_tags($new_instance['excerpt_length'] ) );
		$instance['excerpt_more'] = strip_tags( $new_instance['excerpt_more'] );
		$instance['excerpt_morelink_text'] = strip_tags( $new_instance['excerpt_morelink_text'] );
		$instance['animation'] = strip_tags( $new_instance['animation'] );
		$instance['slideDirection'] = strip_tags( $new_instance['slideDirection'] );
		$instance['slideshowSpeed'] = absint( strip_tags($new_instance['slideshowSpeed'] ) );
		$instance['animationDuration'] = absint( strip_tags($new_instance['animationDuration'] ) );
		$instance['randomize'] = isset( $new_instance['randomize'] ) ? 1 : 0;
		$instance['directionNav'] = isset( $new_instance['directionNav'] ) ? 1 : 0;
		$instance['controlNav'] = isset( $new_instance['controlNav'] ) ? 1 : 0;
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number_posts = isset( $instance['number_posts'] ) ? min( max( 2, $instance['number_posts'] ), 15 ) : 3;
		$category = isset( $instance['category'] ) ? $instance['category'] : get_option( 'default_category' );
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
		$offset = (isset( $instance['offset'] ) && !empty($instance['offset'] ) ) ? $instance['offset'] : 0;
		$thumbnail_size = (isset($instance['thumbnail_size'])) ? $instance['thumbnail_size'] : 'large';
		$content_type = isset( $instance['content_type'] ) ? $instance['content_type'] : 'nothing';
		$excerpt_length = isset( $instance['excerpt_length'] ) ? min(max(5, $instance['excerpt_length']), 80) : 20;
		$excerpt_more = isset( $instance['excerpt_more'] ) ? $instance['excerpt_more'] : '...';
		$excerpt_morelink_text = isset( $instance['excerpt_morelink_text'] ) ? $instance['excerpt_morelink_text'] : __( 'Read more...', XF_TEXTDOMAIN );
		$animation = isset( $instance['animation'] ) ? $instance['animation'] : 'slide';
		$slideDirection = isset( $instance['slideDirection'] ) ? $instance['slideDirection'] : 'horizontal';
		$slideshowSpeed = isset($instance['slideshowSpeed']) ? min(max(2000, $instance['slideshowSpeed']), 8000) : 7000;
		$animationDuration = isset($instance['animationDuration']) ? min(max(100, $instance['animationDuration']), 1000) : 700;
		$randomize = isset( $instance['randomize'] ) ? $instance['randomize'] : 0;
		$directionNav = isset( $instance['directionNav'] ) ? $instance['directionNav'] : 0;
		$controlNav = isset( $instance['controlNav'] ) ? $instance['controlNav'] : 0;
		?>
		<h3><?php _e( 'Post Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title) ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_posts' ) ?>"><?php _e('Number of Posts:', XF_TEXTDOMAIN) ?></label>
			<input id="<?php echo $this->get_field_id( 'number_posts' ) ?>" name="<?php echo $this->get_field_name('number_posts') ?>" type="text" value="<?php echo esc_attr($number_posts) ?>" size="3" />
			<br /><small><?php printf( __( '(at most %d)', XF_TEXTDOMAIN ), 15 ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'category' ) ?>"><?php _e( 'Category', XF_TEXTDOMAIN ) ?>:</label>
			<?php wp_dropdown_categories(
				array(
					'name' => $this->get_field_name( 'category' ),
					'selected' => $category,
					'orderby' => 'name' ,
					'hierarchical' => 1,
					'show_option_all' => __('All Categories', XF_TEXTDOMAIN),
					'hide_empty' => 1
					)
				) ?>
		</p>
		<?php $sort = array(
			'date' => __( 'Post Date', XF_TEXTDOMAIN ),
			'title' => __( 'Post Title', XF_TEXTDOMAIN ),
			'ID' => __( 'Post ID', XF_TEXTDOMAIN ),
			'rand' => __( 'Random', XF_TEXTDOMAIN ),
			'comment_count' => __( 'Comment Count', XF_TEXTDOMAIN )
		) ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'orderby' ) ?>"><?php _e( 'Order By:', XF_TEXTDOMAIN ) ?></label>
			<select id="<?php echo $this->get_field_id( 'orderby' ) ?>" name="<?php echo $this->get_field_name( 'orderby' ) ?>">
				<?php foreach( $sort as $key => $val ) : ?>
				<option value="<?php echo $key ?>" <?php selected( $key, $orderby ) ?>><?php echo esc_attr( $val ) ?></option>
				<?php endforeach ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'offset' ) ?>"><?php _e( 'Post Offset:', XF_TEXTDOMAIN ) ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'offset' ) ?>" name="<?php echo $this->get_field_name( 'offset' ) ?>" value="<?php echo esc_attr( $offset ) ?>" size="3" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('thumbnail_size'); ?>"><?php _e('Image Size:', XF_TEXTDOMAIN); ?></label>
			<select class="x_thumbnailsize" id="<?php echo $this->get_field_id('thumbnail_size'); ?>" name="<?php echo $this->get_field_name('thumbnail_size'); ?>">
			<?php global $_wp_additional_image_sizes;
				$sizes = get_intermediate_image_sizes();
				foreach( $sizes as $size) : ?>
					<option value="<?php echo $size; ?>" <?php selected($size, $thumbnail_size, true); ?> style="padding-right: 5px;"><?php echo esc_attr($size); ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<?php $type = array(
			'nothing' => __( 'Nothing', XF_TEXTDOMAIN ),
			'title_only' => __( 'Title only', XF_TEXTDOMAIN ),
			'xtreme_excerpt' => __( 'Xtreme Excerpt', XF_TEXTDOMAIN ),
			'both' => __('Title and Xtreme Excerpt', XF_TEXTDOMAIN )
		) ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'content_type' ) ?>"><?php _e( 'Content type:', XF_TEXTDOMAIN ) ?></label>
			<select class="x-content" id="<?php echo $this->get_field_id( 'content_type' ) ?>" name="<?php echo $this->get_field_name( 'content_type' ) ?>">
				<?php foreach( $type as $c => $d ) : ?>
				<option value="<?php echo $c ?>" <?php selected( $c, $content_type ); ?>><?php echo esc_attr( $d ) ?></option>
				<?php endforeach; ?>
			</select>
		</p>
		<div class="<?php echo $this->get_field_id( 'content_type' ) ?>">
			<p class="excerpt_length">
				<label for="<?php echo $this->get_field_id( 'excerpt_length' ) ?>"><?php _e( 'Excerpt length:', XF_TEXTDOMAIN ); ?></label>
				<input id="<?php echo $this->get_field_id( 'excerpt_length' ) ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ) ?>" type="text" value="<?php echo esc_attr( $excerpt_length ) ?>" size="3" /> <?php _e('Words', XF_TEXTDOMAIN) ?>
				<br /><small><?php printf( __( '(at most %d)', XF_TEXTDOMAIN ), 80 ) ?></small>
			</p>
			<p class="excerpt_more">
				<label for="<?php echo $this->get_field_id('excerpt_more') ?>"><?php _e('Excerpt more Phrase:', XF_TEXTDOMAIN) ?></label>
				<input id="<?php echo $this->get_field_id('excerpt_more') ?>" name="<?php echo $this->get_field_name('excerpt_more') ?>" type="text" value="<?php echo esc_html( $excerpt_more ) ?>"  />
			</p>
			<p class="excerpt_morelink_text">
				<label for="<?php echo $this->get_field_id( 'excerpt_morelink_text' ) ?>"><?php _e( 'Excerpt More Link Text:', XF_TEXTDOMAIN ) ?></label>
				<input id="<?php echo $this->get_field_id( 'excerpt_morelink_text' ) ?>" name="<?php echo $this->get_field_name( 'excerpt_morelink_text' ) ?>" type="text" value="<?php echo esc_html( $excerpt_morelink_text ) ?>" />
			</p>
		</div>
		<h3><?php _e( 'Javascript Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
			<label for="<?php echo $this->get_field_id( 'animation' ) ?>"><?php _e( 'Animation:', XF_TEXTDOMAIN ) ?></label>
			<select id="<?php echo $this->get_field_id( 'animation' ) ?>" name="<?php echo $this->get_field_name( 'animation' ) ?>">
				<?php
				$effects = array( 'fade', 'slide');
				foreach ( $effects as $ef ) :
				?>
				<option value="<?php echo esc_attr( $ef ) ?>" <?php selected( $ef, $animation ) ?>><?php echo esc_attr( $ef ) ?></option>
				<?php endforeach ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'slideDirection' ) ?>"><?php _e( 'Direction:', XF_TEXTDOMAIN ) ?></label>
			<select id="<?php echo $this->get_field_id( 'slideDirection' ) ?>" name="<?php echo $this->get_field_name( 'slideDirection' ) ?>">
				<?php
				$direction = array( 'horizontal', 'vertical');
				foreach ( $direction as $d ) :
				?>
				<option value="<?php echo esc_attr( $d ) ?>" <?php selected( $d, $slideDirection ) ?>><?php echo esc_attr( $d ) ?></option>
				<?php endforeach ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'slideshowSpeed' ) ?>"><?php _e( 'Slideshow Speed:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'slideshowSpeed' ) ?>" name="<?php echo $this->get_field_name( 'slideshowSpeed' ) ?>" type="text" value="<?php echo esc_attr( $slideshowSpeed ) ?>" size="4" /> ms
			<br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 2000, 8000 ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'animationDuration' ) ?>"><?php _e( 'Animation Duration:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'animationDuration' ) ?>" name="<?php echo $this->get_field_name( 'animationDuration' ) ?>" type="text" value="<?php echo esc_attr( $animationDuration ) ?>" size="4" /> ms
			<br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 100, 1000 ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'randomize' ) ?>"><?php _e( 'Randomize Slices:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'randomize' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'randomize' ) ?>" value="1" <?php checked( 1, $randomize ) ?>/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'directionNav' ) ?>"><?php _e( 'Show DirectionNav:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'directionNav' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'directionNav' ) ?>" value="1" <?php checked( 1, $directionNav ) ?>/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'controlNav' ) ?>"><?php _e( 'Show ControlNav:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'controlNav' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'controlNav' ) ?>" value="1" <?php checked( 1, $controlNav ) ?>/>
		</p>
		<?php
	}
}
