<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Featurelist_Posts"; return $classes;'));

class Xtreme_Featurelist_Posts extends Xtreme_Widget_Base {
    function __construct() {
        $widget_ops = array( 'classname' => 'xtreme_featurelist_posts', 'description' => __( 'A cool Slider', XF_TEXTDOMAIN ) );
        $control_ops = array('width' => 300);
        parent::__construct(__FILE__, 'xtreme-featurelist-posts', __( 'Xtreme Feature List Posts', XF_TEXTDOMAIN ), $widget_ops, $control_ops );
    }

	function ensure_widget_scripts($instance) {
		global $xtreme_script_manager;
		$xtreme_script_manager->ensure_feature_list();
		$xtreme_script_manager->add_widget_data('xtreme-featurelist', $this->id, array(
			'rm_bottom' => $instance['rm_bottom'],
			'rm_right' => $instance['rm_right'],
			'transition_interval' => $instance['speed'] * 1000,
			'calc' => ($instance['autocalc'] === 1 ? true : false),
			'rm_pos' => ($instance['list_position'] === 'left' ? 'right' : 'left')
		));
	}
	
    function widget( $args, $instance ) {
        global $wpdb;
        extract( $args );
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
        $number_posts =  absint( $instance['number_posts']);
        $ul_width = esc_attr( $instance['content_width'] . $instance['content_width_value'] );
        $menu_width = esc_attr( $instance['list_width'] . $instance['list_width_value'] );
        $ul_height = esc_attr( $instance['slider_height'] ) .'px';
        $pos = $instance['list_position']=== 'left' ? 'right' : 'left';
        $thumbnail_size = esc_attr( $instance['thumbnail_size'] );

        $r = new WP_Query(
            array(
                'cat' => esc_attr( $instance['category'] ),
                'showposts' => $number_posts,
                'offset' => ( int ) $instance['offset'],
                'nopaging' => 0,
                'post_type' => 'post',
                'post_status' => 'publish',
                XF_STICKY_HANDLING => 1,
                'orderby' => esc_attr( $instance['orderby'] )
            )
        );

        if($r->have_posts()):
            $i = 0;
            echo $before_widget;
            if ( $title ) echo $before_title . $title . $after_title;
            echo "<div id='x-" . $this->id . "' class='fl-wrapper' style='height:" . $ul_height . "'><ul class='fl-tabs fl-" . esc_attr( $instance['list_position'] ) ."' style='width:" . $menu_width . ";'>";
            while ( $r->have_posts() ) : $r->the_post();
				$i++;
				echo "<li class='fl-li-" . $i . "'><a href='javascript:;'>";
				if ( $instance['show_thumbnail'] && has_post_thumbnail() ):
					the_post_thumbnail( array( $instance['thumbnail_width'], 9999 ) );
				endif;
				?>
				<span class="fl-title"><?php the_title() ?></span>
				<?php
				if ( $instance['content_type'] === 'both' ) {
					$p = array( '<p>','</p>' );
					$span = array( '<span class="fl-excerpt">', '</span>' );
					echo str_replace( $p, $span, xtreme_excerpt( $instance['excerpt_length'], '', esc_html( $instance['excerpt_more'] ), false ) );
				}
				?>
				</a></li>
			<?php
            endwhile;
            echo "</ul>";
            echo "<ul class='fl-output' style='width:" . $ul_width . ";" . $pos . ":0'>";
            while ( $r->have_posts()) : $r->the_post();
            echo "<li>";
            the_post_thumbnail( $thumbnail_size, array( 'alt' => $title, 'class'=> "" )); ?>
            <a class='fl-read-more' href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php the_title_attribute(); ?>"><?php echo esc_attr($instance['excerpt_morelink_text']) ?></a>
            </li>
            <?php

            endwhile;
            echo "</ul></div>";
            echo $after_widget;
        endif;

        wp_reset_query();
    }

    function update($new_instance, $old_instance) {
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
        $instance['excerpt_morelink_text'] = strip_tags($new_instance['excerpt_morelink_text']);
        $instance['content_width'] = absint( strip_tags($new_instance['content_width'] ) );
        $instance['content_width_value'] = strip_tags($new_instance['content_width_value']);
        $instance['list_width'] = absint( strip_tags($new_instance['list_width'] ) );
        $instance['list_width_value'] = strip_tags($new_instance['list_width_value']);
        $instance['list_position'] = strip_tags($new_instance['list_position']);
        $instance['rm_bottom'] = absint( strip_tags($new_instance['rm_bottom'] ) );
        $instance['rm_right'] = absint( strip_tags($new_instance['rm_right'] ) );
        $instance['thumbnail_width'] = absint( strip_tags($new_instance['thumbnail_width'] ) );
        $instance['show_thumbnail'] = (isset($new_instance['show_thumbnail'])) ? 1 : 0;
        $instance['slider_height'] = absint( strip_tags($new_instance['slider_height'] ) );
        $instance['speed'] = absint( strip_tags($new_instance['speed'] ) );
        $instance['autocalc'] = (isset($new_instance['autocalc'])) ? 1 : 0;

        return $instance;
    }

