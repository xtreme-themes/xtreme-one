<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Carousel"; return $classes;')); 
 
class Xtreme_Carousel extends Xtreme_Widget_Base {
	function __construct() {
		$widget_ops = array( 'classname' => 'xtreme_carousel', 'description' => __('A carousel for your posts', XF_TEXTDOMAIN ) );
		parent::__construct(__FILE__, 'xtreme-carousel', __('Xtreme Post Carousel', XF_TEXTDOMAIN ), $widget_ops);
	}
	
	function ensure_widget_scripts($instance) {
		global $xtreme_script_manager;
		$xtreme_script_manager->ensure_carousel();
		$xtreme_script_manager->add_widget_data('xtreme-carousel', $this->id, array(
			'infinite' => $instance['animation'] === 'circular' ? false : true,
			'circular' => $instance['animation'] === 'circular' ? true : false,
			'direction' => $instance['direction'],
			'items_visible' => $instance['items_visible'],
			'items_height' => $instance['item_size_height'] === 'value' ? $instance['item_height']+2*$instance['item_margin'] : null,
			'items_width' => $instance['item_size_width'] === 'value' ? $instance['item_width']+2*$instance['item_margin'] : null,
			'scroll_items' => $instance['items_scroll'],
			'scroll_fx' => $instance['item_fx'],
			'scroll_pauseonhover' => $instance['pause_on_hover'] ? true : false,
			'scroll_duration' => $instance['scroll_duration'],
			//'prev_button'
			'prev_key' => 'left',
			//'next_button'
			'next_key' => 'right',
			//'pagination_container'
			'pagination_keys' => true,
			'auto_play' => $instance['play'] ? true : false,
			'auto_delay' => $instance['delay'])
		);
	}

