<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

function xtreme_get_template_data($template_file) {
	$default_headers = array(
		'Name' => 'Xtreme Name',
	);
	//using the WP 2.9 introduce function for us too.
	$template_data = get_file_data( $template_file, $default_headers, '' );
	return $template_data;
}

/**
 * loading Template Files recursive
 *
 * @param   String $dir
 * @param   Array $xtreme_templates
 *
 * @return  Array
 */
function xtreme_find_templates( $dir, &$xtreme_templates = array() ){
	$unallowed_template_files = array(
		'header.php',
		'footer.php',
		'functions.php',
		'sidebar.php',
		'comments.php',
		'searchform.php'
	);

	/**
	 * Filter to exclude additional Templates from Xtreme One -> Layouts
	 * @param    array $unallowed_template_files
	 * @return  array $unallowed_template_files
	 */
	$unallowed_template_files = apply_filters( 'xtreme_unallowed_template_files', $unallowed_template_files );

	if ( is_dir( $dir ) && $dh = opendir( $dir ) ) {

		while ( ( $filename = readdir($dh ) ) !== false ) {

			if ( in_array( $filename, array( '.', '..' ) ) ) {
				continue;
			}

			// we have to set the dir to the file/folder
			$dir_filename = $dir . '/' . $filename;

			if ( substr( $filename, -4 ) == '.php' && !in_array( $filename, $unallowed_template_files ) ) {

				$template_data = xtreme_get_template_data( $dir_filename );

				if ( empty ( $template_data['Name'] ) ) {
					continue;
				}
				// Fix für die mataboxvalues um kollision mit page-bla-foo.php zu vermeiden
				$template_data[ 'metavalue' ]   = str_replace('-', '_', $filename );
				$template_data[ 'Name' ]        = ( (bool)( stristr( $dir, XF_CHILD_THEME_DIR ) ) ? ' [child-theme] ' : '' ) . $template_data[ 'Name' ];

				$xtreme_templates[ $filename ] = $template_data;
			}
		}
		@closedir($dh);
	}

	return $xtreme_templates;
}

/**
 * Loading all Template-File from Parent- and Child-Theme
 *
 * @return  Array $xtreme_templates
 */
function xtreme_load_templates() {

	$xtreme_templates  = array ();
	$path[] = XF_THEME_DIR;
	if( XF_THEME_DIR !== XF_CHILD_THEME_DIR ) {
		$path[] = XF_CHILD_THEME_DIR;
	}

	foreach ($path as $dir) {
		$xtreme_templates = xtreme_find_templates( $dir, $xtreme_templates );
	}
	return $xtreme_templates;

}
