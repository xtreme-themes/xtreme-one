jQuery(document).ready(function( $ ){

      // add your shortcode attribute and its default value to the gallery settings list
      _.extend(wp.media.gallery.defaults, {
    	  size: 'thumbnail',
    	  target: 'auto'
      });

      // merge default gallery settings template with our
      wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
        template: function(view){
          return wp.media.template('gallery-settings')(view)
               + wp.media.template('custom-gallery-setting')(view);
        }
      });
      
      $( ".link-to" ).live( "change", function(){
    	  if ( $( ".link-to" ).val() == 'post' )
    		  $( '#xt-setting-display-size' ).hide();
      });
});