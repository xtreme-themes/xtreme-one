<?php

class Xtreme_Dialog_Manager {
	
	function __construct() {
		
		add_action('wp_ajax_edit_widget_filter_exceptions', array(&$this, 'on_edit_widget_filter_exceptions'));
		add_action('wp_ajax_edit_widget_filter_expressions', array(&$this, 'on_edit_widget_filter_expressions'));
		add_filter('icon_dir', array(&$this, 'on_icon_dir'));
		add_filter('wp_mime_type_icon', array(&$this, 'on_xtreme_mime_type_icon'), 10, 3);
		add_action('wp_ajax_xtreme_mime_type_viewer', array(&$this, 'on_xtreme_mime_type_viewer'));

		$this->total_items 	= 0;
		$this->total_pages 	= 5;
		$this->current_page = 1;
		
		$this->selected_items = array();
		
		$this->iframe_mime_types = array(
			'application/pdf',
			'application/zip'
		);
	}

	function pagination( $which ) {
		$output = '<span class="displaying-num">' . sprintf( _n( '1 item', '%s items', $this->total_items, XF_TEXTDOMAIN ), number_format_i18n( $this->total_items ) ) . '</span>';

		$current = $this->current_page;

		$current_url = ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

		$current_url = remove_query_arg( array( 'hotkeys_highlight_last', 'hotkeys_highlight_first' ), $current_url );

		$page_links = array();

		$disable_first = $disable_last = '';
		if ( $current == 1 )
			$disable_first = ' disabled';
		if ( $current == $this->total_pages )
			$disable_last = ' disabled';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'first-page' . $disable_first,
			esc_attr__( 'Go to the first page', XF_TEXTDOMAIN ),
			esc_url( remove_query_arg( 'paged', $current_url ) ),
			'&laquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'prev-page' . $disable_first,
			esc_attr__( 'Go to the previous page', XF_TEXTDOMAIN ),
			esc_url( add_query_arg( 'paged', max( 1, $current-1 ), $current_url ) ),
			'&lsaquo;'
		);

		if ( 'bottom' == $which )
			$html_current_page = $current;
		else
			$html_current_page = sprintf( "<input class='current-page' title='%s' type='text' name='%s' value='%s' size='%d' />",
				esc_attr__( 'Current page', XF_TEXTDOMAIN ),
				esc_attr( 'paged' ),
				$current,
				strlen( $this->total_pages )
			);

