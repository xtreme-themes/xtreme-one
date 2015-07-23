<?php

/**
 * Returning all Grids with inner and outer container
 * @since   1.6.4
 * @return  array $grids
 */
function xtreme_get_grids(){
	$grids = array(
		2 => array(
			0 => array( 'outer' => 'ym-g50 ym-gl grid-1', 'inner' => 'ym-gbox-left' ),
			1 => array( 'outer' => 'ym-g50 ym-gr grid-2', 'inner' => 'ym-gbox-right' )
		),
		3 => array(
			0 => array( 'outer' => 'ym-g33 ym-gl grid-1', 'inner' => 'ym-gbox-left' ),
			1 => array( 'outer' => 'ym-g33 ym-gl grid-2', 'inner' => 'ym-gbox' ),
			2 => array( 'outer' => 'ym-g33 ym-gr grid-3', 'inner' => 'ym-gbox-right' )
		),
		4 => array(
			0 => array( 'outer' => 'ym-g25 ym-gl grid-1', 'inner' => 'ym-gbox-left' ),
			1 => array( 'outer' => 'ym-g25 ym-gl grid-2', 'inner' => 'ym-gbox' ),
			2 => array( 'outer' => 'ym-g25 ym-gl grid-3', 'inner' => 'ym-gbox' ),
			3 => array( 'outer' => 'ym-g25 ym-gr grid-4', 'inner' => 'ym-gbox-right' ),
		),
		5 => array(
			0 => array( 'outer' => 'ym-g20 ym-gl grid-1', 'inner' => 'ym-gbox-left' ),
			1 => array( 'outer' => 'ym-g20 ym-gl grid-2', 'inner' => 'ym-gbox' ),
			2 => array( 'outer' => 'ym-g20 ym-gl grid-3', 'inner' => 'ym-gbox' ),
			3 => array( 'outer' => 'ym-g20 ym-gl grid-4', 'inner' => 'ym-gbox' ),
			4 => array( 'outer' => 'ym-g20 ym-gr grid-5', 'inner' => 'ym-gbox-right' ),
		)
	);
	return apply_filters( 'xtreme_get_grids', $grids );
}


function xtreme_get_layout_class( $tpl ) {
    $options = get_option (XF_TEMPLATES );
    if ( $options ) {
        if ( array_key_exists( $tpl, $options ) ) {
            $layout = $options[$tpl];
        } else {
            $layout = 'xf_layout-default';
        }
    } else {
            $layout = 'xf_layout-default';
    }
    return $layout;
}
/* since 1.01 */
function xtreme_layout_body_class( $classes ) {
    $options = get_option( XF_OPTIONS );
    if ( (int) $options['xc_general']['appearance']['value'] === 1 ) {
        $classes[] = " design-fullpage";
    } elseif ( ( int ) $options['xc_general']['appearance']['value'] === 0 ) {
        $classes[] = " design-blog";
    }
    if ( xtreme_is_html5() ) {
	$classes[] = "xf-html5";
    }
	if (xtreme_is_layout_2()) {
		$classes[] = "xf-layout2";
	}
    return $classes;
}
add_filter( 'body_class','xtreme_layout_body_class' );

function xtreme_do_stylesheets() {

	$stylesheet = (defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) ? 'production.css' : 'production-min.css' ;

	$last_modified = '';
	$t_string = '';
	if ( !xtreme_is_html5() ) {
		$t_string = 'type="text/css"';
	}
	$first = '/css/screen/production.css';
	//no longer using direct XF_BLOG_ID
	$live = XF_REL_OUTPUT_DIR_THEME_BASED . '/' . $stylesheet;
	$live_compat = XF_REL_OUTPUT_DIR_COMPATIBILITY . '/production.css';
	if ( file_exists( XF_THEME_DIR . $live ) || file_exists( XF_THEME_DIR . $live_compat )) {
		if (!file_exists( XF_THEME_DIR . $live )) {
			$live = $live_compat;
		}
		$last_modified = date ("Ymd-GH", filemtime(XF_THEME_DIR . $live));
		$styles = XF_THEME_URI . $live;
	} else {
		$live = $first;
		$styles = XF_THEME_URI . $live;
	}
	

	echo "<link rel='stylesheet' href='" . $styles . "?ver=" .$last_modified ."' " . $t_string . " />\n";

}
add_action('xtreme_stylesheets', 'xtreme_do_stylesheets');

function xtreme_patch() {

	$firstpatch = '/css/patches/xtreme_patch.css';
	//no longer using direct XF_BLOG_ID
	$livepatch = XF_REL_OUTPUT_DIR_THEME_BASED . '/xtreme_patch.css';

	if ( file_exists( XF_THEME_DIR . $livepatch ) ) {
		$file = XF_THEME_URI. $livepatch;
	}
	else {
		$file = XF_THEME_URI . $firstpatch;
	}

	echo "<!--[if lte IE 8]><link href='" . $file ."' rel='stylesheet' type='text/css' /><![endif]-->\n";
	do_action('xtreme_print_ie_style', XF_IE_MAJOR, XF_IE_MINOR);
}
add_action('xtreme_ie_patch', 'xtreme_patch');

function xtreme_theme_stylesheet() {
	$t_string = '';
	if ( !xtreme_is_html5() ) {
		$t_string = 'type="text/css"';
	}
	$live = XF_REL_OUTPUT_DIR_THEME_BASED . '/production.css';
	$live_compat = XF_REL_OUTPUT_DIR_COMPATIBILITY . '/production.css';
	if ( file_exists( XF_THEME_DIR . $live ) || file_exists( XF_THEME_DIR . $live_compat )) return;
	$theme_css_file = XF_CHILD_THEME_DIR . '/css/screen/theme.css';
	$theme_css_uri = XF_CHILD_THEME_URI . '/css/screen/theme.css';
	if (file_exists($theme_css_file)) {
		$last_modified = date ("Ymd-GH", filemtime($theme_css_file));
		echo "<link href='" . $theme_css_uri . "?ver=" .$last_modified ."' rel='stylesheet' " . $t_string . " />";
	}
}
add_action('xtreme_theme_stylesheet', 'xtreme_theme_stylesheet');

