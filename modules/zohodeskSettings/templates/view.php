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
        jQuery( document ).ready( function( ) {
                jQuery( "#Fieldnames" ).hide(  );
        });

</script>
<input type="hidden" id="get_config" value="<?php echo $config_data ?>" >
<input type="hidden" id="revert_old_crm_pro" value="zohodesk">
 <form id="smack-zohodesk-settings-form"  action="<?php echo esc_url($_SERVER['REQUEST_URI']); ?>" method="post">

	<input type="hidden" name="smack-zohodesk-settings-form" value="smack-zohodesk-settings-form" />
	<input type="hidden" id="plug_URL" value="<?php echo esc_url(WP_HELPDESK_INTEGRATION_PLUG_URL);?>" />
	<div class="wp-common-crm-content" style="width:100%;float: left;">
	<table style="width: 40%;">
		<tr>
			<td><label id="inneroptions" style="margin-top:6px;margin-right:4px;font-weight:bold"><?php echo esc_html__("Select the HelpDesk you use", WP_HELPDESK_INTEGRATION_SLUG ); ?></label></td>

			<td>
			<?php
				$ContactFormPluginsObj = new SmackHelpDeskUIHelper();
				echo $ContactFormPluginsObj->getPluginActivationHtml();
			?>
			</td>
			<tr><td> <br /></tr></td>
		</tr>
        </table>
	<label id="inneroptions" style="font-weight:bold;">ZohoDesk Settings:</label>
        <table class="settings-table">

                <tr><td></td></tr>
		<tr>
			<td  style='width:250px;padding-left:40px;'>
				<label id="innertext"> <?php echo esc_html__('Username *', WP_HELPDESK_INTEGRATION_SLUG); ?> </label><div style='float:right;'> : </div>
			</td>
			<td>
				<input type='text' class='smack-vtiger-settings' name='username' id='username' value="<?php echo sanitize_text_field($config['username']) ?>"/>
                       </td> 
		</tr>
		<tr>
			<td style='width:160px;padding-left:40px;'>
				<label id="innertext"> <?php echo esc_html__('Password *', WP_HELPDESK_INTEGRATION_SLUG ); ?> </label><div style='float:right;'> : </div>
			</td>
			<td>
				<input type='password' class='smack-vtiger-settings' name='password' id='password' value="<?php echo sanitize_text_field($config['password']) ?>"/>
			</td>
		</tr>
		<tr>
                        <td  style='width:250px;padding-left:40px;'>
                                <label id="innertext"> <?php echo esc_html__('Portal Name *' , WP_HELPDESK_INTEGRATION_SLUG ); ?> </label><div style='float:right;'> : </div>
                        </td>
                        <td>
                                <input type='text' class='smack-vtiger-settings' name='portalname' id='portalname' value="<?php echo sanitize_text_field($config['portalname']) ?>"/>
                       </td>
                </tr>
		<tr>
                        <td  style='width:250px;padding-left:40px;'>
                                <label id="innertext"> <?php echo esc_html__('Department Name *' , WP_HELPDESK_INTEGRATION_SLUG ); ?> </label><div style='float:right;'> : </div>
                        </td>
                        <td>
                                <input type='text' class='smack-vtiger-settings' name='departmentname' id='departmentname' value="<?php echo sanitize_text_field($config['departmentname']) ?>"/>
                       </td>
                </tr>


<!-- TWO FACTOR AUTHENTICATION -->
		<tr>
		<td style="width:160px;padding-left:40px;">
		<label id="innertext"><?php echo esc_html__("Two Factor Authentication" , WP_HELPDESK_INTEGRATION_SLUG ); ?></label>
		</td>
		<td>
			<span style="float: left;margin-left: -14px;">:</span>
			<input type='checkbox' class="smack-vtiger-settings cmn-toggle cmn-toggle-yes-no" name='TFA_check' id='TFA_check' <?php if(isset($config['TFA_check']) && sanitize_text_field($config['TFA_check']) == 'on') { echo "checked=checked"; }  ?> onclick="enablesmackTFA(this.id)" />

		<label class="TFA_check" for="TFA_check" id="innertext" data-on="On" data-off="Off" ></label>
		<input type="hidden" id="TFA_check"  >
 </td>
		</tr>

		<tr id="TFA_tr_show_hide">
			<td style="width:160px;padding-left:40px;">
			<label id="innertext"> <?php echo esc_html__("Specify Authtoken" , WP_HELPDESK_INTEGRATION_SLUG ); ?></label><div style='float:right;'>:</div>

		</td>
		<td>
			<input type="text" id="TFA_authkey" onblur="TFA_Authkey_Save(this.value)" value="<?php echo get_option('SmackHelpDeskTFA_zoho_authtoken');?>" <?php if( !isset( $config['TFA_check'] ) || sanitize_text_field($config['TFA_check']) != 'on' ){ ?> disabled="disabled" <?php } ?> >
                        </div>
                                <div style="margin-left:195px">
                                <div class="tooltip"  style="margin-top:-24px">
                               <?php echo $help ?>
				 <span class="tooltipPostStatus" style="width:200px;">
                               <h5>Zoho AuthToken</h5>Generate and Specify the TFA AuthToken from Zoho Accounts.
				<a target="_blank" href="https://www.zoho.com/support/help/api/using-authentication-token.html">Refer Zoho Help</a>
                                </span> </div>
                                </div>

		</td>
		</tr>
<!-- END TFA -->
        </table>
	<table style="float:right;">
		<tr>
			<td>
				<p class="submit" style='position:absolute'>
				<input type="hidden" name="posted" value="<?php echo 'posted';?>">
				<input class="smack_settings_input_text" type="hidden" id="authkey" name="authkey" value="" />
		<input type="hidden" id="site_url" name="site_url" value="<?php echo esc_attr($siteurl) ;?>">
		<input type="hidden" id="active_plugin" name="active_plugin" value="<?php echo esc_attr($skinnyData['activatedplugin']); ?>">
		<input type="hidden" id="tickets_fields_tmp" name="tickets_fields_tmp" value="smack_whi_zohodesk_tickets_fields-tmp">
		<input type="hidden" id="contact_fields_tmp" name="contact_fields_tmp" value="smack_whi_zohodesk_contacts_fields-tmp">
		<p class='submit' style=''>
		<span style="padding-right:10px;">
	<input type="button" id="save_crm_config" value="<?php echo esc_attr__('Save Helpdesk Configuration', WP_HELPDESK_INTEGRATION_SLUG );?>" id="save"  class="button-primary" onclick="saveHelpDeskConfiguration(this.id);" />
</span>
				</p>
			</td>
		</tr>
	</table>
	</div>
</form>

<div id="loading-sync" style="display: none; background:url(<?php echo esc_url(WP_HELPDESK_INTEGRATION_DIR);?>images/ajax-loaders.gif) no-repeat center #fff;"><?php echo esc_html__('Syncing', WP_HELPDESK_INTEGRATION_SLUG ); ?>...</div>
<div id="loading-image" style="display: none; background:url(<?php echo esc_url(WP_HELPDESK_INTEGRATION_DIR);?>images/ajax-loaders.gif) no-repeat center #fff;"><?php echo esc_html__('Please Wait' , WP_HELPDESK_INTEGRATION_SLUG ); ?>...</div>
