/** XTREME THEME HEADER **/
 
(function($){
	$.fn.mobilefish = function(op){
		return this.each(function() {
			$('li:has(ul)',this).each(function() {
				if ($(this).parent().hasClass('sf-menu')) {
					$('>a:first-child',this).append('<span class="sf-sub-indicator-mobile">&#9660;</span>');	//9650
				}else{
					$('>a:first-child',this).append('<span class="sf-sub-indicator-mobile">&#9658;</span>');	//9668			
				}
			});
			$('.sub-menu', this).each(function() { $(this).css( {'visibility' : 'hidden'}) });
			$('.sf-sub-indicator-mobile', this).click(function(event) {
				event.preventDefault();
				if($(this).closest('li').toggleClass('sfHover').hasClass('sfHover')) {
					$('.sub-menu:first', $(this).closest('li')).css( {'visibility' : 'visible'});
				}else{
					$('.sub-menu', $(this).closest('li')).css( {'visibility' : 'hidden'});
					$('.sfHover', $(this).closest('li')).removeClass('sfHover');
				}
				
			});
		});
	};
})(jQuery);