function xtreme_body() {
    $options = get_option(XF_OPTIONS);
    $cls = '';
    if ( ( int ) $options['xc_header']['show_logo']['value'] === 1 || ( int ) $options['xc_header']['show_logo']['value'] === 3 ) {
        $cls = 'logo';
    }
    $tpl = $GLOBALS['xtreme_template'];
    $class = xtreme_get_layout_class($tpl);
    $body_id = _xtreme_get_body_id($tpl);
    if ( $body_id === '404' ) {
        $body_id = 'error' . $body_id;
    }
    echo "<body id=\"" . $body_id . "\" class=\"" .  $cls . ' ' . join(' ', get_body_class( $class ) ) . "\">\n";
    do_action('xtreme_after_body_tag');
}

function xtreme_skiplinks() {
    $options = get_option(XF_OPTIONS);
    ?>
      <ul class="ym-skiplinks">
    <?php if ( (int) $options['xc_navigation']['primary_position']['value'] !== 0 ) : ?>
        <li><a class="ym-skip" href="#primarynav"><?php _e('Skip to primary navigation (Press Enter).', XF_TEXTDOMAIN)?></a></li>
    <?php endif; ?>
    <?php if ( (int) $options['xc_navigation']['secondary_position']['value'] !== 0) : ?>
        <li><a class="ym-skip" href="#secondarynav"><?php _e('Skip to secondary navigation (Press Enter).', XF_TEXTDOMAIN)?></a></li>
    <?php endif; ?>
        <li><a class="ym-skip" href="#content"><?php _e('Skip to content (Press Enter).', XF_TEXTDOMAIN)?></a></li>
        <?php do_action('xtreme_add_skip_link') ?>
    </ul>
    <?php
}
add_action('xtreme_after_body_tag', 'xtreme_skiplinks', 0);

function _xtreme_get_body_id( $tpl ) {
    return substr( strtolower( $tpl ), 0, -4 );
}

function xtreme_start_layout() {
	$tpl = xtreme_get_template();
	$GLOBALS['xtreme_template'] = $tpl;
	xtreme_body();
	do_action('xtreme_after_start_layout');
	do_action('xtreme_before_header');
	xtreme_header();
	do_action('xtreme_after_header');
	do_action('xtreme_before_teaser');
	xtreme_teaser();
	do_action('xtreme_after_teaser');
	do_action('xtreme_before_main');
	xtreme_start_container('main', '', 'div');
	do_action('xtreme_before_columns');
	if ( !xtreme_is_layout_2()) {
		// use filter to allow childthemes to hide xtreme_columns
		if ( ! apply_filters( 'xtreme_hide_columns', FALSE, $tpl ) )
			xtreme_columns( $tpl );
		echo "<div id='content' class='ym-col3' " . xtreme_aria_required( 'main', false ) .">";
		echo "  <div class='ym-cbox ym-clearfix'>";
		do_action('xtreme_col3_top');
	} else {
		echo "<div id='content' class='ym-col1' " . xtreme_aria_required( 'main', false ) .">";
		echo "  <div class='ym-cbox-left ym-clearfix'>";
		do_action('xtreme_layout2_col1_top');
	}
}

function xtreme_end_layout() {
	$tpl = $GLOBALS['xtreme_template'];
	if ( !xtreme_is_layout_2()) {
    do_action('xtreme_col3_bottom');
    echo "  </div>";
    echo "  <div class='ym-ie-clearing'>&nbsp;</div>";
    echo "</div>";
	} else {
		do_action('xtreme_layout2_col1_bottom');
		echo "</div></div>";
		if ( ! apply_filters( 'xtreme_hide_columns', FALSE, $tpl ) )
			xtreme_layout2_columns($tpl);
	}
    do_action('xtreme_after_columns');
    xtreme_end_container('div');
    do_action('xtreme_after_main');
    do_action('xtreme_before_footer');
    xtreme_footer();
    do_action('xtreme_after_footer');
    do_action('xtreme_before_siteinfo');
    xtreme_siteinfo();
    do_action('xtreme_after_siteinfo');
    do_action('xtreme_after_end_layout');
}

function xtreme_main_class() {
    $tpl = $GLOBALS['xtreme_template'];
    $layout = xtreme_get_layout_class($tpl);
    $current = get_option(XF_LAYOUTS);

    return isset($current[$layout]['mainclass']) ? esc_attr($current[$layout]['mainclass']['value']) : '';
}

function xtreme_start_container( $div, $role, $el='div' ) {
    $el = sanitize_key( $el );
    $cls = "class='xf-container ym-clearfix linearize-level-1" . xtreme_container_class();

    if($div === 'main') {
        $cls .= ' ' . xtreme_main_class();
	    // fallback for the old bug #173
	    $cls .= ' ' . xtreme_container_class() . xtreme_main_class();
    }
    if($div === 'primarynav' || $div === 'secondarynav') {
        $nav = substr( $div , 0, -3 );
        $cls .= " " . _xtreme_nav_stylesheet( $nav );
    }
    $cls .= "'";
    $html = "<" . $el . " id='" . $div . "' " . $cls . " " . xtreme_aria_required( $role, false ). ">\n";
    $html .= xtreme_create_appearance( 'before' );

    echo $html;
}

function xtreme_end_container( $el='div' ) {
    $html = xtreme_create_appearance( 'after' );
    $html .= "</" . $el . ">\n";

    echo $html;
}

