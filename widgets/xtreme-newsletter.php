<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Newsletter_Widget"; return $classes;'));

class Xtreme_Newsletter_Widget extends Xtreme_Widget_Base {
    function __construct() {
        $widget_ops = array( 'classname' => 'xtreme_newsletter', 'description' => __( 'A Feedburner email subscribe form', XF_TEXTDOMAIN ) );
        parent::__construct(__FILE__, 'xtreme-newsletter', __( 'Xtreme Newsletter' , XF_TEXTDOMAIN ), $widget_ops );
    }

    function widget( $args, $instance ) {
        extract( $args );
        $loc = get_locale();
        $emailtxt = 'text';
        if ( xtreme_is_html5() ) {
            $emailtxt = 'email';
        }
        echo $before_widget;
        if ( $instance['title'] )
            echo $before_title . apply_filters( 'widget_title', $instance['title'] ) . $after_title;
        ?>
<div>
	<form action="http://feedburner.google.com/fb/a/mailverify" method="post" <?php xtreme_aria_required('application', true) ?>>
		<div>
        <?php if($instance['text'] != '') :
			echo "<label for='x-email'>" . esc_html($instance['text']) . "</label>";
        endif; ?>
			<input class="x-newsletter-email" id="x-email" <?php xtreme_aria_required('email', true) ?> type="<?php echo $emailtxt ?>" name="email" value="<?php echo esc_attr( $instance['email'] ); ?>" onfocus="if (this.value == '<?php echo esc_js( $instance['email'] ); ?>') {this.value = '';}" onblur="if (this.value == '') {this.value = '<?php echo esc_js( $instance['email'] ); ?>';}" />
			<input class="x-newsletter-submit" type="submit" value="<?php echo esc_attr( $instance['button_text'] ); ?>" />
			<input type="hidden" value="<?php echo esc_attr( $instance['feedburner_id'] ); ?>" name="uri" />
			<input type="hidden" name="loc" value="<?php echo $loc ?>" />
		</div>
	</form>
</div>
        <?php
        echo $after_widget;
}

    function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['text'] = strip_tags( $new_instance['text'] );
        $instance['feedburner_id'] = strip_tags( $new_instance['feedburner_id'] );
        $instance['email'] = strip_tags( $new_instance['email'] );
        $instance['button_text'] = strip_tags( $new_instance['button_text'] );
        return $instance;
    }

    function form( $instance ) {
        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $text = !empty( $instance['text'] ) ? esc_attr( $instance['text'] ) : '';
        $feedburner_id = !empty( $instance['feedburner_id'] ) ? esc_attr( $instance['feedburner_id'] ) : '';
        $email = isset( $instance['email']) ? $instance['email'] : __( 'Enter your email...', XF_TEXTDOMAIN );
        $button_text = !empty( $instance['button_text'] ) ? esc_attr( $instance['button_text'] ) : __( 'Subscribe!', XF_TEXTDOMAIN );
        ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php  _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title ) ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'text' ) ?>"><?php  _e( 'Additional text before input field:', XF_TEXTDOMAIN ) ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'text' ) ?>" name="<?php echo $this->get_field_name( 'text' ) ?>" type="text" value="<?php echo esc_attr( $text ) ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id('feedburner_id') ?>"><?php _e('Google/Feedburner ID:', XF_TEXTDOMAIN) ?></label>
            <input id="<?php echo $this->get_field_id('feedburner_id') ?>" type="text" name="<?php echo $this->get_field_name( 'feedburner_id' ) ?>" value="<?php echo $feedburner_id ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'email' ) ?>"><?php _e( 'Input Text:', XF_TEXTDOMAIN ) ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'email' ) ?>" name="<?php echo $this->get_field_name( 'email' ) ?>" type="text" value="<?php echo esc_attr( $email ) ?>" />
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'button_text' ) ?>"><?php _e( 'Button Text:', XF_TEXTDOMAIN ) ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'button_text' ) ?>" name="<?php echo $this->get_field_name( 'button_text' ) ?>" type="text" value="<?php echo esc_attr( $button_text ) ?>" />
        </p>
        <?php
    }
}