	function widget($args, $instance ) {
		extract( $args );
		$html = '';
		$li_style = '';
		$margin = 2 * $instance['item_margin'];
		if ( $instance['item_size_height'] === 'value' ) {
			$h = "height:".esc_attr( $instance['item_height'] ) . "px;";
		}else{
			$h ='';
		}
		if ( $instance['item_size_width'] === 'value' ) {
			$w = "width:". esc_attr( $instance['item_width'] ) . "px;";
		}else{
			$w ='';
		}
		$li_style = ' style="'. $h .' '. $w .' margin:' . esc_attr( $instance['item_margin'] ) . 'px"';

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		$number_posts = absint( $instance['number_posts'] );
		$thumbnail_size = esc_attr( $instance['thumbnail_size'] );
		$direction = esc_attr( $instance['direction'] );
		$r = new WP_Query(
			array(
				'cat' => esc_attr( $instance['category'] ),
				'showposts' => $number_posts,
				'offset' => absint( $instance['offset'] ),
				'nopaging' => 0,
				'post_type' => 'post',
				'post_status' => 'publish',
				'orderby' => esc_attr( $instance['orderby'] )
			)
		);

		if ( $r->have_posts() ):
			$i = 0;
			echo $before_widget;
			if ( $title ) echo $before_title . $title . $after_title;
			echo '<ul id="x-' . $this->id . '" class="x-carousel">';
			$hide = '';
			while ( $r->have_posts() ) : $r->the_post();
				$i++;
				$cls = 'class="cfs-li-'.$i.'" ';
				?><li <?php echo $cls; echo $li_style; ?>><?php 
				if ( $instance['show_thumbnail'] && has_post_thumbnail() ) : ?>
					<?php if ( $instance['use_permalink'] )  {  ?>
						<a class="<?php echo esc_attr( $instance['image_alignment'] ) ?>" href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', XF_TEXTDOMAIN ) ?> <?php the_title_attribute() ?>">
					        <?php the_post_thumbnail( $thumbnail_size ) ?>
						</a>
					<?php
					}
					else {
						the_post_thumbnail( $thumbnail_size );
					}
					?>
				<?php endif; ?>
				<?php if ( $instance['show_posttitle'] || $instance['show_excerpt'] || $instance['show_byline'] ) : ?>
					<div class="fred-content">
					<?php if ( $instance['show_posttitle'] ) :
						if ( $instance['use_permalink'] )  {  ?>
							<h2 class="posttitle"><a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', XF_TEXTDOMAIN ) ?> <?php the_title_attribute() ?>"> <?php the_title() ?></a></h2>
						<?php
						}
						else { ?>
							<h2 class="posttitle"><?php the_title() ?></h2>
						<?php }
					endif;
					if ( $instance['show_byline'] ) : 
						xtreme_byline();
					endif;
					if ( $instance['show_excerpt'] ) :
						xtreme_excerpt( $instance['excerpt_length'], esc_html($instance['excerpt_morelink_text']), esc_html( $instance['excerpt_more'] ) );
					endif; ?>
					</div>
				<?php endif; ?>
				</li>
			<?php
			endwhile;
			echo '</ul>';
			if ( $instance['prevnext']) {
				$html .= '<a class="prev dir-' . $direction . '" id="prev-' . $this->id . '" href="#"><span>prev</span></a>';
				$html .= '<a class="next dir-' . $direction . '" id="next-' . $this->id . '" href="#"><span>next</span></a>';
			}
			if ( $instance['pagination']) {
				$html .= '<div class="cf-pagination" id="pag-' . $this->id . '"></div>';
			}
			echo $html;
			echo $after_widget;
		endif;
		wp_reset_query();
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number_posts'] = absint( strip_tags( $new_instance['number_posts'] ) );
		$instance['category'] = strip_tags( $new_instance['category'] );
		$instance['orderby'] = strip_tags( $new_instance['orderby'] );
		$instance['offset'] = absint( strip_tags($new_instance['offset'] ) );
		$instance['thumbnail_size'] = strip_tags($new_instance['thumbnail_size']);
		$instance['image_alignment'] = strip_tags($new_instance['image_alignment']);
		$instance['show_thumbnail'] = (isset($new_instance['show_thumbnail'])) ? 1 : 0;
		$instance['show_posttitle'] = (isset($new_instance['show_posttitle'])) ? 1 : 0;
		$instance['use_permalink'] = (isset($new_instance['use_permalink'])) ? 1 : 0;
		$instance['show_byline'] = (isset($new_instance['show_byline'])) ? 1 : 0;
		$instance['show_excerpt'] = (isset($new_instance['show_excerpt'])) ? 1 : 0;
		$instance['excerpt_length'] = absint(strip_tags($new_instance['excerpt_length']));
		$instance['excerpt_more'] = strip_tags($new_instance['excerpt_more']);
		$instance['excerpt_morelink_text'] = strip_tags($new_instance['excerpt_morelink_text']);
		$instance['item_size_height'] = (isset($new_instance['item_size_height'])) ? strip_tags($new_instance['item_size_height']) : 'auto';
		$instance['item_size_width'] = (isset($new_instance['item_size_width'])) ? strip_tags($new_instance['item_size_width']) : 'auto';
		$instance['item_width'] = strip_tags($new_instance['item_width']);
		$instance['item_height'] = strip_tags($new_instance['item_height']);
		$instance['item_margin'] = intval( strip_tags( $new_instance['item_margin'] ) );
		$instance['items_visible'] = absint( strip_tags( $new_instance['items_visible'] ) );
		$instance['items_scroll'] = absint( strip_tags( $new_instance['items_scroll'] ) );
		$instance['scroll_duration'] = absint( strip_tags( $new_instance['scroll_duration'] ) );
		$instance['direction'] = strip_tags( $new_instance['direction'] );
		$instance['animation'] = strip_tags( $new_instance['animation'] );
		$instance['pause_on_hover'] = isset( $new_instance['pause_on_hover'] ) ? 1 : 0;
		$instance['play'] = isset( $new_instance['play'] ) ? 1 : 0;
		$instance['delay'] = absint( strip_tags( $new_instance['delay'] ) );
		$instance['pagination'] = isset( $new_instance['pagination'] ) ? 1 : 0;
		$instance['prevnext'] = isset( $new_instance['prevnext'] ) ? 1 : 0;
		$instance['item_fx'] = strip_tags( $new_instance['item_fx'] );

	return $instance;
	}