function xtreme_columns($tpl) {
    $layout = xtreme_get_layout_class($tpl);
    $current = get_option(XF_LAYOUTS);
    $cur = isset($current[$layout]['columnlayout']) ? (int) $current[$layout]['columnlayout']['value'] : 1;
    switch( $cur ) {
        case 1:
        case 2:
	    _xtreme_column('ym-col1', $tpl);
            break;
        case 3:
        case 4:
        case 5:
	    _xtreme_column('ym-col1', $tpl);
	    _xtreme_column('ym-col2', $tpl);
            break;
    }
}

function xtreme_layout2_columns($tpl) {
    $layout = xtreme_get_layout_class($tpl);
    $current = get_option(XF_LAYOUTS);
    $cur = isset($current[$layout]['columnlayout']) ? (int) $current[$layout]['columnlayout']['value'] : 1;
    switch( $cur ) {
        case 1:
        case 2:
	    _xtreme_column('ym-col3', $tpl);
            break;
        case 3:
        case 4:
        case 5:
	    _xtreme_column('ym-col2', $tpl);
	    _xtreme_column('ym-col3', $tpl);
            break;
    }
}

function _xtreme_column( $col, $tpl ) {

	$el = 'div';
	$layout = xtreme_get_layout_class( $tpl );
	$current = get_option( XF_LAYOUTS );
	$options = get_option( XF_OPTIONS );
	$identify_col = str_replace( 'ym-', '', $col );
	
	if (!xtreme_is_layout_2()) {
		$sb = isset($current[$layout][$identify_col . 'content']) ? $current[$layout][$identify_col . 'content']['value']. '.php' : 'sidebar-one.php';
		$action_top = 'xtreme_' . $identify_col . '_top';
		$action_bottom = 'xtreme_' . $identify_col . '_bottom';
	} else {
		$sb = isset($current[$layout]['layout_2_' . $identify_col . 'content']) ? $current[$layout]['layout_2_' . $identify_col . 'content']['value']. '.php' : 'sidebar-one.php';
		$action_top = 'xtreme_layout2_' . $identify_col . '_top';
		$action_bottom = 'xtreme_layout2_' . $identify_col . '_bottom';
	}
	if (xtreme_is_html5() ) $el = $options['xc_layout']['html5_tag']['value'];
	echo '<' . $el . ' class="' . $col . '" ' . xtreme_aria_required('complementary', false) .'>';
	echo '<div class="' . $col . '_content ym-clearfix">';
	do_action($action_top);
	$located = '';
	foreach(array($sb) as $file) { 

		if ( file_exists(XF_CHILD_THEME_DIR . '/includes/sidebars/' . $file)) {
			$located = XF_CHILD_THEME_DIR . '/includes/sidebars/' . $file;
			break;
		} elseif ( file_exists(XF_THEME_DIR . '/includes/sidebars/' . $file) ) {
			$located = XF_THEME_DIR . '/includes/sidebars/' . $file;
			break;
		} else {
			$located = XF_THEME_DIR . '/includes/sidebars/sidebar-one.php';
		}
	}
	include($located);
	do_action($action_bottom);
	if ($col === 'ym-col3') {
		echo "  </div>";
		echo "  <div class='ym-ie-clearing'>&nbsp;</div>";
		echo '</' . $el . '>';
	} else {
		echo '</div></' . $el . '>';
	}
}

function xtreme_create_appearance( $position ){
    $html = "";
    $options = get_option( XF_OPTIONS );
    if ( (int) $options['xc_general']['appearance']['value'] === 1 ) {
        switch( $position ) {
            case 'before':
                $html .= "<div class='ym-wrapper'><div class='ym-wbox ym-clearfix'>";
                break;
            case 'after':
                $html .= "</div></div>\n";
                break;
        }
    }
    return $html;
}

function xtreme_start_bloglayout(){
    $html = "";
    $options = get_option( XF_OPTIONS );
    if ( (int) $options['xc_general']['appearance']['value'] === 0 ) {
        $html .= "<div class='ym-wrapper'><div class='ym-wbox'>\n";
    }
    echo $html;
}
add_action('xtreme_after_start_layout', 'xtreme_start_bloglayout', 0);

function xtreme_end_bloglayout(){
    $html = "";
    $options = get_option( XF_OPTIONS );
    if ( (int) $options['xc_general']['appearance']['value'] === 0 ) {
        $html .= "</div></div>\n";
    }
    echo $html;
}
add_action('xtreme_after_end_layout', 'xtreme_end_bloglayout',0);

function xtreme_container_class(){
    $cls = "";
    $options = get_option( XF_OPTIONS );
    if ( (int) $options['xc_general']['appearance']['value'] === 1 ) {
        $cls = " xf-fullpage";
    } elseif ( (int) $options['xc_general']['appearance']['value'] === 0 ) {
        $cls = " xf-blog";
    }
    return $cls;
}

function xtreme_aria_required( $role, $echo = true ) {
    $html = '';
    if( !empty ( $role ) ) {
        $options = get_option( XF_OPTIONS );
        if( true === $options['xc_general']['aria_required']['value'] && !empty ( $role ) ) {
            $html = 'role="' . esc_attr( $role ). '"';
        }
    }
    if ( $echo ) {
        echo $html;
    } else {
        return $html;
    }
}

function xtreme_image_header() {
	if (current_theme_supports('xtreme-image-header-area')) {
		ob_start();
		dynamic_sidebar( 'image-header-area' );
		$content = ob_get_clean();
		if (!empty($content)) {
			xtreme_start_container('imageheader', 'banner', xtreme_is_html5() ? 'section' : 'div');
			echo $content;
			xtreme_end_container(xtreme_is_html5() ? 'section' : 'div');
		}
	}
	do_action('xtreme_after_image_header');
}
add_action('xtreme_before_header', 'xtreme_image_header');

