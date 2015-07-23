/** XTREME THEME HEADER **/
 
 (function($) {
   
	$(document).ready(function(){
		
		$('body').bind('convert_checkboxes', function(e, scope) {
			//replacement of checkboxes
			$('.checkbox-js', scope).each(function(i,el) {
				var name = $(el).attr('name');
				var inverse = $(el).hasClass('checkbox-inverse');
				var val = $(el).val();
				var state = 'x-yes';
				var inp = name;
				if($(el).attr('checked')) {
					state = 'x-yes';
					if(inverse) {
						state = 'x-no';
					}
				}else{
					state = 'x-no';
					inp = '';
					if(inverse) {
						state = 'x-yes';
					}				
				}
				if($(el).hasClass('x-no-locked')) {
					state += ' x-no-locked';
				}
				if($(el).hasClass('x-yes-locked')) {
					state += ' x-yes-locked';
				}
				$(el).replaceWith('<a target="'+name+'" rel="'+(inverse ?  'inverse' : '')+'" href="#" class="alignleft x-yesno '+state+'"><input type="hidden" name="'+inp+'" value="'+val+'" /></a>');
			});
		});
		$('body').trigger('convert_checkboxes', document);
		
		//attach click handler for widgets
		$('.x-yesno').live('click', function(event) {
			event.preventDefault();
						
			if($(this).hasClass('x-no-locked') || $(this).hasClass('x-no-locked')) {
				return; //prevent any actions
			}
			$(this).toggleClass('x-yes').toggleClass('x-no');
			var inverse = $(this).attr('rel') == 'inverse';
			var name = $(this).attr('target');
			if($(this).hasClass('x-yes')) {
				if (inverse) name = '';
			}else{
				if(!inverse) name = '';
			}
			$(this).find('input').attr('name', name);
			$('body').trigger('change_group', $(this));
			
			if($('body').hasClass('widgets-php')) {
				var v = $(this).find('input').val() || '';
				if($(this).hasClass('x-no') || $(this).hasClass('x-no-locked')) 
					$('.warning-'+v, $(this).closest('.widget')).addClass('x-warning');
				else
					$('.warning-'+v, $(this).closest('.widget')).removeClass('x-warning');
			}else{
				var v = $(this).attr('target').replace('filter-', '').replace('[]',  '');
				if($(this).hasClass('x-no') || $(this).hasClass('x-no-locked')) 
					$('.warning-'+v, $(this).closest('.x-admin-widget')).addClass('x-warning');
				else
					$('.warning-'+v, $(this).closest('.x-admin-widget')).removeClass('x-warning');				
			}
			
		});

		$('.x-filter-section .x-yesno').live('click', function(event) {
			if($('body').hasClass('widgets-php')) {
				$('body').trigger('visualize_filter_state', $(this).closest('.widget'));
			}else{
				$('body').trigger('visualize_filter_state', $(this).closest('.x-admin-widget'));
			}
		});

		$('body').bind('change_group', function(e, button) {
			//switch complete group
			if($(button).attr('target').match(/_group$/)){
				var group = $(button);
				group.closest('.postbox').find('div .x-admin-widget span a.x-yesno').each(function(i, el) {
					if(group.hasClass('x-yes')) {
						if($(el).hasClass('x-no')) $(el).trigger('click');
					}else{
						if($(el).hasClass('x-yes')) $(el).trigger('click');
					}
				});
			}
		});
		
		//handler for change of filter system
		$('a.x-yesno[target="enable_filter_system"]').click(function(event) {
			event.preventDefault();
			$('.x-filter-wrapper').toggle($(this).hasClass('x-yes'));
		});
		
		//perform initial state of filter sections
		$('.x-filter-wrapper').toggle($('a.x-yesno[target="enable_filter_system"]').hasClass('x-yes'));
		
		//attach filter handler
		$('.x-admin-widget .x-edit-filter').click(function(event) {
			event.preventDefault();
			$(this).closest('.x-admin-widget').find('.x-filter-section').removeClass('x-hidden');
			$(this).closest('.x-admin-widget').find('.x-edit-filter').addClass('x-hidden');
			$(this).closest('.x-admin-widget').find('.x-done-filter').removeClass('x-hidden');
		});
		$('.x-admin-widget .x-done-filter').click(function(event) {
			event.preventDefault();
			$(this).closest('.x-admin-widget').find('.x-filter-section').addClass('x-hidden');
			$(this).closest('.x-admin-widget').find('.x-edit-filter').removeClass('x-hidden');
			$(this).closest('.x-admin-widget').find('.x-done-filter').addClass('x-hidden');
		});

		$('.x-admin-widget .x-remove-all-filter').click(function(event) {
			event.preventDefault();
			$(this).closest('.x-admin-widget').find('.x-filter-section a.x-yesno').each(function(i, el){
				if($(el).hasClass('x-no')) $(el).trigger('click');
			});
			$(this).closest('.x-admin-widget').find('.x-done-filter').trigger('click');
		});
		
		$('body').bind('visualize_filter_state', function(e, scope) {		
			$('.x-filter-section', scope).each(function(i, el) {
				var filtered = false;
				if($('body').hasClass('widgets-php')) {
					$(this).closest('.widget').find('.x-filter-section a.x-yesno').each(function(i, el){
						filtered = filtered || $(el).hasClass('x-no');
						filtered = filtered || $(el).hasClass('x-no-locked');						
						if($(el).hasClass('x-yes')) {
							var a = $(el).parent().parent().find('.xtreme-fex input').val() || "";
							filtered = filtered || (a.length != 0);
						}			
					});
					if( filtered ) {
						$(this).closest('.widget').find('.widget-title h4').addClass('x-active-filter');
					}else{
						$(this).closest('.widget').find('.widget-title h4').removeClass('x-active-filter');
					}					
				}else{
					$(this).closest('.x-admin-widget').find('.x-filter-section a.x-yesno').each(function(i, el){
						filtered = filtered | $(el).hasClass('x-no');
					});
					if( filtered ) {
						$(this).closest('.x-admin-widget').find('span').addClass('x-active-filter');
						$(this).closest('.x-admin-widget').css({'background-color' : '#FFF6B5'});
					}else{
						$(this).closest('.x-admin-widget').find('span').removeClass('x-active-filter');
						$(this).closest('.x-admin-widget').css({'background-color' : ''});
					}
				}
			});			
		});
		$('body').trigger('visualize_filter_state', document);		
		
		//suppress the prefilled old states
		$('input').attr('autocomplete', 'off');
	});

 })(jQuery);