<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Coin_Slider_Widget"; return $classes;'));

class Xtreme_Coin_Slider_Widget extends Xtreme_Widget_Base {
    function __construct() {
        $widget_ops = array( 'classname' => 'xtreme_coin_slider', 'description' => __('A cool Slider', XF_TEXTDOMAIN ) );
        parent::__construct(__FILE__, 'xtreme-coin-slider', __('Xtreme Coin Slider', XF_TEXTDOMAIN ), $widget_ops);
    }

	function ensure_widget_scripts($instance) {
		global $xtreme_script_manager;
		$xtreme_script_manager->ensure_coin_slider();
		$xtreme_script_manager->add_widget_data('xtreme-coin-slider', $this->id, array(
			'width' => $instance['slider_width'],
			'height' => $instance['slider_height'],
			'spw' => (int)$instance['spw'],
			'sph' => (int)$instance['sph'],
			'delay' => (int)$instance['delay'],
			'sDelay' => (int)$instance['strip_delay'],
			'opacity' => (float)$instance['opacity'],
			'titleSpeed' => (int)$instance['title_speed'],
			'effect' => $instance['effect'],
			'hoverPause' => ($instance['hover_pause'] === 0 ? false : true),
			'navigation' => ($instance['navigation'] === 1 ? true : false),
			'links' => true
		));
	}

    function widget( $args, $instance ) {
        global $wpdb;
        extract( $args );
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
        $number_posts = absint( $instance['number_posts'] );
        $thumbnail_size = esc_attr($instance['thumbnail_size']);

        $r = new WP_Query(
            array(
                'cat' => esc_attr( $instance['category'] ),
                'showposts' => $number_posts,
                'offset' => (int) $instance['offset'],
                'nopaging' => 0,
                'post_type' => 'post',
                'post_status' => 'publish',
                'orderby' => esc_attr( $instance['orderby'] )
            )
        );

        if($r->have_posts()):
            echo $before_widget;
            if ( $title ) echo $before_title . $title . $after_title;
            echo "<div id='x-" . $this->id ."' class='coin_wrapper' style='min-height:".esc_attr($instance['slider_height'])."px'>\n";
            while ($r->have_posts()) : $r->the_post();
                if ( has_post_thumbnail() ):
                    if ( $instance['content_type'] === 'nothing' ) {
                        $title = '';
                    } elseif ( $instance['content_type'] === 'title_only' ) {
                        $title = '<span class="coin-title">' . esc_attr( get_the_title() ) . '</span>';
                    } elseif ( $instance['content_type'] === 'xtreme_excerpt' ) {
                        $p = array('<p>','</p>');
                        $span = array('<span>', '</span>');
                        $title = str_replace( $p, $span, xtreme_excerpt( $instance['excerpt_length'], '', esc_html( $instance['excerpt_more'] ), false ) );
                    } elseif( $instance['content_type'] === 'both' ) {
                        $title = '<span class="coin-title">' . esc_attr( get_the_title() ) . '</span>';
                        $p = array('<p>','</p>');
                        $span = array('<span>', '</span>');
                        $title .= str_replace( $p, $span, xtreme_excerpt( $instance['excerpt_length'], '', esc_html( $instance['excerpt_more'] ), false ) );
                    }?>
<a href="<?php the_permalink() ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php the_title_attribute(); ?>">
    <?php the_post_thumbnail( $thumbnail_size, array( 'class'=> "hide" )) ?>
    <span class="ym-hideme"><?php echo $title; ?></span>
</a>
<?php else : ?>
	<img src="<?php echo XF_THEME_URI; ?>/images/white.gif" alt="" />
<?php endif; ?>
<?php endwhile;
            echo "</div>\n";
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
        $instance['effect'] = strip_tags( $new_instance['effect'] );
        $instance['slider_width'] = absint( strip_tags($new_instance['slider_width'] ) );
        $instance['slider_height'] = absint( strip_tags($new_instance['slider_height'] ) );
        $instance['spw'] = absint( strip_tags($new_instance['spw'] ) );
        $instance['sph'] = absint( strip_tags($new_instance['sph'] ) );
        $instance['delay'] = absint( strip_tags($new_instance['delay'] ) );
        $instance['strip_delay'] = absint( strip_tags($new_instance['strip_delay'] ) );
        $instance['opacity'] = floatval( strip_tags($new_instance['opacity'] ) );
        $instance['title_speed'] = absint( strip_tags($new_instance['title_speed'] ) );
        $instance['hover_pause'] = isset( $new_instance['hover_pause'] ) ? 1 : 0;;
        $instance['navigation'] = isset( $new_instance['navigation'] ) ? 1 : 0;;
        return $instance;
    }

