<?php

add_filter('xtreme-collect-widget-classes', create_function('$classes', '$classes[] = "Xtreme_Media_Carousel"; return $classes;')); 
 
class Xtreme_Media_Carousel extends Xtreme_Widget_Base {
	function __construct() {
		$widget_ops = array( 'classname' => 'xtreme_carousel', 'description' => __('A cool Media Carousel', XF_TEXTDOMAIN ) );
		parent::__construct(__FILE__, 'xtreme-media-carousel', __('Xtreme Media Carousel', XF_TEXTDOMAIN ), $widget_ops);
		$this->media_callback_id = 'carousel';
		add_action('xtreme-dlg-apply-changes_'.$this->media_callback_id, array(&$this, 'on_dlg_apply_changes'));
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

		$count = count($portlet_id);
		if ($count > 0) {
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
			
			$thumbnail_size = esc_attr( $instance['thumbnail_size'] );
			$direction = esc_attr( $instance['direction'] );
			$hide = '';
			echo $before_widget;
			if ( $title ) echo $before_title . $title . $after_title;
			echo '<ul id="x-' . $this->id . '" class="x-carousel">';
			for($i=0; $i<$count; $i++) {
				$cls = 'class="cfs-li-'.$i.'" '; 
				?><li <?php echo $cls; echo $li_style; ?>><?php
				$thumb = wp_get_attachment_image( $portlet_id[$i], $thumbnail_size, false, array( 'alt' => $title ) );
				if ($thumb): 
					switch($media_linktype[$i]) {
						case 'post':
							?><a class="<?php echo esc_attr( $instance['image_alignment'] ) ?>" href="<?php echo get_permalink($media_parentid[$i]) ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php echo esc_attr(strip_tags($media_caption[$i])); ?>"><?php 
							break;
						case 'attachment':
							?><a class="<?php echo esc_attr( $instance['image_alignment'] ) ?>" href="<?php echo get_permalink($portlet_id[$i]) ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php echo esc_attr(strip_tags($media_caption[$i])); ?>"><?php 
							break;
						default:
							?><a class="<?php echo esc_attr( $instance['image_alignment'] ) ?>" href="<?php echo esc_url( $media_linkurl[$i] ) ?>" rel="bookmark" title="<?php _e('Permanent Link to', XF_TEXTDOMAIN); ?> <?php echo esc_attr(strip_tags($media_caption[$i])); ?>"><?php 
							break;
					}
					echo $thumb; ?></a><?php 
				else:
					echo '<img src="'.XF_THEME_URI.'/images/blank.gif" alt="" />';
				endif;
				if ( $instance['show_posttitle'] || $instance['show_excerpt'] ) : ?>
						<div class="fred-content">
						<?php if ( $instance['show_posttitle'] ) : ?>
							<h2 class="posttitle"><?php echo esc_attr($media_caption[$i]) ?></h2>
						<?php endif;
						if ( $instance['show_excerpt'] ) :
							echo '<div>';
							echo $media_description[$i];
							switch($media_linktype[$i]) {
								case 'post':
									echo '<span class="read-more"><a href="' . get_permalink($media_parentid[$i]) .'" rel="bookmark" title="'. __('Permanent Link to', XF_TEXTDOMAIN) . ' ' . esc_attr(strip_tags($media_caption[$i])) .'">'.esc_html( $media_more_link_text[$i] ).'</a></span></div>';
									break;
								case 'attachment':
									echo '<span class="read-more"><a href="' . get_permalink($portlet_id[$i]) .'" rel="bookmark" title="'. __('Permanent Link to', XF_TEXTDOMAIN) . ' ' . esc_attr(strip_tags($media_caption[$i])) .'">'.esc_html( $media_more_link_text[$i] ).'</a></span></div>';
									break;
								default:
									echo '<span class="read-more"><a href="' . esc_url( $media_linkurl[$i] ) .'" rel="bookmark" title="' . esc_attr(strip_tags($media_more_link_text[$i])) .'">'.esc_html( $media_more_link_text[$i] ).'</a></span></div>';
									break;
							}
						endif; ?>
						</div>
				<?php endif; ?></li><?php
			}
			echo '</ul>';
			if ( $instance['prevnext']) {
				$html .= '<a class="prev dir-' . $direction . '" id="prev-' . $this->id . '" href="#"><span>prev</span></a>';
				$html .= '<a class="next dir-' . $direction . '" id="next-' . $this->id . '" href="#"><span>next</span></a>';
			}
			if ( $instance['pagination']) {
				$html .= '<div class="cf-pagination" id="pag-' . $this->id . '"></div>';
			}
			echo $html . $after_widget;
		}
	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['thumbnail_size'] = strip_tags($new_instance['thumbnail_size']);
		$instance['image_alignment'] = strip_tags($new_instance['image_alignment']);
		$instance['show_posttitle'] = (isset($new_instance['show_posttitle'])) ? 1 : 0;
		$instance['show_excerpt'] = (isset($new_instance['show_excerpt'])) ? 1 : 0;
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
		$instance['portlet_id'] = isset( $new_instance['portlet_id'] ) && is_array($new_instance['portlet_id']) ? $new_instance['portlet_id'] : array();
		$instance['media_parentid'] = isset( $new_instance['media_parentid'] ) && is_array($new_instance['media_parentid']) ? $new_instance['media_parentid'] : array();
		$instance['media_caption'] = isset( $new_instance['media_caption'] ) && is_array($new_instance['media_caption']) ? $new_instance['media_caption'] : array();
		if ( current_user_can('unfiltered_html') ) {
			$instance['media_description'] = isset( $new_instance['media_description'] ) && is_array($new_instance['media_description']) ? $new_instance['media_description'] : array();
		} else {
			$instance['media_description'] = isset( $new_instance['media_description'] ) && is_array($new_instance['media_description']) ? stripslashes( wp_filter_post_kses( addslashes( $new_instance['media_description'] ) ) ) : array();
		}
		$instance['media_more_link_text'] = isset( $new_instance['media_more_link_text'] ) && is_array($new_instance['media_more_link_text']) ? $new_instance['media_more_link_text'] : array();
		$instance['media_linktype'] = isset( $new_instance['media_linktype'] ) && is_array($new_instance['media_linktype']) ? $new_instance['media_linktype'] : array();
 		$instance['media_linkurl'] = isset( $new_instance['media_linkurl'] ) && is_array($new_instance['media_linkurl']) ? $new_instance['media_linkurl'] : array();
		return $instance;
	}

