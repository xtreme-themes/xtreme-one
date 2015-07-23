/** XTREME THEME HEADER **/

var xtreme_user_credentials = {checked : false};
	
function check_user_credentials() {

	jQuery('#credentials_dialog > form').find('input').each(function(i, e) {
		if ((jQuery(e).attr('type') == 'radio') && !jQuery(e).attr('checked')) return;
		var s = jQuery(e).attr('name');
		var v = jQuery(e).val();
		xtreme_user_credentials[s] = v;
	});

	//check if we need user credentials on send
	if (!xtreme_user_credentials.checked) {
		jQuery.ajax({
			type: "POST",
			url: "admin-ajax.php",
			data: jQuery.extend({action: 'xtreme_check_user_credentials'}, xtreme_user_credentials),
			success: function(msg){
				xtreme_user_credentials.checked = true;
			},
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				//handled in next version that also support all file system types
				if (XMLHttpRequest.status == '401') {
					jQuery('#credentials_dialog').html(XMLHttpRequest.responseText).dialog({
						width: '500px',
						closeOnEscape: false,
						modal: true,
						resizable: false,
						title: '<b>User Credentials required</b>',
						buttons: { 
							"Ok": function() { 
								setTimeout('check_user_credentials();', 50);
								jQuery('#credentials_dialog').dialog("close");
							},
							"Cancel": function() { 
								jQuery('#credentials_dialog').dialog("close"); 
							} 
						},
						open: function(event, ui) {
							jQuery('#credentials_dialog').show().css('width', 'auto');
						},
						close: function() {
							jQuery('#credentials_dialog').dialog("destroy");
						}
					});						
				}
				else{
					jQuery('#credentials_dialog').html(XMLHttpRequest.responseText).dialog({
						width: '500px',
						closeOnEscape: false,
						modal: true,
						resizable: false,
						title: '<b>Error</b>',
						buttons: { 
							"Ok": function() { 
								jQuery('#credentials_dialog').dialog("close");
							}
						},
						open: function(event, ui) {
							jQuery('#credentials_dialog').show().css('width', 'auto');
						},
						close: function() {
							jQuery('#credentials_dialog').dialog("destroy");
						}
					});
				}
				xtreme_user_credentials.checked = false;
				jQuery('#upgrade').hide().attr('disabled', 'disabled');
			}
		});
	}
}		
