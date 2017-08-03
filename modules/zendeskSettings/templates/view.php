<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$config = get_option("smack_whi_{$skinnyData['activatedplugin']}_settings");

if( $config == "" )
{
        $config_data = 'no';
}
else
{
        $config_data = 'yes';
}

$siteurl = site_url();
$help_img = WP_HELPDESK_INTEGRATION_DIR . "images/help.png";
$callout_img = WP_HELPDESK_INTEGRATION_DIR . "images/callout.gif";
$help="<img src='$help_img'>";
$call="<img src='$callout_img'>";
update_option("smack_whi_{$skinnyData['activatedplugin']}_settings" , $config );
?>
<input type="hidden" id="get_config" value="<?php echo $config_data ?>" >
<span id="save_config" style="font:14px;width:200px;"> </span>

<span id="Fieldnames" style="font-size: 14px;font-weight:bold;float:right;padding-right:10px;padding-top:12px;padding-left:12px;"></span>
<script>
jQuery(document ).ready( function(){

	jQuery( "#Fieldnames" ).hide();
});
</script>
<input type="hidden" id="get_config" value="<?php echo $config_data ?>" >
<input type="hidden" id="revert_old_crm_pro" value="zendesk">
<form id="smack-zendesk-settings-form" action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

	<input type="hidden" name="smack-zendesk-settings-form" value="smack-zendesk-settings-form" />
	<input type="hidden" id="plug_URL" value="<?php echo esc_url(WP_HELPDESK_INTEGRATION_PLUG_URL);?>" />
	<div class="wp-common-crm-content" style="width:100%;float: left;">

		<table>
			<tr>
				<td><label id="inneroptions" style="margin-top:6px;margin-right:4px;font-weight:bold;"><?php echo esc_html__('Select the Helpdesk you use', WP_HELPDESK_INTEGRATION_SLUG ); ?></label></td>
				<td>
					<?php
					$ContactFormPluginsObj = new SmackHelpDeskUIHelper();
					echo $ContactFormPluginsObj->getPluginActivationHtml();
					?>
				</td>
			</tr>
		</table>
		<br>
		<label id="inneroptions" style="font-weight:bold;">Zendesk Settings :</label>
		<table  class="settings-table">
			<tr><td></td></tr>
			<tr>
				<td style='width:100px;padding-left:40px;'>
					<label id="innertext"> <?php echo esc_html__('Domain URL', WP_HELPDESK_INTEGRATION_SLUG ); ?> </label><div style='float:right;'> : </div>
				</td>
				<td colspan="3">
					<input type='text' class='smack-vtiger-settings' style="width: 100%;" name='domain_url' id='domain_url' value="<?php echo sanitize_text_field($config['domain_url']) ?>" />
				</td>
			</tr>
			<tr>
				<td style='width:150px;padding-left:40px;'>
					<label id="innertext"> <?php echo esc_attr__('Username', WP_HELPDESK_INTEGRATION_SLUG ); ?>  </label><div style='float:right;'> : </div>
				</td>
				<td>
					<input type='text' class='smack-vtiger-settings' name='username' id='username' value="<?php echo sanitize_text_field($config['username']) ?>" />

				</td>
				<td style='width:150px;padding-left:40px;'>
					<label id="innertext"> <?php echo esc_attr__('Password', WP_HELPDESK_INTEGRATION_SLUG ); ?>  </label><div style='float:right;'> : </div>
				</td>
				<td>
					<input type='password' class='smack-vtiger-settings' name='password' id='password' value="<?php echo sanitize_text_field($config['password']) ?>" />

				</td>
			</tr>
		</table>
		<table style="float:right;">
			<tr>
				<td>
				<input type="hidden" id="posted" name="posted" value="<?php echo 'posted';?>">
				<input type="hidden" id="site_url" name="site_url" value="<?php echo esc_attr($siteurl) ;?>">
                		<input type="hidden" id="active_plugin" name="active_plugin" value="<?php echo esc_attr($skinnyData['activatedplugin']); ?>">
                		<input type="hidden" id="tickets_fields_tmp" name="tickets_fields_tmp" value="smack_whi_zendesk_tickets_fields-tmp">
                		<input type="hidden" id="contact_fields_tmp" name="contact_fields_tmp" value="smack_whi_zendesk_contacts_fields-tmp">
		<p class='submit'>
        <span style="padding-right:10px;"><input type="button" id="Save_crm_config" value="<?php echo esc_attr__('Save Helpdesk Configuration', WP_HELPDESK_INTEGRATION_SLUG );?>" id="save"  class="button-primary" onclick="saveHelpDeskConfiguration(this.id);" />
				</span> </p>
				</td>
			</tr>
		</table>
	</div>
</form>

<div id="loading-sync" style="display: none; background:url(<?php echo esc_url(WP_HELPDESK_INTEGRATION_DIR);?>images/ajax-loaders.gif) no-repeat center #fff;"><?php echo esc_html__('Syncing', WP_HELPDESK_INTEGRATION_SLUG ); ?>...</div>
<div id="loading-image" style="display: none; background:url(<?php echo esc_url(WP_HELPDESK_INTEGRATION_DIR);?>images/ajax-loaders.gif) no-repeat center #fff;"><?php echo esc_html__('Please Wait', WP_HELPDESK_INTEGRATION_SLUG); ?>...</div>
