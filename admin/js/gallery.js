/** XTREME THEME HEADER **/
jQuery(document).ready(function($) {
	var gallerySortable, gallerySortableInit, w, desc = false;
	
	gallerySortableInit = function() {

		$('.media-item .describe-toggle-on').before('<div style="float:right;margin: 8px 8px 0 0;"><input class="exclude" type="checkbox" checked="checked" /></div>');
		$('th.actions-head').append('<input type="checkbox" style="float:right;margin: 0px 4px 0 0;" checked="checked" />');
		$('th.actions-head input').click(function(e) {
			checked = $(this).is(':checked');
			$('input.exclude').each(function(i, ex) {
				if(checked)
					$(ex).attr('checked', 'checked');
				else
					$(ex).removeAttr('checked');
			});
		});
		$('input[name="linkto"]').change(function() {
			if ($(this).val() == 'file') {
				$("#target-row").css({ 'visibility':'visible'});
			}else{
				$("#target-row").css({ 'visibility':'hidden'});
			}
		});
		
		gallerySortable = $('#media-items').sortable( {
			items: 'div.media-item',
			placeholder: 'sorthelper',
			axis: 'y',
			distance: 2,
			handle: 'div.filename',
			stop: function(e, ui) {
				// When an update has occurred, adjust the order for each item
				var all = $('#media-items').sortable('toArray'), len = all.length;
				$.each(all, function(i, id) {
					var order = desc ? (len - i) : (1 + i);
					$('#' + id + ' .menu_order input').val(order);
				});
			}
		} );
		
	};

	sortIt = function() {
		var all = $('.menu_order_input'), len = all.length;
		all.each(function(i){
			var order = desc ? (len - i) : (1 + i);
			$(this).val(order);
		});
	};

	clearAll = function(c) {
		c = c || 0;
		$('.menu_order_input').each(function(){
			if ( this.value == '0' || c ) this.value = '';
		});
	};

	$('#asc').click(function(){desc = false; sortIt(); return false;});
	$('#desc').click(function(){desc = true; sortIt(); return false;});
	$('#clear').click(function(){clearAll(1); return false;});
	$('#showall').click(function(){
		$('#sort-buttons span a').toggle();
		$('a.describe-toggle-on').hide();
		$('a.describe-toggle-off, table.slidetoggle').show();
		return false;
	});
	$('#hideall').click(function(){
		$('#sort-buttons span a').toggle();
		$('a.describe-toggle-on').show();
		$('a.describe-toggle-off, table.slidetoggle').hide();
		return false;
	});

	// initialize sortable
	gallerySortableInit();
	clearAll();

	if ( $('#media-items>*').length > 1 ) {
		w = wpgallery.getWin();

		$('#save-all, #gallery-settings').show();
		if ( typeof w.tinyMCE != 'undefined' && w.tinyMCE.activeEditor && ! w.tinyMCE.activeEditor.isHidden() ) {
			wpgallery.mcemode = true;
			wpgallery.init();
		} else {
			$('#insert-gallery').show();
		}
	}
	
});

jQuery(window).unload( function () { tinymce = tinyMCE = wpgallery = null; } ); // Cleanup

/* gallery settings */
var tinymce = null, tinyMCE, wpgallery;

