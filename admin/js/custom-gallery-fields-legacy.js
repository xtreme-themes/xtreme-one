jQuery(document).ready(function( $ ){

      // merge default gallery settings template with our
      wp.media.view.Settings.Gallery = wp.media.view.Settings.Gallery.extend({
        template: function(view){
    	  templates = wp.media.template( 'custom-gallery-legacy' )( view );
    	  
    	  // load advanced view if available
    	  if ( $( '#tmpl-custom-gallery-setting' ).html() ) {
    		  templates = wp.media.template( 'custom-gallery-setting' )( view ) + templates ;
    	  }
          return wp.media.template('gallery-settings')(view)
               + templates;
        }
      });
});