    function form($instance) {
        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $category = isset( $instance['category'] ) ? $instance['category'] : get_option( 'default_category' );
        $orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
        $offset = (isset( $instance['offset'] ) && !empty($instance['offset'] ) ) ? $instance['offset'] : 0;
        $thumbnail_size = (isset($instance['thumbnail_size'])) ? $instance['thumbnail_size'] : 'large';
        $content_type = isset( $instance['content_type'] ) ? $instance['content_type'] : 'both';
        $excerpt_length = isset( $instance['excerpt_length'] ) ? min(max(3, $instance['excerpt_length']), 20) : 5;
        $excerpt_more = isset( $instance['excerpt_more'] ) ? $instance['excerpt_more'] : '...';
        $excerpt_morelink_text = isset($instance['excerpt_morelink_text']) ? $instance['excerpt_morelink_text'] : __('Read more...', XF_TEXTDOMAIN);
        $rm_bottom = isset( $instance['rm_bottom'] ) ? min(max(5, $instance['rm_bottom']), 100) : 20;
        $rm_right = isset( $instance['rm_right'] ) ? min(max(5, $instance['rm_right']), 100) : 20;
        $show_thumbnail = isset($instance['show_thumbnail']) ? $instance['show_thumbnail'] : 0;
        $autocalc = isset($instance['autocalc']) ? $instance['autocalc'] : 0;
        $thumbnail_width = isset($instance['thumbnail_width']) ? min(max(20, $instance['thumbnail_width']), get_option('thumbnail_size_w')) : 40;
        $content_width_value = isset($instance['content_width_value']) ? esc_attr($instance['content_width_value']) : '%';
        $list_position = isset($instance['list_position']) ? esc_attr($instance['list_position']) : 'left';
        switch($content_width_value) {
            case 'px':
                $content_width = isset($instance['content_width']) ? min(max(100, $instance['content_width']), 1000) : 300;
                break;
            case 'em':
                $content_width = isset($instance['content_width']) ? min(max(20, $instance['content_width']), 70) : 20;
                break;
            case '%':
                $content_width = isset($instance['content_width']) ? min(max(10, $instance['content_width']), 100) : 50;
                break;
        }
        $list_width_value = isset($instance['list_width_value']) ? esc_attr($instance['list_width_value']) : '%';
        switch($list_width_value) {
            case 'px':
                $list_width = isset($instance['list_width']) ? min(max(100, $instance['list_width']), 1000) : 300;
                break;
            case 'em':
                $list_width = isset($instance['list_width']) ? min(max(20, $instance['list_width']), 70) : 20;
                break;
            case '%':
                $list_width = isset($instance['list_width']) ? min(max(10, $instance['list_width']), 100) : 50;
                break;
        }
        $slider_height = isset($instance['slider_height']) ? min(max(50, $instance['slider_height']), 1200) : 200;
        $speed = isset($instance['speed']) ? min(max(1, $instance['speed']), 10) : 5;
        $number_posts = isset( $instance['number_posts'] ) ? min( max( 2, $instance['number_posts'] ), 15 ) : 3;
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

        <h3><?php _e( 'Slider Options', XF_TEXTDOMAIN ) ?></h3>
        <p>
            <label for="<?php echo $this->get_field_id( 'slider_height' ) ?>"><?php _e( 'Slider Height:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'slider_height' ) ?>" name="<?php echo $this->get_field_name( 'slider_height' ) ?>" type="text" value="<?php echo esc_attr( $slider_height ) ?>" size="4" /> px 
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'speed' ) ?>"><?php _e( 'Transition Interval:', XF_TEXTDOMAIN ) ?></label>
            <select class="x-content" id="<?php echo $this->get_field_id( 'speed' ) ?>" name="<?php echo $this->get_field_name( 'speed' ) ?>">
                <?php for( $t = 1; $t<=10;$t++ ) : ?>
                <option value="<?php echo $t ?>" <?php selected( $t, $speed ); ?>><?php printf('%d %s', $t, __( 'sec', XF_TEXTDOMAIN )) ?></option>
                <?php endfor; ?>
            </select>
        </p>
        <h3><?php _e( 'Content Side Options', XF_TEXTDOMAIN ) ?></h3>
        <p>
            <label for="<?php echo $this->get_field_id( 'content_width' ) ?>"><?php _e( 'Content width:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'content_width' ) ?>" name="<?php echo $this->get_field_name( 'content_width' ) ?>" type="text" value="<?php echo esc_attr( $content_width ) ?>" size="4" /> 
            <select id="<?php echo $this->get_field_id('content_width_value') ?>" name="<?php echo $this->get_field_name('content_width_value') ?>">
                <option value="%" <?php selected('%', $content_width_value) ?>>%</option>
                <option value="em" <?php selected('em', $content_width_value) ?>>em</option>
                <option value="px" <?php selected('px', $content_width_value) ?>>px</option>
            </select>
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
        <h3><?php _e( 'Read more Settings', XF_TEXTDOMAIN ) ?></h3>
        <p>
            <label for="<?php echo $this->get_field_id( 'rm_bottom' ) ?>"><?php _e( 'Distance bottom:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'rm_bottom' ) ?>" name="<?php echo $this->get_field_name( 'rm_bottom' ) ?>" type="text" value="<?php echo esc_attr( $rm_bottom ) ?>" size="4" /> <?php _e('px', XF_TEXTDOMAIN) ?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'rm_right' ) ?>"><?php _e( 'Distance side:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'rm_right' ) ?>" name="<?php echo $this->get_field_name( 'rm_right' ) ?>" type="text" value="<?php echo esc_attr( $rm_right ) ?>" size="4" /> <?php _e('px', XF_TEXTDOMAIN) ?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('excerpt_morelink_text') ?>"><?php _e('Excerpt More Link Text:', XF_TEXTDOMAIN) ?></label>
            <input id="<?php echo $this->get_field_id('excerpt_morelink_text') ?>" name="<?php echo $this->get_field_name('excerpt_morelink_text') ?>" type="text" value="<?php echo esc_html($excerpt_morelink_text) ?>" />
        </p>
        <h3><?php _e( 'List Side', XF_TEXTDOMAIN ) ?></h3>
        <p>
            <label for="<?php echo $this->get_field_id( 'list_width' ) ?>"><?php _e( 'List Width:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'list_width' ) ?>" name="<?php echo $this->get_field_name( 'list_width' ) ?>" type="text" value="<?php echo esc_attr( $list_width ) ?>" size="4" />
            <select id="<?php echo $this->get_field_id('list_width_value') ?>" name="<?php echo $this->get_field_name('list_width_value') ?>">
                <option value="%" <?php selected('%', $list_width_value) ?>>%</option>
                <option value="em" <?php selected('em', $list_width_value) ?>>em</option>
                <option value="px" <?php selected('px', $list_width_value) ?>>px</option>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'list_position' ) ?>"><?php _e( 'List Position:', XF_TEXTDOMAIN ); ?></label>
            <select id="<?php echo $this->get_field_id('list_position') ?>" name="<?php echo $this->get_field_name('list_position') ?>">
                <option value="left" <?php selected('left', $list_position) ?>><?php _e( 'left', XF_TEXTDOMAIN ) ?></option>
                <option value="right" <?php selected('right', $list_position) ?>><?php _e( 'right', XF_TEXTDOMAIN ) ?></option>
            </select>
        </p>
        <p>
            <input id="<?php echo $this->get_field_id('autocalc') ?>" type="checkbox" name="<?php echo $this->get_field_name('autocalc') ?>" value="1" <?php checked(1, $autocalc) ?>/>
            <label for="<?php echo $this->get_field_id('autocalc') ?>"><?php _e('Auto Calculate Anchor Height', XF_TEXTDOMAIN) ?></label>
        </p>
        <?php $type = array(
            'title_only' => __( 'Title only', XF_TEXTDOMAIN ),
            'both' => __('Title and Xtreme Excerpt', XF_TEXTDOMAIN )
            ) ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'content_type' ) ?>"><?php _e( 'Content:', XF_TEXTDOMAIN ) ?></label>
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
        </div>
        <p>
            <input class="x-switcher" id="<?php echo $this->get_field_id('show_thumbnail') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_thumbnail') ?>" value="1" <?php checked(1, $show_thumbnail) ?>/>
            <label for="<?php echo $this->get_field_id('show_thumbnail') ?>"><?php _e('Show Thumbnail', XF_TEXTDOMAIN) ?></label>
        </p>
        <div class="<?php echo $this->get_field_id('show_thumbnail') ?>">
            <p>
                <label for="<?php echo $this->get_field_id('thumbnail_width') ?>"><?php _e('Thumbnail Width:', XF_TEXTDOMAIN) ?></label>
                <input id="<?php echo $this->get_field_id('thumbnail_width') ?>" name="<?php echo $this->get_field_name('thumbnail_width') ?>" type="text" value="<?php echo esc_attr($thumbnail_width) ?>" size="3" /> px
            </p>
        </div>
       <?php
    }
}
