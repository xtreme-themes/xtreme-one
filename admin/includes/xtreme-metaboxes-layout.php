<?php

class xc_templayout extends xc_base {
	function __construct(&$owner) {
		parent::__construct($owner, get_class($this), __('New Layout Settings', XF_TEXTDOMAIN));
		$this->options[] = new xtreme_option_textfield($this, false, 'nicename', __('Name of the Layout:', XF_TEXTDOMAIN), 'new Layout', 'new Layout');
		$this->options[] = new xtreme_option_select_pair($this, false, 'columnlayout', __('Layout:', XF_TEXTDOMAIN), 1, array(__('1 column', XF_TEXTDOMAIN), __('2 columns - right sidebar', XF_TEXTDOMAIN), __('2 columns - left sidebar', XF_TEXTDOMAIN), __('3 columns - left and right sidebars', XF_TEXTDOMAIN), __('3 columns - 2 right sidebars', XF_TEXTDOMAIN),__('3 columns - 2 left sidebars', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_description($this, false, 'col2tip', '<em>' . __('In this case width of Sidebar 1 and width of Sidebar 2 must have the same units!', XF_TEXTDOMAIN) . '</em>');

		//wir brauchen den layoutwert
		$this->options[] = new xtreme_option_hidden($this, false, 'is_layout_2', '', (int) xtreme_is_layout_2());
		//altes Layout
		$measures = $this->owner->get_measures();
		$this->options[] = new xtreme_option_numeric($this, false, 'col1width', __('Sidebar 1 Width:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(80,500), 'em' => array(5,50), '%' => array(5,50)), false, false);
		$this->options[] = new xtreme_option_numeric($this, false, 'col2width', __('Sidebar 2 Width:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(80,500), 'em' => array(5,50), '%' => array(5,50)), false, false);
		$this->options[] = new xtreme_option_locate_files($this, false, 'col1content', __('File Sidebar 1:', XF_TEXTDOMAIN), 'sidebar-one', '/includes/sidebars/', 'php', false);
		$this->options[] = new xtreme_option_locate_files($this, false, 'col2content', __('File Sidebar 2:', XF_TEXTDOMAIN), 'sidebar-two', '/includes/sidebars/', 'php', false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'col1txtalign', __('Sidebar 1 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'col2txtalign', __('Sidebar 2 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'col3txtalign', __('Content Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		//neues Layout
		$this->options[] = new xtreme_option_numeric($this, false, 'layout_2_col1width', __('Content Width:', XF_TEXTDOMAIN), 75, '%', $measures, array('px' => array(100,1600), '%' => array(5,100)), false, false);
		$this->options[] = new xtreme_option_numeric($this, false, 'layout_2_col2width', __('Sidebar 2 Width:', XF_TEXTDOMAIN), 25, '%', $measures, array('px' => array(80,500), '%' => array(5,50)), false, false);
		$this->options[] = new xtreme_option_locate_files($this, false, 'layout_2_col3content', __('File Sidebar 1:', XF_TEXTDOMAIN), 'sidebar-one', '/includes/sidebars/', 'php', false);
		$this->options[] = new xtreme_option_locate_files($this, false, 'layout_2_col2content', __('File Sidebar 2:', XF_TEXTDOMAIN), 'sidebar-two', '/includes/sidebars/', 'php', false);
		$this->options[] = new xtreme_option_select_pair($this, false, 'layout_2_col3txtalign', __('Sidebar 1 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'layout_2_col2txtalign', __('Sidebar 2 Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));
		$this->options[] = new xtreme_option_select_pair($this, false, 'layout_2_col1txtalign', __('Content Textalignment:', XF_TEXTDOMAIN), 0, array(__('left', XF_TEXTDOMAIN), __('center', XF_TEXTDOMAIN), __('right', XF_TEXTDOMAIN)));

		$this->options[] = new xtreme_option_bool($this, false, 'use_header' ,__('Use Header:', XF_TEXTDOMAIN), true);
		$this->options[] = new xtreme_option_bool($this, false, 'use_teaser' ,__('Use Teaser:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'use_footer' ,__('Use Footer:', XF_TEXTDOMAIN), false);
		$this->options[] = new xtreme_option_bool($this, false, 'use_siteinfo' ,__('Use Siteinfo:', XF_TEXTDOMAIN), true);
		$sanitized = sanitize_title('Default');
		$name = 'xf_layout-' . str_replace(' ', '-', strtolower($sanitized));
		$this->options[] = new xtreme_option_hidden($this, false, 'layoutname', '', $name);
		$this->options[] = new xtreme_option_hidden($this, false, 'description', '', __('2 columns - right sidebar', XF_TEXTDOMAIN));
		$classes = xtreme_classes_array();
		$this->options[] = new xtreme_option_hidden($this, false, 'mainclass', '', $classes[1]);
		$this->options[] = new xtreme_option_description($this, false, 'desc_row_1', '<strong>' . __('Apply these Template Files to the New Layout:', XF_TEXTDOMAIN) . '</strong>');
		$files = xtreme_load_templates();
		foreach ($files as $file => $value) {
			$this->options[] = new xtreme_option_bool($this, false, substr($value['metavalue'], 0, -4), $value['Name'], false);
		}
		$this->options[] = new xtreme_option_hidden($this, false, 'mode', '', 'add');
	}
}

function _xtreme_metaboxes_layout($box_classes) {
	$box_classes[] = 'xc_templayout';
	return $box_classes;
}
add_filter('xtreme_metaboxes_layout', '_xtreme_metaboxes_layout');