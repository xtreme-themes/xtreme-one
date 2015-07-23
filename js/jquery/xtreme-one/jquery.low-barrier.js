/** XTREME THEME HEADER 

 * Modified by Sunflower to work with custom low-barrier.css
 * http://xfco.de/patch/155/xtreme-low-barrier
**/
(function($) {
	var initial_body_fontsize = parseInt($('body').css('font-size'));

    $.fn.extend({
        xtremeLowBarrier: function(config) {
            var defaults = {
				automatic: true,
				medium: 150,
				maximum: 200
			};
			this.options = $.extend(defaults,config);
			var o = this;
			return this.each(function() {
				var el = $(this);
				$('ul li a', el).click(function(event) {
					event.preventDefault();
					$(this).blur();
					var html_class = $(this).attr('rel');
					if($(this).hasClass('barrier-font')) {
						if(!$("html").hasClass(html_class)) $("html").removeClass('original').removeClass('medium').removeClass('maximum');
						$('.xtreme_low_barrier ul li a.barrier-font').removeClass('current');
						document.cookie = "xfont="+html_class+"; path=/";
					       // T.C.	if (o.options.automatic == true) {
							var fac = 1.0;
							switch(html_class) {
								case 'medium':
									fac = o.options.medium / 100.0;
									break;
								case 'maximum':
									fac = o.options.maximum / 100.0;
									break;
								default:
									fac = 1.0;
									break;
							}
							$('body').css({ 'font-size' : (initial_body_fontsize * fac) +'px' });
					       // T.C.	}
						$('#imageheader .flex-media .flexslider .slides li a img').attr({ 'width' : '100%'});
						$('.xtreme_low_barrier ul li a.barrier-font[rel="'+html_class+'"]').addClass("current");

					}else if($(this).hasClass('barrier-contrast')) {
						if(!$("html").hasClass(html_class)) $("html").removeClass('themecontrast').removeClass('highcontrast').removeClass('highcontrast_auto');
						$('.xtreme_low_barrier ul li a.barrier-contrast').removeClass('current');
						document.cookie = "xcontrast="+html_class+"; path=/";
						$('.xtreme_low_barrier ul li a.barrier-contrast[rel="'+html_class+'"]').addClass("current");
						if(html_class == 'highcontrast' && o.options.automatic == true) {
							html_class = 'highcontrast_auto';
						}
					}
					if(html_class != 'original' && html_class != 'themecontrast') $("html").addClass(html_class);
				});
				//execution of possible cookie settings
				var a = document.cookie.split(';');
				for(var i=0; i<a.length; i++) {
					if (a[i].match(/^\s*xfont\s*=\s*/gi)) {
						$("ul li a[rel='"+a[i].replace(/^\s*xfont\s*=\s*/gi, '')+"']", el).trigger('click');
					}
					if (a[i].match(/^\s*xcontrast\s*=\s*/gi)) {
						$("ul li a[rel='"+a[i].replace(/^\s*xcontrast\s*=\s*/gi, '')+"']", el).trigger('click');
					}
				}

			});
		}
    });
})(jQuery);