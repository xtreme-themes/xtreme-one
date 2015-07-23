<?php 

class Xtreme_Subtitle_Management {

	function __construct() {		
		add_action('init', array(&$this, 'on_init'), 100);
	}
	
	function on_init() {
		$support = get_theme_support( 'xtreme-subtitles' );
		$this->support = array_intersect(get_post_types(), (array)$support[0]);
		$actions = array();
		foreach($this->support as $posttype) {
			switch($posttype) {
				case 'post':
					if (!isset($actions['edit_form_advanced'])) $actions['edit_form_advanced'] = array(&$this, 'on_subtitle_input');
					break;
				case 'page':
					if (!isset($actions['edit_page_form'])) $actions['edit_page_form'] = array(&$this, 'on_subtitle_input');
					break;
				case 'attachment':
				case 'revision':
				case 'nav_menu_item':
					//nothing
					break;
				default: 
					//custom post type
					if (!isset($actions['edit_form_advanced'])) $actions['edit_form_advanced'] = array(&$this, 'on_subtitle_input');
					break;
			}
		}
		foreach($actions as $name => $func) {
			add_action($name, $func);
		}
		if (count($this->support) !== 0) {
			add_action('save_post', array(&$this, 'on_save_subtitle'));
		}
	}
	
	function on_subtitle_input() {
		global $post;
		if (!in_array($post->post_type, $this->support)) return;
		?>
		<div id="xtreme_subtitle">
			<table class="fixed"><tr><td>
			<div style="padding: 15px 0;"><strong><?php _e('Subtitle:',XF_TEXTDOMAIN);?></strong></div>
			</td><td width="100%">
			<input type="text" name="_subtitle" value="<?php echo get_post_meta($post->ID, "_subtitle", true);?>" style="width:100%" />
			</td></tr></table><br/>
		</div>
		<script type="text/javascript">
		(function($) {
            $( document ).ready( function() {
				$('#xtreme_subtitle').insertBefore('.postarea:first');
            } );
		 })(jQuery);
		</script>
		<?php
	}
	
	/**
	 * Save subtitle of post in meta data
	 * 
	 * @version  09/26/2013
	 * @param    Integer 	 $post_id  Id of post
	 * @return   void
	 */
	function on_save_subtitle( $post_id ) {

		if ( isset( $_POST['_subtitle'] ) ) {
			//save data
			if ( empty( $_POST['_subtitle'] ) )
				delete_post_meta( $post_id, '_subtitle' );
			else
				update_post_meta( $post_id, '_subtitle', strip_tags( $_POST['_subtitle'] ) );
		}
	}
}

$xtreme_subtitles = new Xtreme_Subtitle_Management();
 
//default functions
function the_subtitle($before = '', $after = '', $echo = true) {
	$title = get_the_subtitle();

	if ( strlen($title) == 0 )
		return;

	$title = $before . $title . $after;

	if ( $echo )
		echo $title;
	else
		return $title;
}

function get_the_subtitle( $id = null ) {
	
	$post = get_post($id);
	$id = isset($post->ID) ? $post->ID : (int) $id;
	$title = get_post_meta($id, "_subtitle", true);

	if ( !is_admin() ) {
		if ( !empty($post->post_password) ) {
			$protected_title_format = apply_filters('protected_title_format', __('Protected: %s', XF_TEXTDOMAIN));
			$title = sprintf($protected_title_format, $title);
		} else if ( isset($post->post_status) && 'private' == $post->post_status ) {
			$private_title_format = apply_filters('private_title_format', __('Private: %s', XF_TEXTDOMAIN));
			$title = sprintf($private_title_format, $title);
		}
	}
	return apply_filters( 'the_title', $title, $id );
}

function has_subtitle( $id = 0 ) {
	$sb = get_the_subtitle($id);
	return !empty($sb);
}
