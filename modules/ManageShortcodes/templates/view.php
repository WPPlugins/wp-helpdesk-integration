<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$SmackHelpDeskIntegrationCoreFunctionsObj = new SmackHelpDeskIntegrationCoreFunctions();
$result = $SmackHelpDeskIntegrationCoreFunctionsObj->CheckFetchedDetails();

//require_once( WP_HELPDESK_INTEGRATION_DIRECTORY . "templates/thirdparty_mapping.php" );
if( !$result['status'] )
{
	$display_content = "<br>". $result['content']." to create Forms <br><br>";
	echo "<div style='font-weight:bold;  color:red; font-size:16px;text-align:center'> $display_content </div>";
}
else
{
	global $availableHelpDeskPortals, $helpDeskInformations;
	global $attrname;
	global $migrationmap;
	global $wpdb;
	$skinnyObj = SmackHelpDeskManageShortCodeObj::getInstance();
	$HelperObj = new SmackHelpDeskIntegrationHelper();
	$module = $HelperObj->Module;
	$moduleslug = $HelperObj->ModuleSlug;
	$activatedplugin = $HelperObj->ActivatedPlugin;
	$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
	$plugin_url= WP_HELPDESK_INTEGRATION_DIRECTORY;
	$onAction= 'onCreate';
	$siteurl= site_url();
	$helpdesk_users = get_option("smack_helpdesk_users");
	$users_detail = array();
	foreach( $helpdesk_users[$activatedplugin]['id'] as $key => $value )
	{
		$users_detail[$value] = array( 'user_name' => $helpdesk_users[$activatedplugin]['user_name'][$key] , 'first_name' => $helpdesk_users[$activatedplugin]['first_name'][$key] , 'last_name' => $helpdesk_users[$activatedplugin]['last_name'][$key]  );
	}


	$content1 = "";
	$content1 .= "<h3 style='margin-left:15px;'>".__('Forms and Shortcodes', WP_HELPDESK_INTEGRATION_SLUG)." ( {$helpDeskInformations[$activatedplugin]['Label']} ) : </h3>
			<div class='wp-common-crm-content'>
			<table style='margin-right:20px;margin-bottom:20px;border: 1px solid #dddddd;width: 98%;'>
				<tr style='border-top: 1px solid #dddddd;'>
				</tr>
				<tr class='smack-crm-pro-highlight smack-crm-pro-alt' style='border-top: 1px solid #dddddd;'>
					<th class='smack-crm-free-list-view-th' style='width: 300px;'>".__('Shortcode / Title', WP_HELPDESK_INTEGRATION_SLUG)."</th>
					<th class='smack-crm-free-list-view-th' style='width: 200px;'>".__('Assignee', WP_HELPDESK_INTEGRATION_SLUG)."</th>
					<th class='smack-crm-free-list-view-th' style='width: 200px;'>".__('Form Type', WP_HELPDESK_INTEGRATION_SLUG)."</th>
					<th class='smack-crm-free-list-view-th' style='width: 200px;'>".__('Module', WP_HELPDESK_INTEGRATION_SLUG)."</th>
					<th class='smack-crm-free-list-view-th' style='width: 200px;'>".__('Thirdparty', WP_HELPDESK_INTEGRATION_SLUG)."</th>

					<th class='smack-crm-free-list-view-th' style='width: 200px;'>".__('Actions', WP_HELPDESK_INTEGRATION_SLUG)."</th>
				</tr>";

	$shortcodemanager = $wpdb->get_results( $wpdb->prepare("select *from wp_smackhelpdesk_shortcode_manager where crm_type = %s", $activatedplugin) );
	
	foreach($shortcodemanager as $shortcode_fields)
	{
		$content1 .= "<tr>";
		$shortcode_name = "[" . $shortcode_fields->crm_type . "-web-form name='" . $shortcode_fields->shortcode_name . "']";
		$oldshortcodename = "";
		$oldshortcode_reveal_html = "";
		$oldshortcode_html = "";
		if( $shortcode_fields->old_shortcode_name != NULL )
		{
			$oldshortcodename = $shortcode_fields->old_shortcode_name;
			$oldshortcode_reveal_html = "<p><a style='cursor:pointer;' id='oldshortcodename_reveal{$shortcode_fields->shortcode_id}' onclick='jQuery(\"#oldshortcodename\"+{$shortcode_fields->shortcode_id}).show(); jQuery(\"#oldshortcodename_reveal\"+{$shortcode_fields->shortcode_id}).hide(); '> Click here to reveal old shortcode </a></p>";
			$oldshortcode_html = "<p style='display:none;' id='oldshortcodename{$shortcode_fields->shortcode_id}'> $oldshortcodename </p>";
		}

		$content1 .= "<td class='smack-crm-pro-highlight' style='border-top: 1px solid #dddddd; text-align: center'>" .$shortcode_name . "$oldshortcode_reveal_html $oldshortcode_html</td>";
		$content1 .= "<td class='smack-crm-pro-highlight' style='border-top: 1px solid #dddddd; text-align: center'>".__("Admin")."</td>";
		$content1 .= "<td class='smack-crm-pro-highlight' style='border-top: 1px solid #dddddd; text-align: center'>" . $shortcode_fields->form_type . "</td>";

		$content1 .= "<td class='smack-crm-pro-highlight' style='border-top: 1px solid #dddddd; text-align: center'>" . $shortcode_fields->module . "</td>";
		$content1 .= "<td class='smack-crm-pro-highlight' style='border-top: 1px solid #dddddd; text-align: center'> ".__("None")." </td>";

		$content1 .= "<td class='smack-crm-pro-highlight' style='border-top: 1px solid #dddddd; text-align:center;'>";
		$content1 .= "<a href='".esc_url(WP_HELPDESK_INTEGRATION_PLUG_URL)."&__module=ManageShortcodes&__action=ManageFields&portal_type=". $shortcode_fields->crm_type ."&module=". $shortcode_fields->module ."&EditShortcode=". $shortcode_fields->shortcode_name ."' > ".__("Edit")." </a>";
		$content1 .= "<a style='padding-left:10px;' title='".__("Upgrade To PRO")."' >".__("Delete")." </a>";	
	
		$content1 .= "</td>";
		$content1 .= "</tr>";
	}
	SmackHelpDeskIntegrationHelper::CreateStaticShortCodes('post');
	SmackHelpDeskIntegrationHelper::CreateStaticShortCodes('widget');


	$content1 .= "</table></div>";
	echo $content1;
	print $HelperObj->Module;
	if(array_key_exists($activatedplugin, $availableHelpDeskPortals['Available Helpdesk'])) { ?>
		<input class="button-primary" disabled style="float:left;margin:1px;margin-left:25%;" type="submit" value="<?php echo esc_attr__("Create Ticket Form");?>"/>
	<?php }
	?>
	<input class="button-primary" disabled style="float:left;margin-top:-1px;margin-left:50px;" type="submit" value="<?php echo esc_attr__("Create Contact Form");?>"/>

	<input class="button-primary" disabled style="float:left;margin-top:-1px;margin-left:65px;" type="button" id="thirdparty_map" value="<?php echo esc_attr__("Use Existing Form");?>"/>
	<br></br>
		<a href ="https://www.smackcoders.com/wp-helpdesk-integration.html" class="free-notice" style="float:left;margin-top:-1px;margin-left:27%;text-color:red" target="_blank"><h6 style="text-color:red"><?php echo esc_html__("Upgrade To Pro");?></h6>
</a>
		<a href ="https://www.smackcoders.com/wp-helpdesk-integration.html" class="free-notice" style="float:left;margin-top:-1px;margin-left:11%;" target="_blank"><h6><?php echo esc_html__("Upgrade To Pro");?></h6>
</a>
		<a href ="https://www.smackcoders.com/wp-helpdesk-integration.html" class="free-notice" style="float:left;margin-top:-1px;margin-left:11%;" target="_blank"><h6><?php echo esc_html__("Upgrade To Pro");?></h6>
</a>


<?php
}
?>
