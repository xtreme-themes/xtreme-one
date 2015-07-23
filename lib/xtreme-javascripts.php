<?php

require_once(dirname(dirname(__FILE__)).'/js/load.php');

class Xtreme_Script_Manager {

	function __construct() {
		$this->is_fontend_rendering = !is_admin();
		if($this->is_fontend_rendering) {
			//inside comment form processing, will always be called, comments are open here
			add_action('comment_form_before', array(&$this, 'on_ensure_comments_open_scripts'));
			//inside comment form processing, will always be called, comments are open here
			add_action('comment_form_comments_closed', array(&$this, 'on_ensure_comments_closed_scripts'));

			//hook into wp_head for html js class to enable as fast as possible js depending css
			add_action('wp_head', array(&$this, 'on_wp_head_js_class'), 9999);

			//hook scripting engine to put out our special document ready script
			add_action('wp_footer', array(&$this, 'on_wp_print_footer_scripts'), 0);
			add_action('wp_footer', array(&$this, 'on_wp_print_footer_scripts'), 2000);
			add_action('shutdown', array(&$this, 'on_shutdown_define_queries'), 0);
		}
		else{
			add_action('admin_footer', array(&$this, 'on_wp_print_footer_scripts'), 0);
			add_action('admin_print_footer_scripts', array(&$this, 'on_wp_print_footer_scripts'), 0);
			add_action('admin_footer', array(&$this, 'on_wp_print_footer_scripts'), 99);
			add_action('admin_print_footer_scripts', array(&$this, 'on_wp_print_footer_scripts'), 99);
		}
		$this->skip_compression = ( ini_get('zlib.output_compression') || 'ob_gzhandler' == ini_get('output_handler') );

		//create the script catalog
		$this->catalog = new Xtreme_Script_Collection(XF_FRONT_SCRIPTS, XF_THEME_URI.'/js');

		//define script localizations
		$this->script_localizations = array(
		);

		//define script specific document ready initializer
		$this->script_document_ready = array();
		$this->script_document_ready['xtreme-accessible-tabs'] =
			<<<EOD
					//accessible tabs support
		$.each(xtreme_accessible_tabs.data, function(k, v) {
			$('#'+k+'> div').addClass('jquery_tabs').accessibleTabs({
				tabbody:	'.tab-content',
				tabhead:	'h5',
				syncheights: v.syncheights,
				fx:			v.fx,
				fxspeed:	v.fxspeed ,
				autoAnchor: true
			});
		});		
EOD;
		$this->script_document_ready['comments_closed_scripts'] =
			<<<EOD
					//tabbed comments support
		var cpt = $('#comments').find('#comments-title')[0].nodeName;
		if($('#comments .tab-content').size() > 1){
			$('#comments').addClass('jquery_tabs').accessibleTabs({tabbody:'.tab-content',tabhead:cpt,fx:'fadeIn',fxspeed:'slow',autoAnchor: true});
		}
EOD;
		$this->script_document_ready['xtreme-featurelist'] =
			<<<EOD
					//featurelist support
		$.each(xtreme_featurelist.data, function(k, v) {	
			$.featureList($('#x-'+k+' .fl-tabs li a'), $('#x-'+k+' .fl-output li'), v);
		});
EOD;
		$this->script_document_ready['xtreme-coin-slider'] =
			<<<EOD
					//coin slider support
		$.each(xtreme_coin_slider.data, function(k, v) {
			$('#x-'+k).coinslider(v);
		});
EOD;
		$this->script_document_ready['xtreme-jqfancy-slider'] =
			<<<EOD
					//jqfancy slider support
		$.each(xtreme_jqfancy_slider.data, function(k, v) {
			$('#x-'+k).jqFancyTransitions(v);
		});
EOD;
		$this->script_document_ready['xtreme-flexslider'] =
			<<<EOD
					//flexslider support
		$.each(xtreme_flexslider.data, function(k, v) {
			$('#x-'+k).flexslider(v);
		});
EOD;
		$this->script_document_ready['xtreme-carousel'] =
			<<<EOD
					//carousel support - needs loaded images for dynamic heights
		$(window).load(function () {
			$.each(xtreme_carousel.data, function(k, v) {
				$('#x-'+k).carouFredSel({
					infinite: v.infinite,
					circular: v.circular,
					direction: v.direction,
					items:{
						visible: v.items_visible,
						height: v.items_height,
						width: v.items_width
					},
					scroll:{
						items: v.scroll_items,
						fx: v.scroll_fx,
						pauseOnHover: v.scroll_pauseonhover,
						duration: v.scroll_duration
					},
					prev:{
						button:'#prev-'+k,
						key: v.prev_key
					},
					next:{
						button:'#next-'+k,
						key: v.next_key
					},
					pagination:{
						container:'#pag-'+k,
						keys: v.pagination_keys
					},
					auto:{
						play: v.auto_play,
						delay: v.auto_delay
					},
					onCreate: function(items, sizes) {
						$('body').bind('xtreme_zoomed', function() { 
							var cw = $('#'+k).innerWidth() - $('#'+k+' .caroufredsel_wrapper').outerWidth();
							$('#'+k).css('padding-left', (cw/2));
						});
						$('body').trigger('xtreme_zoomed');
						$('#'+k).css({'visibility' : 'visible'});
					}
				});
			});
			$('#main .ym-col1, #main .ym-col2, #main .ym-col3').syncHeight();
		});
EOD;
		$this->script_document_ready['xtreme-fancybox'] =
			<<<EOD
					//original fancy box support
		if (xtreme_fancybox.data.settings.fancybox) {
			$('a.fancybox').fancybox(xtreme_fancybox.data.settings.options);
		}
		
		//advanced WordPress galleries
		if(xtreme_fancybox.data.settings.gallery) {
			$('.gallery').each(function(i, g) {
				var rel=$(g).attr('id');
				$('a', $(g)).each(function(j, a) {
					if($(a).attr('href').match(/\.(jpg|gif|png|bmp|jpeg)(.*)?$/i)) {
						$(a).addClass('xf-fancybox').attr('rel', rel);
					}
				});
			});
		}
		
		//image viewer support
		$('a.xf-fancybox').fancybox(xtreme_fancybox.data.settings.options);
EOD;
		$this->script_document_ready['xtreme-low-barrier'] =
			<<<EOD
					//low barrier scripting
		$.each(xtreme_low_barrier.data, function(k, v) {
			$('#'+k).xtremeLowBarrier(v);
		});
EOD;
		//storage for all necessary footer inlays
		$this->footer_data = array();
		$this->footer_l10n = array();
		$this->footer_initializer = array();
		$this->footer_turn = 0;

		//patch conditionally enqueued scripts
		add_action('init', array(&$this, 'on_check_conditionals'));
	}

