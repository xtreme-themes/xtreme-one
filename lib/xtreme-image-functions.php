<?php

add_action( 'init', 'xtreme_init_gallery' );
function xtreme_init_gallery() {
	$start_xtreme_gallery = apply_filters( 'use_xtreme_gallery', FALSE );
	if ( $start_xtreme_gallery ) {
		remove_shortcode( 'gallery', 'gallery_shortcode' );
		add_shortcode( 'gallery', 'xtreme_gallery_shortcode' );
	}
}

function xtreme_get_gallery_attachment_url( $post_id = 0, $target_size ) {
	$post_id = (int) $post_id;
	if ( !$post =& get_post( $post_id ) )
		return false;

	if ( 'attachment' != $post->post_type )
		return false;

	$imagedata = wp_get_attachment_metadata( $post->ID );
	
	$file = (is_array($imagedata) && isset($imagedata['sizes'][$target_size])) ? dirname(get_post_meta( $post->ID, '_wp_attached_file', true)).'/'.$imagedata['sizes'][$target_size]['file'] : get_post_meta( $post->ID, '_wp_attached_file', true);
		
	$url = '';
	if ( $file ) { //Get attached file	
		if ( ($uploads = wp_upload_dir()) && false === $uploads['error'] ) { //Get upload directory
			if ( 0 === strpos($file, $uploads['basedir']) ) //Check that the upload base exists in the file location
				$url = str_replace($uploads['basedir'], $uploads['baseurl'], $file); //replace file location with url location
			elseif ( false !== strpos($file, 'wp-content/uploads') )
				$url = $uploads['baseurl'] . substr( $file, strpos($file, 'wp-content/uploads') + 18 );
			else
				$url = $uploads['baseurl'] . "/$file"; //Its a newly uploaded file, therefor $file is relative to the basedir.
		}
	}

	if ( empty($url) ) //If any of the above options failed, Fallback on the GUID as used pre-2.7, not recommended to rely upon this.
		$url = get_the_guid( $post->ID );

	if ( empty( $url ) )
		return false;

	return $url;
}

function xtreme_get_gallery_attachment_link( $id = 0, $size = 'thumbnail', $target_size = 'auto') {
	if ($target_size == 'auto') 
		return wp_get_attachment_link( $id, $size, false, false );

	$id = intval( $id );
	$_post = & get_post( $id );

	if ( empty( $_post ) || ( 'attachment' != $_post->post_type ) || ! $url = xtreme_get_gallery_attachment_url( $_post->ID, $target_size ) )
		return __( 'Missing Attachment', XF_TEXTDOMAIN );

	$post_title = esc_attr( $_post->post_title );

	if ( $size && 'none' != $size )
		$link_text = wp_get_attachment_image( $id, $size, false );
	else
		$link_text = '';

	if ( trim( $link_text ) == '' )
		$link_text = $_post->post_title;

	return apply_filters( 'wp_get_attachment_link', "<a href='$url' title='$post_title'>$link_text</a>", $id, $size, false, false, false );		
}

