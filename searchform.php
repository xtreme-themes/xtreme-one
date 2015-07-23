<?php
$options = get_option(XF_OPTIONS);
$submitcls = ( false === $options['xc_navigation']['show_submit']['value'] ) ? "class='ym-hideme'" : "";
$submittxt = $options['xc_navigation']['submit_text']['value'];
$searchtxt = $options['xc_navigation']['input_text']['value'];
$txt = 'text';
if ( xtreme_is_html5() ) {
    $txt = 'search';
}
?>
<form <?php xtreme_aria_required( 'search', true ) ?> method="get" id="searchform" action="<?php echo home_url() ?>/" >
    <div><label class="screen-reader-text" for="s"><?php esc_attr_e('Search for:', XF_TEXTDOMAIN) ?></label>
    <input type="<?php echo $txt ?>" value="<?php esc_attr_e($searchtxt) ?>" name="s" id="s" accesskey="s"/>
    <input type="submit" id="searchsubmit" value="<?php esc_attr_e($submittxt) ?>"  <?php echo $submitcls ?> />
    </div>
</form>