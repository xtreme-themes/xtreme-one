<?php

class xc_general extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('General Settings', XF_TEXTDOMAIN));
		
		$measures = $this->owner->get_measures();
		$this->options[] = new xtreme_option_bool($this, false, 'responsive', __('Responsive Design:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'html5', __('Output HTML5:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_numeric($this, false, 'bodyfontsize', __('Body Fontsize:', XF_TEXTDOMAIN), 75, '%', $measures, false, array('%' => array(62.5, 68.75, 75.0, 81.25, 87.5, 100.0))) ;
		$this->options[] = new xtreme_option_select_pair($this, false, 'layout', __('Layout:', XF_TEXTDOMAIN), 1, array(__('fixed', XF_TEXTDOMAIN),__('flexible', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_numeric($this, false, 'minwidth', __('Minimum Layout Width:', XF_TEXTDOMAIN), 740, 'px', $measures, array('px' => array(100,2000)), false, false );
		$this->options[] = new xtreme_option_numeric($this, false, 'maxwidth', __('Maximum Layout Width:', XF_TEXTDOMAIN), 80, 'em', $measures, array('em' => array(20,200), '%' => array(20,100)), false, false);
		$this->options[] = new xtreme_option_numeric($this, false, 'width', __('Layout Width:', XF_TEXTDOMAIN), 960, 'px', $measures, array('px' => array(100,2000)), false, false );
		
		$this->options[] = new xtreme_option_select_pair($this, false, 'layoutalign', __('Alignment:', XF_TEXTDOMAIN), 1, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		
		$this->options[] = new xtreme_option_select_pair($this, false, 'appearance', __('Design:', XF_TEXTDOMAIN), 0, array(__('blog layout', XF_TEXTDOMAIN),__('full page layout', XF_TEXTDOMAIN)));

		if (current_theme_supports('xtreme-color-styles')) {
			$support = get_theme_support('xtreme-color-styles');
			$colors = $support[0];
			$color_keys = array_keys( $colors );

			$this->options[] = new xtreme_option_select_pair($this, false, 'color_styles', __('Color Style:', XF_TEXTDOMAIN), $color_keys[0], $colors);
		}else{
			$this->options[] = new xtreme_option_hidden($this, false, 'color_styles', '', '');
		}

		/*
		$this->options[] = new xtreme_option_select_pair($this, false, 'post_socials', __('Social Bookmark Support on Posts:', XF_TEXTDOMAIN), 'none', array('disabled' => __('disabled', XF_TEXTDOMAIN), 'none' => __('enabled, but not predefined', XF_TEXTDOMAIN), 'option' => __('enabled, predefined globally', XF_TEXTDOMAIN), 'postmeta' => __('enabled, predefined post specific', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'post_socials_layout', __('Social Bookmark Layout Placement:', XF_TEXTDOMAIN), 'xtreme_after_single_post', array('xtreme_before_single_post' => __('before the post', XF_TEXTDOMAIN), 'xtreme_after_single_post' => __('after the post', XF_TEXTDOMAIN), 'xtreme_authorbox_inside_vcard' => __('inside author box', XF_TEXTDOMAIN), 'xtreme_after_comments_template' => __('after comments', XF_TEXTDOMAIN), 'xtreme_social_template_tag' =>__('manual template tag', XF_TEXTDOMAIN) ));
		*/

		$this->options[] = new xtreme_option_bool($this, false, 'aria_required', __('Activate WAI Aria Roles:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'nav_vlist', __('Include YAMLs nav_vlist.css:', XF_TEXTDOMAIN), false);
		
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<em>'. __('If you switch the content position, make sure to check your settings in the layout manager for every layout.', XF_TEXTDOMAIN).'</em>');
		$this->options[] = new xtreme_option_bool($this, false, 'layout_2', __('Generate Markup with Content at first Position:', XF_TEXTDOMAIN), false);

	}
}

class xc_layout extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Default Layout Settings', XF_TEXTDOMAIN));
		
		$measures = $this->owner->get_measures();
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<em>'. __('You can overrule these settings for each template file under Xtreme Layouts.', XF_TEXTDOMAIN).'</em>');
		$this->options[] = new xtreme_option_select_pair($this, false, 'columnlayout', __('Layout:', XF_TEXTDOMAIN), 1, array(__('1 column', XF_TEXTDOMAIN), __('2 columns - right sidebar', XF_TEXTDOMAIN), __('2 columns - left sidebar', XF_TEXTDOMAIN), __('3 columns - left and right sidebars', XF_TEXTDOMAIN), __('3 columns - 2 right sidebars', XF_TEXTDOMAIN),__('3 columns - 2 left sidebars', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_description($this, false, 'col2tip', '<em>'. __('In this case width of Sidebar 1 and width of Sidebar 2 must have the same units!', XF_TEXTDOMAIN).'</em>');
		//altes Layout
		$this->options[] = new xtreme_option_numeric($this, false, 'col1width', __('Sidebar 1 Width:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(80,500), 'em' => array(5,50), '%' => array(5,50)), false, false);
		$this->options[] = new xtreme_option_numeric($this, false, 'col2width', __('Sidebar 2 Width:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(80,500), 'em' => array(5,50), '%' => array(5,50)), false, false);
		//neues Layout
		$this->options[] = new xtreme_option_numeric($this, false, 'layout_2_col1width', __('Content Width:', XF_TEXTDOMAIN), 75, '%', $measures, array('px' => array(100,1600), '%' => array(5,100)), false, false);
		$this->options[] = new xtreme_option_numeric($this, false, 'layout_2_col2width', __('Sidebar 2 Width:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(80,500), '%' => array(5,50)), false, false);
		//altes Layout
		$this->options[] = new xtreme_option_locate_files($this, false, 'col1content', __('File Sidebar 1:', XF_TEXTDOMAIN), 'sidebar-one', '/includes/sidebars/', 'php', false);
		$this->options[] = new xtreme_option_locate_files($this, false, 'col2content', __('File Sidebar 2:', XF_TEXTDOMAIN), 'sidebar-two', '/includes/sidebars/', 'php', false);
		//neues Layout
		$this->options[] = new xtreme_option_locate_files($this, false, 'layout_2_col3content', __('File Sidebar 1:', XF_TEXTDOMAIN), 'sidebar-one', '/includes/sidebars/', 'php', false);
		$this->options[] = new xtreme_option_locate_files($this, false, 'layout_2_col2content', __('File Sidebar 2:', XF_TEXTDOMAIN), 'sidebar-two', '/includes/sidebars/', 'php', false);
		//altes Layout
		$this->options[] = new xtreme_option_select_pair($this, false, 'col1txtalign', __('Sidebar 1 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'col2txtalign', __('Sidebar 2 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'col3txtalign', __('Content Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		//neues Layout		
		$this->options[] = new xtreme_option_select_pair($this, false, 'layout_2_col3txtalign', __('Sidebar 1 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'layout_2_col2txtalign', __('Sidebar 2 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'layout_2_col1txtalign', __('Content Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));

		$this->options[] = new xtreme_option_bool($this, false, 'use_header' ,__('Use Header:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_bool($this, false, 'use_teaser' ,__('Use Teaser:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'use_footer' ,__('Use Footer:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'use_siteinfo' ,__('Use Siteinfo:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_hidden($this, false, 'nicename', '', 'Default');
		
		$sanitized = sanitize_title('Default');
		
		$name = 'xf_layout-' . str_replace(' ', '-', strtolower($sanitized));
		
		$this->options[] = new xtreme_option_hidden($this, false, 'layoutname', '', $name);
		$this->options[] = new xtreme_option_hidden($this, false, 'description', '', 1);
		
		$classes = xtreme_classes_array();
		
		$this->options[] = new xtreme_option_hidden($this, false, 'mainclass', '', $classes[1]);
		
		$files = xtreme_load_templates();
		
		foreach ($files as $file => $value) {
			$this->options[] = new xtreme_option_hidden($this, false, substr($value['metavalue'], 0, -4), $value['Name'], true);
		}
		
		$this->options[] = new xtreme_option_select($this, false, 'sidebar_tag', __('Sidebar Tag:', XF_TEXTDOMAIN), 'ul', array('ul', 'div'));
		$this->options[] = new xtreme_option_select($this, false, 'sidebar_headline_tag', __('Tag before Title in Sidebars Widget Areas:', XF_TEXTDOMAIN), 'h5', array('h3', 'h4', 'h5', 'h6'));
		$this->options[] = new xtreme_option_select($this, false, 'html5_tag', __('HTML5 Tag of Sidebars:', XF_TEXTDOMAIN), 'aside', xtreme_html5_tags());
	}
}


class xc_header extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Header Settings', XF_TEXTDOMAIN));
		
		$measures = $this->owner->get_measures();
		$this->options[] = new xtreme_option_bool($this, false, 'columns' ,__('Add a Second Area:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'widget_area' ,__('Second Area as Widget Area:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'position', __('Position of the Second Area:', XF_TEXTDOMAIN), 1, array(__('left', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_numeric($this, false, 'colwidth', __('Width of the Second Area:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(50,1024), 'em' => array(2,50), '%' => array(5,95)), false, false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'show_logo', __('Blogtitle:', XF_TEXTDOMAIN), 0, array(__('Text with link', XF_TEXTDOMAIN), __('Image with link', XF_TEXTDOMAIN), __('Text without link', XF_TEXTDOMAIN), __('Image without link', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select($this, false, 'blogtitle_tag', __('Blogtitle Tag:', XF_TEXTDOMAIN), 'h1', array('h1', 'h2', 'h3', 'div', 'p'));
		$this->options[] = new xtreme_option_bool($this, false, 'blog_description' ,__('Show Blog Description:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_select($this, false, 'blogdescription_tag', __('Blog Description Tag:', XF_TEXTDOMAIN), 'p', array('p', 'h1', 'h2', 'h3', 'div'));
		$this->options[] = new xtreme_option_select($this, false, 'html5_tag', __('HTML5 Tag:', XF_TEXTDOMAIN), 'header', xtreme_html5_tags());
	}
}

class xc_footer extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Footer Settings', XF_TEXTDOMAIN));
		
		$this->options[] = new xtreme_option_select_optgroup($this, false, 'subcolumns', __('Layout:', XF_TEXTDOMAIN), 26, xtreme_get_subcolumns_array(), __('Columns', XF_TEXTDOMAIN));
		
		for($i=1; $i<=5; $i++) {
			$this->options[] = new xtreme_option_select_pair($this, false, 'txtalign_' . $i, sprintf(__('Area %d Textalign (from left to right):', XF_TEXTDOMAIN), $i), 'left', xtreme_left_center_right());
		}
		
		$this->options[] = new xtreme_option_select($this, false, 'tag_before_widget', __('Tag before Widget:', XF_TEXTDOMAIN), 'ul', array('ul', 'div'));
		$this->options[] = new xtreme_option_select($this, false, 'tag_before_title', __('Tag before Title:', XF_TEXTDOMAIN), 'h5', array('h3', 'h4', 'h5', 'h6', 'div'));
		$this->options[] = new xtreme_option_bool($this, false, 'hide_powered_by' ,__('Hide Powered by:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'syncheight' ,__('Use Syncheight Script:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_select($this, false, 'html5_tag', __('HTML5 Tag:', XF_TEXTDOMAIN), 'section', xtreme_html5_tags());
	}
}

class xc_teaser extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Teaser Settings', XF_TEXTDOMAIN));
		
		$this->options[] = new xtreme_option_select_optgroup($this, false, 'subcolumns', __('Layout:', XF_TEXTDOMAIN), 0, xtreme_get_subcolumns_array(), __('Columns', XF_TEXTDOMAIN));
		
		for($i=1; $i<=5; $i++) {
			$this->options[] = new xtreme_option_select_pair($this, false, 'txtalign_' . $i, sprintf(__('Area %d Textalignment (from left to right):', XF_TEXTDOMAIN), $i), 'left', xtreme_left_center_right());
		}
		
		$this->options[] = new xtreme_option_select($this, false, 'tag_before_widget', __('Tag before Widget:', XF_TEXTDOMAIN), 'div', array('ul', 'div'));
		$this->options[] = new xtreme_option_select($this, false, 'tag_before_title', __('Tag before Title:', XF_TEXTDOMAIN), 'h5', array('h3', 'h4', 'h5', 'h6', 'div'));
		$this->options[] = new xtreme_option_bool($this, false, 'syncheight' , __('Use Syncheight Script:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_select($this, false, 'html5_tag', __('HTML5 Tag:', XF_TEXTDOMAIN), 'section', xtreme_html5_tags());
	}
}

class xc_siteinfo extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Siteinfo Settings', XF_TEXTDOMAIN));
		
		$measures = $this->owner->get_measures();
		$this->options[] = new xtreme_option_bool($this, false, 'columns' ,__('Add a Second Area:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'widget_area' ,__('Second Area as Widget Area:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'position', __('Position of the Second Area:', XF_TEXTDOMAIN), 1, array(__('left', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_numeric($this, false, 'colwidth', __('Width of the Second Area:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(50,800), 'em' => array(2,50), '%' => array(5,75)), false, false);
		$this->options[] = new xtreme_option_bool($this, false, 'copyright' , __('Use a Copyright Message:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_textfield($this, false, 'copyright_start', __('Copyright Start Year:', XF_TEXTDOMAIN), '2010', false);
		$this->options[] = new xtreme_option_bool($this, false, 'debug' , __('Show Queries:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_select($this, false, 'html5_tag', __('HTML5 Tag:', XF_TEXTDOMAIN), 'footer', xtreme_html5_tags());
	}
}

class xc_navigation extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Navigation/Search Settings', XF_TEXTDOMAIN));
		
		$this->options[] = new xtreme_option_description($this, false, 'note', '<em>'. __('Note: If you select the position &raquo;inside header - first area&laquo; or &raquo;inside header - second area&laquo; for a navigation, please make sure to enable &raquo;Add a Second Area&laquo; in the section of &raquo;Header Settings&laquo;.', XF_TEXTDOMAIN). '</em>');
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<strong>' . __('Primary Navigation', XF_TEXTDOMAIN). '</strong>');
		$this->options[] = new xtreme_option_select_pair($this, false, 'primary_position', __('Position:', XF_TEXTDOMAIN), 5,
			   array(__('not used', XF_TEXTDOMAIN), __('before header', XF_TEXTDOMAIN), __('inside of header at the top', XF_TEXTDOMAIN), __('inside header - second area', XF_TEXTDOMAIN),__('inside of header at the bottom', XF_TEXTDOMAIN), __('after header', XF_TEXTDOMAIN), __('before main', XF_TEXTDOMAIN), __('before siteinfo', XF_TEXTDOMAIN), __('inside header - first area', XF_TEXTDOMAIN), __('inside of siteinfo at the top', XF_TEXTDOMAIN), __('inside siteinfo - second area', XF_TEXTDOMAIN), __('inside of siteinfo at the bottom', XF_TEXTDOMAIN), __('inside siteinfo - first area', XF_TEXTDOMAIN), __('after image header', XF_TEXTDOMAIN) ));
		$this->options[] = new xtreme_option_locate_files($this, false, 'primary_stylesheet', __('Stylesheet:', XF_TEXTDOMAIN), 'shinybuttons', '/css/navigation/', 'css', false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'primary_content', __('Content:', XF_TEXTDOMAIN), 'pages', xtreme_get_nav_menus());
		$stuff = array('menu_order' => __('Menu Order/ID', XF_TEXTDOMAIN), 'post_title' => __('Post Title/Name', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_select_pair($this, false, 'primary_order', __('Sort by:', XF_TEXTDOMAIN), 'menu_order', $stuff);
		$excl = array('none' => __('none', XF_TEXTDOMAIN), 'include' => __('include', XF_TEXTDOMAIN), 'exclude' => __('exclude', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_select_pair($this, false, 'primary_limitation', __('Include/Exclude from Menu:', XF_TEXTDOMAIN),'none' ,$excl);
		$this->options[] = new xtreme_option_textfield($this, false, 'primary_ids', __('Enter IDs, comma separated:', XF_TEXTDOMAIN),'', false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'primary_depth', __('Menu Depth:', XF_TEXTDOMAIN), 1, array(__('No limit', XF_TEXTDOMAIN), 1,2,3));
		$this->options[] = new xtreme_option_bool($this, false, 'primary_showhome' , __('Create a Home Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_textfield($this, false, 'primary_homelink', __('Text of the Home Link:', XF_TEXTDOMAIN), __('Home', XF_TEXTDOMAIN), __('Home', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_bool($this, false, 'primary_desc_walker' , __('Use Menu Description:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'primary_script' , __('Enable Superfish Dropdown JavaScript:', XF_TEXTDOMAIN), false);
		//secondary navigation
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<strong>' . __('Secondary Navigation', XF_TEXTDOMAIN). '</strong>');
		$this->options[] = new xtreme_option_select_pair($this, false, 'secondary_position', __('Position:', XF_TEXTDOMAIN), 0,
			   array(__('not used', XF_TEXTDOMAIN), __('before header', XF_TEXTDOMAIN), __('inside of header at the top', XF_TEXTDOMAIN), __('inside header - second area', XF_TEXTDOMAIN),__('inside of header at the bottom', XF_TEXTDOMAIN), __('after header', XF_TEXTDOMAIN), __('before main', XF_TEXTDOMAIN), __('before siteinfo', XF_TEXTDOMAIN), __('inside header - first area', XF_TEXTDOMAIN), __('inside of siteinfo at the top', XF_TEXTDOMAIN), __('inside siteinfo - second area', XF_TEXTDOMAIN), __('inside of siteinfo at the bottom', XF_TEXTDOMAIN), __('inside siteinfo - first area', XF_TEXTDOMAIN), __('after image header', XF_TEXTDOMAIN) ));
		$this->options[] = new xtreme_option_locate_files($this, false, 'secondary_stylesheet', __('Stylesheet:', XF_TEXTDOMAIN), 'shinybuttons', '/css/navigation/', 'css', false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'secondary_content', __('Content:', XF_TEXTDOMAIN), 'categories', xtreme_get_nav_menus());
		$this->options[] = new xtreme_option_select_pair($this, false, 'secondary_order', __('Sort by:', XF_TEXTDOMAIN),'menu_order' ,$stuff);
		$this->options[] = new xtreme_option_select_pair($this, false, 'secondary_limitation', __('Include/Exclude from Menu:', XF_TEXTDOMAIN),'none' ,$excl);
		$this->options[] = new xtreme_option_textfield($this, false, 'secondary_ids', __('Enter IDs, comma separated:', XF_TEXTDOMAIN),'', false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'secondary_depth', __('Menu Depth:', XF_TEXTDOMAIN), 1, array(__('No limit', XF_TEXTDOMAIN), 1,2,3));
		$this->options[] = new xtreme_option_bool($this, false, 'secondary_showhome' , __('Create a Home Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_textfield($this, false, 'secondary_homelink', __('Text of the Home Link:', XF_TEXTDOMAIN), __('Home', XF_TEXTDOMAIN), __('Home', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_bool($this, false, 'secondary_desc_walker' , __('Use Menu Description:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'secondary_script' , __('Enable Superfish Dropdown JavaScript:', XF_TEXTDOMAIN), false);
		//general
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<strong>' . __('General Search Options', XF_TEXTDOMAIN) . '</strong>');
		$this->options[] = new xtreme_option_select_pair($this, false, 'navi_search', __('Searchform in Navigation:', XF_TEXTDOMAIN), 0, array(__('not used', XF_TEXTDOMAIN), __('primary navigation', XF_TEXTDOMAIN), __('secondary navigation', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_bool($this, false, 'show_submit' ,__('Show Submit Button', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_textfield($this, false, 'submit_text', __('Submit Button Text:', XF_TEXTDOMAIN), __('Submit', XF_TEXTDOMAIN), __('Submit', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_textfield($this, false, 'input_text', __('Searchfield Text:', XF_TEXTDOMAIN), __('Search...', XF_TEXTDOMAIN), __('Search...', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_select($this, false, 'html5_tag', __('HTML5 Tag:', XF_TEXTDOMAIN), 'nav', xtreme_html5_tags());
	}
}

class xc_pagination extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Archive Pagination Settings', XF_TEXTDOMAIN));
		
		$this->options[] = new xtreme_option_select_pair($this, false, 'pagination_type', __('Pagination Type:', XF_TEXTDOMAIN), 0, array(__('Older/Newer Post Links', XF_TEXTDOMAIN),__('Page Number List', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'end_size', __('Number of Pages at start / end of list:', XF_TEXTDOMAIN), 1, array(1 => 1,2 => 2,3 => 3,4 => 4,5 => 5,6 => 6,7 => 7,8 => 8,9 => 9,10 => 10));
		$this->options[] = new xtreme_option_select_pair($this, false, 'mid_size', __('Number of Pages left / right from current:', XF_TEXTDOMAIN), 2, array(1 => 1,2 => 2,3 => 3,4 => 4,5 => 5,6 => 6,7 => 7,8 => 8,9 => 9,10 => 10));
		$this->options[] = new xtreme_option_bool($this, false, 'show_all' ,__('Show all available Pages:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'prev_next' ,__('Show Prev/Next Short Links:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_select_pair($this, false, 'type', __('Display as:', XF_TEXTDOMAIN), 'plain', array('plain' => __('pure links only',XF_TEXTDOMAIN), 'list' => __('unordered list',XF_TEXTDOMAIN)));		
		$this->options[] = new xtreme_option_textfield($this, false, 'previous_string', __('Previous Posts String:', XF_TEXTDOMAIN), __( 'Newer Posts <span>&rarr;</span>', XF_TEXTDOMAIN ), __( 'Newer Posts <span>&rarr;</span>', XF_TEXTDOMAIN ));
		$this->options[] = new xtreme_option_textfield($this, false, 'next_string', __('Next Posts String:', XF_TEXTDOMAIN), __( '<span>&larr;</span> Older Posts', XF_TEXTDOMAIN ), __( '<span>&larr;</span> Older Posts', XF_TEXTDOMAIN ));
	}
}

class xc_comments extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Comment and Comment Form Settings', XF_TEXTDOMAIN));
		
		//comment settings
		$measures = $this->owner->get_measures();;
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<strong>' . __('Comments Settings', XF_TEXTDOMAIN) . '</strong>');
		$this->options[] = new xtreme_option_bool($this, false, 'show_avatar' ,__('Show Gravatar:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_numeric($this, false, 'avatar_size', __('Gravatar Size:', XF_TEXTDOMAIN), 40, 'px', $measures, array('px' => array(10,96)), false, false );
		$this->options[] = new xtreme_option_select($this, false, 'avatar_align', __('Gravatar Alignment:', XF_TEXTDOMAIN), 0, array('left', 'right', 'none'));
		$this->options[] = new xtreme_option_bool($this, false, 'tabbed_comments' ,__('Comments and Pings displaying in Tabs:', XF_TEXTDOMAIN), false);
		
		//comment form settings
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<strong>' . __('Comment Form Settings', XF_TEXTDOMAIN) . '</strong>');
		$this->options[] = new xtreme_option_select_pair($this, false, 'form_class', __('Comment Form Layout:', XF_TEXTDOMAIN), 0, array(__('linear', XF_TEXTDOMAIN), __('ym-columnar', XF_TEXTDOMAIN), __('ym-full', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_textfield($this, false, 'title_reply', __('Reply Title:', XF_TEXTDOMAIN), __( 'Leave a Reply', XF_TEXTDOMAIN ), __( 'Leave a Reply', XF_TEXTDOMAIN ));
		$this->options[] = new xtreme_option_textfield($this, false, 'cancel_reply_link', __('Cancel Reply Title:', XF_TEXTDOMAIN), __( 'Cancel reply', XF_TEXTDOMAIN ), __( 'Cancel reply', XF_TEXTDOMAIN ));
		$this->options[] = new xtreme_option_textfield($this, false, 'comment_notes_default', __('Notes on Top of Comment Form:', XF_TEXTDOMAIN), __('Your email address will not be published. Required fields are marked *.', XF_TEXTDOMAIN), __('Your email address will not be published. Required fields are marked *.', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_textfield($this, false, 'comment_notes_before_add', __('Additional Text before Form Fields:', XF_TEXTDOMAIN), '', false);
		$this->options[] = new xtreme_option_textarea($this, false, 'comment_notes_before_textarea', __('Additional Text before Textarea:', XF_TEXTDOMAIN), '');
		$this->options[] = new xtreme_option_bool($this, false, 'allowed_tags' ,__('Display allowed HTML Tags:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_textfield($this, false, 'label_text', __('Label Title:', XF_TEXTDOMAIN), __( 'Your Comment', XF_TEXTDOMAIN ), __( 'Your Comment', XF_TEXTDOMAIN ));
		$this->options[] = new xtreme_option_textfield($this, false, 'label_submit', __('Submit Button Text:', XF_TEXTDOMAIN),  __( 'Post Comment', XF_TEXTDOMAIN ), __( 'Post Comment', XF_TEXTDOMAIN ));
		$this->options[] = new xtreme_option_select($this, false, 'comment_ping_headline_tag', __('Tag before comments and ping Title:', XF_TEXTDOMAIN), 'h5', array('h2', 'h3', 'h4', 'h5', 'h6'));
	}
}

class xc_print extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('Print Stylesheet Settings', XF_TEXTDOMAIN));
		
		$this->options[] = new xtreme_option_bool($this, false, 'byline' ,__('Print Byline:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'authorbox' ,__('Print Authorbox on Single View:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'comments' ,__('Print Comments:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'url_output' ,__('Print the URL of Hyperlinks:', XF_TEXTDOMAIN), false);
	}
}

class xc_wordpress extends xc_base {
	
	function __construct( &$owner ) {
		
		parent::__construct($owner, get_class($this), __('General WordPress Settings', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_bool($this, false, 'xtreme_title' ,__('Remove Document Title:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'xtreme_set_viewport' ,__('Remove Viewport:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'xtreme_meta_description' ,__('Remove Meta Description:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'xtreme_meta_robots' ,__('Remove Meta Robots:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'feed_links_extra' ,__('Remove Extra Feeds:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'feed_links' ,__('Remove Feed Links:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'rsd_link' ,__('Remove RSD Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'wlwmanifest_link' ,__('Remove Windows Live Writer Manifest Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'index_rel_link' ,__('Remove Index Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'parent_post_rel_link' ,__('Remove Parent Post Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'start_post_rel_link' ,__('Remove Start Post Rel Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'adjacent_posts_rel_link' ,__('Remove Adjacent Rel Post Link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'wp_shortlink_wp_head' ,__('Remove Short link:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'wp_generator' ,__('Remove WP Generator:', XF_TEXTDOMAIN), false);
	}
}

class xc_performance extends xc_base {

	function xc_performance( &$owner ) {

		parent::__construct($owner, get_class($this), __('Tune your Site Performance', XF_TEXTDOMAIN));

		// lazy gravatar loading
		$this->options[] = new xtreme_option_description($this, false, 'desc_row', '<strong>'.__('Optimize Gravatars', XF_TEXTDOMAIN).'</strong>');
		$this->options[] = new xtreme_option_bool($this, false, 'lazy_gravatars', __('Lazy Loading Gravatars:', XF_TEXTDOMAIN), false);
		if(function_exists('fopen') && function_exists('ini_get') && true == ini_get('allow_url_fopen') && function_exists('imagecreatefromgd')) {
			$this->options[] = new xtreme_option_bool($this, false, 'proxy_gravatars', __('Use Domain as Gravatar Proxy:', XF_TEXTDOMAIN), false);
		}else{
			$this->options[] = new xtreme_option_hidden($this, false, 'proxy_gravatars', '', false);
		}

	}
}

function _xtreme_metaboxes_default( $box_classes ) {
	
	$box_classes[] = 'xc_general';
	$box_classes[] = 'xc_layout';
	$box_classes[] = 'xc_header';
	$box_classes[] = 'xc_footer';
	$box_classes[] = 'xc_teaser';
	$box_classes[] = 'xc_siteinfo';
	$box_classes[] = 'xc_navigation';
	$box_classes[] = 'xc_pagination';
	$box_classes[] = 'xc_comments';
	$box_classes[] = 'xc_print';
	$box_classes[] = 'xc_wordpress';
	$box_classes[] = 'xc_performance';

	return $box_classes;
}
add_filter('xtreme_metaboxes_default', '_xtreme_metaboxes_default');