function xtreme_gallery_shortcode( $attr ) {
	global $xf_gallery_target_size;
	
	$post = get_post();
	
	static $instance = 0;
	$instance ++;
	
	// look for the parameter ids -> load these images
	if ( ! empty( $attr[ 'ids' ] ) ) {
		// 'ids' is explicitly ordered, unless you specify otherwise, set default for orderby
		if ( empty( $attr[ 'orderby' ] ) && ! isset( $attr[ 'xtorderby' ] ) )
			$attr[ 'orderby' ] = 'post__in';
		$attr[ 'include' ] = $attr[ 'ids' ];
	}
	
	// Allow plugins/themes to override the default gallery template.
	$output = apply_filters( 'post_gallery', '', $attr );
	if ( $output != '' )
		return $output;

	// check for wp 3.5 code
	if ( isset( $attr[ 'xtorderby' ] ) && ! isset( $attr[ 'orderby' ] ) ) {
		$attr[ 'orderby' ] = $attr[ 'xtorderby' ]; 
	}
	if ( isset( $attr[ 'xtorder' ] ) )
		$attr[ 'order' ] = $attr[ 'xtorder' ];

	if ( isset( $attr['orderby'] ) ) {
		$attr['orderby'] = sanitize_sql_orderby( $attr['orderby'] );
		if ( ! $attr['orderby'] )
			unset( $attr['orderby'] );
	}

	$default_attr = array(
		'order'      => 'ASC',
		'orderby'    => 'menu_order ID',
		'id'         => $post->ID,
		'itemtag'    => 'dl',
		'icontag'    => 'dt',
		'captiontag' => 'dd',
		'columns'    => 3,
		'size'       => 'thumbnail',
		'target' 	 => 'auto',
		'include'    => '',
		'exclude'    => '',
		'link'		 => 'file'
	);
	$attr   = shortcode_atts( $default_attr, $attr  );
	if ( ! isset( $attr[ 'link' ] ) )
		$attr[ 'link' ] = 'file';

	$id = intval( $attr[ 'id' ] );
	if ( 'RAND' == $attr[ 'order' ] )
		$attr[ 'orderby' ] = 'none';

	if ( ! empty( $include ) ) {
		$_attachments = get_posts( array( 'include' => $include, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $attr[ 'order' ], 'orderby' => $attr[ 'orderby' ] ) );
		$attachments = array();
		foreach ( $_attachments as $key => $val ) {
			$attachments[$val->ID] = $_attachments[$key];
		}
	} elseif ( ! empty($exclude) ) {
		$attachments = get_children( array( 'post_parent' => $id, 'exclude' => $attr[ 'exclude' ], 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $attr[ 'order' ], 'orderby' => $attr[ 'exclude' ] ) );
	} else {
		$attachments = get_children( array( 'post_parent' => $id, 'post_status' => 'inherit', 'post_type' => 'attachment', 'post_mime_type' => 'image', 'order' => $attr[ 'order' ], 'orderby' => $attr[ 'orderby' ] ) );
	}

	if ( empty( $attachments ) )
		return '';

	if ( is_feed() ) {
		$output = "\n";
		foreach ( $attachments as $att_id => $attachment ) {
			$output .= wp_get_attachment_link( $att_id, $attr[ 'size' ], TRUE ) . "\n";
		}
		return $output;
	}
	
	$html5 = xtreme_is_html5();
	if ( $html5 ) {
		$el         = 'ul';
		$attr[ 'itemtag' ]    = 'li';
		$attr[ 'icontag' ]    = 'figure';
		$attr[ 'captiontag' ] = 'figcaption';
	} else {
		$el         = 'div';
	}
	$attr[ 'itemtag' ]    = tag_escape( $attr[ 'itemtag' ] );
	$attr[ 'icontag' ]    = tag_escape( $attr[ 'icontag' ] );
	$attr[ 'captiontag' ] = tag_escape( $attr[ 'captiontag' ] );
	$valid_tags = wp_kses_allowed_html( 'post' );
	if ( ! isset( $valid_tags[ $attr[ 'itemtag' ] ] ) )
		$attr[ 'itemtag' ] = 'dl';
	if ( ! isset( $valid_tags[ $attr[ 'captiontag' ] ] ) )
		$attr[ 'captiontag' ] = 'dd';
	if ( ! isset( $valid_tags[ $attr[ 'icontag' ] ] ) )
		$attr[ 'icontag' ] = 'dt';
	
	$columns    = intval( $attr[ 'columns' ] );
	$selector   = "gallery-{$instance}";
	$output     = apply_filters( 'gallery_style', "<{$el} id='$selector' class='gallery galleryid-{$id}'>" );
	
	$i = 0;
	foreach ( $attachments as $id => $attachment ) {
		$i ++;
		if ( $attr[ 'link' ] === 'file' ) {
			$image_output = xtreme_get_gallery_attachment_link( $id, $attr[ 'size' ], $attr[ 'target' ] );
		}
		else if ( $attr[ 'link' ] === 'none' ) {
			$image_output = wp_get_attachment_image( $id, $attr[ 'size' ], false );
		}
		else {
			$image_output = wp_get_attachment_link( $id, $attr[ 'size' ], true, false );
		}

		$cls = '';
		if ( $columns > 0 && $i % $columns == 0 ) {
			$cls = 'last';
		} elseif ( $columns > 0 && $i % $columns == 1) {
			$cls = 'first';
		}
		$output .= "<{$attr[ 'itemtag' ]} class='gallery-item col-{$columns} {$cls}'>";
		$output .= "<{$attr[ 'icontag' ]} class='gallery-icon'>{$image_output}</{$attr[ 'icontag' ]}>";

		if ( $attr[ 'captiontag' ] && trim( $attachment->post_excerpt ) ) {
			$output .= "<{$attr[ 'captiontag' ]} class='gallery-caption'>";
			$output .= wptexturize( $attachment->post_excerpt );
			$output .= "</{$attr[ 'captiontag' ]}>";
		}
		if ( $html5 ) {
			$output .= "</{$attr[ 'icontag' ]}>";
		}
		$output .= "</{$attr[ 'itemtag' ]}>";
	}
	$output .= "</{$el}>\n";
	
	return $output;
}
