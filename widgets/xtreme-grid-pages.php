<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Grid_Pages_Widget"; return $classes;'));

class Xtreme_Grid_Pages_Widget extends Xtreme_Widget_Base {

	function __construct() {
		$widget_ops = array(
			'classname' => 'xtreme_grid_pages',
			'description' => __( 'This Widget will display your pages in x columns.', XF_TEXTDOMAIN )
		);
		parent::__construct(__FILE__, 'xtreme-grid-pages', __( 'Xtreme Grid Pages', XF_TEXTDOMAIN), $widget_ops );

		$this->classes = xtreme_get_grids();
	}

	function widget( $args, $instance ) {
		global $wpdb;
		extract( $args );

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title']);
		$columns = absint( $instance['columns'] );
		$thumbnail_size = esc_attr( $instance['thumbnail_size'] );
		if ( !isset( $instance['as_backgroundimage'] ) ) $instance['as_backgroundimage'] = 0;
		if ( !isset( $instance['wrap_content'] ) ) $instance['wrap_content'] = 1;
		$el = 'div';
		$html5 = xtreme_is_html5();
		if ( $html5 ) {
			$el = 'article';
		}
		$contentorder = array(
			'content' => isset( $instance['content_pos'] ) ? (int) $instance['content_pos'] : 4,
			'subtitle' => isset( $instance['subtitle_pos'] ) ? (int) $instance['subtitle_pos'] : 3,
			'posttitle' => isset( $instance['posttitle_pos'] ) ? (int) $instance['posttitle_pos'] : 2,
			'thumbnail' => isset( $instance['image_pos'] ) ? (int) $instance['image_pos'] : 1
		);

		$suppress_posttitle_link =  isset( $instance[ 'suppress_posttitle_link' ]) ? (bool)( $instance[ 'suppress_posttitle_link' ] == 1) : false;
		$suppress_subtitle_link =  isset( $instance[ 'suppress_subtitle_link' ]) ? (bool)( $instance[ 'suppress_subtitle_link' ] == 1) : false;
		$tag = isset ( $instance['posttitle_tag'] ) ? esc_attr( $instance['posttitle_tag'] ) : 'h2';
		$subtag = isset ( $instance['subtitle_tag'] ) ? esc_attr( $instance['subtitle_tag'] ) : 'h3';
		$instance['show_content'] = 1;
		if ( !isset( $instance['content_pos'] ) )
			$instance['show_posttitle'] = 1;

		if(!isset($instance['show_subtitle'])) $instance['show_subtitle'] = 0;
		asort( $contentorder, SORT_NUMERIC );
		foreach ( $contentorder as $key => $value ) {
			if ( $instance['show_' . $key] ) {
				$new_sort[] = $key;
			}
		}

		echo $before_widget;
		if ( $title ) echo $before_title . $title . $after_title;
		if ( $columns > 1 ) echo '<div class="ym-grid linearize-level-2">';
		for ( $i = 0; $i < $columns; $i++ ) :
			if ( $columns > 1 ) {
				echo "<div class='" . $this->classes[$columns][$i]['outer'] . "'>\n";
				echo "<div class='" . $this->classes[$columns][$i]['inner'] . " ym-g" . ( $i + 1 ) . "'>\n";
			}
			$r = new WP_Query(
				array( 'page_id' => $instance['page_id-' . $i] )
			);
			if ( $r->have_posts() ):
				while ( $r->have_posts() ) : $r->the_post();?>
					<<?php echo $el ?> <?php post_class() ?>>
					<?php
					if(isset($new_sort)) {
						$c = count( $new_sort );
						for ( $y = 0; $y < $c; $y++ ) {
							switch ( $new_sort[$y] ) {
								case 'thumbnail':
									if ( $instance['as_backgroundimage'] == 0 ) {
										xtreme_post_thumbnail( $thumbnail_size, esc_attr( $instance['image_alignment'] ) );
									} else {
										xtreme_background_post_thumbnail( $thumbnail_size, esc_attr( $instance['image_alignment'] ), esc_html( $instance['excerpt_morelink_text'] ) );
									}
									break;

								case 'subtitle':
									xtreme_widget_post_subtitle( $subtag, ! $suppress_subtitle_link, true);
									break;

								case 'posttitle':
									if ( $html5 ) echo '<header>';
									$custom_posttitle = esc_html( $instance[ 'custom_posttitle-' . $i ] );
									xtreme_widget_post_headline( $tag, ! $suppress_posttitle_link, true, $custom_posttitle );
									if ( $html5 ) echo '</header>';
									break;

								case 'content':
									if ( $instance['wrap_content'] == 1 ) {
										echo '<div class="entry-content">';
									}
									switch ( $instance['content_type-'.$i] ) {
										case 'xtreme_excerpt':
											$excerpt_length     = esc_attr( $instance['excerpt_length-' . $i] );
											$excerpt_more_text  = esc_html( $instance['excerpt_morelink_text-' . $i] );
											$excerpt_more       = esc_html( $instance['excerpt_more-' . $i] );
											$excerpt_show_tags = isset( $instance['excerpt_show_tags-' . $i] );
											xtreme_excerpt( $excerpt_length, $excerpt_more_text, $excerpt_more, true, $excerpt_show_tags );
											break;
										case 'content':
											the_content( );
											break;
										case 'excerpt':
											$new_excerpt = get_the_excerpt();
											echo "<p>$new_excerpt <span class='read-more'><a href='" . get_permalink() . "' title='" . __( 'Read more about', XF_TEXTDOMAIN ) . ' ' . esc_attr( get_the_title() ) . "'>" . esc_html( $instance['excerpt_morelink_text-' .$i] ) . "</a></span></p>";
											break;
									}
									if ( $instance['wrap_content'] == 1 ) {
										echo '</div>';
									}
									break;
							}
						}
						echo '</' . $el . '>';
					}
				endwhile;
				if ( $columns > 1 ) echo "</div></div>";
				wp_reset_query();
			endif;
		endfor;
		if ( $columns > 1 ) echo '</div>';
		echo $after_widget;
	}

