<?php

add_filter( 'xtreme-collect-widget-classes', 'xtreme_category_tabs_widget' );
function xtreme_category_tabs_widget( $classes ) {
	
	$classes[] = "Xtreme_Category_Tabs_Widget";
	
	return $classes;
}

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Category_Tabs_Widget"; return $classes;'));
class Xtreme_Category_Tabs_Widget extends Xtreme_Widget_Base {

	function __construct() {
		
		$widget_ops = array(
			'classname' => 'xtreme_category_tabs',
			'description' => __( 'Each Category separated in Tabs.', XF_TEXTDOMAIN ) 
		);
		
		parent::__construct(__FILE__, 'xtreme-category-tabs', __( 'Xtreme Accessible Post Tabber', XF_TEXTDOMAIN ), $widget_ops );
	}
	
	function ensure_widget_scripts($instance) {
		global $xtreme_script_manager;
		
		$xtreme_script_manager->ensure_accessible_tabs();
		$xtreme_script_manager->add_widget_data('xtreme-accessible-tabs', $this->id, array(
			'fx' => $instance['fx'],
			'fxspeed' => $instance['fxspeed'],
			'syncheights' => $instance['syncheight'] === 1 ? true : false)
		);
	}

    function widget( $args, $instance ) {
		global $wpdb;

		extract( $args );
	    $title      = empty( $instance['title'] ) ? '' : $instance['title'];
		$title      = apply_filters( 'widget_title', $title );
		$columns    = absint( $instance['columns'] );
		$thumbnail_size = esc_attr( $instance['thumbnail_size'] );

	    $el = 'div';
		$html5 = xtreme_is_html5();

		if ( $html5 ) {
		    $el = 'article';
		}

		$contentorder = array(
		    'content'   => isset( $instance['content_pos'] ) ? ( int ) $instance['content_pos'] : 4,
		    'byline'    => isset( $instance['byline_pos'] ) ? (int) $instance['byline_pos'] : 3,
		    'posttitle' => isset( $instance['posttitle_pos'] ) ? ( int ) $instance['posttitle_pos'] : 2,
		    'thumbnail' => isset( $instance['image_pos'] ) ? ( int ) $instance['image_pos'] : 1
		);
		$suppress_posttitle_link = isset( $instance['suppress_posttitle_link']) ? (bool)($instance['suppress_posttitle_link'] == 1) : false;

		$tag = isset ( $instance['posttitle_tag'] ) ? esc_attr( $instance['posttitle_tag'] ) : 'h2';
		if ( !isset( $instance['content_pos'] ) ) {
		    $instance['show_posttitle'] = 1;
		    $instance['show_content'] = 1;
		}
		asort( $contentorder, SORT_NUMERIC );
		foreach ( $contentorder as $key => $value ) {
		    if ( $instance['show_' . $key] ) {
				$new_sort[] = $key;
		    }
		}

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}

		echo '<div>';
		for ( $i = 0; $i < $columns; $i++ ) :

			$key = 'category-'. $i;
			if( !array_key_exists( $key, $instance ) ){
				continue;
			}

			$query_args = array(
				'cat'           => $instance[ $key ],
				'showposts'     => 1,
				'offset'        => (int) $instance['offset'],
				'nopaging'      => 0,
				'post_type'     => 'post',
				'post_status'   => 'publish',
				'orderby'       => esc_attr( $instance['orderby'] )
			);

			$r = new WP_Query( $query_args );
		    if( !$r->have_posts() ) {
			    continue;
		    }

			while ( $r->have_posts() ) : $r->the_post();

				$cat = get_category( $instance[ $key ] );

				if( is_wp_error( $cat ) ) {
					continue;
				}

				$cls = 'category-' . $cat->slug;