	function on_wp_head_js_class() {
		if (XTOPT_COMPRESSION_HTML_MODE == 0) {
			echo '<script type="text/javascript">document.documentElement.className = document.documentElement.className.length ? document.documentElement.className + " js" : "js";</script>';
		}
		else {
			echo "<html_js_class>";
		}
	}

	function on_shutdown_define_queries() {
		define('XTOPT_NUM_SQL_QUERIES', get_num_queries());
	}

	function on_lazy_gravatar($avatar, $id_or_email, $size, $default, $alt) {
		if($this->is_fontend_rendering && ($this->proxy_gravatars || $this->lazy_gravatars)) {
			$file = xtreme_locate_file_from_uri(array('images/xtreme-avatar-lazy.gif'));
			if (preg_match("/src=('|\")([^'^\"]*)('|\")/", $avatar, $hits)) {
				$name = $hits[2];
				$src = $hits[2];
				if($this->proxy_gravatars) {
					$data = md5(dirname(dirname(__FILE__))).'|'.$name;
					$proxy = XF_THEME_URI.'/images/gravatar.php?data='.base64_encode($data);
					if($this->lazy_gravatars) {
						$name = $proxy;
					}else{
						$src = $proxy;
						$name = '';
					}
				}
				if($this->lazy_gravatars) {
					$src = $file;
					$this->_ensure('xtreme-lazy-gravatars');
				}
				$avatar = preg_replace("/src=('|\")([^'^\"]*)('|\")/", "src='$src' longdesc='$name'", $avatar);
			}
		}
		return $avatar;
	}

