<?php

if (!function_exists ('add_action')) {
	header('Status: 403 Forbidden');
	header('HTTP/1.1 403 Forbidden');
	exit();
}

class Xtreme_github_updater {

	function __construct() {

		add_action( 'admin_init', array( $this, 'check_notice' ) );
	}

	public static function check_notice() {

		if ( isset( $_POST[ 'xtreme_github_updater_notice_off' ] ) )
			update_option( 'xtreme_github_updater_notice_off', 1 );
		if( get_option( 'xtreme_github_updater_notice_off', FALSE ) == FALSE )
			add_action( 'admin_notices', array( __CLASS__, 'show_header_notice' ) );
	}

	static function show_header_notice() {
	?>
		<div class="updated woocommerce-de-message warning">
			<h4><?php _e( 'Update Notifications for Xtreme One and Xtreme Child Themes', XF_TEXTDOMAIN ); ?></h4>
			<p>
				<?php echo sprintf( __( 'In order to receive update notifications for Xtreme One and its child themes it is recommend that you install the GitHub Updater plugin. Download the latest release of GitHub Updater from <a href="%s">https://github.com/afragen/github-updater</a>.', XF_TEXTDOMAIN ), 'https://github.com/afragen/github-updater' ); ?>
			</p>

			<form action="<?php admin_url( 'admin.php' ); ?>" method="post">
				<input type="submit" class="button" name="xtreme_github_updater_notice_off" value="<?php _e( 'Dismiss this notice.' ); ?>" />
			</form>
		</div>
<?php
	}
}
$xtreme_github_updater = new Xtreme_github_updater();
