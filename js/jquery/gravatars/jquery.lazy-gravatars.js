/** XTREME THEME HEADER **/
 
 function patch_gravatars() {
		jQuery('img[src$="xtreme-avatar-lazy.gif"]').each(function(i, el) {
			v = jQuery(el).attr('longdesc');
			jQuery(el).attr('longdesc', '');
			jQuery(el).attr('src', v);
		});
 };
 
 (function($) {
	$(document).ready(function(){
		window.setTimeout("patch_gravatars()", 1000);
	});
 })(jQuery);