function xtreme_header() {
    $el = 'div';
    $tpl = $GLOBALS['xtreme_template'];
    $layout = xtreme_get_layout_class( $tpl );
    $options = get_option( XF_LAYOUTS );
    $opt = get_option(XF_OPTIONS);
    if ( xtreme_is_html5() ) $el = $opt['xc_header']['html5_tag']['value'];
    if( isset($options[$layout]['use_header']) && true === $options[$layout]['use_header']['value'] ) {
        xtreme_start_container('header', 'banner', $el);
        do_action('xtreme_header_top');
        if( (int) $opt['xc_header']['columns']['value'] === 1 ) : ?>
            <div class="ym-col1"><div class="ym-cbox-left ym-clearfix"><?php do_action('xtreme_header_col1') ?></div></div>
            <div class="ym-col3"><div class="ym-cbox ym-clearfix"><?php do_action('xtreme_header_col3'); ?></div></div>
    <?php else:
            do_action('xtreme_header_col3');
        endif;
        do_action('xtreme_header_bottom');
        xtreme_end_container( $el );
    }
}

function xtreme_header_widget_area() {
    $options = get_option(XF_OPTIONS);
    if ( ( int ) $options['xc_header']['columns']['value'] === 1 && ( int ) $options['xc_header']['widget_area']['value'] === 1) {
        if ( is_active_sidebar( 'header-col1' ) ) :
            dynamic_sidebar( 'header-col1' );
        else:
           do_action('xtreme_blindtext');
        endif;
    }
}
add_action('xtreme_header_col1', 'xtreme_header_widget_area', 3 );

function xtreme_blogtitle() {
    $options = get_option(XF_OPTIONS);
    $val = ( int ) $options['xc_header']['show_logo']['value'];
    if ( $val === 0 || $val === 1 ) {
        //with link
        $before = '<a href="' . home_url() . '" title="' . esc_attr__( 'Jump to Homepage', XF_TEXTDOMAIN ) . '">';
        $after = '</a>';
    } elseif ( $val === 2 || $val === 3 ) {
        $before = '<span>';
        $after = '</span>';
    }
    $titletag = array( 'h1', 'h2', 'h3', 'div', 'p' );
    $ttag = $options['xc_header']['blogtitle_tag']['value'];
    if ( !in_array( $ttag, $titletag ) ) {
        $ttag = 'h1';
    }
    echo '<' . $ttag .' class="blogtitle">' . $before . get_bloginfo('name') . $after . '</' . $ttag . '>';
}
add_action('xtreme_header_col3', 'xtreme_blogtitle', 3 );

function xtreme_blog_description() {
    $html = '';
    $options = get_option( XF_OPTIONS );
    $dtag = $options['xc_header']['blogdescription_tag']['value'];
    $desc_tags = array( 'p', 'h1', 'h2', 'h3', 'div' );
    if ( !in_array( $dtag, $desc_tags ) ) {
        $dtag = 'p';
    }
    if ( (int) $options['xc_header']['blog_description']['value'] === 1 ) {
        $html .= '<' . $dtag . ' class="description">' . get_bloginfo( 'description' ) . '</' . $dtag . '>';
    }
    echo $html;
}
add_action('xtreme_header_col3', 'xtreme_blog_description', 5 );

function xtreme_teaser() {
    $el = 'div';
    $options = get_option( XF_LAYOUTS );
    $opt = get_option( XF_OPTIONS );
    $tpl = $GLOBALS['xtreme_template'];
    $layout = xtreme_get_layout_class( $tpl );
    if ( xtreme_is_html5() ) $el = $opt['xc_teaser']['html5_tag']['value'];
    if (isset($options[$layout]['use_teaser']) && true === $options[$layout]['use_teaser']['value'] ) {
        xtreme_teaser_footer( 'teaser', 'complementary', $el );
    }
}

function xtreme_footer() {
    $el = 'div';
    $tpl = $GLOBALS['xtreme_template'];
    $layout = xtreme_get_layout_class( $tpl );
    $options = get_option( XF_LAYOUTS );
    $opt = get_option( XF_OPTIONS );
    if ( xtreme_is_html5() ) $el = $opt['xc_footer']['html5_tag']['value'];
    if ( isset($options[$layout]['use_footer']) && true === $options[$layout]['use_footer']['value'] ) {
        xtreme_teaser_footer( 'footer', 'complementary', $el );
    }
}

function xtreme_siteinfo() {
    $el = 'div';
    $tpl = $GLOBALS['xtreme_template'];
    $layout = xtreme_get_layout_class( $tpl );
    $options = get_option( XF_LAYOUTS );
    $opt = get_option( XF_OPTIONS );
    if ( xtreme_is_html5() ) $el = $opt['xc_siteinfo']['html5_tag']['value'];
    if ( isset($options[$layout]['use_siteinfo']) && true === $options[$layout]['use_siteinfo']['value'] ) {
        xtreme_start_container( 'siteinfo', 'contentinfo', $el );
        do_action( 'xtreme_siteinfo_top' );
        if ( ( int ) $opt['xc_siteinfo']['columns']['value'] === 1 ) : ?>
            <div class="ym-col1"><div class="ym-cbox-left ym-clearfix"><?php do_action( 'xtreme_siteinfo_col1' ) ?></div></div>
            <div class="ym-col3"><div class="ym-cbox ym-clearfix"><?php do_action( 'xtreme_siteinfo_col3' ) ?></div></div>
    <?php else:
            do_action( 'xtreme_siteinfo_col3' );
        endif;
        do_action( 'xtreme_siteinfo_bottom' );
        xtreme_end_container( $el );
    }
}

function xtreme_siteinfo_widget_area() {
    $options = get_option(XF_OPTIONS);
    if ( (int) $options['xc_siteinfo']['columns']['value'] === 1 && (int) $options['xc_siteinfo']['widget_area']['value'] === 1) {
        if ( is_active_sidebar('siteinfo-col1') ) :
            dynamic_sidebar('siteinfo-col1');
        else:
            do_action('xtreme_blindtext');
        endif;
    }
}
add_action('xtreme_siteinfo_col1', 'xtreme_siteinfo_widget_area');