	function on_check_conditionals() {
		//let check  the necessary theme options for compression
		$this->options = get_option(XF_OPTIONS);


		define('XTOPT_COMPRESSION_HTML_MODE', 0);
		define('XTOPT_COMPRESSION_JS_MODE', 0);
		define('XTOPT_RENDER_PERF_STATS', false);

		if(
			isset($this->options['xc_performance']['proxy_gravatars'])
			&&
			(function_exists('fopen') && function_exists('ini_get') && true == ini_get('allow_url_fopen') && function_exists('imagecreatefromgd'))
		) {
			$this->proxy_gravatars = (bool)$this->options['xc_performance']['proxy_gravatars']['value'];
		}else{
			$this->proxy_gravatars = false;
		}
		if(isset($this->options['xc_performance']['lazy_gravatars'])){
			$this->lazy_gravatars = (bool)$this->options['xc_performance']['lazy_gravatars']['value'];
		}else{
			$this->lazy_gravatars = false;
		}

		if ($this->proxy_gravatars || $this->lazy_gravatars) {
			add_filter('get_avatar', array(&$this, 'on_lazy_gravatar'), 9999, 5);
			if(is_admin_bar_showing() && $this->lazy_gravatars) {
				$this->_ensure('xtreme-lazy-gravatars');
			}
		}


		if ( current_theme_supports('xtreme-fancybox') !== true) {
			unset($this->catalog->dep_tree['xtreme-fancybox-easing']);
			unset($this->catalog->dep_tree['xtreme-fancybox-wheel']);
			unset($this->catalog->dep_tree['xtreme-fancybox']);
		}

		if($this->is_fontend_rendering) {
			$searchtxt = esc_attr($this->options['xc_navigation']['input_text']['value']);

			//fix focus always
			$this->ensure_yaml_focusfix();
			//jquery always
			$this->ensure_jquery();
			//mandatory scripting injections
			$this->ensure_syncheight();
			$this->footer_initializer[]  =
				<<<EOD

		//mandatory framework settings
		var searchtxt = "{$searchtxt}";
		$('#main .ym-col1, #main .ym-col2, #main .ym-col3').syncHeight();
		$('#s').focus(function(){if(\$(this).val() == searchtxt){\$(this).val('');}});
		$('#s').blur(function(){if(\$(this).val() == ''){\$(this).val(searchtxt);}});
		$('#primarynav ul.sf-menu > li').first().addClass('first');
		$('#secondarynav ul.sf-menu > li').first().addClass('first');
		$('#primarynav ul.sf-menu > li').last().addClass('last');	
		$('#secondarynav ul.sf-menu > li').last().addClass('last');			
		setInterval('detect_zoom()', 100);
EOD;

			//superfish conditionally by nav usage
			$script = '';
			foreach ( array( 'primary', 'secondary' ) as $nav ) {
				$nav_pos = ( int ) $this->options['xc_navigation'][$nav . '_position']['value'];
				if ( $nav_pos !== 0 ) {
					$nav_type = $this->options['xc_navigation'][$nav . '_stylesheet']['value'];
					$do_fish = ( int ) $this->options['xc_navigation'][$nav . '_script']['value'];

					if ($do_fish === 1) {
						$this->ensure_superfish();
						$superfish =
							<<<EOD
									$('#{$nav}nav .sf-menu').superfish({delay:700,animation:{opacity:'show',height:'show'},dropShadows:false});
EOD;
						$script .= apply_filters( 'xtreme_script_superfish', $superfish, $nav );
					}

				}
			}
			if(!empty($script)) $this->footer_initializer[] = "\t\t//navigation effects\n$script";

			//syncheights conditionally by teaser / footer usage
			$script = '';
			$containers = array( 'teaser', 'footer' );
			foreach ( $containers as $container ) {
				if ( $this->options['xc_' . $container]['syncheight']['value'] === true ) {
					$this->ensure_syncheight();
					$script .=
						<<<EOD
								$('#$container .sync').syncHeight();\n
EOD;
				}
			}
			if(!empty($script)) {
				$this->footer_initializer[] = "\t\t//automatic sync heights\n$script";
				$this->footer_initializer[] =  "\t\t//automatic sync heights by zooming\n\t\t$('body').bind('xtreme_zoomed', function() {\n\t$script\t\t});\n";
			}
		}

	}