	function form($instance) {
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$thumbnail_size = (isset($instance['thumbnail_size'])) ? $instance['thumbnail_size'] : 'thumbnail';
		$image_alignment = isset($instance['image_alignment']) ? $instance['image_alignment'] : 'alignnone';
		$show_posttitle = isset($instance['show_posttitle']) ? $instance['show_posttitle'] : 0;
		$show_excerpt = isset($instance['show_excerpt']) ? $instance['show_excerpt'] : 0;
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
		$portlet_id = isset( $instance['portlet_id'] ) && is_array($instance['portlet_id']) ? $instance['portlet_id'] : array();
		$media_parentid = isset( $instance['media_parentid'] ) && is_array($instance['media_parentid']) ? $instance['media_parentid'] : array();
		$media_caption = isset( $instance['media_caption'] ) && is_array($instance['media_caption']) ? $instance['media_caption'] : array();
		$media_description = isset( $instance['media_description'] ) && is_array($instance['media_description']) ? $instance['media_description'] : array();
		$media_more_link_text = isset( $instance['media_more_link_text'] ) && is_array($instance['media_more_link_text']) ? $instance['media_more_link_text'] : array();
		$media_linktype = isset( $instance['media_linktype'] ) && is_array($instance['media_linktype']) ? $instance['media_linktype'] : array();
		$media_linkurl = isset( $instance['media_linkurl'] ) && is_array($instance['media_linkurl']) ? $instance['media_linkurl'] : array();
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ) ?>"><?php _e( 'Title (or leave it blank):', XF_TEXTDOMAIN ) ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ) ?>" name="<?php echo $this->get_field_name( 'title' ) ?>" type="text" value="<?php echo esc_attr( $title) ?>" />
		</p>
		<h3><?php _e( 'Image Options', XF_TEXTDOMAIN ) ?></h3>
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
		<p>
			<input id="<?php echo $this->get_field_id('show_posttitle') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_posttitle') ?>" value="1" <?php checked(1, $show_posttitle) ?>/>
			<label for="<?php echo $this->get_field_id('show_posttitle') ?>"><?php _e('Show Post Title', XF_TEXTDOMAIN) ?></label>
		</p>

		<p>
			<input class="x-switcher" id="<?php echo $this->get_field_id('show_excerpt') ?>" type="checkbox" name="<?php echo $this->get_field_name('show_excerpt') ?>" value="1" <?php checked(1, $show_excerpt) ?>/>
			<label for="<?php echo $this->get_field_id('show_excerpt') ?>"><?php _e('Show Xtreme Excerpt', XF_TEXTDOMAIN) ?></label>
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
					//TODO: better checks instead suppression
					@$this->_portlet($portlet_id[$i],$media_caption[$i],$media_description[$i],$media_more_link_text[$i], $media_linktype[$i],$media_linkurl[$i]);
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
				$post 		= get_post($id);
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
