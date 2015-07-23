/** XTREME THEME HEADER **/
 
 (function($) {
   
	$(document).ready(function(){
			
		$('a[href^="theme-editor.php?file"]').each(function(i, link) {
			if ($(link).attr('href').split("file=")[1].split("&")[0].split("/").length > 4)
				$(link).closest("li").remove();
		});
		
    });
 
 })(jQuery);	