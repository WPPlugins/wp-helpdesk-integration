<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$HelperObj = new SmackHelpDeskIntegrationHelper;
$plugin_url= WP_HELPDESK_INTEGRATION_DIRECTORY;
$onAction= 'onCreate';
$siteurl= site_url();
$module = $HelperObj->Module;
$moduleslug = $HelperObj->ModuleSlug;
$activate_crm = get_option( 'SmackHelpDeskIntegrationActivePortal' );

$crmSettings = get_option("smack_whi_{$activate_crm}_settings");
if($crmSettings == '' && sanitize_text_field($_REQUEST['__module']) != 'Settings') {
	$configurationURL = WP_HELPDESK_INTEGRATION_PLUG_URL . '&__module=Settings&__action=view';
	require_once (ABSPATH . 'wp-includes/pluggable.php');
	wp_safe_redirect($configurationURL);
}
$disabledMenu = '';
if(!$crmSettings) {
	$disabledMenu = "style='pointer-events:none;opacity:0.7;'";
}
?>

<div id="test-modal" class="white-popup-block mfp-hide">
	<label id='inneroptions'>Integration of Woo Commerce Status with CRM is supported for all CRM except Salesforce CRM. </label>
	<div style='width:500px;'><a style='color:red;font-weight:bold;margin-left:400px;text-decoration:none' class="popup-modal-dismiss" href="#">Dismiss</a></div>
</div>

<input type='hidden' id='active_helpdesk' value="<?php echo $activate_crm ?>">

<script>
	jQuery( document).ready(function(){
		var active_helpdesk = jQuery( "#active_helpdesk" ).val();

		if( active_helpdesk != 'wptigerpro' )
		{
			jQuery('.popup-modal').magnificPopup({
				type: 'inline',
				preloader: false,
				focus: '#ecommerce_integration',
				modal: true
			});
			jQuery(document).on('click', '.popup-modal-dismiss', function (e) {
				e.preventDefault();
				jQuery.magnificPopup.close();
			});

		}
	});
</script>

<nav class='navbar navbar-default' role='navigation'>
	<div>
		<?php
		$admin_url = 'admin.php';
		$settings_page = add_query_arg( array( 'page' => WP_HELPDESK_INTEGRATION_SLUG . '/index.php' , '__module' => 'Settings' , '__action' => 'view' ) , $admin_url );
		$crm_forms = add_query_arg( array( 'page' => WP_HELPDESK_INTEGRATION_SLUG . '/index.php' , '__module' => 'ManageShortcodes' , '__action' => 'view' ) , $admin_url );
		$captcha = add_query_arg( array( 'page' => WP_HELPDESK_INTEGRATION_SLUG . '/index.php' , '__module' => 'Captcha' , '__action' => 'view' ) , $admin_url );
		$Thirdparty = add_query_arg( array( 'page' => WP_HELPDESK_INTEGRATION_SLUG . '/index.php' , '__module' => 'Thirdparty' , '__action' => 'view' ) , $admin_url );
		$syncuser = add_query_arg( array( 'page' => WP_HELPDESK_INTEGRATION_SLUG . '/index.php' , '__module' => 'SyncUser' , '__action' => 'view' ) , $admin_url );
		$ecommerce =  add_query_arg( array( 'page' => WP_HELPDESK_INTEGRATION_SLUG . '/index.php' , '__module' => 'EcommerceSettings' , '__action' => 'view' ) , $admin_url );
		?>
		<ul class='nav navbar-nav'>
			<!-- for third party plugin settings -->
			<li class="<?php if( sanitize_text_field($_REQUEST['__module']) =='ManageShortcodes' ){ echo 'activate'; }else { echo 'deactivate'; }?>" <?php echo $disabledMenu; ?> >
				<a href='<?php echo esc_url( $crm_forms ); ?>'><span id='shortcodetab'> <?php echo esc_html__("Forms", WP_HELPDESK_INTEGRATION_SLUG ) ; ?></span></a>
			</li>
			<li class="<?php if( (sanitize_text_field($_REQUEST['__module'])=='Thirdparty' ) && ( sanitize_text_field($_REQUEST['__action'])=='view' ) ){ echo 'activate'; }else{ echo 'deactivate'; }?>" <?php echo $disabledMenu; ?> >
				<a href='<?php echo esc_url( $Thirdparty ) ?>'><span id='settingstab'> <?php echo esc_html__("Form Settings", WP_HELPDESK_INTEGRATION_SLUG ); ?> </span></a>
			</li>
			<li class="<?php if( sanitize_text_field($_REQUEST['__module']) =='SyncUser' ) { echo 'activate'; }else{ echo 'deactivate'; }?>" <?php echo $disabledMenu; ?> >
				<a href='<?php echo esc_url( $syncuser ) ?>'><span id='settingstab'><?php echo esc_html__('WP Users Sync', WP_HELPDESK_INTEGRATION_SLUG ); ?> </span></a>
			</li>
			<li  class="<?php if( sanitize_text_field($_REQUEST['__module']) =='EcommerceSettings' ) { echo 'activate'; }else{ echo 'deactivate'; }?>" <?php echo $disabledMenu; ?> >
				<?php
				if( $activate_crm == 'freshdesk' || $activate_crm == 'zendesk' || $activate_crm == 'zohodesk'||$activate_crm == 'Vtigertickets')
				{ ?>
					<a href='<?php echo esc_url( $ecommerce ) ?>'><span id='settingstab'><?php echo esc_html__('eCommerce Integration', WP_HELPDESK_INTEGRATION_SLUG ); ?> </span></a>
					<?php
				}
				else
				{ ?>
					<a class='popup-modal' href='#test-modal'><span id='settingstab'><?php echo esc_html__('eCommerce Integration', WP_HELPDESK_INTEGRATION_SLUG ); ?> </span></a>
					<?php
				}
				?>
			</li>
			<li class="<?php if( (sanitize_text_field($_REQUEST['__module'])=='Settings' ) && ( sanitize_text_field($_REQUEST['__action'])=='view' ) ){ echo 'activate'; }else{ echo 'deactivate'; }?>" >
				<a href='<?php echo esc_url( $settings_page ); ?>'><span id='settingstab'> <?php echo esc_html__("Configuration", WP_HELPDESK_INTEGRATION_SLUG ); ?> </span></a>
			</li>
		</ul>
	</div>
</nav>
