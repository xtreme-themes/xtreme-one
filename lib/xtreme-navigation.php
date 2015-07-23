<?php

function xtreme_navigation( $nav ) {
    $el = 'div';
    $options = get_option( XF_OPTIONS );
    if ( xtreme_is_html5() ) $el = $options['xc_navigation']['html5_tag']['value'];
    xtreme_start_container( $nav . 'nav', 'navigation', $el );
    do_action('xtreme_'.$nav.'nav_top');
    echo _xtreme_navigation_content($nav);
    do_action('xtreme_'.$nav.'nav_bottom');
    xtreme_end_container($el);
}

function xtreme_header_navigation( $nav ) {
    $el = 'div';
    $options = get_option( XF_OPTIONS );
    if ( xtreme_is_html5() ) $el = $options['xc_navigation']['html5_tag']['value'];
    echo '<' . $el . ' id="' . $nav . 'nav" ' . xtreme_aria_required( 'navigation', false ) . ' class="' . _xtreme_nav_stylesheet( $nav ) . ' ym-clearfix">';
    do_action('xtreme_header'.$nav.'nav_top');
    echo _xtreme_navigation_content( $nav );
    do_action('xtreme_header'.$nav.'nav_bottom');
    echo '</' . $el . '>';
}

function _xtreme_primary_nav_position() {
    $options = get_option(XF_OPTIONS);
    switch( (int) $options['xc_navigation']['primary_position']['value'] ) {
        case 0:
            return false;
            break;
        case 1:
            return array('hook' =>'xtreme_before_header', 'priority' => 0);
            break;
        case 2:
            return array('hook' =>'xtreme_header_top', 'priority' => 0);
            break;
        case 3:
            return array('hook' =>'xtreme_header_col1', 'priority' => 0);
            break;
        case 4:
            return array('hook' =>'xtreme_header_bottom', 'priority' => 2);
            break;
        case 5:
            return array('hook' =>'xtreme_after_header', 'priority' => 0);
            break;
        case 6:
            return array('hook' =>'xtreme_before_main', 'priority' => 0);
            break;
        case 7:
            return array('hook' =>'xtreme_before_siteinfo', 'priority' => 0);
            break;
        case 8:
            return array('hook' =>'xtreme_header_col3', 'priority' => 0);
            break;
        case 9:
            return array('hook' =>'xtreme_siteinfo_top', 'priority' => 0);
            break;
        case 10:
            return array('hook' =>'xtreme_siteinfo_col1', 'priority' => 0);
            break;
        case 11:
            return array('hook' =>'xtreme_siteinfo_bottom', 'priority' => 0);
            break;
        case 12:
            return array('hook' =>'xtreme_siteinfo_col3', 'priority' => 0);
            break;
		case 13:
			return array('hook' =>'xtreme_after_image_header', 'priority' => 0);
			break;
    }
}

function _xtreme_secondary_nav_position() {
    $options = get_option(XF_OPTIONS);
    switch( (int) $options['xc_navigation']['secondary_position']['value'] ) {
        case 0:
            return false;
            break;
        case 1:
            return array('hook' =>'xtreme_before_header', 'priority' => 1);
            break;
        case 2:
            return array('hook' =>'xtreme_header_top', 'priority' => 1);
            break;
        case 3:
            return array('hook' =>'xtreme_header_col1', 'priority' => 2);
            break;
        case 4:
            return array('hook' =>'xtreme_header_bottom', 'priority' => 2);
            break;
        case 5:
            return array('hook' =>'xtreme_after_header', 'priority' => 1);
            break;
        case 6:
            return array('hook' =>'xtreme_before_main', 'priority' => 1);
            break;
        case 7:
            return array('hook' =>'xtreme_before_siteinfo', 'priority' => 1);
            break;
        case 8:
            return array('hook' =>'xtreme_header_col3', 'priority' => 1);
            break;
        case 9:
            return array('hook' =>'xtreme_siteinfo_top', 'priority' => 1);
            break;
        case 10:
            return array('hook' =>'xtreme_siteinfo_col1', 'priority' => 1);
            break;
        case 11:
            return array('hook' =>'xtreme_siteinfo_bottom', 'priority' => 1);
            break;
        case 12:
            return array('hook' =>'xtreme_siteinfo_col3', 'priority' => 1);
            break;
		case 13:
			return array('hook' =>'xtreme_after_image_header', 'priority' => 0);
			break;
    }
}

