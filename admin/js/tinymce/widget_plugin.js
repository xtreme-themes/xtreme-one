/** XTREME THEME HEADER **/

(function() {

	tinymce.create('tinymce.plugins.XtremeContentWidgets', {

		// on init
		init : function( editor, url ) {
			var t			= this;

			t.editor				= editor;
			t.url 					= url;
			t.init_ready			= false;
			t.supportedWidgets		= editor.getParam( "xtreme_tinymce_widgets_supported", "[]" );
			t.post_ID				= editor.getParam( 'post_ID', false );

			if( ! t.post_ID ){
				return;
			}


			t._create_button();
			t._create_widget_edit_buttons();

			/**
			 * Registering Event for "nodeChange"
			 *
			 * This is an old comment which is still supported in TinyMCE 4 for WordPress
			 *  @see /wp-includes/js/tinymce/plugins/compat3x/plugin.js L198
			 */
			editor.onNodeChange.add( function( editor, command, elem ) {
				t._on_node_change( editor, command, elem );
			} );

			/**
			 *  mouse down event on widget images
			 *  to show the edit/delete-buttons
			 *
			 *  This is an old comment which is still supported in TinyMCE 4 for WordPress
			 *  @see /wp-includes/js/tinymce/plugins/compat3x/plugin.js L190
			 */
			editor.onMouseDown.add( function( editor, event ) {

				if ( !t.supportedWidgets.permission )
					return;

				if ( event.target.nodeName == 'IMG' && editor.dom.hasClass(event.target, 'mcexcontentwidget' ) ) {
					t._show_buttons(event.target, 'xf_edit_buttons');
				}
				else {
					t._hide_buttons();
				}
			} );


			/**
			 *  hiding the button before we exec some commands
			 *  This is an old comment which is still supported in TinyMCE 4 for WordPress
			 *  @see /wp-includes/js/tinymce/plugins/compat3x/plugin.js L191
			 */
			editor.onBeforeExecCommand.add( function( ed, cmd, ui, val ) {
				t._hide_buttons();
			} );


			/**
			 * adding an event for scrolling in editor
			 * to hide the buttons
			 */
			editor.onInit.add(function( editor ) {
				tinymce.DOM.bind(
					editor.getWin(),
					'scroll',
					function(){
						t._hide_buttons();
					}
				);
			});

			/**
			 * registering our callback to set the correct widget replacement
			 */
			editor.onBeforeSetContent.add( function( editor, object ) {
				object.content = t._do_widget( object.content );
			});


			editor.onPostProcess.add( function( editor, object ) {
				if ( object.get )
					object.content = t._get_widget( object.content );
			});			


			//do not provide the commands if no permission
			if ( t.supportedWidgets.permission ) {

				editor.addCommand( 'mceInsertWidget', function( ui, content ) {
					var new_content = t._do_widget( content );
					editor.execCommand( 'mceInsertContent', ui, new_content );
				});

				editor.addCommand( 'mceReplaceWidget', function( ui, content ) {
					var new_content = t._do_widget( content );
					editor.execCommand( 'mceReplaceContent', ui, new_content );
				});

			}
		},

		/**
		 * Callback to our TinyMCE-Button to set it active/inactive or enabled/disabled.
		 * @param	editor
		 * @param	command
		 * @param	elem
		 * @access	private
		 */
		_on_node_change: function( editor, command, elem ) {

			var t = this;

			// Activates the link button when the caret is placed in a anchor element
			if (	elem.nodeName == 'IMG'
				&&	editor.dom.getAttrib( elem, 'class' ).indexOf( 'mcexcontentwidget' ) != -1 ) {


				command.setActive( 'xtreme_tinymce_widgets', false);
				command.setDisabled( 'xtreme_tinymce_widgets', true);

				//patch the p and img element
				var parent = editor.dom.getParent( elem, 'p' );
				if ( parent ) {
					//first apply the p alignment changes to img
					var attr_class	= editor.dom.getAttrib( parent, 'class' ),
						match		= new RegExp('align(none|left|right|center)', 'g').exec( attr_class )
					;

					if ( match ) {
						var shortcode	= editor.dom.getAttrib( elem, 'title' ),
							tc			= shortcode.replace(/align=(none|left|right|center)/g, "align=" +  match[ 1 ] );

						editor.dom.setAttrib( elem, 'title', tc );
						t._define_margin_buttons( editor , elem );
						if ( tc != shortcode ) {
							editor.execCommand( 'mceRepaint' );
						}
					}
				}
			}
			else {
				command.setDisabled( 'xtreme_tinymce_widgets', false );
			}
		},

		/**
		 * Callback to insert our Widget-Placeholder in the_content()
		 * @access	private
		 * @param	String content
		 * @return	String new_content
		 */
		_do_widget : function( content ) {
			var t = this;
			return content.replace(
				/(?:<p>)?\[(xwidgetwordpress|xwidget3rdparty|xwidgetxtreme)([^\]]*)\](?:<\/p>)?/g ,
				function( a, b, c ) {
					return t._replace_shortcode( a, b, c )
				}
			);
		},

		/**
		 * Helper Function to find an attribute in Content
		 *
		 * @access	private
		 *
		 * @param	String content
		 * @param	String attribute
		 * @return	String ouput
		 */
		_find_attribute_in_content : function( content, attribute ){
			var found	= new RegExp( attribute + '=([^ ]+)', 'g' ).exec( content ),
				output	= ''
			;
			if( found ){
				output = tinymce.DOM.decode( found[ 1 ] );
			}
			return output;
		},

		/**
		 * Callback to replace all Shortcodes in content
		 *
		 * @access	private
		 *
		 * @param	String paragraph
		 * @param	String shortcode
		 * @param	String content
		 *
		 * @return	String output
		 */
		_replace_shortcode : function ( paragraph, shortcode, content ){

			var t			= this,
				align		= t._find_attribute_in_content( content, 'align' ),
				width		= t._find_attribute_in_content( content, 'width'),
				margins		= t._find_attribute_in_content( content, 'sidemargins' ),
				styles		= '',
				output
			;

			if ( align ) {
				if ( align === 'left' && margins === 'off' ) {
					styles = 'margin-right:0;';
				}
				if ( align === 'right' && margins === 'off') {
					styles = 'margin-left:0;';
				}
				align = 'align' + align;
			}
			if ( width && width !== 'auto' ) {
				styles = styles + "width:" + width + ';';
			}
			if ( styles.length > 0)
				styles = ' style="' + styles + '"';

			output = '<p class="' + align + '"' + styles + '>';
			output += '<img src="' + t.url + '/img/t.gif" class="mcexcontentwidget mceItem mceTemp mce' + shortcode + ' mceItemNoResize mceNonEditable" title="' + shortcode + '' + tinymce.DOM.encode( content ) + ' " />';
			output += '</p>';

			return output;
		},

		/**
		 * Callback on init the TinyMCE-Editor
		 *
		 * @access	private
		 * @var		String content
		 * @return	String content
		 */
		_get_widget : function( content ) {

			function getAttr(s, n) {
				n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
				return n ? tinymce.DOM.decode( n[ 1 ] ) : '';
			}

			return content.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function( a, im ) {
				var cls = getAttr( im, 'class' );
								
				if ( cls.indexOf( 'mcexcontentwidget' ) != -1 ) {
					var shortcode = tinymce.trim( getAttr( im, 'title' ) );
					return '<p>[' + shortcode+ ']</p>';
				}

				return a;

			});
		},

		/**
		 * Creating our Xtreme One Button for the TinyMCE4
		 * @access private
		 */
		_create_button : function() {

			if( !this.post_ID ) {
				return;
			}

			var _post_ID		= this.post_ID,
				_xtreme_menu	= [],
				editor			= this.editor
			;

			// creating the sub-menus for our splitbutton
			tinymce.each( this.supportedWidgets.mainmenu.submenus, function( sub ) {

				// lvl1
				var menu_item = {
					'text'	: sub.title,
					'class'	: 'mceMenuItemTitle',
					'menu'	: []
				};

				// adding the sub-items to the lvl1-button
				tinymce.each( sub.items, function( sub_item ) {

					var sub_menu_item = {
						text	: sub_item.title,
						onclick : function() {
							editor.windowManager.open( {
									file	: "admin-ajax.php?action=mce_xcontentwidget&action2=insert&widget_class=" + sub_item.widget + '&post_id=' + _post_ID,
									width	: 620,
									height	: 500,
									inline	: 1,
									title	: sub_item.title
								},
								{
									plugin_url : editor.url
								});
						}
					};

					// pushing our sub_menu_item to the menu_item
					menu_item.menu.push( sub_menu_item );

				});

				// pushing the menu_item to the menu
				_xtreme_menu.push( menu_item );

			} );

			// creating our tinymce button for xtreme one!
			editor.addButton('xtreme_tinymce_widgets', {
				text	: 'Xtreme One',
				type	: "splitbutton",
				icon	: false,
				menu	: _xtreme_menu
			});
		},

		/**
		 * Creating the Edit Buttons for the Content-Widgets in the_content()
		 * @access private
		 */
		_create_widget_edit_buttons : function() {

			if( !this.post_ID ){
				return;
			}

			var t	= this,
				DOM	= tinymce.DOM,
				editButton,
				post_ID = this.post_ID
			;

			DOM.remove('xf_edit_buttons');

			if ( !t.supportedWidgets.permission ) {
				return;
			}

			DOM.add(document.body, 'div', {
				id : 'xf_edit_buttons',
				style : 'display:none;'
			} );

			editButton = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/edit.png',
				id : 'xf_editwidget',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.edit
			});

			tinymce.DOM.bind(editButton, 'mousedown', function( event ) {
				var ed	= tinyMCE.activeEditor,
					el	= ed.selection.getNode()
				;

				event.preventDefault();

				if ( el.nodeName != 'IMG' ) return;
				if ( ed.dom.getAttrib(el, 'class').indexOf('mcexcontentwidget') == -1 )	return;

				t._hide_buttons();
				
				//post_id & widget_id
				var n = new RegExp('id=(\\d+)\.(\\d+)', 'g').exec(ed.dom.getAttrib(el, 'title'));
				var pid = n ? tinymce.DOM.decode(n[1]) : post_ID;
				var wid = n ? tinymce.DOM.decode(n[2]) : '0';
				//align
				n = new RegExp('align=(none|left|right|center)', 'g').exec(ed.dom.getAttrib(el, 'title'));
				var a = n ? tinymce.DOM.decode(n[1]) : 'none';
				//width
				n = new RegExp('width=([^ ]+)', 'g').exec(ed.dom.getAttrib(el, 'title'));
				var w = n ? tinymce.DOM.decode(n[1]) : 'auto';
				//sidemargins
				n = new RegExp('sidemargins=([^ ]+)', 'g').exec(ed.dom.getAttrib(el, 'title'));
				var m = n ? tinymce.DOM.decode(n[1]) : 'on';
									
				t.editor.windowManager.open({
						file : "admin-ajax.php?action=mce_xcontentwidget&action2=update&widget_id="+wid+'&post_id='+pid+'&align='+a+'&width='+w+'&sidemargins='+m,
						width : 620,
						height : 500,
						inline : 1,
						title : ""
					}, 
					{ 
					plugin_url : t.url
				});				
			});

		
			var align_left_button = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/t.gif',
				id : 'xf_widget_a_left',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.margin_r
			});
			
			tinymce.DOM.bind(align_left_button, 'mousedown', function( event ) {
				var ed	= tinyMCE.activeEditor,
					el	= ed.selection.getNode(),
					sc	= ed.dom.getAttrib(el, 'title' ),
					n	= new RegExp('sidemargins=([^ ]+)', 'g').exec(ed.dom.getAttrib(el, 'title') ),
					m	= n ? tinymce.DOM.decode(n[1]) : 'on',
					tc
				;

				event.preventDefault();

				//sidemargins
				if (m == 'on') {
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=off");
					ed.dom.setAttrib(el, 'title', tc);
				}else{
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=on");
					ed.dom.setAttrib(el, 'title', tc);					
				}
				t._define_margin_buttons(ed, el);
				ed.execCommand('mceRepaint');
			});
		
			var align_right_button = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/t.gif',
				id : 'xf_widget_a_right',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.margin_l
			});
			
			tinymce.DOM.bind(align_right_button, 'mousedown', function( event ) {
				var t	= this,
					ed	= tinyMCE.activeEditor,
					el	= ed.selection.getNode(),
					sc	= ed.dom.getAttrib(el, 'title'),
					n	= new RegExp('sidemargins=([^ ]+)', 'g').exec(ed.dom.getAttrib(el, 'title') ),
					m = n ? tinymce.DOM.decode(n[1]) : 'on',
					tc
				;

				event.preventDefault();

				//sidemargins
				if (m == 'on') {
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=off");
					ed.dom.setAttrib(el, 'title', tc);
				}
				else{
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=on");
					ed.dom.setAttrib(el, 'title', tc);					
				}
				t._define_margin_buttons(ed, el);
				ed.execCommand('mceRepaint');
			});
			
			var delete_button = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/delete.png',
				id : 'xf_delwidget',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.del
			});

			tinymce.DOM.bind(delete_button, 'mousedown', function( event ) {
				var ed = tinyMCE.activeEditor,
					el = ed.selection.getNode()
				;

				event.preventDefault();

				if ( el.nodeName == 'IMG' && ed.dom.hasClass(el, 'mcexcontentwidget') ) {
				
					//will be trashed at save_post action
					var parent = ed.dom.getParent(el, 'p');
					if ( parent )
						ed.dom.remove( parent );
					else
						ed.dom.remove(el);

					ed.execCommand('mceRepaint');
				}
			});			
		},

		/**
		 * don't really know what this functions does..maybe some positioning and hiding stuff..looks awful but works..
		 *
		 * @param	editor
		 * @param	el
		 * @access	private
		 */
		_define_margin_buttons : function( editor, el ) {
			var DOM = tinymce.DOM;
			//align
			var n = new RegExp( 'align=(none|left|right|center)', 'g' ).exec( editor.dom.getAttrib( el, 'title' ) );
			var a = n ? tinymce.DOM.decode( n[ 1 ] ) : 'none';

			//sidemargins
			n = new RegExp( 'sidemargins=([^ ]+)', 'g' ).exec( editor.dom.getAttrib( el, 'title' ) );
			var m = n ? tinymce.DOM.decode( n[ 1 ] ) : 'on';


			var p = editor.dom.getParent( el, 'p' );

			//suppress margin buttons
			DOM.setStyles( 'xf_widget_a_left', { 'display' : 'none' } );
			DOM.setStyles( 'xf_widget_a_right', { 'display' : 'none' } );

			//restore the appropriate margin buttons
			switch( a ) {

				case 'left':
					DOM.setStyles(' xf_widget_a_left', { 'display' : 'inline' } );
					if ( m === 'off' ) {
						DOM.addClass( 'xf_widget_a_left', 'off' );
						editor.dom.setStyles( p, { 'margin-left': '', 'margin-right': '0' } );
					}
					else{
						DOM.removeClass( 'xf_widget_a_left', 'off' );
						editor.dom.setStyles( p, { 'margin-left': '', 'margin-right': '' } );
					}
					break;

				case 'right':
					DOM.setStyles( 'xf_widget_a_right', { 'display' : 'inline' } );
					if ( m === 'off' ) {
						DOM.addClass( 'xf_widget_a_right', 'off' );
						editor.dom.setStyles( p, { 'margin-left': '0', 'margin-right': '' } );
					}
					else{
						DOM.removeClass( 'xf_widget_a_right', 'off' );
						editor.dom.setStyles( p, { 'margin-left': '', 'margin-right': '' } );
					}
					break;

				default:
					editor.dom.setStyles( p, { 'margin-left':'', 'margin-right':'' } );
					break;
			}
		},

		/**
		 * Callback to show the Buttons on a Widget in the_content()
		 * @param	elem
		 * @param	id
		 * @access	private
		 */
		_show_buttons : function( elem, id ) {
			var t		= this,
				editor	= tinyMCE.activeEditor,
				DOM		= tinymce.DOM,
				p1		= DOM.getPos( editor.getContentAreaContainer() ),
				p2		= editor.dom.getPos( elem ),
				vp		= editor.dom.getViewPort( editor.getWin() ),
				X,
				Y
			;

			t._define_margin_buttons( editor, elem );

			X = Math.max( p2.x - vp.x, 0 ) + p1.x;
			Y = Math.max( p2.y - vp.y, 0 ) + p1.y;

			DOM.setStyles( id, {
				'top'		: ( Y+5 ) + 'px',
				'left'		: ( X+5 ) + 'px',
				'display'	: 'block'
			} );

			if ( t.mceTout )
				clearTimeout( t.mceTout );

			t.mceTout = setTimeout(
				function(){
					t._hide_buttons();
				},
				5000
			);

		},

		/**
		 * Callback to hide the Buttons on a Widget in the_content()
		 * @access	private
		 */
		_hide_buttons : function() {

			if ( !this.mceTout )
				return;

			if ( document.getElementById( 'xf_edit_buttons' ) )
				tinymce.DOM.hide( 'xf_edit_buttons' );

			clearTimeout( this.mceTout );
			this.mceTout = 0;
		},

		/**
		 * callback to TinyMCE 4-API to get some Infos about our Xtreme one Plugin
		 * @returns Object
		 */
		getInfo : function() {
			return {
				longname : 'WP TinyMCE Xtreme Content Widgets',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}
		
	});

	// Register plugin
	tinymce.PluginManager.add('xtreme_tinymce_widgets', tinymce.plugins.XtremeContentWidgets );

})();