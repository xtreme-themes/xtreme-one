/** XTREME THEME HEADER **/

 (function($) {
    $.fn.extend({
        xLayoutColumnSelector: function() {
            return this.each(function(){
             var self = $(this);
			 var layout = '';
			 var lm = $(this).parents('.form-table').find(':input[name*="xc_templayout-is_layout_2-value"]');
			 if(lm.length>0) {
				 layout = '#xtreme-meta-box-xc_templayout .form-table';
			 }else{
				 layout = '#xtreme-meta-box-xc_general .form-table';
			 }
                self.change(function() {
                    var i=$(this).val();
				if($(layout).find('#xc_general-layout_2-value').attr('checked') || lm.val() == 1) {
					$(this).parents('.form-table').find('.r-layout_2_col1width, .r-layout_2_col2width, .r-layout_2_col2content, .r-layout_2_col3content, .r-layout_2_col1txtalign, .r-layout_2_col2txtalign, .r-layout_2_col3txtalign, .r-col1width, .r-col2width, .r-col1content, .r-col2content, .r-col1txtalign, .r-col2txtalign, .r-col3txtalign, .r-col2tip').addClass('x-hidden-important');
					if(i==0) {
						$(this).parents('.form-table').find('.r-layout_2_col1txtalign').removeClass('x-hidden-important');
					}
					if (i>0) {
					    $(this).parents('.form-table').find('.r-layout_2_col1width, .r-layout_2_col3content, .r-layout_2_col3txtalign, .r-layout_2_col1txtalign').removeClass('x-hidden-important');
					    if (i>2 && i<6) $(this).parents('.form-table').find('.r-layout_2_col2width, .r-layout_2_col2content, .r-layout_2_col2txtalign').removeClass('x-hidden-important');
					    if (i == 4 || i == 5) $(this).parents('.form-table').find('.r-col2tip').removeClass('x-hidden-important');
					    if (i == 3) {
							$(this).parents('.form-table').find('#xc_layout-layout_2_col2width-unit option[value="px"], #xc_layout-layout_2_col1width-unit option[value="px"]').remove();
							$(this).parents('.form-table').find('#xc_templayout-layout_2_col2width-unit option[value="px"], #xc_templayout-layout_2_col1width-unit option[value="px"]').remove();
						}
						else {
							$(this).parents('.form-table').find('#xc_layout-layout_2_col2width-unit, #xc_layout-layout_2_col1width-unit, #xc_templayout-layout_2_col2width-unit, #xc_templayout-layout_2_col1width-unit').each(function(i,el) {
								if($(el).find('option[value="px"]').length == 0)
									$(el).append('<option value="px">px</option>');
							});
						}
					}
				} else {
					$(this).parents('.form-table').find('.r-layout_2_col1width, .r-layout_2_col2width, .r-layout_2_col2content, .r-layout_2_col3content, .r-layout_2_col1txtalign, .r-layout_2_col2txtalign, .r-layout_2_col3txtalign, .r-col1width, .r-col2width, .r-col1content, .r-col2content, .r-col1txtalign, .r-col2txtalign, .r-col2tip').addClass('x-hidden-important');
					if(i==0) {
						$(this).parents('.form-table').find('.r-col3txtalign').removeClass('x-hidden-important');
					}
					if (i>0) {
						$(this).parents('.form-table').find('.r-col1width, .r-col1content, .r-col1txtalign').removeClass('x-hidden-important');
						if (i>2 && i<6) $(this).parents('.form-table').find('.r-col2width, .r-col2content, .r-col2txtalign').removeClass('x-hidden-important');
						if (i == 4 || i == 5) $(this).parents('.form-table').find('.r-col2tip').removeClass('x-hidden-important');
					}
				}
                }).trigger('change');
            });
        },
        xWidthSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    var i=$(this).val();
                    $(this).parents('.form-table').find('.r-minwidth, .r-maxwidth, .r-width').addClass('x-hidden-important');
                    if(i == 0) {
                        $(this).parents('.form-table').find('.r-width').removeClass('x-hidden-important');
                    }else if(i == 1){
                        $(this).parents('.form-table').find('.r-minwidth, .r-maxwidth').removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
        },
        xHeaderAreaSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-widget_area, .r-position, .r-colwidth').addClass('x-hidden-important');
                    if($(this).attr('checked')) {
                        $(this).parents('.form-table').find('.r-widget_area, .r-position, .r-colwidth').removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
        },
        xHeaderDescSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-blogdescription_tag').addClass('x-hidden-important');
                    if($(this).attr('checked')) {
                        $(this).parents('.form-table').find('.r-blogdescription_tag').removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
        },
        xPrimaryNavSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    var i=$(this).val();
                    $(this).parents('.form-table').find('.r-primary_stylesheet, .r-primary_content, .r-primary_depth, .r-primary_order, .r-primary_limitation, .r-primary_ids, .r-primary_script, .r-primary_showhome, .r-primary_homelink, .r-primary_desc_walker').addClass('x-hidden-important');
                    if(i > 0) { 
                        $(this).parents('.form-table').find('.r-primary_stylesheet, .r-primary_content').removeClass('x-hidden-important');
                        var content = $('#xc_navigation-primary_content-value').val();
                        if(content == 'pages' || content == 'categories') {
                            $(this).parents('.form-table').find('.r-primary_depth, .r-primary_script, .r-primary_order, .r-primary_limitation, .r-primary_showhome').removeClass('x-hidden-important');
                            if($('#xc_navigation-primary_limitation-value').val() !== 'none'){
                                $(this).parents('.form-table').find('.r-primary_ids').removeClass('x-hidden-important');
                            }
                            if($('#xc_navigation-primary_showhome-value').attr('checked') ) {
                               $(this).parents('.form-table').find('.r-primary_homelink').removeClass('x-hidden-important');
                            }
                        }else{
                            $(this).parents('.form-table').find('.r-primary_script, .r-primary_desc_walker').removeClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xSecondaryNavSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    var i=$(this).val();
                    $(this).parents('.form-table').find('.r-secondary_stylesheet, .r-secondary_content, .r-secondary_depth, .r-secondary_order, .r-secondary_limitation, .r-secondary_ids, .r-secondary_script, .r-secondary_showhome, .r-secondary_homelink, .r-secondary_desc_walker').addClass('x-hidden-important');
                    if(i > 0) {
                        $(this).parents('.form-table').find('.r-secondary_stylesheet, .r-secondary_content').removeClass('x-hidden-important');
                        var content = $('#xc_navigation-secondary_content-value').val();
                        if(content == 'pages' || content == 'categories') {
                            $(this).parents('.form-table').find('.r-secondary_depth, .r-secondary_script, .r-secondary_order, .r-secondary_limitation, .r-secondary_showhome').removeClass('x-hidden-important');
                            if($('#xc_navigation-secondary_limitation-value').val() !== 'none'){
                                $(this).parents('.form-table').find('.r-secondary_ids').removeClass('x-hidden-important');
                            }
                            if($('#xc_navigation-secondary_showhome-value').attr('checked') ) {
                               $(this).parents('.form-table').find('.r-secondary_homelink').removeClass('x-hidden-important');
                            }
                        }else{
                            $(this).parents('.form-table').find('.r-secondary_script, .r-secondary_desc_walker').removeClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xPriNavContentSelector: function() {
            return this.each(function(){
                var self = $(this);
                self.change(function() {
                    var i=$(this).val();
                    $(this).parents('.form-table').find('.r-primary_order, .r-primary_limitation, .r-primary_ids, .r-primary_depth, .r-primary_showhome, .r-primary_homelink, .r-primary_desc_walker').addClass('x-hidden-important');
                    if($('#xc_navigation-primary_position-value').val() != 0){
                        if (i=== 'pages' || i === 'categories') {
                            $(this).parents('.form-table').find('.r-primary_order, .r-primary_limitation, .r-primary_depth, .r-primary_showhome').removeClass('x-hidden-important');
                            if($('#xc_navigation-primary_limitation-value').val() !== 'none'){
                                $(this).parents('.form-table').find('.r-primary_ids').removeClass('x-hidden-important');
                            }
                            if($('#xc_navigation-primary_showhome-value').attr('checked') ) {
                               $(this).parents('.form-table').find('.r-primary_homelink').removeClass('x-hidden-important');
                            }
                        }else{
                            $(this).parents('.form-table').find('.r-primary_desc_walker').removeClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xPriNavShowhomeSelector: function() {
            return this.each(function(){
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-primary_homelink').addClass('x-hidden-important');
                    if($('#xc_navigation-primary_position-value').val() != 0) {
                        if($(this).attr('checked')) {
                            $(this).parents('.form-table').find('.r-primary_homelink').removeClass('x-hidden-important');
                        }else{
                            $(this).parents('.form-table').find('.r-primary_homelink').addClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xPriNavLimitationSelector: function() {
            return this.each(function(){
                var self = $(this);
                self.change(function() {
                    var i=$(this).val();
                    $(this).parents('.form-table').find('.r-primary_ids').addClass('x-hidden-important');
                    if($('#xc_navigation-primary_position-value').val() != 0){
                        if (i=== 'include' || i === 'exclude') {
                            $(this).parents('.form-table').find('.r-primary_ids').removeClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xSecNavContentSelector: function() {
            return this.each(function(){
                var self = $(this);
                self.change(function() {
                    var i=$(this).val();
                    $(this).parents('.form-table').find('.r-secondary_order, .r-secondary_limitation, .r-secondary_ids, .r-secondary_depth, .r-secondary_showhome, .r-secondary_homelink, .r-secondary_desc_walker').addClass('x-hidden-important');
                    if($('#xc_navigation-secondary_position-value').val() != 0){
                        if (i=== 'pages' || i === 'categories') {
                            $(this).parents('.form-table').find('.r-secondary_order, .r-secondary_limitation, .r-secondary_depth, .r-secondary_showhome').removeClass('x-hidden-important');
                            if($('#xc_navigation-secondary_limitation-value').val() !== 'none'){
                                $(this).parents('.form-table').find('.r-secondary_ids').removeClass('x-hidden-important');
                            }
                            if($('#xc_navigation-secondary_showhome-value').attr('checked') ) {
                               $(this).parents('.form-table').find('.r-secondary_homelink').removeClass('x-hidden-important');
                            }
                        }else{
                            $(this).parents('.form-table').find('.r-secondary_desc_walker').removeClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xSecNavShowhomeSelector: function() {
            return this.each(function(){
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-secondary_homelink').addClass('x-hidden-important');
                    if($('#xc_navigation-secondary_position-value').val() != 0) {
                        if($(this).attr('checked')) {
                            $(this).parents('.form-table').find('.r-secondary_homelink').removeClass('x-hidden-important');
                        }else{
                            $(this).parents('.form-table').find('.r-secondary_homelink').addClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xSecNavLimitationSelector: function() {
            return this.each(function(){
                var self = $(this);
                self.change(function() {
                    var i=$(this).val();
                    $(this).parents('.form-table').find('.r-secondary_ids').addClass('x-hidden-important');
                    if($('#xc_navigation-secondary_position-value').val() != 0){
                        if (i=== 'include' || i === 'exclude') {
                            $(this).parents('.form-table').find('.r-secondary_ids').removeClass('x-hidden-important');
                        }
                    }
                }).trigger('change');
            });
        },
        xCommentSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-avatar_size, .r-avatar_align').addClass('x-hidden-important');
                    if($(this).attr('checked')) {
                        $(this).parents('.form-table').find('.r-avatar_size, .r-avatar_align').removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
        },
        xSubcolSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-txtalign_1, .r-txtalign_2, .r-txtalign_3, .r-txtalign_4, .r-txtalign_5').addClass('x-hidden-important');
                            var rel = $(this).find('option:selected').parent('optgroup').attr('rel');
                    for (i=1; i <= rel; i++){
                        $(this).parents('.form-table').find('.r-txtalign_'+i).removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
        },
        xSiteinfoSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-copyright_start').addClass('x-hidden-important');
                    if($(this).attr('checked')) {
                        $(this).parents('.form-table').find('.r-copyright_start').removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
        },
        xSiteinfoAreaSelector: function() {
            return this.each(function() {
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-widget_area, .r-position, .r-colwidth').addClass('x-hidden-important');
                    if($(this).attr('checked')) {
                        $(this).parents('.form-table').find('.r-widget_area, .r-position, .r-colwidth').removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
        },
		xPostSocialSelector: function() {
           return this.each(function() {
                var self = $(this);
                self.change(function() {
                    $(this).parents('.form-table').find('.r-post_socials_layout').addClass('x-hidden-important');
                    if($(this).val() != 'disabled') {
                        $(this).parents('.form-table').find('.r-post_socials_layout').removeClass('x-hidden-important');
                    }
                }).trigger('change');
            });
		},
        xLayout2Selector: function() {
            return this.each(function() {
                var self = $(this);
			 var target = '#xtreme-meta-box-xc_layout .form-table';
                self.change(function() {
                    $(target).find('.r-col1width, .r-col2width, .r-col1content, .r-col2content, .r-col1txtalign, .r-col2txtalign, .r-col3txtalign, .r-layout_2_col1width, .r-layout_2_col2width, .r-layout_2_col3width, .r-layout_2_col2content, .r-layout_2_col3content, .r-layout_2_col1txtalign, .r-layout_2_col2txtalign, .r-layout_2_col3txtalign').addClass('x-hidden-important');
                    var layout = parseInt($(target).find('#xc_layout-columnlayout-value').val());
					if($(this).attr('checked')) {
						switch(layout) {
							case 0:
								$(target).find('.r-layout_2_col1txtalign').removeClass('x-hidden-important');
								break;
							case 1:
							case 2:
								$(target).find('.r-layout_2_col1width, .r-layout_2_col3content, .r-layout_2_col1txtalign, .r-layout_2_col3txtalign').removeClass('x-hidden-important');
								break;
							case 3:
								$(target).find('.r-layout_2_col1width, .r-layout_2_col2width, .r-layout_2_col3content, .r-layout_2_col2content, .r-layout_2_col1txtalign, .r-layout_2_col2txtalign, .r-layout_2_col3txtalign').removeClass('x-hidden-important');
								$(target).find('#xc_layout-layout_2_col2width-unit option[value="px"], #xc_layout-layout_2_col1width-unit option[value="px"]').remove();
							case 4:
							case 5:
								$(target).find('.r-layout_2_col1width, .r-layout_2_col2width, .r-layout_2_col3content, .r-layout_2_col2content, .r-layout_2_col1txtalign, .r-layout_2_col2txtalign, .r-layout_2_col3txtalign').removeClass('x-hidden-important');
								break;
						}
					}
					if(!$(this).attr('checked')) {
						switch(layout) {
							case 0:
								$(target).find('.r-col3txtalign').removeClass('x-hidden-important');
								break;
							case 1:
							case 2:
								$(target).find('.r-col1width, .r-col1content, .r-col1txtalign, .r-col3txtalign').removeClass('x-hidden-important');
								break;
							case 3:
							case 4:
							case 5:
								$(target).find('.r-col1width, .r-col2width, .r-col1content, .r-col2content, .r-col1txtalign, .r-col2txtalign, .r-col3txtalign').removeClass('x-hidden-important');
								break;
						}
					}
                }).trigger('change');
            });
        }
     });
    $(document).ready(function(){
		$('#xc_layout-columnlayout-value, #xc_templayout-columnlayout-value').xLayoutColumnSelector();
        $('#xc_general-layout-value').xWidthSelector();
        $('#xc_header-columns-value').xHeaderAreaSelector();
        $('#xc_navigation-primary_position-value').xPrimaryNavSelector();
        $('#xc_navigation-secondary_position-value').xSecondaryNavSelector();
        $('#xc_comments-show_avatar-value').xCommentSelector();
        $('#xc_siteinfo-copyright-value').xSiteinfoSelector();
        $('#xc_siteinfo-columns-value').xSiteinfoAreaSelector();
        $('#xc_navigation-primary_content-value').xPriNavContentSelector();
        $('#xc_navigation-primary_limitation-value').xPriNavLimitationSelector();
        $('#xc_navigation-secondary_content-value').xSecNavContentSelector();
        $('#xc_navigation-secondary_limitation-value').xSecNavLimitationSelector();
        $('#xc_footer-subcolumns-value').xSubcolSelector();
        $('#xc_teaser-subcolumns-value').xSubcolSelector();
        $('#xc_navigation-primary_showhome-value').xPriNavShowhomeSelector();
        $('#xc_navigation-secondary_showhome-value').xSecNavShowhomeSelector();
        $('#xc_header-blog_description-value').xHeaderDescSelector();
		$('#xc_general-post_socials-value').xPostSocialSelector();
		$('#xc_general-layout_2-value').xLayout2Selector();
		$('#xc_pagination-pagination_type-value').change(function(el) {
			if($(this).val() != 0) {
				$(this).closest('table').find('.r-end_size, .r-mid_size, .r-show_all, .r-prev_next, .r-type').removeClass('x-hidden-important');
			}else{
				$(this).closest('table').find('.r-end_size, .r-mid_size, .r-show_all, .r-prev_next, .r-type').addClass('x-hidden-important');
			}
		}).trigger('change');
		//prevent caching input field status on reload (especially Firefox)
		$('form').attr('autocomplete', 'off');
    });
 })(jQuery);