				?>
				<<?php echo $el ?> class="<?php echo join(' ', get_post_class( 'tab-content' ) ) ?>">
					<h5 class="<?php echo $cls ?>" id="<?php echo $cls; ?>"><?php echo esc_attr( $cat->name ) ?></h5>
					<?php
					if(isset($new_sort)) {
						$c = count( $new_sort );
						 for ( $y = 0; $y < $c; $y++ ) {
							switch ( $new_sort[$y] ) {
							    case 'thumbnail':
									xtreme_post_thumbnail( $thumbnail_size, esc_attr( $instance['image_alignment'] ) );
								   break;

							    case 'posttitle':
								   if ( $html5 ) echo '<header>';
								   xtreme_widget_post_headline( $tag, ! $suppress_posttitle_link );
								   if ( $html5 && ( ( $y+1 >= $c ) || ( $new_sort[$y+1]  !== 'byline') ) )
									  echo '</header>';
								   break;

							    case 'byline':
								   if ( !$html5 ) {
									  xtreme_byline();
								   } else {
									  xtreme_html5_byline();
									  if ( ($y-1 >= 0) && $new_sort[$y-1] == 'posttitle') echo '</header>';
								   }
								   break;

							    case 'content':
									   echo '<div class="entry-content">';
									  switch( $instance['content_type'] ) {
										 case 'xtreme_excerpt':
											xtreme_excerpt( $instance['excerpt_length'], esc_html( $instance['excerpt_morelink_text'] ), esc_html( $instance['excerpt_more'] ) );
											break;
										 case 'excerpt':
											$new_excerpt = wptexturize(get_the_excerpt() );
											echo "<p>$new_excerpt <span class='read-more'><a href='" . get_permalink() . "' title='" . __( 'Read more about', XF_TEXTDOMAIN ) . ' ' . esc_attr( get_the_title() ) . "'>" . esc_html( $instance['excerpt_morelink_text'] ) . "</a></span></p>";
											break;
										case 'content':
											  default:
											  the_content();
											  break;
									  }
									  echo '</div>';
									  break;
								   }
						 }
						 echo '</' . $el . '>';
				    }
			endwhile;
			wp_reset_query();

		endfor;

