<?php

function xtreme_request_filesystem_credentials($headline, $form_post, $type = '', $error = false, $context = false) {
	$req_cred = apply_filters('request_filesystem_credentials', '', $form_post, $type, $error, $context);
	if ( '' !== $req_cred )
		return $req_cred;

	if ( empty($type) )
		$type = get_filesystem_method(array(), $context);

	if ( 'direct' == $type )
		return true;

	$credentials = get_option('ftp_credentials', array( 'hostname' => '', 'username' => ''));

	// If defined, set it to that, Else, If POST'd, set it to that, If not, Set it to whatever it previously was(saved details in option)
	$credentials['hostname'] = defined('FTP_HOST') ? FTP_HOST : (!empty($_POST['hostname']) ? $_POST['hostname'] : $credentials['hostname']);
	$credentials['username'] = defined('FTP_USER') ? FTP_USER : (!empty($_POST['username']) ? $_POST['username'] : $credentials['username']);
	$credentials['password'] = defined('FTP_PASS') ? FTP_PASS : (!empty($_POST['password']) ? $_POST['password'] : '');

	// Check to see if we are setting the public/private keys for ssh
	$credentials['public_key'] = defined('FTP_PUBKEY') ? FTP_PUBKEY : (!empty($_POST['public_key']) ? $_POST['public_key'] : '');
	$credentials['private_key'] = defined('FTP_PRIKEY') ? FTP_PRIKEY : (!empty($_POST['private_key']) ? $_POST['private_key'] : '');

	//sanitize the hostname, Some people might pass in odd-data:
	$credentials['hostname'] = preg_replace('|\w+://|', '', $credentials['hostname']); //Strip any schemes off

	if ( strpos($credentials['hostname'], ':') )
		list( $credentials['hostname'], $credentials['port'] ) = explode(':', $credentials['hostname'], 2);
	else
		unset($credentials['port']);

	if ( defined('FTP_SSH') || (defined('FS_METHOD') && 'ssh' == FS_METHOD) )
		$credentials['connection_type'] = 'ssh';
	else if ( defined('FTP_SSL') && 'ftpext' == $type ) //Only the FTP Extension understands SSL
		$credentials['connection_type'] = 'ftps';
	else if ( !empty($_POST['connection_type']) )
		$credentials['connection_type'] = $_POST['connection_type'];
	else if ( !isset($credentials['connection_type']) ) //All else fails (And its not defaulted to something else saved), Default to FTP
		$credentials['connection_type'] = 'ftp';

	if ( ! $error &&
			(
				( !empty($credentials['password']) && !empty($credentials['username']) && !empty($credentials['hostname']) ) ||
				( 'ssh' == $credentials['connection_type'] && !empty($credentials['public_key']) && !empty($credentials['private_key']) )
			) ) {
		$stored_credentials = $credentials;
		if ( !empty($stored_credentials['port']) ) //save port as part of hostname to simplify above code.
			$stored_credentials['hostname'] .= ':' . $stored_credentials['port'];

		unset($stored_credentials['password'], $stored_credentials['port'], $stored_credentials['private_key'], $stored_credentials['public_key']);
		update_option('ftp_credentials', $stored_credentials);
		return $credentials;
	}
	$hostname = '';
	$username = '';
	$password = '';
	$connection_type = '';
	if ( !empty($credentials) )
		extract($credentials, EXTR_OVERWRITE);
	if ( $error ) {
		$error_string = __('<strong>Error:</strong> There was an error connecting to the server, Please verify the settings are correct.', XF_TEXTDOMAIN);
		if ( is_wp_error($error) )
			$error_string = $error->get_error_message();
		echo '<div id="message" class="error"><p>' . $error_string . '</p></div>';
	}
?>
<script type="text/javascript">
<!--
jQuery(function($){
	jQuery("#ssh").click(function () {
		jQuery("#ssh_keys").show();
	});
	jQuery("#ftp, #ftps").click(function () {
		jQuery("#ssh_keys").hide();
	});
});
-->
</script>
<div id="xtreme_credentials" class="wrap">
<h2><?php echo $headline;  ?></h2>
<p><?php _e('To modify theme related files, connection information is required.',XF_TEXTDOMAIN) ?></p>
<p>
	<?php _e('Your WordPress installation does not permit direct file access and have to use FTP access therefore.',XF_TEXTDOMAIN) ?><br/>
	<?php _e('If you want to avoid repeatedly requests for credentials here, you could also define at <b><i>wp-config.php</i></b> this constants:',XF_TEXTDOMAIN) ?><br/>
</p>
<p style="padding-left:20px;font-size: 10px;">
	<?php _e('define(\'FTP_HOST\', \'your ftp hostname\');',XF_TEXTDOMAIN) ?><br/>
	<?php _e('define(\'FTP_USER\', \'your ftp username\');',XF_TEXTDOMAIN) ?><br/>
	<?php _e('define(\'FTP_PASS\', \'your ftp password\');',XF_TEXTDOMAIN) ?><br/>
</p>
<table class="form-table">
<tr valign="top">
<th scope="row"><label for="hostname"><?php _e('Hostname', XF_TEXTDOMAIN) ?></label></th>
<td><input name="hostname" type="text" id="hostname" value="<?php echo esc_attr($hostname); if ( !empty($port) ) echo ":$port"; ?>"<?php if( defined('FTP_HOST') ) echo ' disabled="disabled"' ?> size="40" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="username"><?php _e('Username', XF_TEXTDOMAIN) ?></label></th>
<td><input name="username" type="text" id="username" value="<?php echo esc_attr($username) ?>"<?php if( defined('FTP_USER') ) echo ' disabled="disabled"' ?> size="40" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="password"><?php _e('Password', XF_TEXTDOMAIN) ?></label></th>
<td><input name="password" type="password" id="password" value="<?php if ( defined('FTP_PASS') ) echo '*****'; ?>"<?php if ( defined('FTP_PASS') ) echo ' disabled="disabled"' ?> size="40" /></td>
</tr>

<?php if ( extension_loaded('ssh2') && function_exists('stream_get_contents') ) : ?>
<tr id="ssh_keys" valign="top" style="<?php if ( 'ssh' != $connection_type ) echo 'display:none' ?>">
<th scope="row"><?php _e('Authentication Keys', XF_TEXTDOMAIN) ?>
<div class="key-labels textright">
<label for="public_key"><?php _e('Public Key:', XF_TEXTDOMAIN) ?></label ><br />
<label for="private_key"><?php _e('Private Key:', XF_TEXTDOMAIN) ?></label>
</div></th>
<td><br /><input name="public_key" type="text" id="public_key" value="<?php echo esc_attr($public_key) ?>"<?php if( defined('FTP_PUBKEY') ) echo ' disabled="disabled"' ?> size="40" /><br /><input name="private_key" type="text" id="private_key" value="<?php echo esc_attr($private_key) ?>"<?php if( defined('FTP_PRIKEY') ) echo ' disabled="disabled"' ?> size="40" />
<div><?php _e('Enter the location on the server where the keys are located. If a passphrase is needed, enter that in the password field above.', XF_TEXTDOMAIN) ?></div></td>
</tr>
<?php endif; ?>

<tr valign="top">
<th scope="row"><?php _e('Connection Type', XF_TEXTDOMAIN) ?></th>
<td>
<fieldset><legend class="screen-reader-text"><span><?php _e('Connection Type', XF_TEXTDOMAIN) ?></span></legend>
<label><input id="ftp" name="connection_type"  type="radio" value="ftp" <?php checked('ftp', $connection_type); if ( defined('FTP_SSL') || defined('FTP_SSH') ) echo ' disabled="disabled"'; ?>/> <?php _e('FTP', XF_TEXTDOMAIN) ?></label>
<?php if ( 'ftpext' == $type ) : ?>
<br /><label><input id="ftps" name="connection_type" type="radio" value="ftps" <?php checked('ftps', $connection_type); if ( defined('FTP_SSL') || defined('FTP_SSH') ) echo ' disabled="disabled"';  ?>/> <?php _e('FTPS (SSL)', XF_TEXTDOMAIN) ?></label>
<?php endif; ?>
<?php if ( extension_loaded('ssh2') && function_exists('stream_get_contents') ) : ?>
<br /><label><input id="ssh" name="connection_type" type="radio" value="ssh" <?php checked('ssh', $connection_type);  if ( defined('FTP_SSL') || defined('FTP_SSH') ) echo ' disabled="disabled"'; ?>/> <?php _e('SSH', XF_TEXTDOMAIN) ?></label>
<?php endif; ?>
</fieldset>
</td>
</tr>
</table>

<?php if ( isset( $_POST['version'] ) ) : ?>
<input type="hidden" name="version" value="<?php echo esc_attr($_POST['version']) ?>" />
<?php endif; ?>
<?php if ( isset( $_POST['locale'] ) ) : ?>
<input type="hidden" name="locale" value="<?php echo esc_attr($_POST['locale']) ?>" />
<?php endif; ?>
<br/><br/>
</div>
<?php
	return false;
}