		$html_total_pages = sprintf( "<span class='total-pages'>%s</span>", number_format_i18n( $this->total_pages ) );
		$page_links[] = '<span class="paging-input">' . sprintf( _x( '%1$s of %2$s', 'paging', XF_TEXTDOMAIN ), $html_current_page, $html_total_pages ) . '</span>';

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'next-page' . $disable_last,
			esc_attr__( 'Go to the next page', XF_TEXTDOMAIN ),
			esc_url( add_query_arg( 'paged', min( $this->total_pages, $current+1 ), $current_url ) ),
			'&rsaquo;'
		);

		$page_links[] = sprintf( "<a class='%s' title='%s' href='%s'>%s</a>",
			'last-page' . $disable_last,
			esc_attr__( 'Go to the last page', XF_TEXTDOMAIN ),
			esc_url( add_query_arg( 'paged', $this->total_pages, $current_url ) ),
			'&raquo;'
		);

		$output .= "\n<span class='pagination-links'>" . join( "\n", $page_links ) . '</span>';

		if ( $this->total_pages )
			$page_class = $this->total_pages < 2 ? ' one-page' : '';
		else
			$page_class = ' no-pages';
			
		echo "<div class='tablenav-pages{$page_class}'>$output</div>";

	}

	function search_box( $which, $text, $input_id ) {
		if ( $which != "top")
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
?>
<p class="search-box">
	<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
	<input type="text" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
	<?php submit_button( $text, 'button', false, false, array('id' => 'search-submit') ); ?>
</p>
<?php
	}
	
	function navigation( $which, $text, $input_id ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<div class="alignleft">
				<?php $this->search_box( $which, $text, $input_id ); ?>
			</div>
		
		<?php
		$this->pagination( $which );
		?>

			<br class="clear" />
		</div>
		<?php		
	}
	
	function __wrap_number($a) { return esc_sql($a); }
	function __wrap_text($a) { return "'".esc_sql($a)."'";	}
	
	function setup_data(){
	
		global $wpdb;
		$is_cpt = (bool)preg_match("/^x\-cpt(a|)\-/", $_POST['filter']);
		$is_tax = (bool)preg_match("/^x\-tax(a|)\-/", $_POST['filter']);
		
		$sc = "";
		$scu = "";
		
		//pre-process search
		if(isset($_POST['perform']) && $_POST['perform'] == 'search')
			$_POST['page'] = 1;
		if(isset($_POST['s']) && !empty($_POST['s'])) {
			$term = esc_sql( like_escape( $_POST['s'] ) ); 
			switch($_POST['filter']) {
				case 'is_single':
				case 'is_page':
				case 'is_attachment':
				case 'is_paged':
					$sc = " AND post_title LIKE '%$term%'";
					break;
				case 'is_category':
				case 'in_category':
				case 'is_tag':
				case 'has_tag':
				case 'is_tax':
					$sc = " AND T1.name LIKE '%$term%'";
					break;
				case 'is_author':
				case 'has_author':
					$sc = " AND user_nicename LIKE '%$term%'";
					$scu = " AND T2.user_nicename LIKE '%$term%'";
					break;
				case 'in_postformat':
					$formats = get_theme_support('post-formats');
					$formats = $formats[0];
					$sc = array();
					foreach($formats as $format) {
						if (stripos($format, $term) !== false)
							$sc[] = $format;
					}
					break;
				case 'has_role':
					global $wp_roles;
					$roles = array_keys($wp_roles->roles);
					$sc = array();
					foreach($roles as $role) {
						if (stripos($role, $term) !== false)
							$sc[] = $role;
					}
					break;
				case 'has_cap':
					global $wp_roles;
					$caps = array_keys($wp_roles->roles['administrator']['capabilities']);
					$sc = array();
					foreach($caps as $cap) {
						if (stripos($cap, $term) !== false)
							$sc[] = $cap;
					}					
					break;
				default:
					if ($is_cpt) {
						$sc = " AND post_title LIKE '%$term%'";
					}
					else if ($is_tax) {
						$sc = " AND T1.name LIKE '%$term%'";
					}
					break;
			}
		}
		
		if(isset($_POST['s_media']) && !empty($_POST['s_media']) && $_POST['s_media'] != 'all' && $_POST['filter'] == 'is_attachment' ) {
			$term = esc_sql( like_escape( $_POST['s_media'] ) );
			$sc .= " AND post_mime_type LIKE '%$term%'";
		}
		
		//reduce to images only
		if($_POST['filter'] == 'is_attachment' && isset($_POST['callback_id']) && !empty($_POST['callback_id'])) {
			$sc .= " AND post_mime_type LIKE '%image/%'";
		}
		
		//pre-process content type
		if(isset($_POST['content']) && $_POST['content'] == 'selection' && isset($_POST['value'])) {
			$v = $_POST['value'];
			if(!empty($v)) {
				switch($_POST['filter']) {
					case 'is_single':
					case 'is_page':
					case 'is_attachment':
					case 'is_paged':
						$v = implode(',',array_map(array(&$this, '__wrap_number'), explode(',', $v)));
						$sc .= " AND id IN ($v)";
						break;
					case 'is_category':
					case 'in_category':
					case 'is_tag':
					case 'has_tag':
					case 'is_tax':
						$v = implode(',',array_map(array(&$this, '__wrap_text'), explode(',', $v)));
						$sc .= " AND T1.slug IN ($v)";
						break;
					case 'is_author':
					case 'has_author':
						$sc .= " AND id IN ($v)";
						$scu .= " AND T2.id IN ($v)";
						break;
					case 'post-format':
					case 'in_postformat':
					case 'has_role':
					case 'has_cap':
						$t = empty($v) ? array() : explode(',', $v);
						if (is_array($sc))
							$sc = array_intersect($t, $sc);
						else
							$sc = $t;
						break;
						break;
					default:
						if ($is_cpt) {
							$v = implode(',',array_map(array(&$this, '__wrap_number'), explode(',', $v)));
							$sc .= " AND id IN ($v)";
						}
						else if ($is_tax) {
							$v = implode(',',array_map(array(&$this, '__wrap_text'), explode(',', $v)));
							$sc .= " AND T1.slug IN ($v)";
						}
						break;
				}
			}
		}
		
		$this->selected_items = isset($_POST['value']) && !empty($_POST['value']) ? explode(',',$_POST['value']) : array();
		switch($_POST['filter']) {
			case 'is_single':
				$this->total_items = $wpdb->get_var( "SELECT COUNT( * ) FROM $wpdb->posts WHERE post_type = 'post' AND post_status != 'trash' AND post_status != 'auto-draft'$sc" );
				break;
			case 'is_page':
			case 'is_paged':
				$this->total_items = $wpdb->get_var( "SELECT COUNT( * ) FROM $wpdb->posts WHERE post_type = 'page' AND post_status != 'trash' AND post_status != 'auto-draft'$sc" );
				break;
			case 'is_attachment':
				$this->total_items = $wpdb->get_var( "SELECT COUNT( * ) FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_status != 'auto-draft'$sc" );
				break;
			case 'is_category':
			case 'in_category':
				$this->total_items = $wpdb->get_var( "SELECT COUNT( DISTINCT T2.term_id ) FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy = 'category'$sc" );
				break;
			case 'is_tag':
			case 'has_tag':
				$this->total_items = $wpdb->get_var( "SELECT COUNT( DISTINCT T2.term_id ) FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy = 'post_tag'$sc" );
				break;
			case 'is_tax':
				$this->total_items = $wpdb->get_var( "SELECT COUNT( DISTINCT T2.term_id ) FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy NOT IN ('nav_menu', 'category', 'post_tag', 'link_category')$sc" );
				break;
			case 'is_author':
			case 'has_author':
				//ensure only user from this blog!
				//TODO: check later on the capabilities itself to exclude subscriber! 
				$capabilities = $wpdb->prefix . 'capabilities';
				$this->total_items = $wpdb->get_var( "SELECT COUNT(DISTINCT T1.user_id ) FROM $wpdb->usermeta T1, $wpdb->users T2 WHERE T2.ID = T1.user_id AND T1.meta_key = '$capabilities'$scu" );
				break;
			case 'post-format':
			case 'in_postformat':
				$formats = get_theme_support('post-formats');
				$formats = $formats[0];
				if (!is_array($sc)) {
					$this->total_items = count($formats);
				}else{
					$this->total_items = count($sc);
				}
				break;
			case 'has_role':
				global $wp_roles;
				$this->total_items = count(array_keys($wp_roles->roles));
				if (is_array($sc)) 
					$this->total_items = count($sc);					
				break;
			case 'has_cap':
				global $wp_roles;
				$this->total_items = count(array_keys($wp_roles->roles['administrator']['capabilities']));
				if (is_array($sc)) 
					$this->total_items = count($sc);					
				break;
			default:
				if ($is_cpt) {
					$posttype = esc_sql(preg_replace("/^x-cpt(a|)-/", '', $_POST['filter']));
					$this->total_items = $wpdb->get_var( "SELECT COUNT( * ) FROM $wpdb->posts WHERE post_type = '$posttype' AND post_status != 'trash' AND post_status != 'auto-draft'$sc" );
				}
				else if ($is_tax) {
					$taxonomy = esc_sql(preg_replace("/^x-tax(a|)-/", '', $_POST['filter']));
					$this->total_items = $wpdb->get_var( "SELECT COUNT( DISTINCT T2.term_id ) FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy = '$taxonomy'$sc" );
				}
				break;
		}
		
		//calculate correct pagination
		$ec = 8;
		$this->total_pages = max(1, (int)ceil($this->total_items / $ec));
		$this->current_page = isset($_POST['page']) ? max(1, min((int)$_POST['page'],$this->total_pages))  : 1;
		switch($_POST['perform']) {
			case "first-page":
				$this->current_page = 1;
				break;
			case "prev-page":
				$this->current_page = max(0, $this->current_page -1 );
				break;
			case "next-page":
				$this->current_page = min($this->current_page +1, $this->total_pages);
				break;
			case "last-page":
				$this->current_page = $this->total_pages;
				break;
		}
		$es = ($this->current_page - 1) * $ec;
		
		$this->results = array();		
		//now lookup data itself
		switch($_POST['filter']) {
			case 'is_single':
				$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type = 'post' AND post_status != 'trash' AND post_status != 'auto-draft'$sc LIMIT $es,$ec" );
				foreach($result as $post) {
					$this->results[] = array(
						"id" => $post->ID,
						"title" => $post->post_title,
						"extra" => implode(' ', array_slice(explode(' ', strip_tags(strip_shortcodes($post->post_content))), 0, 20)).'...'
					);
				}
				break;
			case 'is_page':
			case 'is_paged':
				$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type = 'page' AND post_status != 'trash' AND post_status != 'auto-draft'$sc LIMIT $es,$ec" );
				foreach($result as $post) {
					$this->results[] = array(
						"id" => $post->ID,
						"title" => $post->post_title,
						"extra" => implode(' ', array_slice(explode(' ', strip_tags(strip_shortcodes($post->post_content))), 0, 20)).'...'
					);
				}
				break;
			case 'is_attachment':
				$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type = 'attachment' AND post_status != 'trash' AND post_status != 'auto-draft'$sc LIMIT $es,$ec" );
				//support special files by internal viewer
				foreach($result as $post) {
					if (in_array($post->post_mime_type, $this->iframe_mime_types)) {
						$this->results[] = array(
							"id" => $post->ID,
							"title" => $post->post_title,							
							"extra" => '<a href="'.get_admin_url().'admin-ajax.php?action=xtreme_mime_type_viewer&amp;mime_type='.$post->post_mime_type.'&amp;url='.wp_get_attachment_url($post->ID).'&amp;TB_iframe=true" class="thickbox" alt="'. esc_html($post->post_title).'" title="'.esc_html($post->post_title).'">'
										.wp_get_attachment_image( $post->ID, array( 80, 60 ), true ) 
						);
					}
					else{
						$this->results[] = array(
							"id" => $post->ID,
							"title" => $post->post_title,
							"extra" => '<a href="'.wp_get_attachment_url($post->ID).'" class="thickbox" alt="'. esc_html($post->post_title).'" title="'.esc_html($post->post_title).'">'
										.wp_get_attachment_image( $post->ID, array( 80, 60 ), true ) 
						);
					}
				}
				break;
			case 'is_category':
			case 'in_category':
				$result = $wpdb->get_results( "SELECT T1.term_id, T1.slug, T1.name, T2.description FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy = 'category' AND T1.term_id = T2.term_id$sc LIMIT $es,$ec" );
				foreach($result as $post) {
					$this->results[] = array(
						"id" => $post->slug,
						"title" => rtrim(get_category_parents($post->term_id, false, ' | '), ' | '),
						"extra" => $post->description
					);
				}				
				break;
			case 'is_tag':
			case 'has_tag':
				$result = $wpdb->get_results( "SELECT T1.slug, T1.name, T2.description FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy = 'post_tag' AND T1.term_id = T2.term_id$sc LIMIT $es,$ec" );
				foreach($result as $post) {
					$this->results[] = array(
						"id" => $post->slug,
						"title" => $post->name,
						"extra" => $post->description
					);
				}				
				break;
			case 'is_tax':
				$result = $wpdb->get_results( "SELECT T1.slug, T1.name, T2.description FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy NOT IN ('nav_menu', 'category', 'post_tag', 'link_category') AND T1.term_id = T2.term_id$sc LIMIT $es,$ec" );
				foreach($result as $post) {
					$this->results[] = array(
						"id" => $post->slug,
						"title" => $post->name,
						"extra" => $post->description
					);
				}				
				break;
			case 'is_author':
			case 'has_author':
				//ensure only user from this blog!
				//TODO: check later on the capabilities itself to exclude subscriber! 
				$capabilities = $wpdb->prefix . 'capabilities';
				$result = $wpdb->get_results( "SELECT * FROM $wpdb->users WHERE ID in (SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key = '$capabilities')$sc LIMIT $es,$ec" );
				foreach($result as $post) {
					$this->results[] = array(
						"id" => $post->ID,
						"title" => $post->user_nicename,
						"extra" => $post->user_email
					);
				}				
				break;
			case 'post-format':
			case 'in_postformat':
				$formats = get_theme_support('post-formats');
				$formats = is_array($formats[0]) ? $formats[0] : array();
				if (!is_array($sc)) {
					$result = $formats;
				}else{
					$result = $sc;
				}
				$result = array_slice($result,$es,$ec);
				foreach($result as $format) {
					$this->results[] = array(
						"id" => $format,
						"title" => get_post_format_string($format),
						"extra" => ''
					);
				}				
				break;
			case 'has_role':
				global $wp_roles;
				$slugs = array_keys($wp_roles->roles);
				if (!is_array($sc)) {
					$result = $slugs;
				}else{
					$result = $sc;
				}
				$result = array_slice($result,$es,$ec);				
				foreach($result as $slug) {
					$this->results[] = array(
						"id" => $slug,
						"title" => $wp_roles->roles[$slug]['name'],
						"extra" => ''
					);
				}
				break;
			case 'has_cap':
				global $wp_roles;
				$caps = array_keys($wp_roles->roles['administrator']['capabilities']);
				sort($caps);
				if (!is_array($sc)) {
					$result = $caps;
				}else{
					$result = $sc;
				}
				$result = array_slice($result,$es,$ec);				
				foreach($result as $slug) {
					if(is_array($sc) && !in_array($slug, $sc)) continue;
					$this->results[] = array(
						"id" => $slug,
						"title" => $slug,
						"extra" => ''
					);
				}
				break;
			default:
				if ($is_cpt) {
					$posttype = esc_sql(preg_replace("/^x-cpt(a|)-/", '', $_POST['filter']));
					$result = $wpdb->get_results( "SELECT * FROM $wpdb->posts WHERE post_type = '$posttype' AND post_status != 'trash' AND post_status != 'auto-draft'$sc LIMIT $es,$ec" );
					foreach($result as $post) {
						$this->results[] = array(
							"id" => $post->ID,
							"title" => $post->post_title,
							"extra" => implode(' ', array_slice(explode(' ', apply_filters('wp_trim_excerpt', strip_tags($post->post_content))), 0, 20)).'...'
						);
					}
				}
				else if ($is_tax) {
					$taxonomy = esc_sql(preg_replace("/^x-tax(a|)-/", '', $_POST['filter']));
					$this->total_items = $wpdb->get_var( "SELECT COUNT( DISTINCT T2.term_id ) FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy = '$taxonomy'$sc" );
					$result = $wpdb->get_results( "SELECT T1.slug, T1.name, T2.description FROM $wpdb->terms T1, $wpdb->term_taxonomy T2 WHERE T1.term_id = T2.term_id AND T2.taxonomy  = '$taxonomy' AND T1.term_id = T2.term_id$sc LIMIT $es,$ec" );
					foreach($result as $post) {
						$this->results[] = array(
							"id" => $post->slug,
							"title" => $post->name,
							"extra" => $post->description
						);
					}				
				}
				break;
		}
		
	}
	
	function on_edit_widget_filter_exceptions() {
		global $xtreme_widget_manager;
		if ( !current_user_can('edit_theme_options') && !$xtreme_widget_manager->current_user_has_right() ) {
			_e("You do not have the permission to change widget filter exceptions.",XF_TEXTDOMAIN);
			?>
			<div class="widget-control-actions">
				<a id="x-portlet-editing-close" href="#"><span class="ui-icon ui-icon-close"></span><?php _e('Close', XF_TEXTDOMAIN); ?></a>
			</div>
			<?php
			exit();
		}
		$filter = $_POST["filter"];
		if(!isset($_POST['content']) || empty($_POST['content'])) $_POST['content'] = "standard";
		if (in_array($filter, $xtreme_widget_manager->filters_with_exceptions)) {
			$filtername = $xtreme_widget_manager->filters[$filter];		
			?>
			<div class="x-portlet-wrapper-label"><?php _e( 'Edit Filter Exceptions for:', XF_TEXTDOMAIN ); ?> <strong><?php echo esc_attr($filtername); ?></strong></div>
			<?php if(is_rtl()) : ?>
				<image src="<?php echo XF_ADMIN_URL; ?>/images/filter-arrow-rtl.png" class="x-filter-arrow" />
			<?php else : ?>
				<image src="<?php echo XF_ADMIN_URL; ?>/images/filter-arrow.png" class="x-filter-arrow" />
			<?php endif; ?>
			<div class="x-portlet-wrapper-label">
				<small><?php _e( 'Sidebar:', XF_TEXTDOMAIN ); ?> <strong><?php echo esc_attr($_POST['sidebar']); ?></strong>&nbsp;|&nbsp;
				<?php _e( 'Widget:', XF_TEXTDOMAIN ); ?> <strong><?php echo esc_attr($_POST['widget']); ?></strong></small>
			</div>
			<div class="x-portlet-wrapper">
			<?php
			$this->setup_data();			
			$this->navigation("top", __('Search', XF_TEXTDOMAIN), "xtreme-dialog");
			if ($filter == 'is_attachment') {
				echo '<div class="x-media-types"><b>'.__('Media Types', XF_TEXTDOMAIN).':</b>&nbsp;';
				$mt = get_available_post_mime_types('attachment');
				$ma = array(
					'<a href="#" class="x-media-type'.($_POST['s_media'] == 'all' ? ' x-media-type-active' : '').'" rel="all">'.__('All', XF_TEXTDOMAIN).'</a>'					
				);
				if (isset($_POST['callback_id']) && !empty($_POST['callback_id'])) {
					$ma[] = '<a href="#" class="x-media-type'.($_POST['s_media'] == 'image/' ? ' x-media-type-active' : '').'" rel="image/">'.__('Images', XF_TEXTDOMAIN).'</a>';
				}
				foreach($mt as $m) {
					$p = explode('/', $m);
					if (isset($_POST['callback_id']) && !empty($_POST['callback_id']) && !preg_match('|^image/|', $m)) continue;
					$ma[] = '<a href="#" class="x-media-type'.($_POST['s_media'] == $m ? ' x-media-type-active' : '').'" rel="'.$m.'">'.$p[1].'</a>';
				}
				echo implode('', $ma);
				echo "</div>";
			}
			?>
			<table cellspacing="0" class="widefat fixed">
			<thead>
				<tr>	
					<th class="column-cb check-column" scope="col"><input class="fex-maintoggle" type="checkbox"></th>
					<th class="column-title" scope="col"><?php _e("Title", XF_TEXTDOMAIN); ?></th>
					<?php if($filter == 'is_attachment') : ?>
					<th class="column-icon" scope="col"><?php _e("Media Link", XF_TEXTDOMAIN); ?></th>
					<?php else: ?>
					<th class="column-parentcolumn-title" scope="col"><?php _e("Excerpt", XF_TEXTDOMAIN); ?></th>
					<?php endif; ?>
				</tr>
			</thead>
			<tbody>
			<?php foreach($this->results as $data) : 
				$class = in_array($data['id'], $this->selected_items) ? ' class="fex-selected"' : '';
				$checked = in_array($data['id'], $this->selected_items) ? ' checked="checked"' : '';
			?>
				<tr>
					<td<?php echo $class;?>><input class="fex-checkbox" type="checkbox" value="<?php echo $data['id']; ?>"<?php echo $checked;?>></td>
					<td<?php echo $class;?>>
						<strong><?php echo $data['title']; ?></strong>
					</td>
					<td<?php echo $class;?>><?php echo $data['extra']; ?></td>
				</tr>
			<?php endforeach; ?>
			</tbody>
			</table>
			<?php

			$this->navigation("bottom", __('Search', XF_TEXTDOMAIN), "xtreme-dialog");
			?>
			</div>
			<div class="widget-control-actions">
				<span id="x-portlet-editing-selected">&nbsp;|&nbsp;<?php _e('Selected Items:', XF_TEXTDOMAIN); ?> <b><?php echo count($this->selected_items); ?></b></span>
				<a id="x-portlet-editing-assigned" class="dlg-selection<?php echo ($_POST['content'] != "standard" ? " hidden" : ""); ?>" href="#"><span class="ui-icon ui-icon-zoomin"></span><?php _e('Show Selection', XF_TEXTDOMAIN); ?></a>
				<a id="x-portlet-editing-standard" class="dlg-selection<?php echo ($_POST['content'] == "standard" ? " hidden" : ""); ?>" href="#"><span class="ui-icon ui-icon-zoomout"></span><?php _e('Show Overview', XF_TEXTDOMAIN); ?></a>
				<?php if($_POST['dlg_type'] == 'x-dlg-exceptions'): ?>
					<a id="x-portlet-editing-close" href="#"><span class="ui-icon ui-icon-close"></span><?php _e('Close', XF_TEXTDOMAIN); ?></a>
					<a id="x-portlet-editing-apply" href="#"><span class="ui-icon ui-icon-disk"></span><?php _e('Apply', XF_TEXTDOMAIN); ?></a>
				<?php else : ?>
					<a id="x-portlet-editing-back" href="#"><span class="ui-icon ui-icon-arrowrefresh-1-w"></span><?php _e('Back to Condition Editor', XF_TEXTDOMAIN); ?></a>
					<a id="x-portlet-editing-apply" href="#"><span class="ui-icon ui-icon-disk"></span><?php _e('Apply', XF_TEXTDOMAIN); ?></a>
				<?php endif; ?>
			</div>
			<script type="text/javascript">
			xtreme_dialog_data = {
				'action' 		: '<?php echo $_POST['action']; ?>',
				'dlg_type'		: '<?php echo $_POST['dlg_type']; ?>',
				'filter' 		: '<?php echo $_POST['filter']; ?>',
				'widget'		: '<?php echo $_POST['widget']; ?>',
				'sidebar'		: '<?php echo $_POST['sidebar']; ?>',
				'target_input'	: '<?php echo $_POST['target_input']; ?>',
				'value'			: '<?php echo $_POST['value'] ?>',
				's'				: '<?php echo $_POST['s']; ?>',
				's_media'		: '<?php echo $_POST['s_media']; ?>',
				'page'			: <?php echo $this->current_page; ?>,
				'content'		: '<?php echo $_POST['content']; ?>',
				'perform'		: '',
				'callback_id'	: '<?php echo $_POST['callback_id']; ?>'
			};
			<?php if ($_POST['perform'] == 'apply-changes') : ?>				
				<?php 
					if (!empty($_POST['callback_id'])) : 
					?>
					</script>
					<?php do_action('xtreme-dlg-apply-changes_'.$_POST['callback_id'], $_POST['value']); ?>
					<script type="text/javascript">
					jQuery('#x-portlet-editing-close').trigger('click'); //close it now.
				<?php else : ?>
					jQuery('input[name="<?php echo $_POST['target_input']; ?>"]').val(xtreme_dialog_data.value);
					var cnt = 0;
					if(xtreme_dialog_data.value.length != 0) {
						cnt = xtreme_dialog_data.value.split(',').length;
					}
					<?php if($_POST['dlg_type'] == 'x-dlg-exceptions'): ?>
						jQuery('input[name="<?php echo $_POST['target_input']; ?>"]').closest('.xtreme-fex').find('input').each(function(i, e) {
							if (jQuery(this).val().length > 0) 
								cnt++;
						});
						if(cnt == 0)
							jQuery('input[name="<?php echo $_POST['target_input']; ?>"]').closest('.x-filter').removeClass("fex-selected");
						else
							jQuery('input[name="<?php echo $_POST['target_input']; ?>"]').closest('.x-filter').addClass("fex-selected");
						xtreme_dialog_data.value = '';
						jQuery('#x-portlet-editing-close').trigger('click'); //close it now.
						jQuery('body').trigger('visualize_filter_state');
					<?php else: ?>
						jQuery('input[name="<?php echo $_POST['target_input']; ?>"]').closest('li').find('b').html(cnt);
						jQuery('#x-portlet-editing-back').trigger('click');
					<?php endif; ?>
				<?php endif; ?>
			<?php endif; ?>
			</script>
			<?php
			
		}
		exit();
	}
	
	function __print_term($term, $data='') {
		$text = '';
		$input = true;
		$class = 'condition';
		$cnt = empty($data) ? 0 : count(explode(',', $data));
		switch ($term) {
			case 'enter':
				$text = '(';
				$input = false;
				$class = '';
				break;
			case 'leave':
				$text = ')';
				$input = false;
				$class = '';
				break;
			case 'and':
				$text = _x('AND', 'condition filters', XF_TEXTDOMAIN);
				$input = false;
				$class = '';
				break;
			case 'or':
				$text = _x('OR', 'condition filters', XF_TEXTDOMAIN);
				$input = false;
				$class = '';
				break;
			case 'not':
				$text = _x('NOT', 'condition filters', XF_TEXTDOMAIN);
				$input = false;
				$class = '';
				break;
			case 'is_paged':
				$text = _x('is paged', 'condition filters', XF_TEXTDOMAIN);
				$input = false;
				break;
			case 'is_preview':
				$text = _x('is preview', 'condition filters', XF_TEXTDOMAIN);
				$input = false;
				break;
			case 'in_postformat':
				$text = _x('in post formats', 'condition filters', XF_TEXTDOMAIN);
				break;
			case 'in_category':
				$text = _x('in categories', 'condition filters', XF_TEXTDOMAIN);
				break;
			case 'has_tag':
				$text = _x('in tags', 'condition filters', XF_TEXTDOMAIN);
				break;
			case 'has_author':
				$text = _x('in authors', 'condition filters', XF_TEXTDOMAIN);
				break;				
			case 'is_tax': //taxonomies later
				return;
				$text = _x('in taxonomies...', 'condition filters', XF_TEXTDOMAIN);
				break;
			case 'is_logged_in':
				$text = _x('is logged in', 'condition filters', XF_TEXTDOMAIN);
				$input = false;
				break;
			case 'has_role':
				$text = _x('has roles', 'condition filters', XF_TEXTDOMAIN);
				break;
			case 'has_cap':
				$text = _x('has caps', 'condition filters', XF_TEXTDOMAIN);
				break;
			default:
				break;
		}
		?>
		<li term="<?php echo $term;?>"><span class="<?php echo $class; ?>"><?php echo $text; ?></span>
			<a href="#" class="del ui-icon ui-icon-trash">&nbsp;</a>
			<?php if($input) : ?>
			<small><a href="#" class="edit ui-icon ui-icon-pencil">&nbsp;</a><span><?php _e("Items:", XF_TEXTDOMAIN); ?> <b><?php echo $cnt; ?></b></span></small>
			<input type="hidden" value="<?php echo $data; ?>" />
			<?php endif; ?>
		</li>
		<?php
	}
	
	function on_edit_widget_filter_expressions() {
		global $xtreme_widget_manager;
		if ( !current_user_can('edit_theme_options') && !$xtreme_widget_manager->current_user_has_right() ) {
			_e("You do not have the permission to change widget filters expressions.",XF_TEXTDOMAIN);
			?>
			<div class="widget-control-actions">
				<a id="x-portlet-editing-close" href="#"><span class="ui-icon ui-icon-close"></span><?php _e('Close', XF_TEXTDOMAIN); ?></a>
			</div>
			<?php
			exit();
		}
		$filter = $_POST["filter"];
		$filtername = $xtreme_widget_manager->filters[$filter];
		?>
		<div class="x-portlet-wrapper-label"><?php _e( 'Edit Filter Condition for:', XF_TEXTDOMAIN ); ?> <strong><?php echo esc_attr($filtername); ?></strong></div>
		<?php if(is_rtl()) : ?>
			<image src="<?php echo XF_ADMIN_URL; ?>/images/filter-arrow-rtl.png" class="x-filter-arrow" />
		<?php else : ?>
			<image src="<?php echo XF_ADMIN_URL; ?>/images/filter-arrow.png" class="x-filter-arrow" />
		<?php endif; ?>
		<div class="x-portlet-wrapper-label">
			<small><?php _e( 'Sidebar:', XF_TEXTDOMAIN ); ?> <strong><?php echo esc_attr($_POST['sidebar']); ?></strong>&nbsp;|&nbsp;
			<?php _e( 'Widget:', XF_TEXTDOMAIN ); ?> <strong><?php echo esc_attr($_POST['widget']); ?></strong></small>
		</div>
		<div class="x-portlet-wrapper">
			<span><?php _e("logical operators / brackets and simple terms", XF_TEXTDOMAIN); ?></span>
			<ul class="x-meta-filter provider">
				<?php 
				$this->__print_term('enter');
				$this->__print_term('leave');
				$this->__print_term('and');
				$this->__print_term('or');
				$this->__print_term('not');
				$this->__print_term('is_paged');
				$this->__print_term('is_preview');
				?>
			</ul>
			<br/>
			<span><?php _e("editable subset selector conditions", XF_TEXTDOMAIN); ?></span>
			<ul class="x-meta-filter provider">
				<?php 
				$this->__print_term('in_postformat');
				$this->__print_term('in_category');
				$this->__print_term('has_tag');
				$this->__print_term('has_author');
				//$this->__print_term('is_tax');
				?>
			</ul>
			<br/>
			<span><?php _e("authentication related conditions", XF_TEXTDOMAIN); ?></span>
			<ul class="x-meta-filter provider">
				<?php 
				$this->__print_term('is_logged_in');
				$this->__print_term('has_role');
				$this->__print_term('has_cap');
				?>
			</ul>
			<h4><?php _e("Advanced Condition Editor", XF_TEXTDOMAIN); ?><br/><small>(<?php _e("use drag & drop to build your condition", XF_TEXTDOMAIN); ?>)</small></h4>
			<div id="x-expression-editor">
				<ul class="x-meta-filter consumer">
					<?php 
						$value = $_POST['value'];
						if (!empty($value)) {
							$terms = explode('|', $value);
							foreach($terms as $term) {
								list($t, $v) = explode(':', $term);
								$this->__print_term($t, $v); 
							}
							
						}
					?>
				</ul>
			</div>
		</div>
		<div class="widget-control-actions">
			<a id="x-expression-editor-reset" href="#"><span class="ui-icon ui-icon-trash"></span><?php _e('Reset Expression', XF_TEXTDOMAIN); ?></a>
			<a id="x-portlet-editing-close" href="#"><span class="ui-icon ui-icon-close"></span><?php _e('Close', XF_TEXTDOMAIN); ?></a>
			<a id="x-expression-editor-apply" href="#"><span class="ui-icon ui-icon-disk"></span><?php _e('Apply', XF_TEXTDOMAIN); ?></a>
		</div>
		<script type="text/javascript">
		(function($) {
			
			//TODO: patch here the extreme syntax definition
			
			//define real target
			xtreme_condition_target = 'input[name="<?php echo $_POST['target_input']; ?>"]';
			
			$("ul.provider li").draggable({
				connectToSortable: "ul.consumer",
				helper: "clone",
				revert: "invalid"
			});
			$("ul.consumer").sortable({
				placeholder: "x-meta-placeholder",
				update: function(event, ui) {
					$('#x-portlet-expression').trigger('check-syntax');
				}
			}).disableSelection();
			
		
		})(jQuery);
		</script>
		<?php
		exit();
	}
	
	function on_icon_dir($dir) {
		return XF_THEME_DIR.'/admin/images/mime-types';
	}
	
	function on_xtreme_mime_type_icon($icon, $mime, $post_id) {
		if ($mime == 'application/pdf') {
			$icon = XF_THEME_URI . '/admin/images/mime-types/pdf.jpg';
		}
		return $icon;
	}
	
	function on_xtreme_mime_type_viewer() {
		?>
		<html>
			<head><title>Mime Type based Viewer</title></head>
			<body>
		<?php
		switch($_GET['mime_type']) {
			case 'application/pdf':			
					?><embed type="<?php echo $_GET['mime_type']; ?>" src="<?php echo $_GET['url']; ?>" width="100%" height="100%" style="border: solid 1px gray;" /><?php
					break;
			case 'application/zip': 
				?>
				<p><b>Download:</b> <a style="color: #21759B;text-decoration: none;" href="<?php echo $_GET['url'] ?>"><?php echo $_GET['url'] ?></a><br/>
				<b>Filesize:</b> <?php 
					$f = str_replace(network_site_url(), '', $_GET['url']);
					echo size_format(filesize(ABSPATH . $f), 1);
					echo '</p><div style="width:auto;height:300px;overflow:auto;border: solid 1px #cdcdcd;padding: 5px;background-color: #eee;"><small>';
					if (function_exists('zip_open')) {
						$root = '.';
						$zip = zip_open(ABSPATH . $f);
						while($zip_entry = zip_read($zip)):
							$zip_name = zip_entry_name($zip_entry);
							$zip_size = zip_entry_filesize($zip_entry);
							if ($zip_size != 0) {
								echo $root . '/'.$zip_name. '&nbsp;&nbsp;&nbsp;<em style="color:#a03344">('.size_format(zip_entry_filesize($zip_entry),1).')</em><br/>';
							}
							else {
								echo '<b>'.$root . '/'.$zip_name. '</b><br/>';
							}
						endwhile;	
						zip_close($zip);
					}
					echo '</small></div>';
				?>
				<?php
				break;
			default:
				echo "<p>Sorry, Xtreme Viewer does currently not support this mime type.</p>";
				break;
		} ?>
			</body>
		</html>
		<?php
		exit();
	}
}

$xtreme_dialog_manager = new Xtreme_Dialog_Manager();