	function on_wp_print_footer_scripts() {
		if($this->footer_turn === 0) {
			if(wp_script_is('admin-gallery') && current_theme_supports('xtreme-advanced-wpgallery')) {
				global $_wp_additional_image_sizes;
				$this->_add_script_vars('xtreme_gallery', 'images_sizes', get_intermediate_image_sizes(), false, true);
				$this->_add_script_l10n('xtreme_gallery', array(
				                                               'labels' => array(
					                                               'size' => __( 'Size:', XF_TEXTDOMAIN ),
					                                               'targetsize' => __( 'Display Size:', XF_TEXTDOMAIN ),
					                                               'original' => __( 'Original (standard)', XF_TEXTDOMAIN ),
					                                               'behavior' => __( 'Selection Behavior:', XF_TEXTDOMAIN ),
					                                               'include' => __( 'checked as include', XF_TEXTDOMAIN ),
					                                               'exclude' => __( 'unchecked as exclude', XF_TEXTDOMAIN )
				                                               )
				                                          ), false, true);
			}

			//redirected to be after jquery
			if($this->is_fontend_rendering) {
				ob_start();	do_action('xtreme_add_script'); $user_scripts = ob_get_clean();
				if (!empty($user_scripts)) {
					$this->footer_initializer[] = "\t\t//xtreme one - includes by action: 'xtreme_add_script'";
					$this->footer_initializer[] = "try {";
					$this->footer_initializer[] = $user_scripts;
					$this->footer_initializer[] = "} catch(e) {}";
				}
			}
		}

		if ($this->footer_turn === 1) {
			if(XTOPT_COMPRESSION_HTML_MODE != 0) {
				echo XTOPT_MARKER_JS_BODY."\n";
			}
		}

		$extras = xtreme_merge_options(array_keys($this->footer_l10n), array_keys($this->footer_data));

		//if all is empty, don't write a script block
		if(count($extras) == 0 && count($this->footer_initializer) == 0) {
			$this->footer_turn++;
			return;
		}

		if ($this->footer_turn === 1) {
			echo '<script type="text/javascript">'."\n//<![CDATA[\n";

			//1st the localization
			echo "\n//xtreme one - configurations\n";
			if (count($extras) > 0) {
				foreach($extras as $key) {
					$var = str_replace('-','_',$key);
					$l10n = isset($this->footer_l10n[$key]) ? $this->footer_l10n[$key] : null;
					$data = isset($this->footer_data[$key]) ? $this->footer_data[$key] : null;

					echo "var $var = {\n";
					if (!is_null($l10n)) {
						echo "\t\"l10n\" : " . json_encode($l10n);
					}
					if(!is_null($data)) {
						if (!is_null($l10n)) echo ",\n";
						echo "\t\"data\" : " . json_encode($data) . "\n";
					}else{
						echo "\n";
					}
					echo "};\n";
				}
			}

			//2nd the copmpressed document ready initializer
			echo "\n//xtreme one - initializers\n";
			$zoom =
				<<<EOD
				var known_sizes = { 'bw' : jQuery('body').width(), 'bh' : jQuery('body').height(), 'fs' : parseInt(jQuery('body').css('font-size')) };
function detect_zoom() {
	nbfz = { 'bw' : jQuery('body').width(), 'bh' : jQuery('body').height() , 'fs' : parseInt(jQuery('body').css('font-size')), 'c' : false };
	if(known_sizes.bw != nbfz.bw) { known_sizes.bw = nbfz.bw; nbfz.c = true; }
	if(known_sizes.fs != nbfz.fs) { known_sizes.fs = nbfz.fs; nbfz.c = true; }
	if(known_sizes.bh != nbfz.bh) { known_sizes.bh = nbfz.bh; nbfz.c = true; }
	if(nbfz.c) { 
		jQuery('body').trigger('xtreme_zoomed');
		jQuery('#main .ym-col1, #main .ym-col2, #main .ym-col3').syncHeight();
	}	
	nbfz = null;
}

EOD;
			if(count($this->footer_initializer) > 0) {
				echo $zoom;
				echo "(function($) {\n\t$(document).ready(function(){\n";
				echo implode("\n\n", $this->footer_initializer);
				echo "\n\n\t});\n})(jQuery);\n";
			}

			echo "//]]>\n</script>\n";
			if(XTOPT_RENDER_PERF_STATS) {
				if (XTOPT_COMPRESSION_HTML_MODE != 0) {
					echo XTOPT_MARKER_PERF_STATS;
				}
			}

		}

		$this->footer_turn++;
	}

	function _add_script_vars($script_id, $widget_id, $data, $frontend = true, $backend = false){
		if($this->is_fontend_rendering) {
			if($frontend !== true) return;
		}else{
			if($backend !== true) return;
		}
		if (!isset($this->footer_data[$script_id]))
			$this->footer_data[$script_id] = array();
		if (!isset($this->footer_data[$script_id][$widget_id]))
			$this->footer_data[$script_id][$widget_id] = array();
		$this->footer_data[$script_id][$widget_id] = xtreme_merge_options($data, $this->footer_data[$script_id][$widget_id]);
	}

	function _add_script_l10n($script_id, $data, $frontend = true, $backend = false){
		if($this->is_fontend_rendering) {
			if($frontend !== true) return;
		}else{
			if($backend !== true) return;
		}
		$this->footer_l10n[$script_id] = $data;
	}