	function update( $new_instance, $old_instance ) {
		$instance = $new_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['columns'] = ( int ) $new_instance['columns'];
		for ( $c = 0; $c < 5; $c++ ) {
			$instance['page_id-' . $c] = strip_tags( $new_instance['page_id-' . $c] );
			$instance['content_type-' . $c] = strip_tags( $new_instance['content_type-' . $c]);
			$instance['excerpt_length-' . $c] = absint( strip_tags( $new_instance['excerpt_length-' . $c] ) );
			$instance['excerpt_more-' . $c] = strip_tags( $new_instance['excerpt_more-' . $c] );
			$instance['excerpt_morelink_text-' . $c] = strip_tags( $new_instance['excerpt_morelink_text-' . $c] );
			$instance['excerpt_show_tags-' . $c] = isset( $new_instance['excerpt_show_tags-' . $c] ) ? $instance['excerpt_show_tags-' . $c] : 0;

			$instance['custom_posttitle-' . $c] = strip_tags( $new_instance['custom_posttitle-' . $c] );
		}

		$instance['content_type'] = strip_tags( $new_instance['content_type'] );
		$instance['show_thumbnail'] = isset( $new_instance['show_thumbnail'] ) ? 1 : 0;
		$instance['as_backgroundimage'] = isset( $new_instance['as_backgroundimage'] ) ? 1 : 0;
		$instance['thumbnail_size'] = strip_tags( $new_instance['thumbnail_size'] );
		$instance['image_alignment'] = strip_tags( $new_instance['image_alignment'] );
		$instance['image_pos'] = intval( strip_tags( $new_instance['image_pos'] ) );
		$instance['show_posttitle'] = isset( $new_instance['show_posttitle'] ) ? 1 : 0;
		$instance['posttitle_pos'] = intval( strip_tags($new_instance['posttitle_pos'] ) );
		$instance['posttitle_tag'] = strip_tags( $new_instance['posttitle_tag'] );
		$instance['suppress_posttitle_link'] =  isset( $new_instance['suppress_posttitle_link']) ? $new_instance['suppress_posttitle_link'] : 0;
		if(current_theme_supports('xtreme-subtitles')) {
			$instance['show_subtitle'] = isset( $new_instance['show_subtitle'] ) ? 1 : 0;
			$instance['subtitle_pos'] = intval( strip_tags($new_instance['subtitle_pos'] ) );
			$instance['subtitle_tag'] = strip_tags( $new_instance['subtitle_tag'] );
			$instance['suppress_subtitle_link'] =  isset( $new_instance['suppress_subtitle_link']) ? $new_instance['suppress_subtitle_link'] : 0;
		}
		$instance['content_pos'] = intval( strip_tags( $new_instance['content_pos'] ) );
		$instance['wrap_content'] = isset( $new_instance['wrap_content'] ) ? 1 : 0;
		return $instance;
	}

