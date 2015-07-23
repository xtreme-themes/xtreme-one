<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Social_Links_Widget"; return $classes;'));

class Xtreme_Social_Links_Widget extends Xtreme_Widget_Base {
    function __construct() {
        $widget_ops = array( 'classname' => 'xtreme_social_links', 'description' => __( 'Your social links', XF_TEXTDOMAIN ) );
        parent::__construct(__FILE__, 'xtreme-social-links', __( 'Xtreme Social Links' , XF_TEXTDOMAIN ), $widget_ops );
    }

    function widget( $args, $instance ) {
        extract( $args, EXTR_SKIP );
        $title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
        $entry_title = !empty( $instance['entry_title'] ) ? esc_attr( $instance['entry_title'] ) : __( 'Entry RSS', XF_TEXTDOMAIN );
        $comments_title = !empty( $instance['comments_title'] )  ? esc_attr( $instance['comments_title'] ) : __( 'Comments RSS', XF_TEXTDOMAIN );
        $twitter_title = !empty( $instance['twitter_title'] )  ? esc_attr( $instance['twitter_title'] ) : __( 'Follow me', XF_TEXTDOMAIN );
        $facebook_title = !empty( $instance['facebook_title'] )  ? esc_attr( $instance['facebook_title'] ) : __( 'Facebook', XF_TEXTDOMAIN );
	   $googleplus_title = !empty( $instance['googleplus_title'] )  ? esc_attr( $instance['googleplus_title'] ) : __( 'Googleplus', XF_TEXTDOMAIN );
	   if (!isset($instance['show_googleplus'])) $instance['show_googleplus'] = 0;
	   if( $instance['show_entry'] || $instance['show_comments'] || $instance['show_twitter']
			 || $instance['show_facebook'] || $instance['show_googleplus']) {
            echo $before_widget;
            if ( !empty( $title ) ) { echo $before_title . $title . $after_title; }
            echo '<ul class="social">';
            if( $instance['show_entry'] ) {
                echo '  <li class="rss"><a href="' . esc_url( get_bloginfo( 'rss2_url' ) ) . '" title="' . $entry_title . '">' . $entry_title . '</a></li>';
            }
            if( $instance['show_comments'] ) {
                echo '  <li class="rss"><a href="' . esc_url( get_bloginfo( 'comments_rss2_url' ) ) . '" title="'. $comments_title . '">' . $comments_title . '</a></li>';
            }
            if( $instance['show_twitter'] && !empty( $instance['twitter_user'] ) ) {
                echo '  <li class="twitter"><a href="' . esc_url('http://twitter.com/' . $instance['twitter_user'] ) . '" title="'. $twitter_title . '">' . $twitter_title . '</a></li>';
            }
            if( $instance['show_facebook'] && !empty( $instance['facebook_user'] ) ) {
                echo '  <li class="facebook"><a href="' . esc_url('http://facebook.com/' . $instance['facebook_user'] ) . '" title="'. $facebook_title . '">' . $facebook_title . '</a></li>';
            }
            if( $instance['show_googleplus'] && !empty( $instance['googleplus_profil'] ) ) {
                echo '  <li class="googleplus"><a href="' . esc_url('http://plus.google.com/' . $instance['googleplus_profil'] ) . '" title="'. $googleplus_title . '">' . $googleplus_title . '</a></li>';
            }
            echo '</ul>';
            echo $after_widget;
        }
    }

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['show_entry'] = isset( $new_instance['show_entry'] ) ? 1 : 0;
        $instance['entry_title'] = strip_tags( $new_instance['entry_title'] );
        $instance['show_comments'] = isset( $new_instance['show_comments'] ) ? 1 : 0;
        $instance['comments_title'] = strip_tags( $new_instance['comments_title'] );
        $instance['show_twitter'] = isset( $new_instance['show_twitter'] ) ? 1 : 0;
        $instance['twitter_title'] = strip_tags( $new_instance['twitter_title'] );
        $instance['twitter_user'] = strip_tags( $new_instance['twitter_user'] );
        $instance['show_facebook'] = isset( $new_instance['show_facebook'] ) ? 1 : 0;
        $instance['facebook_title'] = strip_tags( $new_instance['facebook_title'] );
        $instance['facebook_user'] = strip_tags( $new_instance['facebook_user'] );
        $instance['show_googleplus'] = isset( $new_instance['show_googleplus'] ) ? 1 : 0;
        $instance['googleplus_title'] = strip_tags( $new_instance['googleplus_title'] );
        $instance['googleplus_profil'] = strip_tags( $new_instance['googleplus_profil'] );
        return $instance;
    }

    function form( $instance ) {
        $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'entry_title' => '', 'comments_title' => '' ) );
        $title = strip_tags( $instance['title'] );
        $show_entry = isset( $instance['show_entry']) ? $instance['show_entry'] : 0;
        $entry_title = !empty( $instance['entry_title'] ) ? esc_attr( $instance['entry_title'] ) : __( 'Entry RSS', XF_TEXTDOMAIN );
        $show_comments = isset( $instance['show_comments'] ) ? $instance['show_comments'] : 0;
        $comments_title = !empty( $instance['comments_title'] ) ? esc_attr( $instance['comments_title'] ) : __( 'Comments RSS', XF_TEXTDOMAIN );
        $show_twitter = isset( $instance['show_twitter'] ) ? $instance['show_twitter'] : 0;
        $twitter_title = !empty( $instance['twitter_title'] ) ? esc_attr( $instance['twitter_title'] ) : __( 'Follow me', XF_TEXTDOMAIN );
        $twitter_user = !empty( $instance['twitter_user'] ) ? $instance['twitter_user'] : '';
        $show_facebook = isset( $instance['show_facebook'] ) ? $instance['show_facebook'] : 0;
        $facebook_title = !empty( $instance['facebook_title'] ) ? esc_attr( $instance['facebook_title'] ) : __( 'Facebook', XF_TEXTDOMAIN );
        $facebook_user = !empty( $instance['facebook_user'] ) ? $instance['facebook_user'] : '';
        $show_googleplus = isset( $instance['show_googleplus'] ) ? $instance['show_googleplus'] : 0;
        $googleplus_title = !empty( $instance['googleplus_title'] ) ? esc_attr( $instance['googleplus_title'] ) : __( 'Google+', XF_TEXTDOMAIN );
        $googleplus_profil = !empty( $instance['googleplus_profil'] ) ? $instance['googleplus_profil'] : '';

        ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php  _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title ) ?>" /></p>
        <p>
            <input id="<?php echo $this->get_field_id('show_entry') ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_entry' ) ?>" value="1" <?php checked( 1, $show_entry ) ?>/>
            <label for="<?php echo $this->get_field_id('show_entry') ?>"><?php _e('Show Entry RSS', XF_TEXTDOMAIN) ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'entry_title' ) ?>"><?php _e( 'Link text for entry feed:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'entry_title' ) ?>" name="<?php echo $this->get_field_name( 'entry_title' ) ?>" type="text" value="<?php echo esc_attr( $entry_title ) ?>" /></p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_comments' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_comments' ) ?>" value="1" <?php checked( 1, $show_comments ) ?>/>
            <label for="<?php echo $this->get_field_id( 'show_comments' ) ?>"><?php _e('Show Comments RSS', XF_TEXTDOMAIN) ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'comments_title' ) ?>"><?php _e( 'Link text for comments feed:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'comments_title' ) ?>" name="<?php echo $this->get_field_name( 'comments_title' ) ?>" type="text" value="<?php echo esc_attr( $comments_title ) ?>" /></p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_twitter' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_twitter' ) ?>" value="1" <?php checked( 1, $show_twitter ) ?>/>
            <label for="<?php echo $this->get_field_id( 'show_twitter' ) ?>"><?php _e('Show Twitter', XF_TEXTDOMAIN) ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'twitter_user' ) ?>"><?php _e( 'Twitter User Name:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'twitter_user' ) ?>" name="<?php echo $this->get_field_name( 'twitter_user' ) ?>" type="text" value="<?php echo esc_attr( $twitter_user ) ?>" /></p>
        <p><label for="<?php echo $this->get_field_id( 'twitter_title' ) ?>"><?php _e( 'Link Text for Twitter Link:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'twitter_title' ) ?>" name="<?php echo $this->get_field_name( 'twitter_title' ) ?>" type="text" value="<?php echo esc_attr( $twitter_title ) ?>" /></p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_facebook' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_facebook' ) ?>" value="1" <?php checked( 1, $show_facebook ) ?>/>
            <label for="<?php echo $this->get_field_id( 'show_facebook' ) ?>"><?php _e('Show Facebook', XF_TEXTDOMAIN) ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'facebook_user' ) ?>"><?php _e( 'Facebook User Name:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'facebook_user' ) ?>" name="<?php echo $this->get_field_name( 'facebook_user' ) ?>" type="text" value="<?php echo esc_attr( $facebook_user ) ?>" /></p>
        <p><label for="<?php echo $this->get_field_id( 'facebook_title' ) ?>"><?php _e( 'Link Text for Facebook Link:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'facebook_title' ) ?>" name="<?php echo $this->get_field_name( 'facebook_title' ) ?>" type="text" value="<?php echo esc_attr( $facebook_title ) ?>" /></p>
        <p>
            <input id="<?php echo $this->get_field_id( 'show_googleplus' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_googleplus' ) ?>" value="1" <?php checked( 1, $show_googleplus ) ?>/>
            <label for="<?php echo $this->get_field_id( 'show_googleplus' ) ?>"><?php _e('Show Google+', XF_TEXTDOMAIN) ?></label>
        </p>
        <p><label for="<?php echo $this->get_field_id( 'googleplus_profil' ) ?>"><?php _e( 'Google+ Profil:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'googleplus_profil' ) ?>" name="<?php echo $this->get_field_name( 'googleplus_profil' ) ?>" type="text" value="<?php echo esc_attr( $googleplus_profil ) ?>" /></p>
        <p><label for="<?php echo $this->get_field_id( 'googleplus_title' ) ?>"><?php _e( 'Link Text for Google+ Link:', XF_TEXTDOMAIN ) ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'googleplus_title' ) ?>" name="<?php echo $this->get_field_name( 'googleplus_title' ) ?>" type="text" value="<?php echo esc_attr( $googleplus_title ) ?>" /></p>
        <?php
    }
}