	function form($instance) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$number_posts = isset( $instance['number_posts'] ) ? min( max( 4, $instance['number_posts'] ), 20 ) : 4;
		$category = isset( $instance['category'] ) ? $instance['category'] : get_option( 'default_category' );
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
		$offset = (isset( $instance['offset'] ) && !empty($instance['offset'] ) ) ? $instance['offset'] : 0;
		$show_thumbnail = isset($instance['show_thumbnail']) ? $instance['show_thumbnail'] : 1;
		$thumbnail_size = (isset($instance['thumbnail_size'])) ? $instance['thumbnail_size'] : 'thumbnail';
		$image_alignment = isset($instance['image_alignment']) ? $instance['image_alignment'] : 'alignnone';
		$show_posttitle = isset($instance['show_posttitle']) ? $instance['show_posttitle'] : 0;
		$use_permalink = isset($instance['use_permalink']) ? $instance['use_permalink'] : 0;
		$show_byline = isset($instance['show_byline']) ? $instance['show_byline'] : 0;
		$show_excerpt = isset($instance['show_excerpt']) ? $instance['show_excerpt'] : 0;
		$excerpt_length = isset($instance['excerpt_length']) ? min(max(5, $instance['excerpt_length']), 80) : 40;
		$excerpt_more = isset($instance['excerpt_more']) ? $instance['excerpt_more'] : '...';
		$excerpt_morelink_text = isset($instance['excerpt_morelink_text']) ? $instance['excerpt_morelink_text'] : __('Read more...', XF_TEXTDOMAIN);
		$item_size_width = isset($instance['item_size_width']) ? $instance['item_size_width'] : 'auto';
		$item_width = isset($instance['item_width']) ? min(max(20, $instance['item_width']),1600) : 150;
		$item_size_height = isset($instance['item_size_height']) ? $instance['item_size_height'] : 'auto';
		$item_height = isset($instance['item_height']) ? min(max(10, $instance['item_height']),1000) : 150;
		$item_margin = isset($instance['item_margin']) ? min(max(0, $instance['item_margin']),100) : 5;
		$item_fx = isset( $instance['item_fx'] ) ? $instance['item_fx'] : 'scroll';
		$items_visible = isset( $instance['items_visible'] ) ? min( max( 1, $instance['items_visible'] ), 20 ) : 4;
		$items_scroll = isset( $instance['items_scroll'] ) ? min( max( 1, $instance['items_scroll'] ), 20 ) : 1;
		$direction = isset( $instance['direction'] ) ? $instance['direction'] : 'right';
		$animation = isset( $instance['animation'] ) ? $instance['animation'] : 'circular';
		$scroll_duration = isset( $instance['scroll_duration'] ) ? min( max( 500, $instance['scroll_duration'] ), 5000 ) : 1000;
		$pause_on_hover = isset( $instance['pause_on_hover'] ) ? $instance['pause_on_hover'] : 0;
		$play = isset( $instance['play'] ) ? $instance['play'] : 1;
		$delay = isset( $instance['delay'] ) ? min( max( 500, $instance['delay'] ), 5000 ) : 1000;
		$pagination = isset( $instance['pagination'] ) ? $instance['pagination'] : 0;
		$prevnext = isset( $instance['prevnext'] ) ? $instance['prevnext'] : 1;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title) ?>" />
		</p>
		<h3><?php _e( 'Post Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
			<label for="<?php echo $this->get_field_id( 'number_posts' ) ?>"><?php _e('Number of Posts:', XF_TEXTDOMAIN) ?></label>
			<input id="<?php echo $this->get_field_id( 'number_posts' ) ?>" name="<?php echo $this->get_field_name('number_posts') ?>" type="text" value="<?php echo esc_attr($number_posts) ?>" size="3" />
			<br /><small><?php printf( __( '(at most %d)', XF_TEXTDOMAIN ), 20 ) ?></small>
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
			<input class="x-switcher" id="<?php echo $this->get_field_id('show_thumbnail') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_thumbnail') ?>" value="1" <?php checked(1, $show_thumbnail) ?>/>
			<label for="<?php echo $this->get_field_id('show_thumbnail') ?>"><?php _e('Show Featured Image', XF_TEXTDOMAIN) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id('show_thumbnail') ?>">
			<p>
				<label for="<?php echo $this->get_field_id('thumbnail_size') ?>"><?php _e('Size:', XF_TEXTDOMAIN) ?></label>
				<select id="<?php echo $this->get_field_id('thumbnail_size') ?>" name="<?php echo $this->get_field_name('thumbnail_size') ?>">
				<?php global $_wp_additional_image_sizes;
				$sizes = get_intermediate_image_sizes();
				foreach( $sizes as $size) : ?>
					<option value="<?php echo $size ?>" <?php selected($size, $thumbnail_size, true) ?>><?php echo esc_attr($size) ?></option>
				<?php endforeach; ?>
				</select>
			</p>
			<p>
			<?php $align = array(
				'alignnone' => __('none', XF_TEXTDOMAIN),
				'alignleft' => __('left', XF_TEXTDOMAIN),
				'alignright' => __('right', XF_TEXTDOMAIN),
				'aligncenter' => __('center', XF_TEXTDOMAIN)
			) ?>
				<label for="<?php echo $this->get_field_id('image_alignment') ?>"><?php _e('Image Alignment:', XF_TEXTDOMAIN) ?></label>
				<select id="<?php echo $this->get_field_id('image_alignment') ?>" name="<?php echo $this->get_field_name('image_alignment') ?>">
				<?php foreach($align as $a => $b): ?>
					<option value="<?php echo $a ?>" <?php selected($a, $image_alignment) ?>><?php echo esc_attr($b) ?></option>
				<?php endforeach; ?>
				</select>
			</p>
		</div>
		<p>
			<input id="<?php echo $this->get_field_id('show_posttitle') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_posttitle') ?>" value="1" <?php checked(1, $show_posttitle) ?>/>
			<label for="<?php echo $this->get_field_id('show_posttitle') ?>"><?php _e('Show Post Title', XF_TEXTDOMAIN) ?></label>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id('use_permalink') ?>" type="checkbox" name="<?php echo $this->get_field_name('use_permalink') ?>" value="1" <?php checked(1, $use_permalink) ?>/>
			<label for="<?php echo $this->get_field_id('use_permalink') ?>"><?php _e('Use Link to Post', XF_TEXTDOMAIN) ?></label>
		</p>
		<p>
		<p>
			<input id="<?php echo $this->get_field_id('show_byline') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_byline') ?>" value="1" <?php checked(1, $show_byline) ?>/>
			<label for="<?php echo $this->get_field_id('show_byline') ?>"><?php _e('Show Byline', XF_TEXTDOMAIN) ?></label>
		</p>
		<p>
			<input class="x-switcher" id="<?php echo $this->get_field_id('show_excerpt') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_excerpt') ?>" value="1" <?php checked(1, $show_excerpt) ?>/>
			<label for="<?php echo $this->get_field_id('show_excerpt') ?>"><?php _e('Show Xtreme Excerpt', XF_TEXTDOMAIN) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id('show_excerpt') ?>">
			<p class="excerpt_length">
				<label for="<?php echo $this->get_field_id('excerpt_length') ?>"><?php _e('Excerpt length:', XF_TEXTDOMAIN); ?></label>
				<input id="<?php echo $this->get_field_id('excerpt_length') ?>" name="<?php echo $this->get_field_name('excerpt_length') ?>" type="text" value="<?php echo esc_attr($excerpt_length) ?>" size="3" /> <?php _e('Words', XF_TEXTDOMAIN) ?>
				<br /><small><?php printf( __( '(at most %s)', XF_TEXTDOMAIN), 80 ) ?></small>
			</p>
			<p class="excerpt_more">
				<label for="<?php echo $this->get_field_id('excerpt_more') ?>"><?php _e('Excerpt more Phrase:', XF_TEXTDOMAIN) ?></label>
				<input id="<?php echo $this->get_field_id('excerpt_more') ?>" name="<?php echo $this->get_field_name('excerpt_more') ?>" type="text" value="<?php echo esc_html($excerpt_more) ?>"  />
			</p>
			<p class="excerpt_morelink_text">
				<label for="<?php echo $this->get_field_id('excerpt_morelink_text') ?>"><?php _e('Excerpt more link text:', XF_TEXTDOMAIN) ?></label>
				<input id="<?php echo $this->get_field_id('excerpt_morelink_text') ?>" name="<?php echo $this->get_field_name('excerpt_morelink_text') ?>" type="text" value="<?php echo esc_html($excerpt_morelink_text) ?>" />
			</p>
		</div>
		<h3><?php _e( 'Carousel Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
			<label for="<?php echo $this->get_field_id( 'items_visible' ) ?>"><?php _e( 'Items visible inside the carousel:', XF_TEXTDOMAIN ) ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'items_visible' ) ?>" name="<?php echo $this->get_field_name( 'items_visible' ) ?>" value="<?php echo esc_attr( $items_visible ) ?>" size="3" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'items_scroll' ) ?>"><?php _e( 'How many items should be scrolled at a time:', XF_TEXTDOMAIN ) ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'items_scroll' ) ?>" name="<?php echo $this->get_field_name( 'items_scroll' ) ?>" value="<?php echo esc_attr( $items_scroll ) ?>" size="3" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('item_size_width') ?>"><?php _e('Item Width:', XF_TEXTDOMAIN) ?></label>
			<select class="x_itemsize" id="<?php echo $this->get_field_id('item_size_width') ?>" name="<?php echo $this->get_field_name('item_size_width') ?>">
				<option value="auto" <?php selected('auto', $item_size_width, true) ?>><?php _e('auto', XF_TEXTDOMAIN) ?></option>
				<option value="value" <?php selected('value', $item_size_width, true) ?>><?php _e('Enter a value', XF_TEXTDOMAIN) ?></option>
			</select>
		</p>
		<div class="<?php echo $this->get_field_id('item_size_width') ?>">
			<small><?php _e('Enter the values for the Item Width:', XF_TEXTDOMAIN) ?></small>
			<p>
				<label for="<?php echo $this->get_field_id('item_width') ?>"><?php _e('Item Width:', XF_TEXTDOMAIN) ?></label>
				<input id="<?php echo $this->get_field_id('item_width') ?>" name="<?php echo $this->get_field_name('item_width') ?>" type="text" value="<?php echo esc_attr($item_width) ?>" size="3" /> px
			</p>
		</div>
		<p>
			<label for="<?php echo $this->get_field_id('item_size_height') ?>"><?php _e('Item Height:', XF_TEXTDOMAIN) ?></label>
			<select class="x_itemsize" id="<?php echo $this->get_field_id('item_size_height') ?>" name="<?php echo $this->get_field_name('item_size_height') ?>">
				<option value="auto" <?php selected('auto', $item_size_height, true) ?>><?php _e('auto', XF_TEXTDOMAIN) ?></option>
				<option value="value" <?php selected('value', $item_size_height, true) ?>><?php _e('Enter a value', XF_TEXTDOMAIN) ?></option>
			</select>
		</p>
		<div class="<?php echo $this->get_field_id('item_size_height') ?>">
			<small><?php _e('Enter the values for the Item Height:', XF_TEXTDOMAIN) ?></small>
			<p>
				<label for="<?php echo $this->get_field_id('item_height') ?>"><?php _e('Item Height:', XF_TEXTDOMAIN) ?></label>
				<input id="<?php echo $this->get_field_id('item_height') ?>" name="<?php echo $this->get_field_name('item_height') ?>" type="text" value="<?php echo esc_attr($item_height) ?>" size="3" /> px
			</p>
		</div>
		<p>
			<label for="<?php echo $this->get_field_id('item_margin') ?>"><?php _e('Item Margin:', XF_TEXTDOMAIN) ?></label>
			<input id="<?php echo $this->get_field_id('item_margin') ?>" name="<?php echo $this->get_field_name('item_margin') ?>" type="text" value="<?php echo esc_attr($item_margin) ?>" size="3" /> px
		</p>
		<?php $d_array = array(
			'right' => __( 'right', XF_TEXTDOMAIN ),
			'left' => __( 'left', XF_TEXTDOMAIN ),
			'up' => __( 'up', XF_TEXTDOMAIN ),
			'down' => __('down', XF_TEXTDOMAIN )
		) ?>
		<h3><?php _e( 'Javascript Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
			<label for="<?php echo $this->get_field_id( 'direction' ) ?>"><?php _e( 'Direction:', XF_TEXTDOMAIN ) ?></label>
			<select class="x-content" id="<?php echo $this->get_field_id( 'direction' ) ?>" name="<?php echo $this->get_field_name( 'direction' ) ?>">
			<?php foreach( $d_array as $c => $d ) : ?>
				<option value="<?php echo $c ?>" <?php selected( $c, $direction ); ?>><?php echo esc_attr( $d ) ?></option>
			<?php endforeach; ?>
			</select>
		</p>
		<?php $fx = array('scroll', 'fade', 'crossfade', 'cover', 'uncover'); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'item_fx' ) ?>"><?php _e( 'FX:', XF_TEXTDOMAIN ) ?></label>
			<select class="x-content" id="<?php echo $this->get_field_id( 'item_fx' ) ?>" name="<?php echo $this->get_field_name( 'item_fx' ) ?>">
			<?php foreach( $fx as $k ) : ?>
				<option value="<?php echo esc_attr( $k ) ?>" <?php selected( $k, $item_fx ); ?>><?php echo esc_attr( $k ) ?></option>
			<?php endforeach; ?>
			</select>
		</p>
		<?php $w_array = array('circular','infinite'); ?>
		<p>
			<label for="<?php echo $this->get_field_id( 'animation' ) ?>"><?php _e( 'Animation:', XF_TEXTDOMAIN ) ?></label>
			<select class="x-content" id="<?php echo $this->get_field_id( 'animation' ) ?>" name="<?php echo $this->get_field_name( 'animation' ) ?>">
			<?php foreach( $w_array as $k ) : ?>
				<option value="<?php echo esc_attr($k) ?>" <?php selected( $k, $animation ); ?>><?php echo esc_attr( $k ) ?></option>
			<?php endforeach; ?>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'scroll_duration' ) ?>"><?php _e( 'Duration:', XF_TEXTDOMAIN ) ?></label>
			<input type="text" id="<?php echo $this->get_field_id( 'scroll_duration' ) ?>" name="<?php echo $this->get_field_name( 'scroll_duration' ) ?>" value="<?php echo esc_attr( $scroll_duration ) ?>" size="4" /> ms
			<br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 500, 5000 ) ?></small>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'pause_on_hover' ) ?>"><?php _e( 'Pause on Hover:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'pause_on_hover' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'pause_on_hover' ) ?>" value="1" <?php checked( 1, $pause_on_hover ) ?>/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'play' ) ?>"><?php _e( 'Auto Play:', XF_TEXTDOMAIN ); ?></label>
			<input class="x-switcher" id="<?php echo $this->get_field_id( 'play' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'play' ) ?>" value="1" <?php checked( 1, $play ) ?>/>
		</p>
		<div class="<?php echo $this->get_field_id('play') ?>">
			<p>
				<label for="<?php echo $this->get_field_id( 'delay' ) ?>"><?php _e( 'Delay before Start:', XF_TEXTDOMAIN ) ?></label>
				<input type="text" id="<?php echo $this->get_field_id( 'delay' ) ?>" name="<?php echo $this->get_field_name( 'delay' ) ?>" value="<?php echo esc_attr( $delay ) ?>" size="4" /> ms
				<br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 500, 5000 ) ?></small>
			</p>
		</div>
		<p>
			<label for="<?php echo $this->get_field_id( 'prevnext' ) ?>"><?php _e( 'Show Prev/Next:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'prevnext' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'prevnext' ) ?>" value="1" <?php checked( 1, $prevnext ) ?>/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'pagination' ) ?>"><?php _e( 'Show Pagination:', XF_TEXTDOMAIN ); ?></label>
			<input id="<?php echo $this->get_field_id( 'pagination' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'pagination' ) ?>" value="1" <?php checked( 1, $pagination ) ?>/>
		</p>
		<?php
	}
}