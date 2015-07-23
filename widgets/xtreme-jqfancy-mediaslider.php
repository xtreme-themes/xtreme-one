<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_jqFancy_MediaSlider_Widget"; return $classes;'));

class Xtreme_jqFancy_MediaSlider_Widget extends Xtreme_Widget_Base {
    function __construct() {
        $widget_ops = array( 'classname' => 'xtreme_jqfancy_slider', 'description' => __('A cool Media Slider', XF_TEXTDOMAIN ) );
        parent::__construct(__FILE__, 'xtreme_jqfancy_slider', __('Xtreme jqFancy Media Slider', XF_TEXTDOMAIN ), $widget_ops);
		$this->media_callback_id = 'jqfancy';
		add_action('xtreme-dlg-apply-changes_'.$this->media_callback_id, array(&$this, 'on_dlg_apply_changes'));
    }

	function ensure_widget_scripts($instance) {
		global $xtreme_script_manager;
		$xtreme_script_manager->ensure_jqfancy_slider();
		$xtreme_script_manager->add_widget_data('xtreme-jqfancy-slider', $this->id, array(
			'effect' => $instance['effect'],
			'width' => $instance['slider_width'],
			'height' => $instance['slider_height'],
			'strips' => (int)$instance['strips'],
			'delay' => (int)$instance['delay'],
			'stripDelay' => (int)$instance['strip_delay'] ,
			'titleOpacity' => (float)$instance['opacity'],
			'titleSpeed' => (int)$instance['title_speed'],
			'position' => $instance['position'],
			'direction' => $instance['direction'],
			'navigation' => ($instance['navigation'] === 1 ? true : false),
			'links' => ($instance['disable_links'] === 1 ? false : true)
		));
	}