function _xtreme_setup_theme_nav() {
	$sec_nav = _xtreme_secondary_nav_position();
	add_action($sec_nav['hook'], '_xtreme_secondary_navigation', $sec_nav['priority']);
	$pri_nav = _xtreme_primary_nav_position();
	add_action($pri_nav['hook'], '_xtreme_primary_navigation', $pri_nav['priority']);
}
add_action('xtreme_setup_theme', '_xtreme_setup_theme_nav');

function _xtreme_primary_navigation() {
	$options = get_option(XF_OPTIONS);
	switch((int) $options['xc_navigation']['primary_position']['value']) {
		case 0:
		case 1:
		case 5:
		case 6:
		case 7:
		case 13:
			xtreme_navigation('primary');
			break;

		case 2:
		case 3:
		case 4:
		case 8:
		case 9:
		case 10:
		case 11:
		case 12:
			xtreme_header_navigation('primary');
			break;
	}
}

function _xtreme_secondary_navigation() {
	$options = get_option(XF_OPTIONS);
	switch((int) $options['xc_navigation']['secondary_position']['value']) {
		case 0:
		case 1:
		case 5:
		case 6:
		case 7:
		case 13:
			xtreme_navigation('secondary');
			break;

		case 2:
		case 3:
		case 4:
		case 8:
		case 9:
		case 10:
		case 11:
		case 12:
			xtreme_header_navigation('secondary');
			break;
	}
}

function _xtreme_navigation_content( $nav ) {
	
    $options    = get_option( XF_OPTIONS );
    $style      = esc_attr( $options['xc_navigation'][$nav . '_stylesheet']['value'] );
    $menu_name  = '';
    $show_home  = 0;
    $walker     = '';
    $limitation = '';
    $ids        = '';
    $val        = $options['xc_navigation'][$nav . '_content']['value'];
    if ( $val === 'pages' || $val === 'categories' ) {
        $depth = (int) $options['xc_navigation'][$nav . '_depth']['value'];
        $order = esc_attr( $options['xc_navigation'][$nav . '_order']['value'] );

        if ( $options['xc_navigation'][$nav . '_limitation']['value'] !== 'none') {
            $limitation = esc_attr($options['xc_navigation'][$nav . '_limitation']['value']);
            $ids = esc_attr($options['xc_navigation'][$nav . '_ids']['value']);
        }
        if((int)$options['xc_navigation'][$nav . '_showhome']['value'] === 1) {
            $show_home = esc_attr($options['xc_navigation'][$nav . '_homelink']['value']);
        }
    } else {
    	// Filter Hook for menu string
		$menu_name = apply_filters( 'xtreme_set_menu_name', esc_attr( $val ), $nav );
        $val = 'wp_nav_menu';
        $depth = 0;
        if (true === $options['xc_navigation'][$nav . '_desc_walker']['value']) {
            $walker = new description_walker();
            //$depth = 0;
        }
    }

    $nav_search_val = (int) $options['xc_navigation']['navi_search']['value'];
    $searchhtml = "";

    if($nav_search_val === 1 ) {
        $nav_search = 'primary';
    } elseif($nav_search_val === 2) {
        $nav_search = 'secondary';
    } else {
        $nav_search = '';
    }

    if( $nav_search === $nav ) {
        $searchhtml = xtreme_navigation_searchform();
    }

    switch ( $val ) {
        case 'wp_nav_menu':
            $args = array(
                'menu' => $menu_name,
                'menu_class' => 'sf-menu',
                'menu_id' => false,
                'container' => '',
                'container_class' => '',
                'depth' => $depth,
                'echo' => false,
                'before' => '', //before link
                'after' => '', //after link
                'link_before' => '',
                'link_after' => '',
                'walker' => $walker
            );
            $menu = '<div class="ym-hlist">' . $searchhtml . wp_nav_menu( $args ) . '</div>';
            break;
        case 'pages':
            if ( $order == 'menu_order' ) {
                $order = 'menu_order, post_title';
            }
            $args = array(
                'show_home' => $show_home,
                'depth' => $depth,
                'menu_class' => 'ym-hlist',
                'echo' => 0,
                $limitation => $ids,
                'sort_column' => $order
            );
            $menu = wp_page_menu($args) ;
            $menu = str_replace('<div class="ym-hlist"><ul>', '<ul class="sf-menu">', $menu);
            $menu = rtrim($menu);
            $menu = substr( $menu, 0, -6 );
            $menu = '<div class="ym-hlist">' . $searchhtml . $menu . '</div>' ;
            break;
        case 'categories':
            if ( $order == 'menu_order' ) {
                $order = 'ID';
            } elseif( $order == 'post_title' ) {
                $order = 'name';
            }
            $args = array(
                'depth' => $depth,
                'title_li' => 0,
                'show_count' => 0,
                'hide_empty' => 1,
                $limitation => $ids,
                'orderby' => $order,
                'echo' => 0
            );
            $link = '';
            if($show_home !== 0) {
                if ( is_front_page() && !is_paged() ) {
                    $cls = 'current_page_item';
                } else {
                    $cls = 'page_item';
                }
                $link = '<li class="' . $cls . '"><a href="' .  home_url() . '" title="' . esc_attr($show_home) . '">' . esc_attr($show_home) . '</a></li>';
            }
            $menu = '<div class="ym-hlist">' . $searchhtml . '<ul class="sf-menu">' .$link . wp_list_categories($args) . '</ul></div>';
            break;
    }
    return $menu;
}

