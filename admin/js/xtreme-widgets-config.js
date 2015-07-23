/** XTREME THEME HEADER **/

var xtreme_widget_partial 		= [];
var xtreme_widget_del_queue 	= [];
xtreme_widget_del_queue_delta 	= 0;
xtreme_widget_del_queue_current = 0;
var xtreme_classes = [".x-content", ".x-switcher", ".x-rows", ".x-columns", ".x-image-url", ".x_itemsize", ".x-social-icons", ".x-social-title", ".x-social-url", ".x-thumbnail"];

 (function($) {
   
	$(document).ready(function(){
			
		//widget component interaction
		//--------------------------------------------------
		
		$('.x-content').live('change', function(){
			switch(this.value) {
				case 'xtreme_excerpt':
					$('.'+ this.id+' .excerpt_length').show();
					$('.'+ this.id+' .excerpt_more').show();
					$('.'+ this.id+' .excerpt_morelink_text').show();
					$('.'+ this.id+' .excerpt_show_tags').show();
					break;
				case 'excerpt':
					$('.'+ this.id+' .excerpt_length').hide();
					$('.'+ this.id+' .excerpt_more').hide();
					$('.'+ this.id+' .excerpt_show_tags').hide();
					$('.'+ this.id+' .excerpt_morelink_text').show();
					break;
				case 'title_only':
					$('.'+ this.id+' .excerpt_length').hide();
					$('.'+ this.id+' .excerpt_more').hide();
					$('.'+ this.id+' .excerpt_show_tags').hide();
					break;
				case 'nothing':
					$('.'+ this.id+' .excerpt_length').hide();
					$('.'+ this.id+' .excerpt_more').hide();
					$('.'+ this.id+' .excerpt_show_tags').hide();
					break;
				case 'both':
					$('.'+ this.id+' .excerpt_length').show();
					$('.'+ this.id+' .excerpt_more').show();
					$('.'+ this.id+' .excerpt_show_tags').hide();
					break;
				case 'content':
					$('.'+ this.id+' .excerpt_length').hide();
					$('.'+ this.id+' .excerpt_more').hide();
					$('.'+ this.id+' .excerpt_morelink_text').hide();
					$('.'+ this.id+' .excerpt_show_tags').hide();
					break;
			}
		});
		
		$('.x-switcher').live('change', function(){
			if(this.checked == true) {
				$('.'+ this.id).show();
			}else{
				$('.'+ this.id).hide();
			}
        });
		
		$('.x-rows').live('change', function(){
			var mybox = $(this).attr('id');
			var num = $('#'+ mybox).val();
			$('.'+mybox).hide();
			for (var i=0; i<num; i++ ) {
				$('.'+ mybox+'-'+i).show();
			}
		});
		
		$('.x-columns').live('change', function(){
			var cols = $(this).attr('id');
			var num = $('#'+ cols).val();
			$('.'+cols).hide();
			for (var i=1; i<=num; i++ ) {
				$('.'+ cols+'-'+(i)).show();
			}
		});
		
        $('.x_itemsize').live('change', function(){
			if(this.value == 'value') {
				$('.'+ this.id).show();
			}else{
				$('.'+ this.id).hide();
			}
		});
	
		$('.x-image-url').live('change', function() {
			if(this.value == 'userdef') {
				$(this).parent().next('.x-image-url-content').first().show();
			} else {
				$(this).parent().next('.x-image-url-content').first().hide();
			}
		});
			
		$('.x-social-icons').live('change', function() {
			if(this.value == 'none') {
				$(this).parent().next('.x-social-icons-content').first().hide();
			} else {
				$(this).parent().next('.x-social-icons-content').first().show();
			}
		});
			
		$('.x-social-title').live('change', function() {
			if(this.value == 'userdef') {
				$(this).parent().next('.x-social-title-content').first().show();
			} else {
				$(this).parent().next('.x-social-title-content').first().hide();
			}
		});
		
		$('.x-social-url').live('change', function() {
			if(this.value == 'userdef') {
				$(this).parent().next('.x-social-url-content').first().show();
			} else {
				$(this).parent().next('.x-social-url-content').first().hide();
			}
		});
		$('.x-thumbnail').live('change', function(){
			if(this.checked == true) {
				$('.'+ this.id).show();
			}else{
				$('.'+ this.id).hide();
			}
		});
		
		$('body').bind('renumber_text_grids_autop', function(e, scope) {
			$('li input[type="checkbox"]', scope).each(function(i, el) {
				$(el).val(i);
			});
		});
		$('body').bind('init_text_grids', function(e, scope) {
			$('.x-textarea-grid', scope).sortable({
				placeholder: 'x-textarea-grid-paceholder',
				update: function(event, ui) {
					$('body').trigger('renumber_text_grids_autop', $(this).closest('.widget'));
				}
			});
			$('.x-textarea-grid', scope).disableSelection();
		});
		$('body').trigger('init_text_grids', document);
		$('.x-textcolumns').live('change', function(){
			var g = $('.x-textarea-grid', $(this).closest('.widget'));
			for(i=2;i<6;i++) { $(g).removeClass('columns-'+i); };
			$(g).addClass('columns-'+$(this).val());
			$('.x-textrows',  $(this).closest('.widget')).trigger('change');
		});
		$('.x-textrows').live('change', function(){
			var g = $('.x-textarea-grid', $(this).closest('.widget'));
			var count = $('.x-textcolumns', $(this).closest('.widget')).val() * $(this).val();
			var a = $('li', g);
			if (a.length != count) {
				if (a.length < count) {
					var c = $('li:first', g);
					for (i=0;i<count-a.length; i++){
						$(g).append(c.clone());
						$('li:last', g).removeClass('none-empty').removeAttr('title');
						$('li:last textarea', g).val("");
						$('li:last input.description', g).val("");
						$('li:last input[type="checkbox"]', g).removeAttr('checked');
					}
				}else{
					for(i=0;i<a.length-count; i++) {
						$('li:last', g).remove();
					}
				}
			}
			$('.x-distribution', $(this).closest('.widget')).addClass('hidden');
			$('.x-distribution-'+$('.x-textcolumns', $(this).closest('.widget')).val(), $(this).closest('.widget')).removeClass('hidden');
		});
		
		
		$('.x-textarea-grid li a.edit').live('click', function(event) {
			event.preventDefault();
			if ($('textarea', $(this).closest('li')).is(':visible')) {
				$(this).addClass('ui-icon-pencil').removeClass('ui-icon-close');
				$(this).closest('.x-textarea-grid').disableSelection().sortable( "option", "disabled", false );
				$('li', $(this).closest('.x-textarea-grid')).show();
				$(this).closest('li').removeClass('x-textarea-grid-full');
				$(this).closest('.x-textarea-grid').removeClass('x-textarea-grid-full');
				$('textarea, p', $(this).closest('li')).hide();
				if ($('textarea',$(this).closest('li')).val().length) {
					$(this).closest('li').addClass('none-empty');
				} else   {
					$(this).closest('li').removeClass('none-empty');
				}
				$(this).closest('li').attr({'title' : $('input.description', $(this).closest('li')).val()});
			}else{
				$(this).removeClass('ui-icon-pencil').addClass('ui-icon-close');
				$(this).closest('.x-textarea-grid').enableSelection().sortable( "option", "disabled", true );
				$('li', $(this).closest('.x-textarea-grid')).hide();
				$(this).closest('li').show().addClass('x-textarea-grid-full');
				$(this).closest('.x-textarea-grid').addClass('x-textarea-grid-full');
				$('textarea, p', $(this).closest('li')).show();
				$(this).closest('li').attr({'title' : '' });
			}
		});
		
		for(j=0; j<xtreme_classes.length; j++){
			$(xtreme_classes[j]).trigger('change');
		}
		
		//portlet handling
		//----------------------------------------------------------------
		
		//custom event
		$('body').bind('check_portlet_content', function(e, scope) {
			if ($(scope).sortable( 'toArray' ).length == 0) {
				$(scope).parent().find(".x-portlet-wrapper-empty").show();
				$(scope).css({'padding-top' : '20px'});
			}
			else {
				$(scope).parent().find(".x-portlet-wrapper-empty").hide();
				$(scope).css({'padding-top' : '0px'});
			}
		});
		
		//custom event
		$('body').bind('init_portlet', function(e, scope) {
			//setup portlets and connect if nessesary
			$( ".x-portlet-column", scope ).each(function(i,el) {
				$(el).sortable({
					containment: $(el).hasClass('x-portlet-dest') ? 'parent' : false,
					delay: 50,
					connectWith: $(el).parent().attr('class').replace(/x\-portlet\-wrapper /,'.')+' .x-portlet-column',
					receive: function(event, ui) {
						$(this).css({'padding-top' : '0px'});
						$(this).parent().find('.x-portlet-wrapper-empty').hide();
					}
				});
			});

			//add the portlet expander icons
			$( ".x-portlet-item", scope ).each(function(i, p) {
				if(!$(p).hasClass('ui-widget')) {
					$(p).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
					.find( ".x-portlet-item-header" )
					.addClass( "ui-widget-header ui-corner-all" )
					.prepend( "<span class='ui-icon ui-icon-plusthick'></span>");
				}
			});

			//collapse inital portlet content
			$( ".x-portlet-item-content", scope ).hide();
			
			//initial content count check
			$( ".x-portlet-column", scope ).each(function(i, el) {
				$('body').trigger('check_portlet_content', $(el));
			});
			
			//multisite burn it setup
			//TODO: simplify it
			if(scope == document) {
				$('.widget').each(function(i, el) {
					var sidebar = $(el).closest('.widgets-sortables').attr('id');
					var id_base = $(el).closest('.widget').find('input[name="id_base"]').val();
					var number = ($(el).closest('.widget').find('input[name="multi_number"]').val() ||
								 $(el).closest('.widget').find('input[name="widget_number"]').val());
					$(el).find('input[name*="xtreme_burn_it_sidebar"]').val( sidebar );
					$(el).find('input[name*="xtreme_burn_it_id_base"]').val( id_base );
					$(el).find('input[name*="xtreme_burn_it_widget_number"]').val( number ) ;
				});		
			}else{
				var sidebar = $(scope).closest('.widgets-sortables').attr('id');
				var id_base = $(scope).closest('.widget').find('input[name="id_base"]').val();
				var number = ($(scope).closest('.widget').find('input[name="multi_number"]').val() ||
							 $(scope).closest('.widget').find('input[name="widget_number"]').val());
				$(scope).closest('.widget').find('input[name*="xtreme_burn_it_sidebar"]').val( sidebar );
				$(scope).closest('.widget').find('input[name*="xtreme_burn_it_id_base"]').val( id_base );
				$(scope).closest('.widget').find('input[name*="xtreme_burn_it_widget_number"]').val( number ) ;
			}
		});
		$('body').trigger('init_portlet', document);

		//attach event for expanding/collapsing portlet content
		$( ".x-portlet-item-header .ui-icon" ).live('click', function() {
			$( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
			$( this ).parents( ".x-portlet-item:first" ).find( ".x-portlet-item-content" ).toggle();
		});
		
		//attach event for removing a portlet
		$( ".x-portlet-item-del" ).live('click', function(event) {
			event.preventDefault();
			col = $(this).parent().parent().parent().parent();
			$(this).parent().parent().parent().remove();
			$('body').trigger('check_portlet_content', col);
		});
		
		//attach event for removing all portlet
		$(".x-portlet-removeall").live('click', function(event) {
			event.preventDefault();
			$(this).parent().prev(".x-portlet-wrapper").first().find(".x-portlet-column").html("");
			$('body').trigger('check_portlet_content', $(this).parent().prev(".x-portlet-wrapper").first().find(".x-portlet-column"));
		});
		
		$('.x-portlet-help').live('click', function(event) {
			event.preventDefault();
			$(this).parent().next('.x-portlet-help-content').toggle();
		});
		
		//prepare globall portlet editing container
		$('body').append('<div id="x-portlet-editing" style="display:none"></div>');
		$('body').append('<div id="x-portlet-expression" style="display:none"></div>');
		
		$('.widget-control-save').live('click', function() {
			$('#x-portlet-editing').hide();
		});
		
		//handle portlet editing
		$('.x-portlet-edit').live('click', function(event) {
			event.preventDefault();
			var anchor = $(this);
			anchor.prev('.ajax-feedback').css({'visibility': 'visible'});
			var ids = [];
			anchor.closest('.widget-content').find('.x-portlet-dest input[name*="portlet_id"]').each(function(i, el) {
				ids.push($(el).val());
			});
			var target_portlet_ofs = anchor.closest('.widget-content').find('.x-portlet-wrapper:first').offset();
			var target_portlet_width =  parseInt(anchor.closest('.widget-content').find('.x-portlet-wrapper:first').width());
			var target_portlet_wrapper_width = parseInt(anchor.closest('.widget-content').find('.x-portlet-wrapper input[name="x-portlet-ajax-width"]').val());
			$.post(ajaxurl, {
					'action' 		: anchor.closest('.widget-content').find('.x-portlet-wrapper input[name="x-portlet-ajax-action"]').val(),
					'widget' 		: anchor.closest('.widget').attr('id'),
					'portlet_id'	: ids,
					'widget_number'	: anchor.closest('.widget').find('.multi_number').val() || anchor.closest('.widget').find('.widget_number').val(),
					'widget-id' 	: anchor.closest('.widget').find('.widget-id').val(),
					'id_base'		: anchor.closest('.widget').find('.id_base').val()
				},
				function(data) {
					if($("body").hasClass("rtl")) {
						$('#x-portlet-editing').html(data).show().css({
							'position' : 'absolute',
							'width' : target_portlet_wrapper_width+'px',
							'top' : target_portlet_ofs.top-25 + 'px',
							'left' : target_portlet_ofs.left+(target_portlet_width+50) + 'px'  
						});
					}else{
						$('#x-portlet-editing').html(data).show().css({
							'position' : 'absolute',
							'width' : target_portlet_wrapper_width+'px',
							'top' : target_portlet_ofs.top-25 + 'px',
							'left' : target_portlet_ofs.left-(target_portlet_wrapper_width+25) + 'px' 
						});
					}

					$('body').trigger('init_portlet', $('#x-portlet-editing'));
					for(j=0; j<xtreme_classes.length; j++){
						$(xtreme_classes[j], $('#x-portlet-editing')).trigger('change');
					}
					anchor.prev('.ajax-feedback').css({'visibility': 'hidden'});
				}
			);
		});
		
		$('#x-portlet-editing-close, .widget-control-close, .widget-action, .widget-control-remove').live('click', function(event) {
			event.preventDefault();
			$('#x-portlet-editing').hide();
			$('#x-portlet-expression').hide();
			if ($('body').hasClass('iframe'))
				$('form').show();
		});
		
		
		// ajax widget call filter
		//-------------------------------------------------------------
		
		function options2array(options) {
			var opt = new Array();
			if(options.data) {
				var a = options.data.split("&");
				for (i=0; i<a.length; i++) {
					var b = a[i].split("=");
					opt[b[0]] = b[1];
				}
			}
			return opt;
		}
		
		if (!$("body").hasClass("widgets-php")) {
			$.ajaxPrefilter( function( options ) {
				var opt = options2array(options);
				if (opt.action == "save-widget") {
					options.data = options.data.replace(/action=save\-widget/, 'action=xcontentwidget-save-widget');
					options.data += "&post_id=" + $("input[name='post_id']").val();
				}else if(opt.action == "widgets-order") {
					options.data = options.data.replace(/action=widgets\-order/, 'action=xcontentwidget-widgets-order');
				}
			});
		}
		
		$(document).ajaxSend( function( event, jqXHR, options ) {	
			var opt = options2array(options); 	
			if (opt.action == "save-widget" || opt.action == 'xtreme_post_social_selector') {
				xtreme_widget_partial.push('div[id$="'+opt['widget-id']+'"]');
			} 
		});
		
		$(document).ajaxSuccess( function( event, XMLHttpRequest, options ) {
			var opt = options2array(options);
			if (opt.action == "save-widget" || opt.action == 'xtreme_post_social_selector') {
				for(i=0;i<xtreme_widget_partial.length;i++){
					for(j=0; j<xtreme_classes.length; j++){
						$(xtreme_classes[j], $(xtreme_widget_partial[i])).trigger('change');
					}
					$('.xtreme-widget-burn-it input[type="checkbox"]').trigger('change');
					$('body').trigger('init_portlet', $(xtreme_widget_partial[i]));	
					$('body').trigger('convert_checkboxes', $(xtreme_widget_partial[i]));
					$('body').trigger('init_text_grids', $(xtreme_widget_partial[i]));
				}
				xtreme_widget_partial = [];
				//bulk erase because avoid concurent erase
				if(xtreme_widget_del_queue.length) {
					wpWidgets.save($(xtreme_widget_del_queue.pop()), 1, 0, 0, 0);
					xtreme_widget_del_queue_current += xtreme_widget_del_queue_delta;
					$('#dialog-confirm .progressbar .widget .widget').css('width', Math.round(xtreme_widget_del_queue_current)+'%');
					$('#dialog-confirm .progressbar .widget .widget div').html(Math.round(xtreme_widget_del_queue_current)+'&nbsp;%');
				}
				else {					
					$( "#dialog-confirm p" ).show();
					$('#dialog-confirm .progressbar').hide();
					$('#dialog-confirm .progressbar .widget .widget div').html('0&nbsp;%');
					$('#dialog-confirm .progressbar .widget .widget').css('width', '0%');
					$('.ui-dialog-buttonpane').show();
					$( "#dialog-confirm" ).dialog("close");
				}
			}
		});	
		
		//burnings
		$('#xtreme-admin-burnings .xtreme-admin-burnings-item').each(function(i, el) {
			$('#'+$(el).html()+' .sidebar-description').append($(el).next('div'));
		});
		$('#xtreme-admin-burnings').remove();
		$('.xtreme-widget-burn-it input[id$="xtreme_burn_it"]').live('change', function() {
			if($(this).attr('checked')){
				$(this).closest('.widget').find('.widget-top').addClass('x-burned-widget');
				$(this).parent().next('p').show();
			}else{
				$(this).parent().next('p').hide();
				$(this).closest('.widget').find('.widget-top').removeClass('x-burned-widget');
			}
		}).trigger('change');
		
		//cloning
		$('.x-duplicate a').live('click', function(event){
			event.preventDefault();
			var widget = $(this).closest('.widget');
			var widget_clone = widget.clone();
			var id_base = widget.find('input.id_base').val();
			var src_number = widget.find('.multi_number').val() || widget.find('input.widget_number').val();
			var template = $('#available-widgets').find('input[value="'+id_base+'"]').closest('.widget');
			
			//taken from WordPress widgets.js
			var add = template.find('input.add_new').val(),
				n = template.find('input.multi_number').val(),
				id = template.attr('id'),
				sb = widget.closest('.widgets-sortables').attr('id');

			if ( 'single' == add ) return; //attention: single widgets are not cloneable
				
			//now tune the clone to be a new one widget
			widget_clone.find('input.add_new').val(add);
			widget_clone.find('input.multi_number').val(n);
			//now tune the clone content and replace numbers
			var content = widget_clone.wrap('<div/>').parent().html();
			var r1 = new RegExp(id_base+'-'+src_number, "g");
			var r2 = new RegExp(id_base+'\\['+src_number+'\\]', "g");
			content = content.replace(r1, id_base+'-'+n);
			content = content.replace(r2, id_base+'['+n+']');
			n++;
			template.find('input.multi_number').val(n);
			widget.after(content);		
			wpWidgets.save( widget.next('.widget'), 0, 0, 1 );
			widget.next('.widget').find('input.add_new').val('');
		});
		var ext_clone = $('#x-duplicate').remove();
		ext_clone.attr('id', '');
		$('.widget').find('.widget-control-actions:last').append(ext_clone.clone());

		//sidebar erase
		var ext_erase = $('#x-erase').remove();
		ext_erase.show().attr('id', '');
		$('#widgets-right .sidebar-name h3,#widgets-left .sidebar-name:last h3').prepend(ext_erase.clone());
		$('.sidebar-name h3>.x-erase').click(function(event) {
			event.preventDefault();
			$(this).parent().trigger('click'); //to ensure same state as before
			var targets = $(this).closest('.widgets-holder-wrap').find('.widgets-sortables .widget').toArray();
			$( "#dialog-confirm" ).dialog({
				resizable: false,
				height:190,
				modal: true,
				buttons: [
					{
						text : xtreme_widgets_config_l10n.erase_button,
						click : function() {
							//perform queued erase to avoid parallel erase conflicts
							xtreme_widget_del_queue = targets;
							if(xtreme_widget_del_queue.length) {
								xtreme_widget_del_queue_delta = 100.0 / xtreme_widget_del_queue.length;
								xtreme_widget_del_queue_current = xtreme_widget_del_queue_delta;
								$('#dialog-confirm p').hide();
								$('#dialog-confirm .progressbar .widget .widget').css('width', Math.round(xtreme_widget_del_queue_current)+'%');
								$('#dialog-confirm .progressbar .widget .widget div').html(Math.round(xtreme_widget_del_queue_current)+'&nbsp;%');
								$('#dialog-confirm .progressbar').show();
								$('.ui-dialog-buttonpane').hide();
								wpWidgets.save($(xtreme_widget_del_queue.pop()), 1, 0, 0, 0);
							}
							else
								$( this ).dialog( "close" );
						}
					},
					{
						text : xtreme_widgets_config_l10n.cancel_button,
						click : function() {
							$( this ).dialog( "close" );
						}
					}
				]
			});		

		});
		
		
		//prevent caching input field status on reload (especially Firefox)
		$('form').attr('autocomplete', 'off');
	
    });
 
 })(jQuery);