/**
 * FeatureList - simple and easy creation of an interactive "Featured Items" widget
 * Examples and documentation at: http://jqueryglobe.com/article/feature_list/
 * Version: 1.0.1 (11/19/2010)
 * Copyright (c) 2009 jQueryGlobe
 * Licensed under the MIT License: http://en.wikipedia.org/wiki/MIT_License
 * Requires: jQuery v1.3+
 * modified by Michael Preuss
**/
;(function($) {
	$.fn.featureList = function(options) {
		var tabs	= $(this);
		var output	= $(options.output);

		new jQuery.featureList(tabs, output, options);

		return this;	
	};

	$.featureList = function(tabs, output, options) {
		function slide(nr) {
			if (typeof nr == "undefined") {
				nr = visible_item + 1;
				nr = nr >= total_items ? 0 : nr;
			}

			tabs.removeClass('current').filter(":eq(" + nr + ")").addClass('current');

			output.stop(true, true).filter(":visible").fadeOut();
			output.filter(":eq(" + nr + ")").fadeIn(function() {
				visible_item = nr;	
			});
		}

		var options			= options || {}; 
		var total_items		= tabs.length;
		var visible_item	= options.start_item || 0;
                var calc = options.calc || false;
                var h = output.parents('.fl-wrapper').height();
                if(calc) {
                    var lh =  Math.ceil(h/total_items);
                    var to = tabs.outerHeight();
                    var th = tabs.height();
                    tabs.each(function(i){
                        if($.browser.msie && $.browser.version < 7){
                            $(this).css('height', th+(lh-to) +'px');
                        }else{
                            $(this).css('min-height', th+(lh-to) +'px');
                        }
                    });
                }
                var rm = output.children('.fl-read-more');
                
		options.pause_on_hover		= options.pause_on_hover		|| true;
		options.transition_interval	= options.transition_interval	|| 5000;
                options.rm_bottom = options.rm_bottom || 10;
                options.rm_right = options.rm_right || 10;
                options.rm_pos = options.rm_pos || 'left';
                rm.css(options.rm_pos , options.rm_right + 'px');
                rm.css('top',h-options.rm_bottom-rm.outerHeight() +'px');
		output.hide().eq( visible_item ).show();
		tabs.eq( visible_item ).addClass('current');

		tabs.click(function() {
			if ($(this).hasClass('current')) {
				return false;	
			}

			slide( tabs.index( this) );
		});

		if (options.transition_interval > 0) {
			var timer = setInterval(function () {
				slide();
			}, options.transition_interval);

			if (options.pause_on_hover) {
				tabs.mouseenter(function() {
					clearInterval( timer );

				}).mouseleave(function() {
					clearInterval( timer );
					timer = setInterval(function () {
						slide();
					}, options.transition_interval);
				});
			}
		}
	};
})(jQuery);