	function _add_initializer_script($script_id, $script, $frontend = true, $backend = false) {
		if(!wp_script_is($script_id)) return;
		if($this->is_fontend_rendering) {
			if($frontend !== true) return;
		}else{
			if($backend !== true) return;
		}
		$this->footer_initializer[] = $script;
	}

	function _ensure($script_id, $frontend = true, $backend = false) {
		if($this->is_fontend_rendering) {
			if($frontend !== true) return;
		}else{
			if($backend !== true) return;
		}
		if (in_array($script_id, array_keys($this->catalog->dep_tree))) {
			$deps = $this->catalog->dep_tree[$script_id];
			foreach($deps as $dep){
				$this->_ensure($dep);
			}
			$file = isset($this->catalog->script_files[$script_id]) ? $this->catalog->script_files[$script_id] : false;
			if(!wp_script_is($script_id)) {
				global $wp_scripts;
				if($file === false) {
					wp_enqueue_script($script_id);
				}else{
					wp_enqueue_script($script_id, $file, $deps, false, true);
				}
				$wp_scripts->in_footer[] = $script_id;

				if(in_array($script_id, array_keys($this->script_localizations))) {
					$this->_add_script_l10n($script_id, $this->script_localizations[$script_id], $frontend, $backend);
				}
				if(in_array($script_id, array_keys($this->script_document_ready))) {
					$this->_add_initializer_script($script_id, $this->script_document_ready[$script_id], $frontend, $backend);
				}
			}
		}
	}

	function add_widget_data($script_id, $widget_id, $data){
		$this->_add_script_vars($script_id, $widget_id, $data);
	}

	function on_ensure_comments_open_scripts() {
		if( get_option('thread_comments') && is_singular() ) {
			global $wp_scripts;
			wp_enqueue_script('comment-reply');
			$wp_scripts->in_footer[] = 'comment-reply';
		}
		$this->on_ensure_comments_closed_scripts();
	}
	function on_ensure_comments_closed_scripts() {
		static $done = false;

		if ( $done != true && ( bool )$this->options['xc_comments']['tabbed_comments']['value'] === true ) {
			$this->ensure_accessible_tabs();
			$this->footer_initializer[] = $this->script_document_ready['comments_closed_scripts'];
			$this->_add_script_vars('xtreme-accessible-tabs', 'cd_', array('syncheights' => false )); //comment dummy inlay
			$done = true;
		}
	}


	function ensure_yaml_focusfix() { $this->_ensure('yaml-focusfix'); }
	function ensure_jquery() { $this->_ensure('jquery'); }
	function ensure_superfish() { $this->_ensure('xtreme-superfish'); }
	function ensure_mobilefish() {  $this->_ensure('xtreme-mobilefish'); }
	function ensure_syncheight() { $this->_ensure('xtreme-syncheight'); }
	function ensure_accessible_tabs() { $this->_ensure('xtreme-accessible-tabs'); }
	function ensure_widget_groups() {
		static $done = false;
		$this->_ensure('xtreme-widget-groups');
		if (!$done) {
			$this->_add_script_vars('xtreme-accessible-tabs', 'wg_', array('syncheights' => false )); //widget groups dummy inlay
			$done = true;
		}
	}
	function ensure_feature_list() { $this->_ensure('xtreme-featurelist'); }
	function ensure_coin_slider() { $this->_ensure('xtreme-coin-slider'); }
	function ensure_jqfancy_slider() { $this->_ensure('xtreme-jqfancy-slider'); }
	function ensure_carousel() { $this->_ensure('xtreme-carousel'); }
	function ensure_flexslider() { $this->_ensure('xtreme-flexslider'); }
	function ensure_FancyBox() {
		$this->_ensure('xtreme-fancybox');
		$support = get_theme_support('xtreme-fancybox');
		$this->add_widget_data('xtreme-fancybox', 'settings',
		                       array(
		                            'options' => (array)$support[0]['options'],
		                            'gallery' => $support[0]['specials']['gallery'],
		                            'fancybox' => in_array('fancybox', $support[0]['compatibility'])
		                       )
		);
	}
	function ensure_low_barrier() { $this->_ensure('xtreme-low-barrier'); }

}
$xtreme_script_manager = new Xtreme_Script_Manager();

//Microsoft IE < 9.0 compatibility
function xtreme_html5shim_script() {
	echo '<!--[if lt IE 9]><script src="'. XF_THEME_URI .'/js/ie-html5.js"></script><![endif]-->' . "\n";
	do_action('xtreme_print_ie_script', XF_IE_MAJOR, XF_IE_MINOR);
}
add_action( 'xtreme_meta', 'xtreme_html5shim_script' );