    function form($instance) {
        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $number_posts = isset( $instance['number_posts'] ) ? min( max( 2, $instance['number_posts'] ), 15 ) : 3;
        $category = isset( $instance['category'] ) ? $instance['category'] : get_option( 'default_category' );
        $orderby = isset( $instance['orderby'] ) ? $instance['orderby'] : 'date';
        $offset = (isset( $instance['offset'] ) && !empty($instance['offset'] ) ) ? $instance['offset'] : 0;
        $thumbnail_size = (isset($instance['thumbnail_size'])) ? $instance['thumbnail_size'] : 'large';
        $content_type = isset( $instance['content_type'] ) ? $instance['content_type'] : 'nothing';
        $excerpt_length = isset( $instance['excerpt_length'] ) ? min(max(5, $instance['excerpt_length']), 80) : 20;
        $excerpt_more = isset( $instance['excerpt_more'] ) ? $instance['excerpt_more'] : '...';
        $effect = isset( $instance['effect'] ) ? $instance['effect'] : 'random';
        $slider_width = isset($instance['slider_width']) ? min(max(100, $instance['slider_width']), 1600) : 800;
        $slider_height = isset($instance['slider_height']) ? min(max(50, $instance['slider_height']), 1200) : 200;
        $spw = isset($instance['spw']) ? min(max(4, $instance['spw']), 10) : 7;
        $sph = isset($instance['sph']) ? min(max(2, $instance['sph']), 10) : 5;
        $delay = isset($instance['delay']) ? min(max(2000, $instance['delay']), 6000) : 5000;
        $strip_delay = isset($instance['strip_delay']) ? min(max(20, $instance['strip_delay']), 100) : 30;
        $opacity = isset($instance['opacity']) ? min(max(0.1, $instance['opacity']), 1.0) : 0.7;
        $title_speed = isset($instance['title_speed']) ? min(max(500, $instance['title_speed']), 2000) : 1000;
        $hover_pause = isset( $instance['hover_pause'] ) ? $instance['hover_pause'] : 0;
        $navigation = isset( $instance['navigation'] ) ? $instance['navigation'] : 0;
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
        </div>
        <h3><?php _e( 'Javascript Options', XF_TEXTDOMAIN ) ?></h3>
        <p>
            <label for="<?php echo $this->get_field_id( 'effect' ) ?>"><?php _e( 'Effect:', XF_TEXTDOMAIN ) ?></label>
            <select id="<?php echo $this->get_field_id( 'effect' ) ?>" name="<?php echo $this->get_field_name( 'effect' ) ?>">
                <?php
                $effects = array( 'random', 'swirl', 'rain', 'straight');
                foreach ( $effects as $ef ) :
                ?>
                <option value="<?php echo esc_attr( $ef ) ?>" <?php selected( $ef, $effect ) ?>><?php echo esc_attr( $ef ) ?></option>
                <?php endforeach ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'slider_width' ) ?>"><?php _e( 'Slider width:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'slider_width' ) ?>" name="<?php echo $this->get_field_name( 'slider_width' ) ?>" type="text" value="<?php echo esc_attr( $slider_width ) ?>" size="4" /> <?php _e('px', XF_TEXTDOMAIN) ?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'slider_height' ) ?>"><?php _e( 'Slider height:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'slider_height' ) ?>" name="<?php echo $this->get_field_name( 'slider_height' ) ?>" type="text" value="<?php echo esc_attr( $slider_height ) ?>" size="4" /> <?php _e('px', XF_TEXTDOMAIN) ?>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'spw' ) ?>"><?php _e( 'squares per width:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'spw' ) ?>" name="<?php echo $this->get_field_name( 'spw' ) ?>" type="text" value="<?php echo esc_attr( $spw ) ?>" size="4" />
            <br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 4, 10 ) ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'sph' ) ?>"><?php _e( 'squares per height:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'sph' ) ?>" name="<?php echo $this->get_field_name( 'sph' ) ?>" type="text" value="<?php echo esc_attr( $sph ) ?>" size="4" />
            <br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 2, 10 ) ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'delay' ) ?>"><?php _e( 'Delay between images:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'delay' ) ?>" name="<?php echo $this->get_field_name( 'delay' ) ?>" type="text" value="<?php echo esc_attr( $delay ) ?>" size="4" /> ms
            <br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 2000, 6000 ) ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'strip_delay' ) ?>"><?php _e( 'Delay between strips:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'strip_delay' ) ?>" name="<?php echo $this->get_field_name( 'strip_delay' ) ?>" type="text" value="<?php echo esc_attr( $strip_delay ) ?>" size="4" /> ms
            <br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 20, 100 ) ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'opacity' ) ?>"><?php _e( 'Caption opacity:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'opacity' ) ?>" name="<?php echo $this->get_field_name( 'opacity' ) ?>" type="text" value="<?php echo esc_attr( $opacity ) ?>" size="4" />
            <br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 0.1, 1.0 ) ?></small>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'title_speed' ) ?>"><?php _e( 'Title speed:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'title_speed' ) ?>" name="<?php echo $this->get_field_name( 'title_speed' ) ?>" type="text" value="<?php echo esc_attr( $title_speed ) ?>" size="4" />
            <br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 500, 2000 ) ?></small>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id( 'navigation' ) ?>"><?php _e( 'Show Navigation:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'navigation' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'navigation' ) ?>" value="1" <?php checked( 1, $navigation ) ?>/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'hover_pause' ) ?>"><?php _e( 'Pause on hover:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'hover_pause' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'hover_pause' ) ?>" value="1" <?php checked( 1, $hover_pause ) ?>/>
        </p>
       <?php
    }
}