wpgallery = {
	mcemode : false,
	editor : {},
	dom : {},
	is_update : false,
	el : {},

	I : function(e) {
		return document.getElementById(e);
	},

	init: function() {
		var t = this, li, q, i, it, w = t.getWin();

		if ( ! t.mcemode ) return;

		li = ('' + document.location.search).replace(/^\?/, '').split('&');
		q = {};
		for (i=0; i<li.length; i++) {
			it = li[i].split('=');
			q[unescape(it[0])] = unescape(it[1]);
		}

		if (q.mce_rdomain)
			document.domain = q.mce_rdomain;

		// Find window & API
		tinymce = w.tinymce;
		tinyMCE = w.tinyMCE;
		t.editor = tinymce.EditorManager.activeEditor;

		t.setup();
	},

	getWin : function() {
		return window.dialogArguments || opener || parent || top;
	},

	setupSizes : function(preview, target, behavior) {
		sizes = '';
		sizes_t = '';
		user_size = preview || getUserSetting('galsize');
		target_size = target || getUserSetting('galtarget') || 'auto';
		jQuery.each(xtreme_gallery.data.images_sizes, function(i, s) {
			sizes += '<option value="'+s+'"'+(user_size == s ? ' selected="selected"' : '')+'>'+s+'</option>';
			sizes_t += '<option value="'+s+'"'+(target_size == s ? ' selected="selected"' : '')+'>'+s+'</option>';
		});
		sizes_t = '<option value="auto"'+(target_size == 'auto' ? ' selected="selected"' : '')+'>'+xtreme_gallery.l10n.labels.original+'</option>' + sizes_t;
		behavior = '<option value="exclude"'+(behavior == 'exclude' ? ' selected="selected"' : '')+'>'+xtreme_gallery.l10n.labels.exclude+'</option>' +
				   '<option value="include"'+(behavior == 'include' ? ' selected="selected"' : '')+'>'+xtreme_gallery.l10n.labels.include+'</option>'
		jQuery('#basic tbody').append('<tr><th class="label" scope="row"><label><span class="alignleft">'+xtreme_gallery.l10n.labels.behavior+'</span></label></th><td class="field"><select name="behavior" id="behavior">'+behavior+'</select></td></tr>');
		jQuery('#basic tbody').append('<tr><th class="label" scope="row"><label><span class="alignleft">'+xtreme_gallery.l10n.labels.size+'</span></label></th><td class="field"><select name="imagesize" id="imagesize">'+sizes+'</select></td></tr>');
		jQuery('#basic tbody').append('<tr id="target-row"><th class="label" scope="row"><label><span class="alignleft">'+xtreme_gallery.l10n.labels.targetsize+'</span></label></th><td class="field"><select name="target" id="target">'+sizes_t+'</select></td></tr>');
	},
	
	setup : function() {
		var t = this, a, ed = t.editor, g, columns, link, order, orderby;
		if ( ! t.mcemode ) return;

		t.el = ed.selection.getNode();

		if ( t.el.nodeName != 'IMG' || ! ed.dom.hasClass(t.el, 'wpGallery') ) {
			this.setupSizes();
			if ( getUserSetting('galfile') == '1' ) t.I('linkto-file').checked = "checked";
			if ( getUserSetting('galdesc') == '1' ) t.I('order-desc').checked = "checked";
			if ( getUserSetting('galcols') ) t.I('columns').value = getUserSetting('galcols');
			if ( getUserSetting('galord') ) t.I('orderby').value = getUserSetting('galord');
			jQuery('#insert-gallery').show();
			return;
		};

		a = ed.dom.getAttrib(t.el, 'title');
		a = ed.dom.decode(a);

		if ( a ) {
			jQuery('#update-gallery').show();
			t.is_update = true;

			columns = a.match(/columns=['"]([0-9]+)['"]/);
			link = a.match(/link=['"]([^'"]+)['"]/i);
			order = a.match(/order=['"]([^'"]+)['"]/i);
			orderby = a.match(/orderby=['"]([^'"]+)['"]/i);

			if ( link && link[1] ) t.I('linkto-file').checked = "checked";
			if ( order && order[1] ) t.I('order-desc').checked = "checked";
			if ( columns && columns[1] ) t.I('columns').value = ''+columns[1];
			if ( orderby && orderby[1] ) t.I('orderby').value = orderby[1];
						
			//exclude as first option
			behavior = 'exclude';
			exclude = a.match(/exclude=['"]([^'"]+)['"]/i);
			if ( exclude && exclude[1]) {
				jQuery.each(exclude[1].split(','), function(i, ex) {
					jQuery('#media-item-'+ex+' .exclude').removeAttr('checked');
				});
			}else {
				//include as fallback
				include = a.match(/include=['"]([^'"]+)['"]/i);
				if ( include && include[1]) {
					behavior = 'include';
					jQuery('input.exclude').each(function(i, ex) { jQuery(ex).removeAttr('checked'); });
					jQuery.each(include[1].split(','), function(i, ex) {
						jQuery('#media-item-'+ex+' .exclude').attr('checked', 'checked');
					});
				}
			}
			
			size = a.match(/size=['"]([^'"]+)['"]/i);
			size = (size && size[1] ? size[1] : 'thumbnail');

			size_t = a.match(/target=['"]([^'"]+)['"]/i);
			size_t = (size_t && size_t[1] ? size_t[1] : 'auto');

			this.setupSizes(size, size_t, behavior);
			
			if (jQuery('#linkto-post').is(':checked')) jQuery("#target-row").css({ 'visibility':'hidden'});
						
		} else {
			jQuery('#insert-gallery').show();
		}
		
	},

	update : function() {
		var t = this, ed = t.editor, all = '', s;

		if ( ! t.mcemode || ! t.is_update ) {
			s = '[gallery'+t.getSettings()+']';
			t.getWin().send_to_editor(s);
			return;
		}

		if (t.el.nodeName != 'IMG') return;

		all = ed.dom.decode(ed.dom.getAttrib(t.el, 'title'));
		all = all.replace(/\s*(order|link|columns|orderby|exclude|include|size|target)=['"]([^'"]+)['"]/gi, '');
		all += t.getSettings();

		ed.dom.setAttrib(t.el, 'title', all);
		t.getWin().tb_remove();
	},

	getSettings : function() {
		var I = this.I, s = '';

		if ( I('linkto-file').checked ) {
			s += ' link="file"';
			setUserSetting('galfile', '1');
		}

		if ( I('order-desc').checked ) {
			s += ' order="DESC"';
			setUserSetting('galdesc', '1');
		}

		if ( I('columns').value != 3 ) {
			s += ' columns="'+I('columns').value+'"';
			setUserSetting('galcols', I('columns').value);
		}

		if ( I('orderby').value != 'menu_order' ) {
			s += ' orderby="'+I('orderby').value+'"';
			setUserSetting('galord', I('orderby').value);
		}
		
		if ( I('imagesize').value != 'thumbnail' ) {
			s += ' size="'+I('imagesize').value+'"';
			setUserSetting('galsize', I('imagesize').value);
		}
		
		if (I('linkto-file').checked && I('target').value != 'auto' ) {
			s += ' target="'+I('target').value+'"';
			setUserSetting('galtarget', I('target').value);
		}

		if ( I('behavior').value == 'exclude') {		
			excludes = [];
			jQuery('.media-item .exclude').not(':checked').each(function(i, ex) {
				excludes.push(jQuery(ex).closest('.media-item').attr('id').replace(/media\-item\-/,''));
			});
			
			if (excludes.length) {
				s += ' exclude="'+excludes.join(',')+'"';
			}		
		}else {
			includes = [];
			jQuery('.media-item .exclude:checked').each(function(i, ex) {
				includes.push(jQuery(ex).closest('.media-item').attr('id').replace(/media\-item\-/,''));
			});
			
			if (includes.length) {
				s += ' include="'+includes.join(',')+'"';
			}
		}

		return s;
	}
};