    function widget($args, $instance) {
        global $wpdb;
        extract( $args );
        $title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		
        $thumbnail_size = esc_attr( $instance['thumbnail_size'] );
		
		$portlet_id = isset( $instance['portlet_id'] ) && is_array($instance['portlet_id']) ? $instance['portlet_id'] : array();
		$media_parentid = isset( $instance['media_parentid'] ) && is_array($instance['media_parentid']) ? $instance['media_parentid'] : array();
		$media_caption = isset( $instance['media_caption'] ) && is_array($instance['media_caption']) ? $instance['media_caption'] : array();
		$media_description = isset( $instance['media_description'] ) && is_array($instance['media_description']) ? $instance['media_description'] : array();
		$media_linktype = isset( $instance['media_linktype'] ) && is_array($instance['media_linktype']) ? $instance['media_linktype'] : array();
		$media_linkurl = isset( $instance['media_linkurl'] ) && is_array($instance['media_linkurl']) ? $instance['media_linkurl'] : array();

		$count = count($portlet_id);
		if ($count > 0) {
            echo $before_widget;
            if ( $title ) echo $before_title . $title . $after_title;
            echo "<div id='x-" . $this->id ."' class='jqfancy_wrapper' style='min-height:".esc_attr($instance['slider_height'])."px'>\n";
			for($i=0; $i<$count; $i++) {
				if ( $instance['content_type'] === 'nothing' ) {
					$title = '';
				} elseif ( $instance['content_type'] === 'title_only' ) {
					$title = '<span class="fancy-title">' . esc_attr( $media_caption[$i] ) . '</span>';
				} elseif ( $instance['content_type'] === 'xtreme_excerpt' ) {
					$title = '<span>'.esc_html($media_description[$i]).'</span>';
				} elseif( $instance['content_type'] === 'both' ) {
					$title = '<span class="fancy-title">' . esc_attr( $media_caption[$i] ) . '</span>';
					$p = array('<p>','</p>');
					$span = array('<span>', '</span>');
					$title .= '<span>'.esc_html($media_description[$i]).'</span>';
				}
				$thumb = wp_get_attachment_image( $portlet_id[$i], $thumbnail_size, false, array( 'alt' => $title, 'class'=> "hide" ) );
				if ($thumb) echo $thumb;
				else echo '<img src="'.XF_THEME_URI.'/images/white.gif" alt="' . esc_html($title). '" class="ym-hideme"/>';
				switch($media_linktype[$i]) {
					case 'post':
						?><a href="<?php echo get_permalink($media_parentid[$i]) ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php echo esc_attr(strip_tags($media_caption[$i])); ?>"></a><?php 
						break;
					case 'attachment':
						?><a href="<?php echo get_permalink($portlet_id[$i]) ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php echo esc_attr(strip_tags($media_caption[$i])); ?>"></a><?php 
						break;
					default:
						?><a href="<?php echo esc_url( $media_linkurl[$i] ) ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php echo esc_attr(strip_tags($media_caption[$i])); ?>"></a><?php 
						break;
				}
			}
			echo "</div>\n";
            echo $after_widget;
		}
    }

    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['thumbnail_size'] = strip_tags($new_instance['thumbnail_size']);
        $instance['content_type'] = strip_tags( $new_instance['content_type'] );
        $instance['effect'] = strip_tags( $new_instance['effect'] );
        $instance['slider_width'] = absint( strip_tags($new_instance['slider_width'] ) );
        $instance['slider_height'] = absint( strip_tags($new_instance['slider_height'] ) );
        $instance['strips'] = absint( strip_tags($new_instance['strips'] ) );
        $instance['delay'] = absint( strip_tags($new_instance['delay'] ) );
        $instance['strip_delay'] = absint( strip_tags($new_instance['strip_delay'] ) );
        $instance['opacity'] = floatval( strip_tags($new_instance['opacity'] ) );
        $instance['title_speed'] = absint( strip_tags($new_instance['title_speed'] ) );
        $instance['position'] = strip_tags( $new_instance['position'] );
        $instance['direction'] = strip_tags( $new_instance['direction'] );
        $instance['navigation'] = isset( $new_instance['navigation'] ) ? 1 : 0;
        $instance['disable_links'] = isset( $new_instance['disable_links'] ) ? 1 : 0;
		$instance['portlet_id'] = isset( $new_instance['portlet_id'] ) && is_array($new_instance['portlet_id']) ? $new_instance['portlet_id'] : array();
		$instance['media_parentid'] = isset( $new_instance['media_parentid'] ) && is_array($new_instance['media_parentid']) ? $new_instance['media_parentid'] : array();
		$instance['media_caption'] = isset( $new_instance['media_caption'] ) && is_array($new_instance['media_caption']) ? $new_instance['media_caption'] : array();
		$instance['media_description'] = isset( $new_instance['media_description'] ) && is_array($new_instance['media_description']) ? $new_instance['media_description'] : array();
 		$instance['media_linktype'] = isset( $new_instance['media_linktype'] ) && is_array($new_instance['media_linktype']) ? $new_instance['media_linktype'] : array();
 		$instance['media_linkurl'] = isset( $new_instance['media_linkurl'] ) && is_array($new_instance['media_linkurl']) ? $new_instance['media_linkurl'] : array();
       return $instance;
    }
	
    function form($instance) {
        $title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
        $thumbnail_size = (isset($instance['thumbnail_size'])) ? $instance['thumbnail_size'] : 'large';
        $content_type = isset( $instance['content_type'] ) ? $instance['content_type'] : 'nothing';
        $effect = isset( $instance['effect'] ) ? $instance['effect'] : 'zipper';
        $slider_width = isset($instance['slider_width']) ? min(max(100, $instance['slider_width']), 1600) : 800;
        $slider_height = isset($instance['slider_height']) ? min(max(50, $instance['slider_height']), 1200) : 200;
        $strips = isset($instance['strips']) ? min(max(5, $instance['strips']), 50) : 20;
        $delay = isset($instance['delay']) ? min(max(2000, $instance['delay']), 6000) : 5000;
        $strip_delay = isset($instance['strip_delay']) ? min(max(20, $instance['strip_delay']), 100) : 50;
        $opacity = isset($instance['opacity']) ? min(max(0.1, $instance['opacity']), 1.0) : 0.7;
        $title_speed = isset($instance['title_speed']) ? min(max(500, $instance['title_speed']), 2000) : 1000;
        $position = isset( $instance['position'] ) ? $instance['position'] : 'alternate';
        $direction = isset( $instance['direction'] ) ? $instance['direction'] : 'fountainAlternate';
        $navigation = isset( $instance['navigation'] ) ? $instance['navigation'] : 0;
        $disable_links = isset( $instance['disable_links'] ) ? $instance['disable_links'] : 0;
		$portlet_id = isset( $instance['portlet_id'] ) && is_array($instance['portlet_id']) ? $instance['portlet_id'] : array();
		$media_parentid = isset( $instance['media_parentid'] ) && is_array($instance['media_parentid']) ? $instance['media_parentid'] : array();
		$media_caption = isset( $instance['media_caption'] ) && is_array($instance['media_caption']) ? $instance['media_caption'] : array();
		$media_description = isset( $instance['media_description'] ) && is_array($instance['media_description']) ? $instance['media_description'] : array();
		$media_linktype = isset( $instance['media_linktype'] ) && is_array($instance['media_linktype']) ? $instance['media_linktype'] : array();
		$media_linkurl = isset( $instance['media_linkurl'] ) && is_array($instance['media_linkurl']) ? $instance['media_linkurl'] : array();
		?>
        <p>
            <label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title) ?>" />
        </p>
		<h3><?php _e( 'Image Options', XF_TEXTDOMAIN ) ?></h3>
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
            'nothing' => __( 'Without any', XF_TEXTDOMAIN ),
            'title_only' => __( 'Caption only', XF_TEXTDOMAIN ),
            'xtreme_excerpt' => __( 'Description only', XF_TEXTDOMAIN ),
            'both' => __('Caption and Description', XF_TEXTDOMAIN )
            ) ?>
        <p>
            <label for="<?php echo $this->get_field_id( 'content_type' ) ?>"><?php _e( 'Slider Inscriptions:', XF_TEXTDOMAIN ) ?></label>
            <select class="x-content" id="<?php echo $this->get_field_id( 'content_type' ) ?>" name="<?php echo $this->get_field_name( 'content_type' ) ?>">
                <?php foreach( $type as $c => $d ) : ?>
                <option value="<?php echo $c ?>" <?php selected( $c, $content_type ); ?>><?php echo esc_attr( $d ) ?></option>
                <?php endforeach; ?>
            </select>
        </p>
		<div class="x-portlet-wrapper-label"><?php _e( 'Mediathek Collection:', XF_TEXTDOMAIN ) ?></div>
		<div class="x-portlet-wrapper <?php echo $this->get_field_id( 'connected' ) ?>">
			<input name="x-portlet-ajax-action" type="hidden" value="<?php echo $this->get_ajax_action('dialog'); ?>" />
			<input name="x-portlet-ajax-width" type="hidden" value="600" />
			<div class="x-portlet-wrapper-empty"><em><?php _e('- empty -', XF_TEXTDOMAIN); ?></em></div>	
			<div class="x-portlet-column x-portlet-dest">
				<?php 
				$entries = count($portlet_id);
				for ($i=0;$i<$entries;$i++){
					$this->_portlet($portlet_id[$i],$media_caption[$i],$media_description[$i],$media_linktype[$i],$media_linkurl[$i]);
				}
				?>
			</div>
			
		</div>
		<div class="widget-control-actions">
			<img class="ajax-feedback" src="<?php echo get_admin_url(); ?>images/loading.gif" title="" alt=""><a title="<?php _e('Add Images', XF_TEXTDOMAIN); ?>" class="x-dlg-media" rel="<?php echo $this->media_callback_id;?>" href="#"><?php _e('Add Images', XF_TEXTDOMAIN); ?></a>
			&nbsp;|&nbsp;
			<a title="<?php _e('Remove All Images', XF_TEXTDOMAIN); ?>" class="x-portlet-removeall" href="#"><?php _e('Remove All Images', XF_TEXTDOMAIN); ?></a>			
			<p><small><em><?php _e("Select your Slider Image content, modify Texts and linked URL's. Afterward sort it by drag 'n drop as usual.",XF_TEXTDOMAIN); ?></em></small></p>
		</div>
        <h3><?php _e( 'Javascript Options', XF_TEXTDOMAIN ) ?></h3>
        <p>
            <label for="<?php echo $this->get_field_id( 'effect' ) ?>"><?php _e( 'Effect:', XF_TEXTDOMAIN ) ?></label>
            <select id="<?php echo $this->get_field_id( 'effect' ) ?>" name="<?php echo $this->get_field_name( 'effect' ) ?>">
                <?php
                $effects = array( 'wave', 'zipper', 'curtain');
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
            <label for="<?php echo $this->get_field_id( 'strips' ) ?>"><?php _e( 'Strips:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'strips' ) ?>" name="<?php echo $this->get_field_name( 'strips' ) ?>" type="text" value="<?php echo esc_attr( $strips ) ?>" size="4" />
            <br /><small><?php printf( __( '(between %1s and %2s)', XF_TEXTDOMAIN), 5, 50 ) ?></small>
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
            <label for="<?php echo $this->get_field_id( 'position' ) ?>"><?php _e( 'Position:', XF_TEXTDOMAIN ); ?></label>
            <select id="<?php echo $this->get_field_id( 'position' ) ?>" name="<?php echo $this->get_field_name( 'position' ) ?>">
                <?php
                $positions = array( 'top', 'bottom', 'alternate', 'curtain');
                foreach ( $positions as $pos ) :
                ?>
                <option value="<?php echo esc_attr( $pos ) ?>" <?php selected( $pos, $position ) ?>><?php echo esc_attr( $pos ) ?></option>
                <?php endforeach ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'direction' ) ?>"><?php _e( 'Direction:', XF_TEXTDOMAIN ); ?></label>
            <select id="<?php echo $this->get_field_id( 'direction' ) ?>" name="<?php echo $this->get_field_name( 'direction' ) ?>">
                <?php
                $directions = array( 'left', 'right', 'alternate', 'random', 'fountain', 'fountainAlternate');
                foreach ( $directions as $dirs ) :
                ?>
                <option value="<?php echo esc_attr( $dirs ) ?>" <?php selected( $dirs, $direction ) ?>><?php echo esc_attr( $dirs ) ?></option>
                <?php endforeach ?>
            </select>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'navigation' ) ?>"><?php _e( 'Show Navigation:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'navigation' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'navigation' ) ?>" value="1" <?php checked( 1, $navigation ) ?>/>
        </p>
        <p>
            <label for="<?php echo $this->get_field_id( 'disable_links' ) ?>"><?php _e( 'Disable Links:', XF_TEXTDOMAIN ); ?></label>
            <input id="<?php echo $this->get_field_id( 'disable_links' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'disable_links' ) ?>" value="1" <?php checked( 1, $disable_links ) ?>/>
        </p>
       <?php
    }
	
	function _portlet($id, $caption, $description, $linktype = 'none', $linkurl = '') {
		$post = get_post($id);
		if(is_null($post)) return;
		$header = $post->post_title;
	?>
		<div class="x-portlet-item">
			<div class="x-portlet-item-header"><?php if(strlen($header) > 20) $header = substr($header,0, 20)."..."; echo $header; ?></div>
			<div class="x-portlet-item-content">
				<a href="<?php echo wp_get_attachment_url($id); ?>" class="thickbox" alt="<?php echo esc_html($post->post_title); ?>" title="<?php echo esc_html($post->post_title); ?>">
				<?php
				if ( $thumb = wp_get_attachment_image( $id, array( 80, 60 ), true ) ) {
					echo $thumb;
				}
				?></a>
				<div><?php echo date_i18n($post->post_date); ?></div>
				<div><?php _e("Type", XF_TEXTDOMAIN) ?>: <em><?php echo $post->post_mime_type; ?></em><br/><br/></div>
				<div><span class="x-portlet-item-trash ui-icon ui-icon-trash"></span><a class="x-portlet-item-del" href="#"><?php _e("Remove Image", XF_TEXTDOMAIN); ?></a></div>
				<input name="<?php echo $this->get_field_name('portlet_id'); ?>[]" type="hidden" value="<?php echo $id; ?>"/>
				<div class="x-portlet-item-sub-content">
				<p>
				<label><small><?php _e("Caption", XF_TEXTDOMAIN); ?></small></label>
				<input name="<?php echo $this->get_field_name('media_caption'); ?>[]" type="text" value="<?php echo esc_html($caption); ?>" size="10" class="widefat"/>
				</p>
				<p>
				<label><small><?php _e("Description", XF_TEXTDOMAIN); ?></small></label>
				<textarea name="<?php echo $this->get_field_name('media_description'); ?>[]" type="text" class="widefat"><?php echo esc_textarea($description); ?></textarea>
				</p>
				<p>
				<label><small><?php _e("Image Link", XF_TEXTDOMAIN); ?></small></label>
				<select name="<?php echo $this->get_field_name( 'media_linktype' ) ?>[]" class="x-image-url">
					<?php
					$linktypes = array(
						'post' 		=> __('Related Post or Page', XF_TEXTDOMAIN), 
						'attachment'=> __('Image Attachment Page', XF_TEXTDOMAIN),
						'userdef' 	=> __('Custom defined URL', XF_TEXTDOMAIN)
					);
					foreach ( $linktypes as $key => $value ) :
						if ($key === 'post' && $post->post_parent == 0) continue;
					?>
					<option value="<?php echo esc_attr( $key ) ?>" <?php selected( $key, $linktype ) ?>><?php echo esc_attr( $value ) ?></option>
					<?php endforeach ?>
				</select>
				</p>
				<div class="x-image-url-content">
					<p>
					<input name="<?php echo $this->get_field_name('media_parentid'); ?>[]" type="hidden" value="<?php echo $post->post_parent; ?>" />
					<label><small><?php _e("Link URL", XF_TEXTDOMAIN); ?></small></label>
					<input name="<?php echo $this->get_field_name('media_linkurl'); ?>[]" type="text" value="<?php echo esc_html($linkurl); ?>" size="10" class="widefat"/>
					</p>
				</div>
				</div>
			</div>
		</div>
	<?php
	}
	
	function on_dlg_apply_changes($ids) {
		$ids = explode(',', $ids);
		if (isset($_POST['target_input'])) {
			$a = (array)explode('-', $_POST['target_input']);
			$this->number = (int)end($a);
		}
	?>
		<div id="x-sl-temp">
		<?php 
			foreach($ids as $id) { 
				$post 			= get_post($id);
				$caption 		= $post->post_excerpt;
				$description 	= $post->post_content;			
				if ( (empty($caption) || empty($description)) && $post->post_parent != 0) {
					$parent = get_post($post->post_parent);
					if (empty($caption)) $caption = $parent->post_title;
					if (empty($description)) $description = wp_html_excerpt($parent->post_content, 200).'...';
				}
				echo $this->_portlet($id, $caption, $description); 
			} 
		?>
		</div>
		<script type="text/javascript">
        //<![CDATA[
			jQuery( ".x-portlet-item", jQuery("#x-sl-temp") ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
				.find( ".x-portlet-item-header" )
					.addClass( "ui-widget-header ui-corner-all" )
					.prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
					.end()
				.find( ".x-portlet-item-content" );
			portlets = jQuery("#x-sl-temp").html();
			jQuery("#x-sl-temp").remove();
			//alert(portlets);
			jQuery('#<?php echo $_POST['target_input']; ?>').find('.x-portlet-column').append(portlets);			
			jQuery('#<?php echo $_POST['target_input']; ?>').find('.x-portlet-wrapper-empty').hide();
			jQuery('#<?php echo $_POST['target_input']; ?>').find('.x-image-url').each(function(i, el) {
				if (jQuery(el).val == 'userdef')
					jQuery(el).parent().next('.x-image-url-content').first().show();
				else
					jQuery(el).parent().next('.x-image-url-content').first().hide();
			});		
		//]]>
		</script>
	<?php	
	}
}
