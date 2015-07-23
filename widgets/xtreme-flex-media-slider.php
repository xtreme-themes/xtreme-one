<?php

add_filter( 'xtreme-collect-widget-classes', create_function( '$classes', '$classes[] = "Xtreme_Flex_Media_Slider"; return $classes;' ) );

class Xtreme_Flex_Media_Slider extends Xtreme_Widget_Base {
	function __construct() {
		$widget_ops = array( 'classname' => 'xtreme_flex_slider', 'description' => __( 'A responsive Slider for your Media', XF_TEXTDOMAIN ) );
		parent::__construct( __FILE__, 'xtreme-flex-media-slider', __( 'Xtreme Media FlexSlider', XF_TEXTDOMAIN ), $widget_ops );
		$this->media_callback_id = 'flexslider';
		add_action('xtreme-dlg-apply-changes_'.$this->media_callback_id, array(&$this, 'on_dlg_apply_changes'));
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
			'controlNav' => ( $instance['controlNav'] === 1 ? true : false ),
			'directionNav' => ( $instance['directionNav'] === 1 ? true : false ),
			'randomize' => (isset( $instance['randomize'] ) && $instance['randomize'] === 1 ? true : false),
			'prevText' =>  __(	'previous', XF_TEXTDOMAIN ),
			'nextText' =>  __(	'next', XF_TEXTDOMAIN )
		) );
	}
	
	function widget($args, $instance) {
		global $wpdb;
		extract( $args );
		$portlet_id = isset( $instance['portlet_id'] ) && is_array($instance['portlet_id']) ? $instance['portlet_id'] : array();
		$media_parentid = isset( $instance['media_parentid'] ) && is_array($instance['media_parentid']) ? $instance['media_parentid'] : array();
		$media_caption = isset( $instance['media_caption'] ) && is_array($instance['media_caption']) ? $instance['media_caption'] : array();
		$media_description = isset( $instance['media_description'] ) && is_array($instance['media_description']) ? $instance['media_description'] : array();
		$media_more_link_text = isset( $instance['media_more_link_text'] ) && is_array($instance['media_more_link_text']) ? $instance['media_more_link_text'] : array();
		$media_linktype = isset( $instance['media_linktype'] ) && is_array($instance['media_linktype']) ? $instance['media_linktype'] : array();
		$media_linkurl = isset( $instance['media_linkurl'] ) && is_array($instance['media_linkurl']) ? $instance['media_linkurl'] : array();

		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'] );
		$thumbnail_size = esc_attr( $instance['thumbnail_size'] );
		$show_posttitle = ( isset($instance['show_posttitle'] ) ) ? $instance['show_posttitle'] : 0;
		$show_excerpt = ( isset($instance['show_excerpt'] ) ) ? $instance['show_excerpt'] : 0;

		$count = count($portlet_id);
		if ($count > 0) {
			echo $before_widget;
			if ( $title ) echo $before_title . $title . $after_title;
			echo "<div id='x-" . $this->id ."' class='flex-container flex-media'><div class='flexslider'><ul class='slides'>\n";
			for ( $i=0; $i < $count; $i++ ) {
				echo '<li>';
				$thumb = wp_get_attachment_image( $portlet_id[$i], $thumbnail_size, false, array( 'alt' => $title ) );
				if ( $thumb ): 
					switch( $media_linktype[$i] ) {
						case 'post':
							?><a href="<?php echo get_permalink( $media_parentid[$i] ) ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', XF_TEXTDOMAIN ); ?> <?php echo esc_attr( strip_tags( $media_caption[$i] ) ); ?>"><?php 
							break;
						case 'attachment':
							?><a href="<?php echo get_permalink( $portlet_id[$i] ) ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', XF_TEXTDOMAIN ); ?> <?php echo esc_attr( strip_tags( $media_caption[$i] ) ); ?>"><?php 
							break;
						default:
							?><a href="<?php echo esc_url( $media_linkurl[$i] ) ?>" rel="bookmark" title="<?php _e( 'Permanent Link to', XF_TEXTDOMAIN ); ?> <?php echo esc_attr( strip_tags( $media_caption[$i] ) ); ?>"><?php 
							break;
					}
					echo $thumb; ?>
					</a>
				<?php else:
					echo '<img src="' . XF_THEME_URI . '/images/white.gif" alt="Default image" />';
				endif;
				$html = '';
				if ( $show_posttitle || $show_excerpt ) {
					if ( $show_posttitle ) {
						$html .= '<h2>' . esc_attr($media_caption[$i]) . '</h2>';
					}
					if ( $show_excerpt ) {
						$html .= '<div class="flex-excerpt">';
						$html .= $media_description[$i];
						switch ( $media_linktype[$i] ) {
							case 'post':
								$html .= '<span class="read-more"><a href="' . get_permalink( $media_parentid[$i] ) .'" rel="bookmark" title="'. __( 'Permanent Link to', XF_TEXTDOMAIN ) . ' ' . esc_attr( strip_tags($media_caption[$i] ) ) .'">'.esc_html( $media_more_link_text[$i] ).'</a></span></div>';
								break;
							case 'attachment':
								$html .= '<span class="read-more"><a href="' . get_permalink( $portlet_id[$i] ) .'" rel="bookmark" title="'. __( 'Permanent Link to', XF_TEXTDOMAIN ) . ' ' . esc_attr( strip_tags($media_caption[$i] ) ) .'">'.esc_html( $media_more_link_text[$i] ).'</a></span></div>';
								break;
							default:
								$html .= '<span class="read-more"><a href="' . esc_url( $media_linkurl[$i] ) .'" rel="bookmark" title="' . esc_attr( strip_tags( $media_more_link_text[$i] ) ) .'">'.esc_html( $media_more_link_text[$i] ).'</a></span></div>';
								break;
						}
					}
					if ( !empty( $html ) ) {
						echo '<div class="flex-caption">' . $html . '</div>';
					}
				}
				echo '</li>';
			}
			echo "</ul></div></div>\n";
			echo $after_widget;
		}
	}

	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['thumbnail_size'] = strip_tags($new_instance['thumbnail_size']);
		$instance['show_posttitle'] = ( isset($new_instance['show_posttitle'] ) ) ? 1 : 0;
		$instance['show_excerpt'] = ( isset($new_instance['show_excerpt'] ) ) ? 1 : 0;
		$instance['portlet_id'] = isset( $new_instance['portlet_id'] ) && is_array( $new_instance['portlet_id'] ) ? $new_instance['portlet_id'] : array();
		$instance['media_parentid'] = isset( $new_instance['media_parentid'] ) && is_array( $new_instance['media_parentid'] ) ? $new_instance['media_parentid'] : array();
		$instance['media_caption'] = isset( $new_instance['media_caption'] ) && is_array( $new_instance['media_caption'] ) ? $new_instance['media_caption'] : array();
		if ( current_user_can('unfiltered_html') ) {
			$instance['media_description'] = isset( $new_instance['media_description'] ) && is_array( $new_instance['media_description'] ) ? $new_instance['media_description'] : array();
		} else {
			$instance['media_description'] = isset( $new_instance['media_description'] ) && is_array( $new_instance['media_description'] ) ? stripslashes( wp_filter_post_kses( addslashes( $new_instance['media_description'] ) ) ) : array();
		}
		$instance['media_more_link_text'] = isset( $new_instance['media_more_link_text'] ) && is_array( $new_instance['media_more_link_text'] ) ? $new_instance['media_more_link_text'] : array();
		$instance['media_linktype'] = isset( $new_instance['media_linktype'] ) && is_array( $new_instance['media_linktype'] ) ? $new_instance['media_linktype'] : array();
 		$instance['media_linkurl'] = isset( $new_instance['media_linkurl'] ) && is_array( $new_instance['media_linkurl'] ) ? $new_instance['media_linkurl'] : array();
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
		$thumbnail_size = ( isset( $instance['thumbnail_size'] ) ) ? $instance['thumbnail_size'] : 'large';
		$show_posttitle = isset( $instance['show_posttitle'] ) ? $instance['show_posttitle'] : 0;
		$show_excerpt = isset( $instance['show_excerpt'] ) ? $instance['show_excerpt'] : 0;
		$portlet_id = isset( $instance['portlet_id'] ) && is_array( $instance['portlet_id'] ) ? $instance['portlet_id'] : array();
		$media_parentid = isset( $instance['media_parentid'] ) && is_array( $instance['media_parentid'] ) ? $instance['media_parentid'] : array();
		$media_caption = isset( $instance['media_caption'] ) && is_array( $instance['media_caption'] ) ? $instance['media_caption'] : array();
		$media_description = isset( $instance['media_description'] ) && is_array( $instance['media_description'] ) ? $instance['media_description'] : array();
		$media_more_link_text = isset( $instance['media_more_link_text'] ) && is_array( $instance['media_more_link_text'] ) ? $instance['media_more_link_text'] : array();
		$media_linktype = isset( $instance['media_linktype'] ) && is_array( $instance['media_linktype'] ) ? $instance['media_linktype'] : array();
		$media_linkurl = isset( $instance['media_linkurl'] ) && is_array( $instance['media_linkurl'] ) ? $instance['media_linkurl'] : array();
		$animation = isset( $instance['animation'] ) ? $instance['animation'] : 'slide';
		$slideDirection = isset( $instance['slideDirection'] ) ? $instance['slideDirection'] : 'horizontal';
		$slideshowSpeed = isset( $instance['slideshowSpeed'] ) ? min( max( 2000, $instance['slideshowSpeed'] ), 8000 ) : 7000;
		$animationDuration = isset( $instance['animationDuration'] ) ? min( max( 100, $instance['animationDuration'] ), 1000) : 700;
		$randomize = isset( $instance['randomize'] ) ? $instance['randomize'] : 0;
		$directionNav = isset( $instance['directionNav'] ) ? $instance['directionNav'] : 0;
		$controlNav = isset( $instance['controlNav'] ) ? $instance['controlNav'] : 0;
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title) ?>" />
		</p>
		<h3><?php _e( 'Image Options', XF_TEXTDOMAIN ) ?></h3>
		<p>
			<label for="<?php echo $this->get_field_id( 'thumbnail_size' ) ?>"><?php _e( 'Size:', XF_TEXTDOMAIN ) ?></label>
			<select id="<?php echo $this->get_field_id( 'thumbnail_size' ) ?>" name="<?php echo $this->get_field_name( 'thumbnail_size' ) ?>">
			<?php global $_wp_additional_image_sizes;
			$sizes = get_intermediate_image_sizes();
			foreach ( $sizes as $size ) : ?>
				<option value="<?php echo $size ?>" <?php selected( $size, $thumbnail_size, true ) ?>><?php echo esc_attr( $size ) ?></option>
			<?php endforeach; ?>
			</select>
		</p>
		<p>
			<input id="<?php echo $this->get_field_id( 'show_posttitle' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_posttitle' ) ?>" value="1" <?php checked( 1, $show_posttitle ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_posttitle' ) ?>"><?php _e('Show Post Title', XF_TEXTDOMAIN) ?></label>
		</p>

		<p>
			<input class="x-switcher" id="<?php echo $this->get_field_id( 'show_excerpt' ) ?>" type="checkbox" name="<?php echo $this->get_field_name( 'show_excerpt' ) ?>" value="1" <?php checked( 1, $show_excerpt ) ?>/>
			<label for="<?php echo $this->get_field_id( 'show_excerpt' ) ?>"><?php _e( 'Show Xtreme Excerpt', XF_TEXTDOMAIN ) ?></label>
		</p>
		<div class="x-portlet-wrapper-label"><?php _e( 'Mediathek Collection:', XF_TEXTDOMAIN ) ?></div>
		<div class="x-portlet-wrapper <?php echo $this->get_field_id( 'connected' ) ?>">
			<input name="x-portlet-ajax-action" type="hidden" value="<?php echo $this->get_ajax_action( 'dialog' ); ?>" />
			<input name="x-portlet-ajax-width" type="hidden" value="600" />
			<div class="x-portlet-wrapper-empty"><em><?php _e( '- empty -', XF_TEXTDOMAIN ); ?></em></div>	
			<div class="x-portlet-column x-portlet-dest">
				<?php 
				$entries = count( $portlet_id );
				for ( $i = 0; $i < $entries; $i++ ){
					//TODO: better checks instead suppression
					@$this->_portlet( $portlet_id[$i], $media_caption[$i], $media_description[$i], $media_more_link_text[$i], $media_linktype[$i], $media_linkurl[$i] );
				}
				?>
			</div>
		</div>
		<div class="widget-control-actions">
			<img class="ajax-feedback" src="<?php echo get_admin_url(); ?>images/loading.gif" title="" alt=""><a title="<?php _e( 'Add Images', XF_TEXTDOMAIN ); ?>" class="x-dlg-media" rel="<?php echo $this->media_callback_id;?>" href="#"><?php _e( 'Add Images', XF_TEXTDOMAIN ); ?></a>
			&nbsp;|&nbsp;
			<a title="<?php _e( 'Remove All Images', XF_TEXTDOMAIN ); ?>" class="x-portlet-removeall" href="#"><?php _e( 'Remove All Images', XF_TEXTDOMAIN ); ?></a>			
			<p><small><em><?php _e( "Select your Slider Image content, modify Texts and linked URL's. Afterward sort it by drag 'n drop as usual.", XF_TEXTDOMAIN ); ?></em></small></p>
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

	function _portlet($id, $caption, $description, $more_link_text = '', $linktype = 'none', $linkurl = '') {
		$post = get_post($id);
		if(is_null($post)) return;
		$header = $post->post_title;
		if (empty($more_link_text)) $more_link_text = __('Read more...', XF_TEXTDOMAIN);
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
				<div class="<?php echo $this->get_field_id('show_excerpt') ?>">
				<p class="excerpt_morelink_text">
				<label><small><?php _e('Excerpt more link text:', XF_TEXTDOMAIN); ?></small></label>
				<input name="<?php echo $this->get_field_name('media_more_link_text'); ?>[]" type="text" value="<?php echo esc_html($more_link_text); ?>" class="widefat"/>
				</p>
				</div>
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
	
	function on_dlg_apply_changes( $ids ) {
		$ids = explode( ',', $ids );
		if (isset($_POST['target_input'])) {
			$a = (array)explode('-', $_POST['target_input']);
			$this->number = (int)end($a);
		}
	?>
		<div id="x-sl-temp">
		<?php 
			foreach( $ids as $id ) { 
				$post 		= get_post( $id );
				$caption 		= $post->post_excerpt;
				$description 	= $post->post_content;			
				if ( ( empty( $caption ) || empty( $description ) ) && $post->post_parent != 0 ) {
					$parent = get_post( $post->post_parent );
					if ( empty( $caption ) ) $caption = $parent->post_title;
					if ( empty( $description ) ) $description = wp_html_excerpt( $parent->post_content, 200 ).'...';
				}
				echo $this->_portlet( $id, $caption, $description ); 
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