		echo "</div>";
		echo $after_widget;
    }

    function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title']      = strip_tags( $new_instance['title'] );
		$instance['columns']    = intval( $new_instance['columns'] );
		$instance['orderby']    = strip_tags( $new_instance['orderby'] );
		$instance['offset']     = absint( strip_tags( $new_instance['offset'] ) );
		$instance['content_type'] = strip_tags( $new_instance['content_type'] );
		$instance['show_thumbnail'] = isset( $new_instance['show_thumbnail'] ) ? 1 : 0;
		$instance['thumbnail_size'] = strip_tags( $new_instance['thumbnail_size'] );
		$instance['image_alignment'] = strip_tags( $new_instance['image_alignment'] );
		$instance['image_pos'] = intval( strip_tags( $new_instance['image_pos'] ) );
		$instance['byline_pos'] = intval( strip_tags( $new_instance['byline_pos'] ) );
		$instance['excerpt_length'] = absint( strip_tags( $new_instance['excerpt_length'] ) );
		$instance['excerpt_more'] = strip_tags( $new_instance['excerpt_more'] );
		$instance['excerpt_morelink_text'] = strip_tags( $new_instance['excerpt_morelink_text'] );
		$instance['show_byline'] = isset( $new_instance['show_byline'] ) ? 1 : 0;
		$instance['show_posttitle'] = isset( $new_instance['show_posttitle'] ) ? 1 : 0;
		$instance['suppress_posttitle_link'] =  isset( $new_instance['suppress_posttitle_link']) ? $new_instance['suppress_posttitle_link'] : 0;
		$instance['posttitle_pos'] = intval( strip_tags($new_instance['posttitle_pos'] ) );
		$instance['posttitle_tag'] = strip_tags( $new_instance['posttitle_tag'] );
		$instance['show_content'] = isset( $new_instance['show_content'] ) ? 1 : 0;
		$instance['content_pos'] = intval( strip_tags( $new_instance['content_pos'] ) );
		$instance['syncheight'] = isset( $new_instance['syncheight'] ) ? 1 : 0;
		$instance['fx'] = strip_tags( $new_instance['fx'] );
		$instance['fxspeed'] = strip_tags( $new_instance['fxspeed'] );
		for( $c = 0; $c < $instance['columns']; $c++ ) {
		    $instance['category-'.$c] = strip_tags( $new_instance['category-'.$c] );
		}
		return $instance;
    }

    function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$columns = isset( $instance['columns'] ) ? absint( $instance['columns'] ) : 2;
		for( $c = 0; $c < 6; $c++ ) {
		    $instance['category-' . $c] = isset( $instance['category-' . $c] ) ? $instance['category-' . $c] : get_option( 'default_category' );
		}
		$orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
		$offset = ( isset( $instance['offset'] ) && !empty( $instance['offset'] ) ) ? $instance['offset'] : 0;
		$content_type = isset( $instance['content_type'] ) ? $instance['content_type'] : 'xtreme_excerpt';
		$show_posttitle = isset( $instance['show_posttitle'] ) ? $instance['show_posttitle'] : 1;
 		$suppress_posttitle_link =  isset( $instance['suppress_posttitle_link']) ? $instance['suppress_posttitle_link'] : 0;
		$posttitle_pos = isset( $instance['posttitle_pos'] ) ? $instance['posttitle_pos'] : 2;
		$posttitle_tag = isset( $instance['posttitle_tag'] ) ? $instance['posttitle_tag'] : 'h2';
		$show_byline = isset( $instance['show_byline'] ) ? $instance['show_byline'] : 0;
		$byline_pos = isset( $instance['byline_pos'] ) ? $instance['byline_pos'] : 3;
		$show_content = isset( $instance['show_content'] ) ? $instance['show_content'] : 1;
		$content_pos = isset( $instance['content_pos'] ) ? $instance['content_pos'] : 4;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : 0;
		$thumbnail_size = isset( $instance['thumbnail_size'] ) ? $instance['thumbnail_size'] : 'thumbnail';
		$image_alignment = isset( $instance['image_alignment'] ) ? $instance['image_alignment'] : 'alignleft';
		$image_pos = isset( $instance['image_pos'] ) ? $instance['image_pos'] : 1;
		$excerpt_length = isset( $instance['excerpt_length'] ) ? min(max(5, $instance['excerpt_length']), 80) : 40;
		$excerpt_more = isset( $instance['excerpt_more'] ) ? $instance['excerpt_more'] : '...';
		$excerpt_morelink_text = isset( $instance['excerpt_morelink_text'] ) ? $instance['excerpt_morelink_text'] : __( 'Read more...', XF_TEXTDOMAIN );
		$syncheight = isset( $instance['syncheight'] ) ? $instance['syncheight'] : 0;
		$fx = isset( $instance['fx'] ) ? $instance['fx'] : 'fadeIn';
		$fxspeed = isset( $instance['fxspeed'] ) ? $instance['fxspeed'] : 'normal';
		?>
		<h3><?php _e( 'Post Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
		    <label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
		    <input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_html( $title ) ?>" />
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'columns' ) ?>"><?php _e( 'Tabs:', XF_TEXTDOMAIN ) ?></label>
		    <select class="x-rows" id="<?php echo $this->get_field_id('columns' ) ?>" name="<?php echo $this->get_field_name( 'columns' ) ?>" >
		    <?php
		    $arr = array( 2, 3, 4, 5, 6 );
		    foreach ( $arr as $v ) :
		    ?>
				<option value="<?php echo esc_attr( $v ) ?>" <?php selected( $v, $columns ) ?>><?php echo esc_attr( $v ) ?></option>
		    <?php
		    endforeach;
		    unset($arr);
		    ?>
		    </select>
		</p>
		<?php
		for( $i = 0; $i < 6; $i++ ) :
		?>
		<p class="<?php echo $this->get_field_id( 'columns' ). '-' . $i . ' ' . $this->get_field_id( 'columns' ) ?>">
		    <label for="<?php echo $this->get_field_id( 'category-'.$i ) ?>"><?php printf( __( 'Tab %d Category:', XF_TEXTDOMAIN ) ,$i+1 ) ?></label>
		    <?php wp_dropdown_categories(
				    array(
						'name' => $this->get_field_name( 'category-'. $i ),
						'selected' => $instance['category-' . $i],
						'orderby' => 'name',
						'hierarchical' => 1,
						'hide_empty' => 1
						)
				    )
		     ?>
		</p>
		<?php
		endfor;
		$sort = array(
		    'date' => __( 'Post Date', XF_TEXTDOMAIN ),
		    'title' => __( 'Post Title', XF_TEXTDOMAIN ),
		    'ID' => __( 'Post ID', XF_TEXTDOMAIN ),
		    'rand' => __( 'Random', XF_TEXTDOMAIN ),
		    'comment_count' => __( 'Comment Count', XF_TEXTDOMAIN )
		    )
		?>
		<p>
		    <label for="<?php echo $this->get_field_id( 'orderby' ) ?>"><?php _e('Order By:', XF_TEXTDOMAIN) ?></label>
		    <select id="<?php echo $this->get_field_id( 'orderby' ) ?>" name="<?php echo $this->get_field_name('orderby') ?>">
		    <?php
		    foreach( $sort as $key => $val ) :
		    ?>
				<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $orderby ) ?>><?php echo esc_attr( $val ) ?></option>
		    <?php
		    endforeach;
		    ?>
		    </select>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'offset' ) ?>"><?php _e( 'Post Offset:', XF_TEXTDOMAIN ) ?></label>
		    <input type="text" id="<?php echo $this->get_field_id( 'offset' ) ?>" name="<?php echo $this->get_field_name( 'offset' ) ?>" value="<?php echo esc_attr( $offset ) ?>" size="3" />
		</p>
		<h3><?php _e( 'Post Template Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
		    <input class="x-switcher" id="<?php echo $this->get_field_id('show_thumbnail') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_thumbnail') ?>" value="1" <?php checked(1, $show_thumbnail) ?>/>
		    <label for="<?php echo $this->get_field_id( 'show_thumbnail' ) ?>"><?php _e( 'Show Featured Image', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id( 'show_thumbnail' ) ?>">
		    <p>
				<label for="<?php echo $this->get_field_id( 'thumbnail_size' ) ?>"><?php _e( 'Size:', XF_TEXTDOMAIN ) ?></label>
				<select class="x_thumbnailsize" id="<?php echo $this->get_field_id( 'thumbnail_size' ) ?>" name="<?php echo $this->get_field_name( 'thumbnail_size' ) ?>">
				<?php global $_wp_additional_image_sizes;
				    $sizes = get_intermediate_image_sizes();
				    foreach( $sizes as $size ) : ?>
						<option value="<?php echo $size ?>" <?php selected( $size, $thumbnail_size, true ) ?>><?php echo esc_attr( $size ) ?></option>
				    <?php endforeach; ?>
				</select>
		    </p>
		    <p>
		    <?php $align = array(
				'alignnone' => __( 'none', XF_TEXTDOMAIN ),
				'alignleft' => __( 'left', XF_TEXTDOMAIN ),
				'alignright' => __( 'right', XF_TEXTDOMAIN ),
				'aligncenter' => __( 'center', XF_TEXTDOMAIN )
		    ) ?>
				<label for="<?php echo $this->get_field_id( 'image_alignment' ) ?>"><?php _e( 'Image Alignment:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'image_alignment' ) ?>" name="<?php echo $this->get_field_name( 'image_alignment' ) ?>">
				<?php foreach( $align as $a => $b ): ?>
				    <option value="<?php echo $a ?>" <?php selected( $a, $image_alignment ) ?>><?php echo $b ?></option>
				<?php endforeach; ?>
				</select>
		    </p>
		    <p>
				<label for="<?php echo $this->get_field_id( 'image_pos' ) ?>"><?php _e( 'Position:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'image_pos' ) ?>" name="<?php echo $this->get_field_name( 'image_pos' ) ?>">
				<?php foreach(array( 1,2,3,4 ) as $c ): ?>
				    <option value="<?php echo $c ?>" <?php selected( $c, $image_pos ) ?>><?php echo $c ?></option>
				<?php endforeach; ?>
				</select>
		    </p>
		</div>

		<p>
		    <input class="x-switcher" id="<?php echo $this->get_field_id( 'show_posttitle' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_posttitle' ) ?>" value="1" <?php checked( 1, $show_posttitle ) ?>/>
		    <label for="<?php echo $this->get_field_id( 'show_posttitle' ) ?>"><?php _e( 'Show Post Title', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id('show_posttitle') ?>">
			<p>
 				<input id="<?php echo $this->get_field_id( 'suppress_posttitle_link' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'suppress_posttitle_link' ) ?>" value="1" <?php checked( 1, $suppress_posttitle_link ) ?>/>
				<label for="<?php echo $this->get_field_id( 'suppress_posttitle_link' ) ?>"><?php _e( 'Post Title without Link', XF_TEXTDOMAIN ) ?></label>
			</p>
		    <p>
				<label for="<?php echo $this->get_field_id( 'posttitle_tag' ) ?>"><?php _e( 'Tag:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'posttitle_tag' ) ?>" name="<?php echo $this->get_field_name( 'posttitle_tag' ) ?>">
				<?php foreach( array( 'h2','h3','h4','h5' ) as $tag ): ?>
				    <option value="<?php echo $tag ?>" <?php selected( $tag, $posttitle_tag ) ?>><?php echo esc_attr( $tag ) ?></option>
				<?php endforeach; ?>
				</select>
		    </p>
		    <p>
				<label for="<?php echo $this->get_field_id( 'posttitle_pos' ) ?>"><?php _e( 'Position:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'posttitle_pos' ) ?>" name="<?php echo $this->get_field_name( 'posttitle_pos' ) ?>">
				<?php foreach( array( 1,2,3,4 ) as $g ): ?>
				    <option value="<?php echo $g ?>" <?php selected( $g, $posttitle_pos ) ?>><?php echo esc_attr( $g ) ?></option>
				<?php endforeach; ?>
				</select>
		    </p>
		</div>

		<p>
		    <input class="x-switcher" id="<?php echo $this->get_field_id( 'show_byline' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_byline' ) ?>" value="1" <?php checked( 1, $show_byline ) ?>/>
		    <label for="<?php echo $this->get_field_id( 'show_byline' ) ?>"><?php _e( 'Show Byline', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id( 'show_byline' ) ?>">
		    <p>
				<label for="<?php echo $this->get_field_id( 'byline_pos' ) ?>"><?php _e( 'Position:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'byline_pos' ) ?>" name="<?php echo $this->get_field_name( 'byline_pos' ) ?>">
				<?php foreach( array( 1,2,3,4 ) as $f ): ?>
				    <option value="<?php echo $f ?>" <?php selected( $f, $byline_pos ) ?>><?php echo esc_attr( $f ) ?></option>
				<?php endforeach; ?>
				</select>
		    </p>
		</div>

		<p>
		    <input class="x-switcher" id="<?php echo $this->get_field_id( 'show_content' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_content' ) ?>" value="1" <?php checked( 1, $show_content ) ?>/>
		    <label for="<?php echo $this->get_field_id( 'show_content' ) ?>"><?php _e( 'Show Content', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id( 'show_content' ) ?>">
		<?php $type = array(
		    'xtreme_excerpt'    => __( 'Xtreme Excerpt', XF_TEXTDOMAIN ),
		    'excerpt'           => __( 'Excerpt', XF_TEXTDOMAIN ),
		    'content'           => __( 'Complete Content', XF_TEXTDOMAIN )
		    ) ?>
		<p>
		    <label for="<?php echo $this->get_field_id( 'content_type' ) ?>"><?php _e( 'Content Type:', XF_TEXTDOMAIN ) ?></label>
		    <select class="x-content" id="<?php echo $this->get_field_id( 'content_type' ) ?>" name="<?php echo $this->get_field_name( 'content_type' ) ?>">
				<?php foreach( $type as $c => $d ): ?>
				<option value="<?php echo $c ?>" <?php selected( $c, $content_type ); ?>><?php echo esc_attr( $d ) ?></option>
				<?php endforeach; ?>
		    </select>
		</p>
		    <div class="<?php echo $this->get_field_id( 'content_type' ) ?>">
				<p class="excerpt_length">
				    <label for="<?php echo $this->get_field_id( 'excerpt_length' ) ?>"><?php _e( 'Excerpt Length:', XF_TEXTDOMAIN ); ?></label>
				    <input id="<?php echo $this->get_field_id( 'excerpt_length' ) ?>" name="<?php echo $this->get_field_name( 'excerpt_length' ) ?>" type="text" value="<?php echo esc_attr( $excerpt_length ) ?>" size="3" /> <?php _e( 'Words', XF_TEXTDOMAIN ) ?>
				    <br /><small><?php printf( __( '(at most %s)', XF_TEXTDOMAIN ), 80 ) ?></small>
				</p>
				<p class="excerpt_more">
				    <label for="<?php echo $this->get_field_id( 'excerpt_more' ) ?>"><?php _e( 'End of Excerpt:', XF_TEXTDOMAIN) ?></label>
				    <input id="<?php echo $this->get_field_id( 'excerpt_more' ) ?>" name="<?php echo $this->get_field_name( 'excerpt_more' ) ?>" type="text" value="<?php echo esc_html( $excerpt_more ) ?>"  />
				</p>
				<p class="excerpt_morelink_text">
				    <label for="<?php echo $this->get_field_id( 'excerpt_morelink_text' ) ?>"><?php _e( 'Excerpt More Link Text:', XF_TEXTDOMAIN ) ?></label>
				    <input id="<?php echo $this->get_field_id( 'excerpt_morelink_text' ) ?>" name="<?php echo $this->get_field_name( 'excerpt_morelink_text' ) ?>" type="text" value="<?php echo esc_html( $excerpt_morelink_text ) ?>" />
				</p>
		    </div>
		    <p>
				<label for="<?php echo $this->get_field_id( 'content_pos' ) ?>"><?php _e( 'Position:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'content_pos' ) ?>" name="<?php echo $this->get_field_name( 'content_pos' ) ?>">
				<?php foreach( array( 1,2,3,4 ) as $f ): ?>
				    <option value="<?php echo $f ?>" <?php selected( $f, $content_pos ) ?>><?php echo $f ?></option>
				<?php endforeach; ?>
				</select>
		    </p>
		</div>
 
		<h3><?php _e( 'Javascript Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
		    <label for="<?php echo $this->get_field_id( 'fx' ) ?>"><?php _e( 'FX:', XF_TEXTDOMAIN ) ?></label>
		    <select id="<?php echo $this->get_field_id( 'fx' ) ?>" name="<?php echo $this->get_field_name( 'fx' ) ?>">
				<option value="fadeIn" <?php selected( 'fadeIn', $fx ) ?>>fadeIn</option>
				<option value="show" <?php selected( 'show', $fx ) ?>>show</option>
				<option value="slideDown" <?php selected( 'slideDown', $fx ) ?>>slideDown</option>
		    </select>
		</p>
		<p>
		    <label for="<?php echo $this->get_field_id( 'fxspeed' ) ?>"><?php _e( 'FX Speed:', XF_TEXTDOMAIN ) ?></label>
		    <select id="<?php echo $this->get_field_id( 'fxspeed' ) ?>" name="<?php echo $this->get_field_name( 'fxspeed' ) ?>">
				<option value="slow" <?php selected( 'slow', $fxspeed ) ?>><?php _e( 'slow', XF_TEXTDOMAIN ) ?></option>
				<option value="normal" <?php selected( 'normal', $fxspeed ) ?>><?php _e( 'normal', XF_TEXTDOMAIN ) ?></option>
				<option value="fast" <?php selected( 'fast', $fxspeed ) ?>><?php _e( 'fast', XF_TEXTDOMAIN ) ?></option>
		    </select>
		</p>
		<p>
		    <input id="<?php echo $this->get_field_id( 'syncheight' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'syncheight' ) ?>" value="1" <?php checked( 1, $syncheight ) ?>/>
		    <label for="<?php echo $this->get_field_id( 'syncheight' ) ?>"><?php _e( 'Use Syncheight', XF_TEXTDOMAIN ) ?></label>
		</p>
    <?php
    }
}