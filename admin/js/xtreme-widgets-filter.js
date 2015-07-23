/** XTREME THEME HEADER **/
 
var xtreme_dialog_data = {
	'action' 		: '',
	'dlg_type'		: '',
	'filter' 		: '',
	'widget'		: '',
	'sidebar'		: '',
	'target_input'	: '',
	'value'			: '',
	's'				: '',
	's_media'		: 'all',
	'page'			: 1,
	'content'		: 'standard',
	'perform'		: 'first-page',
	'callback_id'	: ''
};

var xtreme_dialog_pos = {
	'left' 	: 0,
	'top'	: 0,
	'width'	: 600
};

var xtreme_syntax = {
	'not-first': ['leave','or','and'],
	'not-last' : ['enter','or','and','not'],
	'enter' 	: ['enter', 'not', 'is_paged','is_preview','is_logged_in','has_role','has_cap','in_postformat','in_category','has_tag','has_author'/*TAX,'','','',''*/],
	'leave' 	: ['leave','or','and'],
	'or' 		: ['enter', 'not','is_paged','is_preview','is_logged_in','has_role','has_cap','in_postformat','in_category','has_tag','has_author'/*TAX,'','','',''*/],
	'and' 		: ['enter', 'not','is_paged','is_preview','is_logged_in','has_role','has_cap','in_postformat','in_category','has_tag','has_author'/*TAX,'','','',''*/],
	'not' 		: ['is_paged','is_preview','is_logged_in','has_role','has_cap','in_postformat','in_category','has_tag','has_author'/*TAX,'','','',''*/],
	'is_paged' 		: ['leave','or','and'],
	'is_preview' 	: ['leave','or','and'],
	'is_logged_in' 	: ['leave','or','and'],
	'has_role'		: ['leave','or','and'],
	'has_cap'		: ['leave','or','and'],
	'in_postformat' : ['leave','or','and'],
	'in_category' 	: ['leave','or','and'],
	'has_tag' 		: ['leave','or','and'],
	'has_author' 	: ['leave','or','and']
	/*, TAX follow here
	'' : ['','','','','','','','',''],
	'' : ['','','','','','','','',''],*/
};
var xtreme_condition_target = null;

 
(function($) {
   

      
	$(document).ready(function(){

		//handle filter expander 
		$('.x-filter-expander').live('click', function(event) {
			event.preventDefault();
			$(this).parent().next('.x-filter-section').slideToggle('fast', function() {
				$('span', event.currentTarget).toggle();
				if (!$(event.currentTarget).parent().next('.x-filter-section').is(':visible')) {
					$('#x-portlet-editing-close').trigger('click');
				}
			});
			
		});
		
		//filter exceptions
		$('.xtreme-fex a.x-dlg-exceptions').live('click', function(event) {
			event.preventDefault();
			$('#x-portlet-editing-close').trigger('click');
			
			if($("body").hasClass("iframe")) {
				xtreme_dialog_pos.top = 5;
				xtreme_dialog_pos.left = 0;
				$('form').hide();
			}else{
				xtreme_dialog_pos.top = ($(this).offset().top-5)-23;
				wrapper = $("body").hasClass("widgets_access") ? '.editwidget' : '.widget-content';
				if($("body").hasClass("rtl")) {
					adjustment = $("body").hasClass("widgets_access") ? -250 : 17;
					xtreme_dialog_pos.left = parseInt($(this).closest(wrapper).offset().left)+(parseInt($(this).closest(wrapper).width())+adjustment);
				}else{
					adjustment = $("body").hasClass("widgets_access") ? -200 : 37;
					xtreme_dialog_pos.left = parseInt($(this).closest(wrapper).offset().left)-(xtreme_dialog_pos.width+adjustment);
				}
			}
			
			xtreme_dialog_data.action = "edit_widget_filter_exceptions";
			xtreme_dialog_data.dlg_type = $(this).attr('class');
			xtreme_dialog_data.filter = $(this).closest('.x-filter').find('label:first input').val();
			xtreme_dialog_data.widget = $(this).closest('.widget').find('.widget-title h4').text();
			xtreme_dialog_data.sidebar = $(this).closest('.widgets-holder-wrap').find('.sidebar-name h3').text();
			xtreme_dialog_data.target_input = $(this).closest('.xtreme-fex').find('.fex-input').attr('name');
			xtreme_dialog_data.value = $(this).closest('.xtreme-fex').find('.fex-input').val();
			xtreme_dialog_data.s = '';
			xtreme_dialog_data.s_media = 'all';
			xtreme_dialog_data.content = xtreme_dialog_data.value.length == 0 ? 'standard' : 'selection';
			xtreme_dialog_data.perform = 'first-page';
			xtreme_dialog_data.callback_id = '';
			
			$.post(ajaxurl, xtreme_dialog_data,
				function(data) {
					$('#x-portlet-editing').html(data).show().css({
						'position' 	: 'absolute',
						'width' 	: xtreme_dialog_pos.width + 'px',
						'top' 		: xtreme_dialog_pos.top + 'px',
						'left' 		: xtreme_dialog_pos.left + 'px'  
					});
				}
			);
			
		});
		
		$('.x-dlg-media').live('click', function(event) {
			event.preventDefault();
			$('#x-portlet-editing-close').trigger('click');
				
			if ($("body").hasClass("iframe")) {
				xtreme_dialog_pos.top = 5;
				xtreme_dialog_pos.left = 0;
				$('form').hide();
			}else {
				wrapper = $("body").hasClass("widgets_access") ? '.editwidget' : '.widget-content';
				xtreme_dialog_pos.top = ($(this).closest(wrapper).find('.x-portlet-wrapper:first').offset().top-5)-13;
				if($("body").hasClass("rtl")) {
					adjustment = $("body").hasClass("widgets_access") ? -250 : 17;
					xtreme_dialog_pos.left = parseInt($(this).closest(wrapper).offset().left)+(parseInt($(this).closest(wrapper).width())+adjustment);
				}else{
					adjustment = $("body").hasClass("widgets_access") ? -200 : 37;
					xtreme_dialog_pos.left = parseInt($(this).closest(wrapper).offset().left)-(xtreme_dialog_pos.width+adjustment);
				}
			}
			
			//portlet_id
			if($("body").hasClass("widgets_access")) {
				$('.editwidget').addClass('widget').attr({ 'id' : $('input.widget-id').val()});
			};
			
			xtreme_dialog_data.action = "edit_widget_filter_exceptions";
			xtreme_dialog_data.dlg_type = 'x-dlg-exceptions';
			xtreme_dialog_data.filter = 'is_attachment';
			xtreme_dialog_data.widget = $(this).closest('.widget').find('.widget-title h4').text();
			xtreme_dialog_data.sidebar = $(this).closest('.widgets-holder-wrap').find('.sidebar-name h3').text();
			xtreme_dialog_data.target_input = $(this).closest('.widget').attr('id');
			xtreme_dialog_data.value = ''; 
			xtreme_dialog_data.s = '';
			xtreme_dialog_data.s_media = 'all';
			xtreme_dialog_data.content = xtreme_dialog_data.value.length == 0 ? 'standard' : 'selection';
			xtreme_dialog_data.perform = 'first-page';
			xtreme_dialog_data.callback_id = $(this).attr('rel');
			
			$.post(ajaxurl, xtreme_dialog_data,
				function(data) {
					$('#x-portlet-editing').html(data).show().css({
						'position' 	: 'absolute',
						'width' 	: xtreme_dialog_pos.width + 'px',
						'top' 		: xtreme_dialog_pos.top + 'px',
						'left' 		: xtreme_dialog_pos.left + 'px'  
					});
				}
			);
		});

		//filter exceptions
		$('.xtreme-fex a.x-dlg-expressions').live('click', function(event) {
			event.preventDefault();
			$('#x-portlet-editing-close').trigger('click');
			
			if($("body").hasClass("iframe")) {
				xtreme_dialog_pos.top = 5;
				xtreme_dialog_pos.left = 0;
				$('form').hide();
			}else {
				wrapper = $("body").hasClass("widgets_access") ? '.editwidget' : '.widget-content';
				xtreme_dialog_pos.top = ($(this).offset().top-5)-23;
				if($("body").hasClass("rtl")) {
					adjustment = $("body").hasClass("widgets_access") ? -250 : 17;
					xtreme_dialog_pos.left = parseInt($(this).closest(wrapper).offset().left)+(parseInt($(this).closest(wrapper).width())+adjustment);
				}else{
					adjustment = $("body").hasClass("widgets_access") ? -200 : 37;
					xtreme_dialog_pos.left = parseInt($(this).closest(wrapper).offset().left)-(xtreme_dialog_pos.width+adjustment);
				}
			}
			xtreme_dialog_data.action = "edit_widget_filter_expressions";
			xtreme_dialog_data.dlg_type = $(this).attr('class');
			xtreme_dialog_data.filter = $(this).closest('.x-filter').find('label:first input').val();
			xtreme_dialog_data.widget = $(this).closest('.widget').find('.widget-title h4').text();
			xtreme_dialog_data.sidebar = $(this).closest('.widgets-holder-wrap').find('.sidebar-name h3').text();
			xtreme_dialog_data.target_input = $(this).closest('.xtreme-fex').find('.fec-input').attr('name');
			xtreme_dialog_data.value = $(this).closest('.xtreme-fex').find('.fec-input').val();
			xtreme_dialog_data.s = '';
			xtreme_dialog_data.s_media = 'all';
			xtreme_dialog_data.content = xtreme_dialog_data.value.length == 0 ? 'standard' : 'selection';
			xtreme_dialog_data.perform = 'first-page';
			xtreme_dialog_data.callback_id = '';
			
			$.post(ajaxurl, xtreme_dialog_data,
				function(data) {
					$('#x-portlet-expression').html(data).show().css({
						'position' 	: 'absolute',
						'width' 	: xtreme_dialog_pos.width + 'px',
						'top' 		: xtreme_dialog_pos.top + 'px',
						'left' 		: xtreme_dialog_pos.left + 'px'  
					});

				}
			);
			
		});
		
		function exec_dialog_action(perform) {
			xtreme_dialog_data.perform = perform;
			$.post(ajaxurl, 
				xtreme_dialog_data,
				function(data) {
					if(xtreme_dialog_data.dlg_type != 'x-dlg-exceptions' && $('#x-portlet-expression').is(':visible')) {
						$('#x-portlet-editing').css({
							'position' : 'absolute',
							'width' 	: xtreme_dialog_pos.width + 'px',
							'top' 		: xtreme_dialog_pos.top + 'px',
							'left' 		: xtreme_dialog_pos.left + 'px'  
						}).show();
						$('#x-portlet-expression').hide();					
					}
					$('#x-portlet-editing').html(data);
				}
			);
		}

		//dialog navi handling
		$('#x-portlet-editing #search-submit').live('click', function(event) {
			event.preventDefault();
			xtreme_dialog_data.s = $('#xtreme-dialog-search-input').val();
			exec_dialog_action('search');
		});
		
		$('#x-portlet-editing #xtreme-dialog-search-input').live('keyup', function(event) {
			event.preventDefault();
			if(event.which != 13)
				return
			xtreme_dialog_data.s = $(this).val();
			exec_dialog_action('search');
		});
		
		$('#x-portlet-editing .x-media-type').live('click', function(event) {
			event.preventDefault();
			xtreme_dialog_data.s_media = $(this).attr('rel');
			exec_dialog_action('search');
		});

		$('#x-portlet-editing .first-page').live('click', function(event) {
			event.preventDefault();
			if($(this).hasClass('disabled'))
				return;
			exec_dialog_action('first-page');
		});
		
		$('#x-portlet-editing .prev-page').live('click', function(event) {
			event.preventDefault();
			if($(this).hasClass('disabled'))
				return;
			exec_dialog_action('prev-page');
		});

		$('#x-portlet-editing .next-page').live('click', function(event) {
			event.preventDefault();
			if($(this).hasClass('disabled'))
				return;
			exec_dialog_action('next-page');
		});

		$('#x-portlet-editing .last-page').live('click', function(event) {
			event.preventDefault();
			if($(this).hasClass('disabled'))
				return;
			exec_dialog_action('last-page');
		});
		$('#x-portlet-editing .current-page').live('keyup', function(event) {
			event.preventDefault();
			if($(this).hasClass('disabled') || event.which != 13)
				return;
			xtreme_dialog_data.page = $(this).val();
			exec_dialog_action('this-page');
		});

		$('#x-portlet-editing-apply').live('click', function(event) {
			event.preventDefault();
			exec_dialog_action('apply-changes');
			if ($('body').hasClass('iframe'))
				$('form').show();
		});
		
		$('#x-portlet-editing .dlg-selection').live('click', function(event) {
			event.preventDefault();
			xtreme_dialog_data.content = $(this).attr('id') == 'x-portlet-editing-assigned' ? 'selection' : 'standard';
			exec_dialog_action('first-page');
		});
		
		$('.fex-maintoggle').live('change', function(event) {
			event.preventDefault();
			var ck = $(this).is(":checked");
			$('.fex-checkbox').each(function(i, el) {
				if ($(el).is(":checked") != ck)
					$(el).trigger('click');
			});
		});
		
		$('.fex-checkbox').live('change', function(event) {
			event.preventDefault();
			var fexs = xtreme_dialog_data.value.length != 0 ? xtreme_dialog_data.value.split(',') : [];
			if ($(this).is(':checked')) {
				fexs.push($(this).val());
				xtreme_dialog_data.value = fexs.join(',');
				$(this).closest('tr').find('td').addClass("fex-selected");
				$('#x-portlet-editing-selected b').html(fexs.length);
			}else{
				var fex2 = [];
				for(var i=0; i<fexs.length; i++) {
					if (fexs[i] != $(this).val()) 
						fex2.push(fexs[i]);
				}
				xtreme_dialog_data.value = fex2.length == 0 ? "" : fex2.join(',');
				$(this).closest('tr').find('td').removeClass("fex-selected");
				$('#x-portlet-editing-selected b').html(fex2.length);				
			}
		});
		
		
		// advanced condition editor	

		function check_syntax() {
			var r = true;
			var e = $('#x-expression-editor .consumer li').toArray();
			var bc = { 'enter' : 0, 'leave' : 0 };
			$('#x-expression-editor .consumer li').removeClass('error').removeClass('error-blue');
			for (var i=0; i<e.length; i++) {
				var c = $(e[i]).attr('term');
				var n = $(e[i+1]).attr('term');
				if ($.inArray(c, ['enter','leave']) != -1)
					bc[c] = bc[c] +1;
				if(n && ($.inArray(n, xtreme_syntax[c]) == -1)) {
					$(e[i+1]).addClass('error');
					r = false;
				}
			}
			//first can't be all
			if(e.length > 0 && ($.inArray($(e[0]).attr('term'), xtreme_syntax['not-first']) != -1)) {
				$(e[0]).addClass('error');
				r = false;
			}
			//last can't be all
			if(e.length > 0 && ($.inArray($(e[e.length-1]).attr('term'), xtreme_syntax['not-last']) != -1)) {
				$(e[e.length-1]).addClass('error');
				r = false;
			}
			//bracket count
			if (bc['enter'] != bc['leave']) {
				$('#x-expression-editor .consumer li').each(function(i) {
					if($(this).attr('term') == 'enter')
						$(this).addClass(bc['enter'] > bc['leave'] ? 'error' : 'error-blue');
					if($(this).attr('term') == 'leave')
						$(this).addClass(bc['leave'] > bc['enter'] ? 'error' : 'error-blue');
				});
				r = false;
			}
			$('#x-expression-editor-apply').toggle(r);
			return r;
		}
		
		function apply_expression() {
			//evaluate the complete expression tokens
			var e = $('#x-expression-editor .consumer li').toArray();
			var r = [];
			var t = '';
			for (var i=0; i<e.length; i++) {
				t = $(e[i]).attr('term');
				d = $(e[i]).find('input').val();
				if (d) t += ':'+d;
				r.push(t);
			}
			t = r.join('|');
			$(xtreme_condition_target).val(t);
			var cnt = 0;
			$(xtreme_condition_target).closest('.xtreme-fex').find('input').each(function(i, e) {
				if ($(this).val().length > 0) 
					cnt++;
			});
			if(cnt == 0)
				$(xtreme_condition_target).closest('.x-filter').removeClass("fex-selected");
			else
				$(xtreme_condition_target).closest('.x-filter').addClass("fex-selected");
			xtreme_dialog_data.value = '';
			$('#x-portlet-editing-close').trigger('click'); //close it now.
			$('body').trigger('visualize_filter_state');
		}	
		
		$(".consumer a.del").live('click', function(event) {
			event.preventDefault();
			$(this).closest('li').remove();
			check_syntax();
		});
		$(".consumer a.edit").live('click', function(event) {
			event.preventDefault();
			$(this).closest('li').find('input').attr('name', 'current-condition');
			xtreme_dialog_data.target_input = 'current-condition';
			xtreme_dialog_data.action = 'edit_widget_filter_exceptions';
			xtreme_dialog_data.filter = $(this).closest('li').attr('term');
			xtreme_dialog_data.s = '';
			xtreme_dialog_data.value = $(this).closest('li').find('input').val();
			xtreme_dialog_data.content = xtreme_dialog_data.value.length == 0 ? 'standard' : 'selection';
			exec_dialog_action('first-page');
		});
		
		$('#x-expression-editor-reset').live('click', function(event) {
			event.preventDefault();
			$('#x-expression-editor .consumer li').remove();
		});
		
		$('#x-expression-editor-apply').live('click', function(event) {
			event.preventDefault();
			//semantic check first
			if(check_syntax()) {
				apply_expression();
			}
		});
		
		$('#x-portlet-editing-back').live('click', function(event) {
			event.preventDefault();
			$('#x-portlet-editing').hide();
			$('#x-portlet-expression').show();
			$('input[name="'+xtreme_dialog_data.target_input+'"]').attr('name', '');
		});
		
		$('#x-portlet-expression').live('check-syntax', check_syntax);
    });
		 
})(jQuery);