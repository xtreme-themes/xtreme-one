/** XTREME THEME HEADER **/

 (function($) {
   
	$(document).ready(function(){
						
		var defaulttxt = "auto";
		$('#widget-width').focus(function(){if($(this).val() == defaulttxt){$(this).val('');}});
		$('#widget-width').blur(function(){if($(this).val() == ''){$(this).val(defaulttxt);}});
			
		$('#xf_mce_update, #xf_mce_insert').click(function(event) {
			event.preventDefault();	
			var command = $(this).attr('id') == 'xf_mce_update' ? 'mceReplaceWidget' : 'mceInsertWidget';
			var data = $('body').find('form').serialize(), a;
			a = {
				action: 'mce_xcontentwidget',
				action2: 'save'
			};
			
			data += '&' + $.param(a);
			
			$.post( ajaxurl, data, function(r){
			
				tinyMCEPopup.execCommand(command, false, r );
				tinyMCEPopup.close();
			
			});
			
		});
		
	});

 })(jQuery);		