	function form( $instance ) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$columns = isset( $instance['columns'] ) ? absint( $instance['columns'] ) : 1;

		for ( $c = 0; $c < 5; $c++ ) {
			$instance['page_id-' . $c] = isset( $instance['page_id-' . $c] ) ? $instance['page_id-' . $c] : 0;
			$instance['content_type-' . $c] = isset( $instance['content_type-' . $c] ) ? $instance['content_type-' . $c] : 'xtreme_excerpt';
			$instance['excerpt_length-' . $c] = isset( $instance['excerpt_length-' . $c] ) ? min( max( 5, $instance['excerpt_length-' . $c] ), 80 ) : 40;
			$instance['excerpt_show_tags-' . $c] = isset( $instance['excerpt_show_tags-' . $c] ) ? $instance['excerpt_show_tags-' . $c] : 0;
			$instance['excerpt_more-' . $c] = isset( $instance['excerpt_more-' . $c] ) ? $instance['excerpt_more-' . $c] : '...';
			$instance['excerpt_morelink_text-' . $c] = isset( $instance['excerpt_morelink_text-' . $c] ) ? $instance['excerpt_morelink_text-' . $c] : __( 'Read more...', XF_TEXTDOMAIN );
			$instance['custom_posttitle-' . $c] = isset( $instance['custom_posttitle-' . $c] ) ? $instance['custom_posttitle-' . $c] : '';
		}
		$content_type = isset( $instance['content_type'] ) ? $instance['content_type'] : 'xtreme_excerpt';
		$show_posttitle = isset( $instance['show_posttitle'] ) ? $instance['show_posttitle'] : 1;
		$posttitle_pos = isset( $instance['posttitle_pos'] ) ? $instance['posttitle_pos'] : 2;
		$posttitle_tag = isset( $instance['posttitle_tag'] ) ? $instance['posttitle_tag'] : 'h2';
		$suppress_posttitle_link =  isset( $instance['suppress_posttitle_link']) ? $instance['suppress_posttitle_link'] : 0;
		if(current_theme_supports('xtreme-subtitles')) {
			$show_subtitle = isset( $instance['show_subtitle'] ) ? $instance['show_subtitle'] : 0;
			$subtitle_pos = isset( $instance['subtitle_pos'] ) ? $instance['subtitle_pos'] : 3;
			$subtitle_tag = isset( $instance['subtitle_tag'] ) ? $instance['subtitle_tag'] : 'h3';
			$suppress_subtitle_link =  isset( $instance['suppress_subtitle_link']) ? $instance['suppress_subtitle_link'] : 1;
		}
		$content_pos = isset( $instance['content_pos'] ) ? $instance['content_pos'] : 3;
		$wrap_content = isset( $instance['wrap_content'] ) ? $instance['wrap_content'] : 1;
		$show_thumbnail = isset( $instance['show_thumbnail'] ) ? $instance['show_thumbnail'] : 0;
		$as_backgroundimage = isset( $instance['as_backgroundimage'] ) ? $instance['as_backgroundimage'] : 0;
		$thumbnail_size = isset( $instance['thumbnail_size'] ) ? $instance['thumbnail_size'] : 'thumbnail';
		$image_alignment = isset( $instance['image_alignment'] ) ? $instance['image_alignment'] : 'alignleft';
		$image_pos = isset( $instance['image_pos'] ) ? $instance['image_pos'] : 1;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_html( $title ) ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'columns' ) ?>"><?php _e( 'Columns:', XF_TEXTDOMAIN ); ?></label>
			<select class="x-columns" id="<?php echo $this->get_field_id( 'columns' ) ?>" name="<?php echo $this->get_field_name( 'columns' ) ?>" >
			<?php $arr = array( 1,2,3,4,5 );
			foreach ( $arr as $v ) :
			?>
				<option value="<?php echo $v ?>" <?php selected( $v, $columns ) ?>><?php echo $v ?></option>
			<?php endforeach;
			unset( $arr ) ?>
			</select>
		</p>