if ( !function_exists('xtreme_siteinfo_content') ) {
    function xtreme_siteinfo_content() {
    	$options = get_option(XF_OPTIONS);
    ?>
<p class="skip_top"><a href="#content" title="<?php _e('Skip to top', XF_TEXTDOMAIN) ?>"><span><?php _e('Skip to top', XF_TEXTDOMAIN) ?></span>&nbsp;</a></p>
<p class="copyright"><?php echo xtreme_copyright() . ' ' . get_bloginfo('name') ?>
<?php /*
 YAML Condition: For the free use of the YAML framework, a backlink to the YAML homepage (http://www.yaml.de) in a suitable place (e.g.: footer of the website or in the imprint) is required.
 */ ?>
 | <a href="http://www.yaml.de/">YAML</a>
 | <?php
	if ( ! isset( $options[ 'xc_footer' ][ 'hide_powered_by' ] ) || ( isset( $options[ 'xc_footer' ][ 'hide_powered_by' ] ) && TRUE != $options[ 'xc_footer' ][ 'hide_powered_by' ]['value'] ) ) :
		?><a href="http://wordpress.org/">WordPress</a> | <a href="https://github.com/xtreme-themes/xtreme-one" title="Xtreme One Professional WordPress Framework">powered by Xtreme One</a> |
	<?php endif; ?>
	<?php wp_loginout(); ?>
</p>
    <?php xtreme_show_queries();
    }
}
add_action('xtreme_siteinfo_col3', 'xtreme_siteinfo_content', 3 );

function xtreme_show_queries() {
    $options = get_option(XF_OPTIONS);
    if ( (bool) $options['xc_siteinfo']['debug']['value'] === true) : ?>
        <p><?php echo get_num_queries(); ?> queries. <?php timer_stop(1); ?> seconds.</p>
    <?php endif;
}

function xtreme_copyright() {
    $options = get_option(XF_OPTIONS);
    $msg = '';
    if ( true === $options['xc_siteinfo']['copyright']['value'] ) {
        if ( $options['xc_siteinfo']['copyright_start']['value'] != '' ) {
            $year = date('Y');
            if ( $options['xc_siteinfo']['copyright_start']['value'] === $year ) {
                $msg = 'Copyright &copy; ' . $year;
            } else {
                $msg = 'Copyright &copy; ' . esc_html( $options['xc_siteinfo']['copyright_start']['value'] ) . ' - ' . $year;
            }
        }
    }
    return $msg;
}

