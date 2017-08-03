<?php
/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$siteurl = site_url();
$siteurl = esc_url( $siteurl );
$config = get_option("smack_whi_thirdpartyplugin_settings");
$help_img = WP_HELPDESK_INTEGRATION_DIR.'images/helpdesksyncuser.png';
//print_r($help_img);die;
$help="<img src='$help_img'>";
//print_r($help);die;
?>

</script>
<script>
jQuery(document).ready( function(){
    jQuery( "#dialog-confirm-thirdparty" ).hide();
    jQuery( "#dialog-activate-thirdparty" ).hide();
});
</script>
<div id="dialog-confirm-thirdparty" title="Switch the plugin">
    <p><span  style="float:left; margin:0 7px 20px 0;"></span><?php echo esc_attr__("Do you want to change Form Type??");?></p>
</div>

<div id="dialog-activate-thirdparty" title="Plugin inactive">
    <p><span  style="float:left; margin:0 7px 20px 0;"></span><?php echo esc_attr__("You should activate the plugin first");?></p>
</div>

<?php
$Thirdparty_plugin = get_option( "SmackHelpDeskConfiguredThirdPartyPlugin" );
?>
<input type="hidden" id="third_plugin_value" value='<?php echo $Thirdparty_plugin ;?>'>
<div>
    <!--  Start -->
    <form id="smack-thirdparty-settings-form" action="" method="post">
        <input type="hidden" name="smack-thirdparty-settings-form" value="smack-thirdparty-settings-form" />
        <input type="hidden" id="plug_URL" value="<?php echo esc_url(WP_HELPDESK_INTEGRATION_PLUG_URL);?>" />
        <div class="wp-common-crm-content" style="width: 800px;float: left;">

        </div>

        <script>
            jQuery( "#dialog-modal" ).hide();
        </script>
        <span id="Fields" style="margin-right:20px;"></span>
    </form>
    <!-- End-->
</div>

<div id="loading-image" style="display: none; background:url(<?php echo esc_url(WP_PLUGIN_URL);?>/<?php echo WP_HELPDESK_INTEGRATION_SLUG; ?>/images/ajax-loaders.gif) no-repeat center #fff;"><?php echo esc_html__('Please Wait...', WP_HELPDESK_INTEGRATION_SLUG ); ?> </div>

<?php
echo str_repeat( '<br>' , 2 );
$config = get_option( "SmackHelpDeskCaptchaSettings" );
$captcha = WP_HELPDESK_INTEGRATION_PLUG_URL ;
$captcha_form_url = add_query_arg( array( '__module' => 'Thirdparty' , '__action' => 'view'  ) , $captcha );
?>


<div class = "captcha" style="margin-left:20px;">
    <form id="smack-<?php echo sanitize_text_field($skinnyData['activatedplugin']);?>-captcha-form" action="<?php echo esc_url( $captcha_form_url ) ; ?>" method="post">
        <input type="hidden" name="smack-<?php echo esc_attr($skinnyData['activatedplugin']);?>-captcha-form" value="smack-<?php echo sanitize_text_field($skinnyData['activatedplugin']);?>-captcha-form" />
        <table class="settings-table">
            <tr><td colspan="4">
                    <label id="inneroptions" ><?php echo esc_html__('Debug and Notification :', WP_HELPDESK_INTEGRATION_SLUG );?> </label>
                </td></tr>
            <tr>
                <td  style="width:250px;padding-left:40px;">
                    <label id="innertext" ><?php echo esc_html__('Which log do you need?', WP_HELPDESK_INTEGRATION_SLUG );?> </label>
                    <div style="float:right">:</div>
                </td>
                <td>
                        <span id="circlecheck">
                           <select name="emailcondition" id="emailcondition" onchange="enablesmackemail(this.id)">
                               <option value="none" id='smack_email'
                                   <?php
                                   if(isset($config['emailcondition']) && $config['emailcondition'] == 'none')
                                   {
                                       echo "selected=selected";
                                   }
                                   ?>
                               >None</option>
                               <option value = "success" id= 'successemailcondition'
                                   <?php
                                   if(isset($config['emailcondition']) && $config['emailcondition'] == 'success')
                                   {
                                       echo "selected=selected";
                                   }
                                   ?>
                               >Success</option>
                               <option value="failure" disabled>Failure</option>
                               <option value="both" disabled>Both</option>
                           </select>
                </td>
            </tr>
            <tr>
                <td style='width:50px;padding-left:40px;'>
                    <label id="innertext"> Specify Email </label><div style='float:right;'> : </div>
                </td>
                <td>
                    <input type='text' name='email' disabled='disabled' visibility='hidden' color='transparent'></td>
			<td><a href ="https://www.smackcoders.com/wp-helpdesk-integration.html" class="free-notice" style="float:left;margin-top:-1px;margin-left:-50%;" target="_blank"><h6><?php echo esc_html__("Upgrade To Pro");?> </h6>

                </td>
            </tr>
            <tr>
                <td style='width:50px;padding-left:40px;'>
                    <label id="innertext"><?php echo esc_html__('Enable Debug mode ', WP_HELPDESK_INTEGRATION_SLUG ); ?></label><div style='float:right;'>:</div>
                </td>
                <td>
                    <div class="switch">
                        <input type='checkbox' class='smack-vtiger-settings-text cmn-toggle cmn-toggle-yes-no' name='debugmode' id='debugmode'  <?php if(isset($config['debugmode']) && sanitize_text_field($config['debugmode']) == 'on') { echo "checked=checked"; } ?> onclick="debugmod(this.id)"/>
                        <label for="debugmode" id="innertext" data-on="On" data-off="Off"></label>
                    </div>
            </tr>
        </table>
        <br><br>
        <table style="float:right;margin-right:40px;margin-top:-40px;">
            <tr>
                <td>
                    <input type="hidden" name="posted" value="<?php echo 'posted';?>">
                    <p class="">
                        <input type="submit" value="<?php echo esc_attr__('Save', WP_HELPDESK_INTEGRATION_SLUG );?>" id="innersave" class="button-primary"/>
                    </p>
                </td>
            </tr>
        </table>
	<div class="wp-helpdesk-integration-free-notice" style="text-align:center; width: 94.5%;">
            <a  href ="https://www.smackcoders.com/wp-helpdesk-integration.html"  class="free-notice" target="_blank"><h4><?php echo esc_html__(" Unlock access to reCaptcha key feature by upgrading to our Pro version.");?></h4>
            </a>
            <p><?php echo esc_html__("Remove Free version before installing PRO!");?></p>
        </div>
        <table class="settings-table">
            <tr><td></td></tr>
            <tr>
                <td>
                    <div><?php echo $help ?></div>
                </td>
            </tr>
        </table>
	</form>
</div>
