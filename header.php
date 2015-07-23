<?php
do_action('xtreme_document_head');
do_action('xtreme_meta');
do_action('xtreme_stylesheets');
do_action('xtreme_ie_patch');
do_action('xtreme_theme_stylesheet');
xtreme_frontend_favicon();
?>
<link rel="pingback" href="<?php bloginfo( 'pingback_url' ) ?>" />
<?php wp_head(); ?>
</head>
<?php xtreme_start_layout() ?>