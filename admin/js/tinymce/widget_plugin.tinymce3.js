/** XTREME THEME HEADER **/

(function() {
	var Event = tinymce.dom.Event;

	tinymce.create('tinymce.plugins.XtremeContentWidgets', {
		init : function(ed, url) {
			var t = this;

			t.editor 				= ed;
			t.url 					= url;
			t.init_ready			= false;
			t.supportedWidgets		= ed.getParam("xtreme_tinymce_widgets_supported", "[]");
			t.post_ID				= editor.getParam( 'post_ID', false );

			t._createButtons();

			ed.onNodeChange.add(function(ed, cm, e) {
				// Activates the link button when the caret is placed in a anchor element
				if (e.nodeName == 'IMG' && ed.dom.getAttrib(e, 'class').indexOf('mcexcontentwidget') != -1) {
					cm.setActive('xtreme_tinymce_widgets', false);
					cm.setDisabled('xtreme_tinymce_widgets', true);

					//patch the p and img element
					if ( p = ed.dom.getParent(e, 'p') ) {
						//first apply the p alignment changes to img
						var n = new RegExp('align(none|left|right|center)', 'g').exec(ed.dom.getAttrib(p, 'class'));
						if (n) {
							var sc = ed.dom.getAttrib(e, 'title');
							tc = sc.replace(/align=(none|left|right|center)/g, "align="+n[1]);
							ed.dom.setAttrib(e, 'title', tc);
							ed.plugins.xtreme_tinymce_widgets._define_margin_buttons(ed, e);
							if (tc != sc) {
								ed.execCommand('mceRepaint');
							}
						}
					}
				}
				else {
					cm.setActive('xtreme_tinymce_widgets', true);
					cm.setDisabled('xtreme_tinymce_widgets', false);
				}
			});

			ed.onMouseDown.add(function(ed, e) {
				if ( e.target.nodeName == 'IMG' && ed.dom.hasClass(e.target, 'mcexcontentwidget') ) {
					ed.plugins.xtreme_tinymce_widgets._showButtons(e.target, 'xf_edit_buttons');
				}
				else
					ed.plugins.xtreme_tinymce_widgets._hideButtons();
			});

			ed.onBeforeExecCommand.add(function(ed, cmd, ui, val) {
				t._hideButtons();
			});

			ed.onInit.add(function(ed) {
				tinymce.dom.Event.add(ed.getWin(), 'scroll', function(e) {
					t._hideButtons();
				});
			});

			ed.onSaveContent.add(function(ed, o) {
				t._hideButtons();
			});

			ed.onBeforeSetContent.add(function(ed, o) {
				o.content = t._do_widget(o.content);
			});

			ed.onPostProcess.add(function(ed, o) {
				if (o.get)
					o.content = t._get_widget(o.content);
			});

			//do not provide the commands if no permission
			if (!t.supportedWidgets.permission) return;

			ed.addCommand('mceInsertWidget', function(ui, v) {
				v = t._do_widget(v);
				ed.execCommand('mceInsertContent', ui, v);
			});

			ed.addCommand('mceReplaceWidget', function(ui, v) {
				v = t._do_widget(v);
				ed.execCommand('mceReplaceContent', ui, v);
			});

		},

		_do_widget : function(co) {

			function getAttr(s, n) {
				n = new RegExp(n + '=([^ ]+)', 'g').exec(s);
				return n ? tinymce.DOM.decode(n[1]) : '';
			};

			return co.replace(/(?:<p>)?\[(xwidgetwordpress|xwidget3rdparty|xwidgetxtreme)([^\]]*)\](?:<\/p>)?/g, function(a,b,c){
				var align = getAttr(c, 'align');
				var width = getAttr(c, 'width');
				var sidemargins = getAttr(c, 'sidemargins');
				var styles = '';
				if (align) {
					if (align == 'left' && sidemargins == 'off') {
						styles = 'margin-right:0;';
					}
					if (align == 'right' && sidemargins == 'off') {
						styles = 'margin-left:0;';
					}
					align = 'align'+align;
				}
				if (width && width != 'auto') {
					styles = styles + "width:"+width+';';
				}
				if (styles.length > 0) styles = ' style="'+styles+'"';
				return '<p class="'+align+'"'+styles+'><img src="'+tinyMCE.activeEditor.plugins.xtreme_tinymce_widgets.url+'/img/t.gif" class="mcexcontentwidget mceItem mceTemp mce'+b+' mceItemNoResize mceNonEditable" title="'+b+''+tinymce.DOM.encode(c)+'" /></p>';
			});
		},

		_get_widget : function(co) {

			function getAttr(s, n) {
				n = new RegExp(n + '=\"([^\"]+)\"', 'g').exec(s);
				return n ? tinymce.DOM.decode(n[1]) : '';
			};

			return co.replace(/(?:<p[^>]*>)*(<img[^>]+>)(?:<\/p>)*/g, function(a,im) {
				var cls = getAttr(im, 'class');

				if ( cls.indexOf('mcexcontentwidget') != -1 ) {
					var sc = tinymce.trim(getAttr(im, 'title'));
					return '<p>['+sc+']</p>';
				}

				return a;
			});
		},


		createControl: function(n, cm) {

			if( !this.post_ID ){
				return;
			}

			var t = this,
				post_ID = this.post_ID;
			switch (n) {
				case 'xtreme_tinymce_widgets':
					var c = cm.createSplitButton('xtreme_tinymce_widgets', {
						title : 'WordPress Widgets',
						image : t.url + '/img/widget.png'
					});

					c.onRenderMenu.add(function(c, m) {

						m.add({title : t.supportedWidgets.mainmenu.title, 'class' : 'mceMenuItemTitle'}).setDisabled(1);
						tinymce.each( t.supportedWidgets.mainmenu.submenus, function(sub) {
							var sm = m.addMenu({title : sub.title, 'max_height' : 250, 'class' : 'mceSplitButtonMenu mceListBoxMenu mceNoIcons wp_themeSkin' });
							sm.add({ title : sub.subtitle, 'class' : 'mceMenuItemTitle' }).setDisabled(1);
							tinymce.each(sub.items, function(item) {
								sm.add({
									title : item.title,
									onclick : function() {
										t.editor.windowManager.open({
											file :"admin-ajax.php?action=mce_xcontentwidget&action2=insert&widget_class="+item.widget+'&post_id='+post_ID,
											width : 620,
											height : 500,
											inline : 1,
											title : item.title
										}, {
											plugin_url : t.url
										});
									}
								});
							});
						});

					});

					// Return the new splitbutton instance
					return c;
			}

			return null;
		},

		_createButtons : function() {
			if( !this.post_ID ){
				return;
			}
			var t = this, ed = tinyMCE.activeEditor, DOM = tinymce.DOM, editButton, dellButton, post_ID = this.post_ID;

			DOM.remove('xf_edit_buttons');

			if (!t.supportedWidgets.permission) return;

			DOM.add(document.body, 'div', {
				id : 'xf_edit_buttons',
				style : 'display:none;'
			});

			editButton = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/edit.png',
				id : 'xf_editwidget',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.edit
			});

			tinymce.dom.Event.add(editButton, 'mousedown', function(e) {
				var ed = tinyMCE.activeEditor;
				var el = ed.selection.getNode();

				if ( el.nodeName != 'IMG' ) return;
				if ( ed.dom.getAttrib(el, 'class').indexOf('mcexcontentwidget') == -1 )	return;

				ed.plugins.xtreme_tinymce_widgets._hideButtons();

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


			alignleftButton = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/t.gif',
				id : 'xf_widget_a_left',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.margin_r
			});

			tinymce.dom.Event.add(alignleftButton, 'mousedown', function(e) {
				var DOM = tinymce.DOM;
				var ed = tinyMCE.activeEditor, el = ed.selection.getNode();
				//sidemargins
				sc = ed.dom.getAttrib(el, 'title');
				n = new RegExp('sidemargins=([^ ]+)', 'g').exec(ed.dom.getAttrib(el, 'title'));
				var m = n ? tinymce.DOM.decode(n[1]) : 'on';
				if (m == 'on') {
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=off");
					ed.dom.setAttrib(el, 'title', tc);
				}else{
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=on");
					ed.dom.setAttrib(el, 'title', tc);
				}
				ed.plugins.xtreme_tinymce_widgets._define_margin_buttons(ed, el);
				ed.execCommand('mceRepaint');
			});

			alignrightButton = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/t.gif',
				id : 'xf_widget_a_right',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.margin_l
			});

			tinymce.dom.Event.add(alignrightButton, 'mousedown', function(e) {
				var DOM = tinymce.DOM;
				var ed = tinyMCE.activeEditor, el = ed.selection.getNode();
				//sidemargins
				sc = ed.dom.getAttrib(el, 'title');
				n = new RegExp('sidemargins=([^ ]+)', 'g').exec(ed.dom.getAttrib(el, 'title'));
				var m = n ? tinymce.DOM.decode(n[1]) : 'on';
				if (m == 'on') {
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=off");
					ed.dom.setAttrib(el, 'title', tc);
				}else{
					tc = sc.replace(/sidemargins=([^ ]+)/g, "sidemargins=on");
					ed.dom.setAttrib(el, 'title', tc);
				}
				ed.plugins.xtreme_tinymce_widgets._define_margin_buttons(ed, el);
				ed.execCommand('mceRepaint');
			});

			delButton = DOM.add('xf_edit_buttons', 'img', {
				src : t.url+'/img/delete.png',
				id : 'xf_delwidget',
				width : '24',
				height : '24',
				title : t.supportedWidgets.buttons.del
			});

			tinymce.dom.Event.add(delButton, 'mousedown', function(e) {
				var ed = tinyMCE.activeEditor, el = ed.selection.getNode();

				if ( el.nodeName == 'IMG' && ed.dom.hasClass(el, 'mcexcontentwidget') ) {

					//will be trashed at save_post action
					if ( p = ed.dom.getParent(el, 'p') )
						ed.dom.remove(p);
					else
						ed.dom.remove(el);

					ed.execCommand('mceRepaint');
					return false;
				}
			});
		},

		_define_margin_buttons : function(ed, el) {
			var DOM = tinymce.DOM;
			//align
			n = new RegExp('align=(none|left|right|center)', 'g').exec(ed.dom.getAttrib(el, 'title'));
			var a = n ? tinymce.DOM.decode(n[1]) : 'none';
			//sidemargins
			n = new RegExp('sidemargins=([^ ]+)', 'g').exec(ed.dom.getAttrib(el, 'title'));
			var m = n ? tinymce.DOM.decode(n[1]) : 'on';
			var p = ed.dom.getParent(el, 'p');
			//suppress margin buttons
			DOM.setStyles('xf_widget_a_left', { 'display' : 'none' });
			DOM.setStyles('xf_widget_a_right', { 'display' : 'none' });
			//restore the appropriate margin buttons	
			switch(a) {
				case 'left':
					DOM.setStyles('xf_widget_a_left', { 'display' : 'inline' });
					if (m == 'off') {
						DOM.addClass('xf_widget_a_left', 'off');
						ed.dom.setStyles(p, { 'margin-left':'', 'margin-right':'0'});
					}else{
						DOM.removeClass('xf_widget_a_left', 'off');
						ed.dom.setStyles(p, { 'margin-left':'', 'margin-right':''});
					}
					break;
				case 'right':
					DOM.setStyles('xf_widget_a_right', { 'display' : 'inline' });
					if (m == 'off') {
						DOM.addClass('xf_widget_a_right', 'off');
						ed.dom.setStyles(p, { 'margin-left':'0', 'margin-right':''});
					}else{
						DOM.removeClass('xf_widget_a_right', 'off');
						ed.dom.setStyles(p, { 'margin-left':'', 'margin-right':''});
					}
					break;
				default:
					ed.dom.setStyles(p, { 'margin-left':'', 'margin-right':''});
					break;
			}
		},

		_showButtons : function(n, id) {
			var ed = tinyMCE.activeEditor, p1, p2, vp, DOM = tinymce.DOM, X, Y;

			if (!ed.plugins.xtreme_tinymce_widgets.supportedWidgets.permission) return;

			ed.plugins.xtreme_tinymce_widgets._define_margin_buttons(ed, n);

			vp = ed.dom.getViewPort(ed.getWin());
			p1 = DOM.getPos(ed.getContentAreaContainer());
			p2 = ed.dom.getPos(n);

			X = Math.max(p2.x - vp.x, 0) + p1.x;
			Y = Math.max(p2.y - vp.y, 0) + p1.y;

			DOM.setStyles(id, {
				'top' : Y+5+'px',
				'left' : X+5+'px',
				'display' : 'block'
			});

			if ( this.mceTout )
				clearTimeout(this.mceTout);

			this.mceTout = setTimeout( function(){ed.plugins.xtreme_tinymce_widgets._hideButtons();}, 5000 );

		},

		_hideButtons : function() {
			if ( !this.mceTout )
				return;

			if ( document.getElementById('xf_edit_buttons') )
				tinymce.DOM.hide('xf_edit_buttons');

			clearTimeout(this.mceTout);
			this.mceTout = 0;
		},

		getInfo : function() {
			return {
				longname : 'WP TinyMCE Xtreme Content Widgets',
				author : 'Heiko Rabe',
				authorurl : 'http://www.code-styling.de',
				infourl : 'http://www.code-styling.de',
				version : tinymce.majorVersion + "." + tinymce.minorVersion
			};
		}

	});

	// Register plugin
	tinymce.PluginManager.add('xtreme_tinymce_widgets', tinymce.plugins.XtremeContentWidgets);
})();