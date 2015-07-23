/** XTREME THEME HEADER **/
 
 (function($) {
   
	$(document).ready(function(){
	
		//perform tab grouping now
		var all_grouped_widgets = $('.widget_xtreme_grouped').toArray();
		var groups = {};
		//lets separate the groups now
		$.each(all_grouped_widgets, function (i, widget) {
			var res = $(widget).attr('class').search(/widget_group_id_([^ ]+)/);
			if (res) {
				if(!groups[RegExp.$1]) {
					groups[RegExp.$1] = [];
				}
				groups[RegExp.$1].push(widget); 
			}
		});
		//now lets re-organize the markup for each group
		$.each(groups, function(k, v) {
			//only tab it, if group has more than 1 element
			if(v.length > 1)
			{
				$('<div class="jquery_tabs"></div>').insertBefore($(v[0]));
				var tabber = $(v[0]).prev('.jquery_tabs');
				var head_tag = $(v[0]).find('.widget-title')[0].nodeName;
				var is_parent_ul = ($(tabber).parent()[0].nodeName == 'UL');
				
				$.each(v, function(i, el) {
					if(is_parent_ul) {
						//todo: may break javascript driven widgets while handler gets detached
						var w_id = $(el).attr("id")
						var w_class = $(el).attr("class");
						$(tabber).append('<div id="'+w_id+'" class="'+w_class+'">'+$(el).remove().html()+"</div>");
					}else{
						$(tabber).append($(el).remove());
					}
				});
				
				$(tabber).accessibleTabs({
					tabhead: head_tag,
					tabbody: '.widget_xtreme_grouped',
					syncheights: false,
					fx: 'fadeIn',
					autoAnchor: true
				});
				
				if (is_parent_ul) {
					$(tabber).wrap("<li></li>");
				}
			}
		});
		
	});

 })(jQuery);