function xtreme_get_subcolumns_array() {
    $subcolumns = array(
        array('group' => 1 , 'value' => 0, 'label' => '100%', 'outer' => '', 'inner' => ''),
        array('group' => 2 , 'value' => 1, 'label' => '20% | 80%', 'outer' => array('ym-g20', 'ym-g80'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 2, 'label' => '25% | 75%', 'outer' => array('ym-g25', 'ym-g75'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 3, 'label' => '33% | 66%', 'outer' => array('ym-g33', 'ym-g66'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 4, 'label' => '38% | 62%', 'outer' => array('ym-g38', 'ym-g62'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 5, 'label' => '40% | 60%', 'outer' => array('ym-g40', 'ym-g60'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 6, 'label' => '50% | 50%', 'outer' => array('ym-g50', 'ym-g50'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 7, 'label' => '60% | 40%', 'outer' => array('ym-g60', 'ym-g40'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 8, 'label' => '62% | 38%', 'outer' => array('ym-g62', 'ym-g38'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 9, 'label' => '66% | 33%', 'outer' => array('ym-g66', 'ym-g33'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 10, 'label' => '75% | 25%', 'outer' => array('ym-g75', 'ym-g25'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 2 , 'value' => 11, 'label' => '80% | 20%', 'outer' => array('ym-g80', 'ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 12, 'label' => '20% | 20% | 60%', 'outer' => array('ym-g20', 'ym-g20', 'ym-g60'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 13, 'label' => '20% | 60% | 20%', 'outer' => array('ym-g20', 'ym-g60', 'ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 14, 'label' => '60% | 20% | 20%', 'outer' => array('ym-g60', 'ym-g20', 'ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 15, 'label' => '20% | 40% | 40%', 'outer' => array('ym-g20', 'ym-g40', 'ym-g40'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 16, 'label' => '40% | 20% | 40%', 'outer' => array('ym-g40', 'ym-g20', 'ym-g40'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 17, 'label' => '40% | 40% | 20%', 'outer' => array('ym-g40', 'ym-g40', 'ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 18, 'label' => '25% | 25% | 50%', 'outer' => array('ym-g25', 'ym-g25', 'ym-g50'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 19, 'label' => '25% | 50% | 25%', 'outer' => array('ym-g25', 'ym-g50', 'ym-g25'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 20, 'label' => '50% | 25% | 25%', 'outer' => array('ym-g50', 'ym-g25', 'ym-g25'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 3 , 'value' => 21, 'label' => '33% | 33% | 33%', 'outer' => array('ym-g33', 'ym-g33', 'ym-g33'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox-right')),
        array('group' => 4 , 'value' => 22, 'label' => '20% | 20% | 20% | 40%', 'outer' => array('ym-g20', 'ym-g20','ym-g20', 'ym-g40'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox','ym-gbox-right')),
        array('group' => 4 , 'value' => 23, 'label' => '20% | 20% | 40% | 20%', 'outer' => array('ym-g20', 'ym-g20', 'ym-g40', 'ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox','ym-gbox-right')),
        array('group' => 4 , 'value' => 24, 'label' => '20% | 40% | 20% | 20%', 'outer' => array('ym-g20', 'ym-g40', 'ym-g20', 'ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox','ym-gbox-right')),
        array('group' => 4 , 'value' => 25, 'label' => '40% | 20% | 20% | 20%', 'outer' => array('ym-g40', 'ym-g20', 'ym-g20', 'ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox','ym-gbox-right')),
        array('group' => 4 , 'value' => 26, 'label' => '25% | 25% | 25% | 25%', 'outer' => array('ym-g25', 'ym-g25', 'ym-g25', 'ym-g25'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox','ym-gbox-right')),
        array('group' => 5 , 'value' => 27, 'label' => '20% | 20% | 20% | 20% | 20%', 'outer' => array('ym-g20', 'ym-g20', 'ym-g20', 'ym-g20','ym-g20'), 'inner' => array('ym-gbox-left', 'ym-gbox', 'ym-gbox', 'ym-gbox','ym-gbox-right'))
    );
    return $subcolumns;
}

function xtreme_container_subcol_count($container) {
    $options = get_option(XF_OPTIONS);
    $need = (int) $options['xc_' . $container]['subcolumns']['value'];
    $subcols = xtreme_get_subcolumns_array();
    $count = $subcols[$need]['group'];

    return (int) $count;
}

function xtreme_subcolumns_wrapper($container,$count) {
    $options = get_option(XF_OPTIONS);
    $need = (int) $options['xc_' .$container]['subcolumns']['value'];
    $subcols = xtreme_get_subcolumns_array();
    $txtalign = esc_attr('txt-' . $options['xc_' .$container]['txtalign_'.$count]['value']);
    return "<div class='" . $txtalign . " " . $subcols[$need]['outer'][$count-1] . "'>\n<div class='sync " . $subcols[$need]['inner'][$count-1] . "'>\n";
}

function xtreme_teaser_footer( $container, $role, $el ) {
    xtreme_start_container( $container, $role, $el );
	do_action( 'xtreme_' . $container . '_top' );
    $count = xtreme_container_subcol_count( $container );
    if ( $count == 1 ) :
        do_action( $container . '_subcol', 1 );
    endif;

    if ( $count > 1) :
        echo '<div class="ym-grid ym-equalize linearize-level-1">';
        echo xtreme_subcolumns_wrapper( $container, 1 );
        do_action( $container . '_subcol', 1 );
        echo '</div></div>';
    endif;

    for ( $i = 2; $i <= 5; $i++ ) {
        if ( $count >= $i ) :
            echo xtreme_subcolumns_wrapper( $container, $i );
            do_action( $container . '_subcol', $i );
            echo '</div></div>';
        endif;
    }
    if ( $count > 1 ) :
        echo '</div>';
    endif;
	do_action( 'xtreme_' . $container . '_bottom' );
    xtreme_end_container( $el );
}
function xtreme_teaser_subcol($col) {
    $content_override = apply_filters('teaser_subcol_content_override', false, $col);
    if ($content_override !== false) {
        echo $content_override;
        return;
    }
    $content_override = apply_filters('teaser_subcol_widget_area_override', false, $col);
    if(is_active_sidebar($content_override)) {
        dynamic_sidebar($content_override);
		return;
    }

    if ( is_active_sidebar('teaser-widget-area-' . $col) ) :
        dynamic_sidebar( 'teaser-widget-area-' . $col );
    else:
        //echo '<h5>' . sprintf(__('Teaser Widget Area %s', XF_TEXTDOMAIN), $col) . '</h5>';
        do_action('xtreme_blindtext');
    endif;
}
add_action('teaser_subcol', 'xtreme_teaser_subcol');

function xtreme_footer_subcol($col) {
    $content_override = apply_filters('footer_subcol_content_override', false, $col);
    if ($content_override !== false) {
        echo $content_override;
        return;
    }
    $content_override = apply_filters('footer_subcol_widget_area_override', false, $col);
    if(is_active_sidebar($content_override)) {
        dynamic_sidebar($content_override);
    return;
    }

    if ( is_active_sidebar('footer-widget-area-' . $col) ) :
        dynamic_sidebar( 'footer-widget-area-' . $col );
    else:
         do_action('xtreme_blindtext');
    endif;
}
add_action('footer_subcol', 'xtreme_footer_subcol');

function xtreme_register_dynamic_sidebars() {
    $containers = array( 'teaser' => false , 'footer' => false );
    $layouts  = get_option( XF_LAYOUTS );
    $html5 = xtreme_is_html5();
    if ( !$html5 ) {
        $el = 'div';
    } else {
        $el = 'section';
    }
	
	if (current_theme_supports('xtreme-image-header-area')) {
		register_sidebar( array(
			'name' => __( 'Image Header Area' , XF_TEXTDOMAIN ),
			'id' => 'image-header-area',
			'description' => __( 'Additional Header Widget Area to be used primary with "Xtreme Media Flex Slider" for Image Headers' , XF_TEXTDOMAIN ),
			'before_widget' => '<div id="%1$s" class="widget %2$s ym-cbox">',
			'after_widget' => '</div>',
			'before_title' => '<h3 class="widget-title">',
			'after_title' => '</h3>'
		));	
	}	
	
    foreach( (array)$layouts as $layout ){
		if (!is_array($layouts)) return;
        if( $layout['use_teaser']['value'] === true ) {
            $containers['teaser'] = true;
        }
        if( $layout['use_footer']['value'] === true ) {
            $containers['footer'] = true;
        }
    }

    $options = get_option( XF_OPTIONS );
    foreach ( $containers as $container => $value ) {
        if( $value === true) {
            $need = (int) $options['xc_' .$container]['subcolumns']['value'];
            if ( !$html5 ) {
                $tag_before_widget = $options['xc_' .$container]['tag_before_widget']['value'];
            } else {
                $tag_before_widget = 'section';
            }
            $tag_title = $options['xc_' .$container]['tag_before_title']['value'];
            $tag_next = "";
            $tag_next_end = "";
            if ( $tag_before_widget == 'ul' ) {
                $tag_next = '<li>';
                $tag_next_end = '</li>';
            }

            $subcols = xtreme_get_subcolumns_array();
            $count = $subcols[$need]['group'];

            for ( $i = 1; $i <= $count; $i++ ) {
                $wid = $container . '-widget-area-' . $i;
                register_sidebar( array (
                    'name' => sprintf( '%s Widget Area %s', ucfirst($container) , $i ),
                    'id' => $wid,
                    'description' => sprintf( __( 'The %s widget area %s' , XF_TEXTDOMAIN ),$container, $i ),
                    'before_widget' => '<' . $tag_before_widget . ' id="%1$s" class="widget %2$s ym-cbox">' . $tag_next,
                    'after_widget' => $tag_next_end . '</' . $tag_before_widget . '>',
                    'before_title' => '<' . $tag_title . ' class="widget-title">',
                    'after_title' => '</' . $tag_title . '>',
                ) );
            }
        }
    }
 
    foreach ( array( 'header', 'siteinfo' ) as $box ) {
        if( (int) $options['xc_' . $box]['columns']['value'] === 1 && (int) $options['xc_' . $box]['widget_area']['value'] === 1 ) {
            register_sidebar( array(
                'name' => sprintf( __( '%s Column 1', XF_TEXTDOMAIN), ucfirst( $box ) ),
                'id' => $box . '-col1',
                'description' => sprintf( __( '%1s in your %2s.' , XF_TEXTDOMAIN ), __('Column 1', XF_TEXTDOMAIN), $box ),
                'before_widget' => '<' . $el . ' id="%1$s" class="widget %2$s ym-cbox">',
                'after_widget' => '</' . $el . '>',
                'before_title' => '<h4 class="widget-title">',
                'after_title' => '</h4>'
            ));
        }
    }

   register_sidebar( array(
        'name' => __( 'Widgetized Homepage' , XF_TEXTDOMAIN ),
        'id' => 'widgetized-homepage',
        'description' => __( 'Widget area on the Widgetized Homepage Template' , XF_TEXTDOMAIN ),
        'before_widget' => '<' . $el . ' id="%1$s" class="widget %2$s ym-cbox">',
        'after_widget' => '</' . $el . '>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>'
    ));
    if ( !$html5 ) {
		$t = xtreme_get_sidebar_tag();
		if($t === 'ul') {
			$elem = 'li';
			$sb3 = 'div';
		} else {
			$elem = 'div';
			$sb3 = 'div';
		}
    } else {
        $elem = 'section';
	   $sb3 = 'section';
    }
    register_sidebar( array(
        'name' => __( 'Sidebar One' , XF_TEXTDOMAIN ),
        'id' => 'sidebar-one',
        'description' => __( 'Sidebar One' , XF_TEXTDOMAIN ),
        'before_widget' => '<' . $elem . ' id="%1$s" class="widget %2$s ym-cbox">',
        'after_widget' => '</' . $elem . '>',
        'before_title' => '<' . xtreme_get_sidebar_headline_tag() . ' class="widget-title">',
        'after_title' => '</' . xtreme_get_sidebar_headline_tag() . '>'
    ));
    register_sidebar( array(
        'name' => __( 'Sidebar Two' , XF_TEXTDOMAIN ),
        'id' => 'sidebar-two',
        'description' => __( 'Sidebar Two' , XF_TEXTDOMAIN ),
        'before_widget' => '<' . $elem . ' id="%1$s" class="widget %2$s ym-cbox">',
        'after_widget' => '</' . $elem . '>',
        'before_title' => '<' . xtreme_get_sidebar_headline_tag() . ' class="widget-title">',
        'after_title' => '</' . xtreme_get_sidebar_headline_tag() . '>'
    ));
    register_sidebar( array(
        'name' => __( 'Sidebar Three Top' , XF_TEXTDOMAIN ),
        'id' => 'sidebar-three-top',
        'description' => __( 'Sidebar Three Top' , XF_TEXTDOMAIN ),
        'before_widget' => '<' . $sb3 . ' id="%1$s" class="widget %2$s ym-cbox">',
        'after_widget' => '</' . $sb3 . '>',
        'before_title' => '<' . xtreme_get_sidebar_headline_tag() . ' class="widget-title">',
        'after_title' => '</' . xtreme_get_sidebar_headline_tag() . '>'
    ));
    register_sidebar( array(
        'name' => __( 'Sidebar Three Bottom Left' , XF_TEXTDOMAIN ),
        'id' => 'sidebar-three-bottom-left',
        'description' => __( 'Sidebar Three Bottom Left' , XF_TEXTDOMAIN ),
        'before_widget' => '<' . $sb3 . ' id="%1$s" class="widget %2$s ym-cbox">',
        'after_widget' => '</' . $sb3 . '>',
        'before_title' => '<' . xtreme_get_sidebar_headline_tag() . ' class="widget-title">',
        'after_title' => '</' . xtreme_get_sidebar_headline_tag() . '>'
    ));
    register_sidebar( array(
        'name' => __( 'Sidebar Three Bottom Right' , XF_TEXTDOMAIN ),
        'id' => 'sidebar-three-bottom-right',
        'description' => __( 'Sidebar Three Bottom Right' , XF_TEXTDOMAIN ),
        'before_widget' => '<' . $sb3 . ' id="%1$s" class="widget %2$s ym-cbox">',
        'after_widget' => '</' . $sb3 . '>',
        'before_title' => '<' . xtreme_get_sidebar_headline_tag() . ' class="widget-title">',
        'after_title' => '</' . xtreme_get_sidebar_headline_tag() . '>'
    ));
	
    do_action('xtreme_childtheme_sidebars');
}
add_action( 'widgets_init', 'xtreme_register_dynamic_sidebars' );

function xtreme_frontend_favicon() {
    echo "<link rel='icon' href='" . esc_url(xtreme_locate_file_from_uri( array('images/favicon.ico'))) . "' type='image/x-icon' />\n";
    echo "<link rel='shortcut icon' href='" . esc_url(xtreme_locate_file_from_uri( array('images/favicon.ico'))) . "' type='image/x-icon' />\n";
}

/**
 * Removes the default styles that are packaged with the Recent Comments widget.
 */
function xtreme_remove_recent_comments_style() {
    add_filter( 'show_recent_comments_widget_style', '__return_false' );
}
add_action( 'widgets_init', 'xtreme_remove_recent_comments_style' );

function xtreme_even_odd( $i ) {
    $i = intval( $i );
    if ( $i % 2 ) {
        return 'odd';
    } else {
        return 'even';
    }
}

/**
 * xtreme sidebar start tag
 *
 * @since    0.1
 * @created 13.01.2014, cb
 * @updated 13.01.2014, cb
 *
 * @param   Array $attributes
 *
 * @return  Void
 */
function xtreme_sidebar_widget_start_tag( Array $attributes = array() ) {
	$tag = !xtreme_is_html5() ? xtreme_get_sidebar_tag() : 'div';

	$defaults       = array( 'class' => 'sidebar' );
	$attributes     = wp_parse_args( $attributes, $defaults );
	$attributes     = apply_filters( 'xtreme_sidebar_widget_start_tag_attributes', $attributes );

	$attr = '';
	foreach( $attributes as $k => $v ){
		$attr .= $k . '="' . esc_attr( $v ) . '" ';
	}
	$html = '<' . $tag . ' ' . $attr . '>';

	echo $html;
}

function xtreme_sidebar_widget_end_tag() {
	$tag = !xtreme_is_html5() ? xtreme_get_sidebar_tag() : 'div';
	$html = '</' . $tag . '>';
	echo $html;
}

function xtreme_get_sidebar_tag() {
	$options = get_option( XF_OPTIONS );
	$t = $options['xc_layout']['sidebar_tag']['value'];
	return $t;
}

function xtreme_is_html5() {
	$options = get_option( XF_OPTIONS );
	if( $options['xc_general']['html5']['value'] == 1) {
		return true;
	} else {
		return false;
	}
}

function xtreme_html_tag_classes() {
	$classes = (array)apply_filters('xtreme_html_tag_classes', array());
	if (count($classes)) {
		echo ' class="'.implode(' ', array_unique($classes)).'"';
	}
}

function xtreme_html5_head() {
    ?>
<!DOCTYPE html>
<html <?php language_attributes(); ?><?php xtreme_html_tag_classes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ) ?>" />
    <?php
}

function xtreme_xhtml_head() {
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes() ?><?php xtreme_html_tag_classes(); ?>>
<head profile="http://gmpg.org/xfn/11">
<meta http-equiv="Content-Type" content="<?php bloginfo( 'html_type' ) ?>; charset=<?php bloginfo( 'charset' ) ?>" />
    <?php
}

function xtreme_setup_header() {
	if ( !xtreme_is_html5() ) {
	    add_action( 'xtreme_document_head', 'xtreme_xhtml_head' );
	} else {
	    add_action( 'xtreme_document_head', 'xtreme_html5_head' );
	}
}
add_action('xtreme_setup_theme', 'xtreme_setup_header');

function xtreme_get_sidebar_headline_tag() {
    $options = get_option( XF_OPTIONS );
    $tag = $options['xc_layout']['sidebar_headline_tag']['value'];
    $tags =  array( 'h3', 'h4', 'h5', 'h6' );
    if ( !in_array( $tag, $tags ) ) {
        $tag = 'h5';
    }
    return $tag;
}

function xtreme_widget_area_blindtext() {
    if ( is_user_logged_in() ) {
        echo '<h5>' . __( 'Widget Area', XF_TEXTDOMAIN ) . '</h5>';
    }
}
add_action( 'xtreme_blindtext', 'xtreme_widget_area_blindtext' );

function xtreme_sidebar_one_blindtext() {
    if ( is_user_logged_in() ) {
        $tag = xtreme_get_sidebar_headline_tag();
        echo '<' . $tag . '>' . __( 'Sidebar One', XF_TEXTDOMAIN ) . '</' . $tag . '>';
    }
}
add_action( 'xtreme_sidebar_one', 'xtreme_sidebar_one_blindtext' );

function xtreme_sidebar_two_blindtext() {
    if ( is_user_logged_in() ) {
        $tag = xtreme_get_sidebar_headline_tag();
        echo '<' . $tag . '>' . __( 'Sidebar Two', XF_TEXTDOMAIN ) . '</' . $tag . '>';
    }
}
add_action( 'xtreme_sidebar_two', 'xtreme_sidebar_two_blindtext' );

function xtreme_post_socials() {
	do_action('xtreme_social_template_tag');
}

function xtreme_is_layout_2() {
	$options = get_option( XF_OPTIONS );
	return ( bool ) $options['xc_general']['layout_2']['value'];
}

/**
 * This functions returns true, if the responsive feature is activated, otherwise false
 * @since   1.6
 * @uses    get_option
 * @return  Boolean
 */
function xtreme_is_responsive(){
	$options = get_option( XF_OPTIONS );
	return ( bool ) $options['xc_general']['responsive']['value'];
}

function xtreme_get_template_part( $templatename = 'content' ) {
	$templatename = apply_filters( 'xtreme_loop_templatename', $templatename );
	$docmode = '';
	$f = '';
	if( xtreme_is_html5() ) {
		$docmode = 'html5-';
	}
	if( current_theme_supports( 'post-formats' ) ) {
		$f = get_post_format();
	}
	get_template_part( '/includes/posttemplates/' . $docmode . $templatename, $f );
}

function xtreme_get_comment_ping_title_tag() {
	$options = get_option( XF_OPTIONS );
	$t = $options['xc_comments']['comment_ping_headline_tag']['value'];
	return $t;
}