<?php for ( $i = 0; $i < 5; $i++ ) : ?>
		<div class="<?php echo $this->get_field_id( 'columns' ) . '-' . ( $i + 1 ) . ' ' . $this->get_field_id( 'columns' ) ?>">
			<h3><?php printf( __( 'Column %d', XF_TEXTDOMAIN ), ( $i + 1 ) ) ?></h3>
			<p>
				<label for="<?php echo $this->get_field_id( 'page_id-' . $i ) ?>"><?php _e( 'Page:', XF_TEXTDOMAIN ) ?></label>
				<?php wp_dropdown_pages( array( 'name' => $this->get_field_name( 'page_id-' . $i ), 'selected' => $instance[ 'page_id-' . $i] ) ) ?>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'custom_posttitle-' . $i ) ?>"><?php _e( 'Custom Posttitle:', XF_TEXTDOMAIN ) ?> </label>
				<input id="<?php echo $this->get_field_id( 'custom_posttitle-' . $i ) ?>" name="<?php echo $this->get_field_name( 'custom_posttitle-'.$i) ?>" type="text"  size="10" class="widefat" value="<?php echo esc_html( $instance['custom_posttitle-' . $i] ) ?>" />
			</p>
				<?php $type = array(
					'xtreme_excerpt' => __( 'Xtreme Excerpt', XF_TEXTDOMAIN ),
					'content' => __( 'Full Content', XF_TEXTDOMAIN )
					);
					if (post_type_supports( 'page', 'excerpt' ) )
						$type[ 'excerpt' ] =  __( 'Excerpt', XF_TEXTDOMAIN );
				?>
			<p>
				<label for="<?php echo $this->get_field_id( 'content_type-' . $i ) ?>"><?php _e( 'Content Type:', XF_TEXTDOMAIN ) ?></label>
				<select class="x-content" id="<?php echo $this->get_field_id( 'content_type-' . $i ) ?>" name="<?php echo $this->get_field_name( 'content_type-' . $i ) ?>">
				<?php foreach ($type as $c => $d ): ?>
					<option value="<?php echo $c ?>" <?php selected( $c, $instance['content_type-' . $i] ); ?>><?php echo $d ?></option>
				<?php endforeach; unset( $type ); ?>
				</select>
			</p>
			<div class="<?php echo $this->get_field_id( 'content_type-' .$i ) ?>">
				<p class="excerpt_length">
					<label for="<?php echo $this->get_field_id( 'excerpt_length-' . $i ) ?>"><?php _e( 'Excerpt Lenght:', XF_TEXTDOMAIN ) ?></label>
					<input id="<?php echo $this->get_field_id( 'excerpt_length-' . $i ) ?>" name="<?php echo $this->get_field_name( 'excerpt_length-'. $i ) ?>" type="text" value="<?php echo esc_attr( $instance['excerpt_length-' . $i] ) ?>" size="3" /> <?php _e( 'Words', XF_TEXTDOMAIN ) ?>
					<br /><small><?php printf( __( '(at most %s)', XF_TEXTDOMAIN), 80 ) ?></small>
				</p>
				<p class="excerpt_morelink_text">
					<label for="<?php echo $this->get_field_id( 'excerpt_morelink_text-' . $i ) ?>"><?php _e( 'Excerpt More Link Text:', XF_TEXTDOMAIN ) ?> </label>
					<input id="<?php echo $this->get_field_id( 'excerpt_morelink_text-' . $i ) ?>" name="<?php echo $this->get_field_name( 'excerpt_morelink_text-'.$i) ?>" type="text" value="<?php echo esc_html( $instance['excerpt_morelink_text-' . $i] ) ?>" />
				</p>
				<div class="excerpt_more">
					<p>
						<label for="<?php echo $this->get_field_id( 'excerpt_more-' . $i ) ?>"><?php _e( 'End of Excerpt:', XF_TEXTDOMAIN ) ?></label>
						<input id="<?php echo $this->get_field_id( 'excerpt_more-' . $i ) ?>" name="<?php echo $this->get_field_name( 'excerpt_more-' . $i ) ?>" type="text" value="<?php echo esc_html( $instance['excerpt_more-' . $i] ) ?>"  />
					</p>
					<p class="excerpt_show_tags">
						<input id="<?php echo $this->get_field_id( 'excerpt_show_tags-' . $i ) ?>" name="<?php echo $this->get_field_name( 'excerpt_show_tags-' . $i ) ?>" type="checkbox" value="1" <?php checked(1, $instance['excerpt_show_tags-' . $i] ) ?>  />
						<label for="<?php echo $this->get_field_id( 'excerpt_show_tags-' . $i ) ?>"><?php _e( 'Enable Tags', XF_TEXTDOMAIN ) ?></label>
					</p>

				</div>

			</div>
		</div>