function _xtreme_nav_stylesheet( $nav ) {
    $options = get_option( XF_OPTIONS );
    return esc_attr( $options['xc_navigation'][$nav . '_stylesheet']['value'] );
}

function xtreme_navigation_searchform() {
    $el = 'text';
    if (xtreme_is_html5() ) $el = 'search';
    $options = get_option( XF_OPTIONS );
    $submitcls = ( false === $options['xc_navigation']['show_submit']['value'] ) ? "class='ym-hideme'" : "";
    $submittxt = $options['xc_navigation']['submit_text']['value'];
    $searchtxt = $options['xc_navigation']['input_text']['value'];
    $form = '<form ' . xtreme_aria_required( 'search', false ) . ' method="get" id="searchform" action="' . home_url() . '/" >
    <div><label class="screen-reader-text" for="s">' . __('Search for:', XF_TEXTDOMAIN) . '</label>';
    $form .= "<input type='" . $el . "' value='" . esc_attr( $searchtxt ) . "' name='s' id='s' accesskey='s'/>\n";
    $form .= "<input type='submit' id='searchsubmit' value='" . esc_attr( $submittxt ) . "'  " . $submitcls . " />";
    $form .= "</div></form>";
    $html = '<ul class="nav_search"><li class="navsearch">' . $form . '</li></ul>';
    return $html;
}

/*
 * @see http://www.kriesi.at/archives/improve-your-wordpress-navigation-menu-output
 */
class description_walker extends Walker_Nav_Menu {
	
	function start_el( &$output, $item, $depth = 0, $args = array(), $id = 0 ) {
        global $wp_query;
		
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        $class_names = $value = '';

        $classes = empty( $item->classes ) ? array() : (array) $item->classes;

        $class_names = join( ' ', apply_filters( 'nav_menu_css_class', array_filter( $classes ), $item ) );
        $class_names = ' class="desc '. esc_attr( $class_names ) . '"';

        $output .= $indent . '<li id="menu-item-'. $item->ID . '"' . $value . $class_names .'>';

        $attributes  = ! empty( $item->attr_title ) ? ' title="'  . esc_attr( $item->attr_title ) .'"' : '';
        $attributes .= ! empty( $item->target )     ? ' target="' . esc_attr( $item->target     ) .'"' : '';
        $attributes .= ! empty( $item->xfn )        ? ' rel="'    . esc_attr( $item->xfn        ) .'"' : '';
        $attributes .= ! empty( $item->url )        ? ' href="'   . esc_attr( $item->url        ) .'"' : '';

        $prepend = '';
        $append = '';
        $desc =  trim( $item->description );
        $description  = ! empty( $desc ) ? '<span>'.esc_attr( $desc ).'</span>' : '';

        if($depth != 0) {
            $description = $append = $prepend = "";
        }
        $item_output = $args->before;
        $item_output .= '<a'. $attributes .'>';
        $item_output .= $args->link_before .$prepend.apply_filters( 'the_title', $item->title, $item->ID ).$append;
        $item_output .= $description.$args->link_after;
        $item_output .= '</a>';
        $item_output .= $args->after;

        $output .= apply_filters( 'walker_nav_menu_start_el', $item_output, $item, $depth, $args );
    }
}