<?php endfor; ?>

		<h3><?php _e( 'Post Template Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
			<input class="x-switcher" id="<?php echo $this->get_field_id( 'show_thumbnail' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_thumbnail' ) ?>" value="1" <?php checked (1, $show_thumbnail ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_thumbnail' ) ?>"><?php _e( 'Show Featured Image', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id( 'show_thumbnail' ) ?>">
			<p>
				<input id="<?php echo $this->get_field_id('as_backgroundimage') ?>" type="checkbox" name="<?php echo $this->get_field_name('as_backgroundimage') ?>" value="1" <?php checked(1, $as_backgroundimage) ?>/>
				<label for="<?php echo $this->get_field_id( 'as_backgroundimage' ) ?>"><?php _e( 'Show as Background Image', XF_TEXTDOMAIN ) ?></label>
			</p>
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
				<?php foreach(array( 1,2,3,4) as $c ): ?>
					<option value="<?php echo $c ?>" <?php selected( $c, $image_pos ) ?>><?php echo $c ?></option>
				<?php endforeach; ?>
				</select>
			</p>
		</div>

		<p>
			<input class="x-switcher" id="<?php echo $this->get_field_id( 'show_posttitle' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_posttitle' ) ?>" value="1" <?php checked( 1, $show_posttitle ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_posttitle' ) ?>"><?php _e( 'Show Post Title', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id( 'show_posttitle' ) ?>">
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
<?php if (current_theme_supports('xtreme-subtitles')): ?>
		<p>
			<input class="x-switcher" id="<?php echo $this->get_field_id( 'show_subtitle' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_subtitle' ) ?>" value="1" <?php checked( 1, $show_subtitle ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_subtitle' ) ?>"><?php _e( 'Show Subtitle', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="<?php echo $this->get_field_id('show_subtitle') ?>">
		<p>
			<input id="<?php echo $this->get_field_id( 'suppress_subtitle_link' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'suppress_subtitle_link' ) ?>" value="1" <?php checked( 1, $suppress_subtitle_link ) ?>/>
			<label for="<?php echo $this->get_field_id( 'suppress_subtitle_link' ) ?>"><?php _e( 'Subtitle without Link', XF_TEXTDOMAIN ) ?></label>
		</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'subtitle_tag' ) ?>"><?php _e( 'Tag:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'subtitle_tag' ) ?>" name="<?php echo $this->get_field_name( 'subtitle_tag' ) ?>">
				<?php foreach( array( 'h1', 'h2','h3','h4','h5', 'h6', 'p', 'div' ) as $subtag ): ?>
					<option value="<?php echo $subtag ?>" <?php selected( $subtag, $subtitle_tag ) ?>><?php echo esc_attr( $subtag ) ?></option>
				<?php endforeach; ?>
				</select>
			</p>
			<p>
				<label for="<?php echo $this->get_field_id( 'subtitle_pos' ) ?>"><?php _e( 'Position:', XF_TEXTDOMAIN ) ?></label>
				<select id="<?php echo $this->get_field_id( 'subtitle_pos' ) ?>" name="<?php echo $this->get_field_name( 'subtitle_pos' ) ?>">
				<?php foreach( array( 1,2,3,4 ) as $x ): ?>
					<option value="<?php echo $x ?>" <?php selected( $x, $subtitle_pos ) ?>><?php echo esc_attr( $x ) ?></option>
				<?php endforeach; ?>
				</select>
			</p>
		</div>
<?php endif; ?>
		<p><?php _e( 'Content:', XF_TEXTDOMAIN ) ?></p>
		<p>
			<input class="x-switcher" id="<?php echo $this->get_field_id( 'wrap_content' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'wrap_content' ) ?>" value="1" <?php checked( 1, $wrap_content ) ?>/>
			<label for="<?php echo $this->get_field_id( 'wrap_content' ) ?>"><?php _e( 'Wrap Content in a Div', XF_TEXTDOMAIN ) ?></label>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'content_pos' ) ?>"><?php _e( 'Position:', XF_TEXTDOMAIN ) ?></label>
			<select id="<?php echo $this->get_field_id( 'content_pos' ) ?>" name="<?php echo $this->get_field_name( 'content_pos' ) ?>">
			<?php foreach( array( 1,2,3,4 ) as $f ): ?>
				<option value="<?php echo $f ?>" <?php selected( $f, $content_pos ) ?>><?php echo $f ?></option>
			<?php endforeach; ?>
			</select>
		</p>
		<?php
	}
}
