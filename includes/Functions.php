<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

/*
Cases : 
1) CreateNewFieldShortcode		Will create new field shortcode
2) FetchCrmFields			Will Fetch crm fields from the the crm
3) FieldSwitch				Enable/Disable single field
4) DuplicateSwitch			Change Duplicate handling settings 
5) MoveFields				Change the order of the fields
6) MandatorySwitch			Make Mandatory or Remove Mandatory
7) SaveDisplayLabel			Save Display Label
8) SwitchMultipleFields			Enable/Disable multiple fields
9) SwitchWidget				Enable/Disable widget  form
10) SaveAssignedTo			Save Assignee of the form leads 
11) CaptureAllWpUsers			Capture All wp users
*/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SmackHelpDeskIntegrationCoreFunctions {

	public function CheckFetchedDetails()
	{
		global $wpdb, $availableHelpDeskPortals, $helpDeskInformations;
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$module = $HelperObj->Module;
		$moduleslug = $HelperObj->ModuleSlug;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
		$SettingsConfig = get_option("smack_whi_{$activatedplugin}_settings");
		$shortcodeObj = new SmackHelpDeskDataCapture();

		$leadsynced = $shortcodeObj->selectFieldManager( $activatedplugin , 'Leads' );
		$contactsynced = $shortcodeObj->selectFieldManager( $activatedplugin , 'Contacts' );
		$ticketsynced = $shortcodeObj->selectFieldManager( $activatedplugin, 'Tickets' );
		$users = get_option('smack_helpdesk_users');
		$usersynced = false;
		if( is_array($users[$activatedplugin]) && count( $users[$activatedplugin] ) > 0 )
		{
			$usersynced = true;
		}
		$content = "";
		$flag = true;
	 
		if(array_key_exists( $activatedplugin, $availableHelpDeskPortals['Available Helpdesk'])) {
			if( !$usersynced || !$ticketsynced )
			{
				$content = __( "Please configure your Helpdesk in the Helpdesk Configuration" , "wp-ultimate-tickets-builder-pro"  );
				$flag = false;
			}
		}

		$return_array = array( 'content' => "$content" , 'status' => $flag );
		return $return_array;
	}


	public function CreateNewFieldShortcode( $portal_type , $module ){
		global $helpDeskInformations;
		$module = $module;
		$moduleslug = rtrim( strtolower($module) , "s");
		$tmp_option = "smack_whi_{$portal_type}_{$moduleslug}_fields-tmp";
		if(!function_exists("generateRandomStringActivate"))
		{
			function generateRandomString($length = 10) {
				$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
				$randomString = '';
				for ($i = 0; $i < $length; $i++) {
					$randomString .= $characters[rand(0, strlen($characters) - 1)];
				}
				return $randomString;
			}
		}
		$list_of_shorcodes = Array();
		$shortcode_present_flag = "No";
		$config_fields = get_option($tmp_option);
		$options = "SmackHelpDeskShortCodeFields";
		$config_contact_shortcodes = get_option($options);
		if(is_array($config_contact_shortcodes))
		{
			foreach($config_contact_shortcodes as $shortcode => $values)
			{
				$list_of_shorcodes[] = $shortcode;
			}
		}

		for($notpresent = "no" ; $notpresent == "no"; )
		{
			$random_string = generateRandomString(5);
			if(in_array($random_string, $list_of_shorcodes))
			{
				$shortcode_present_flag = 'Yes';
			}
			if($shortcode_present_flag != 'yes')
			{
				$notpresent = 'yes';
			}
		}
		$options = $tmp_option;
		return $random_string;
	}

	public function doFieldAjaxAction()
	{
		$portal_type = isset($_REQUEST['portal_type']) ? sanitize_text_field($_REQUEST['portal_type']) : '';
		$module = isset($_REQUEST['module']) ? sanitize_text_field($_REQUEST['module']) : '';
		$module_options = $module;
		$options = sanitize_text_field($_REQUEST['option']);
		$onAction = sanitize_text_field($_REQUEST['onAction']);
		$siteurl = site_url();
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$moduleslug = $HelperObj->ModuleSlug;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
		$content = '';
		$FunctionsObj = new SmackHelpDeskIntegrations();
		$tmp_option = "smack_whi_{$activatedplugin}_{$moduleslug}_fields-tmp";
		if($onAction == 'onEditShortCode');
		{
			$original_options = "smack_{$activatedplugin}_fields_shortcodes";
			$original_config_fields = get_option($original_options);
		}
		$SettingsConfig = get_option("smack_whi_{$activatedplugin}_settings");
		if($onAction == 'onCreate')
		{
			$config_fields = get_option($options);
		}
		else
		{
			$config_fields = get_option($options);
		}
		$FieldCount = 0;
		if(isset($config_fields['fields']))
		{
			$FieldCount =count($config_fields['fields']);
		}

		if(isset($config_fields)){
			$error[0] = 'no fields';
		}
		switch($_REQUEST['doaction'])
		{
			case "GetAssignedToUser":
				$Functions = new SmackHelpDeskIntegrations();
				echo $Functions->getUsersListHtml();
				break;
			case "CheckformExits":
				$moduleslug = rtrim( strtolower($module) , "s");
				$config_fields = get_option( "smack_whi_{$portal_type}_{$moduleslug}_fields-tmp" );
				if( !isset($config_fields['fields'][0]) )
					die( "Not synced" );
				else
					die( "Synced" );
				break;
			case "GetTemporaryFields":
				$moduleslug = rtrim( strtolower($module) , "s");
				$config_fields = get_option( "smack_whi_{$portal_type}_{$moduleslug}_fields-tmp" );
				if($options != 'getSelectedModuleFields')
				{
					include(WP_HELPDESK_INTEGRATION_DIRECTORY.'templates/helpdesk-fields-form.php');
				}
				break;
			case "FetchCrmFields":
				$moduleslug = rtrim( strtolower($module) , "s");
				$config_fields = $FunctionsObj->getCrmFields( $module );
				$seq = 1;
				$field_details = array();
				foreach($config_fields['fields'] as $fkey => $fval) {
					$field_details['name'] = $fval['name'];
					$field_details['label'] = $fval['label'];
					$field_details['type'] = $fval['type']['name'];
					$field_details['field_values'] = null;
					if(! empty( $fval['type']['picklistValues'] ) ) {
						$field_details['field_values'] = serialize($fval['type']['picklistValues']);
					}
					$field_details['module'] = $module;
					if( $fval['mandatory'] == 2 )
						$field_details['mandatory'] = 1;
					else
						$field_details['mandatory'] = 0;
					$field_details['portal_type'] = $portal_type;
					$field_details['sequence'] = $seq;
					$field_details['base_model'] = null;
					$field_details['field_ref_id'] = null;
					if(isset($fval['base_model']))
						$field_details['base_model'] = $fval['base_model'];
					if(isset($fval['field_ref_id']))
						$field_details['field_ref_id'] = $fval['field_ref_id'];
					$seq++;

					if($field_details['label']=='Date of Birth')
					{
						$field_details['type']='date';
					}
					$DataObj = new SmackHelpDeskDataCapture();
					$DataObj->fieldManager( $field_details , $module );
					$DataObj->updateShortcodeFields( $field_details , $module );
				}
				if($options != 'getSelectedModuleFields')
				{
					include(WP_HELPDESK_INTEGRATION_DIRECTORY.'templates/display-log.php');
				}
				$options = "smack_whi_{$portal_type}_{$moduleslug}_fields-tmp";
				update_option($options, $config_fields);
				$options = "SmackHelpDeskShortCodeFields";
				$edit_config_fields = get_option($options);
				$edit_config_fields[sanitize_text_field($_REQUEST['shortcode'])] = $config_fields;
				update_option($options, $edit_config_fields);
				break;
			case "FetchAssignedUsers":
				$HelperObj = new SmackHelpDeskIntegrationHelper();
				$module = $HelperObj->Module;
				$moduleslug = $HelperObj->ModuleSlug;
				$activatedplugin = $HelperObj->ActivatedPlugin;
				$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
				$FunctionsObj = new SmackHelpDeskIntegrations();
				$crmusers = get_option( 'smack_helpdesk_users' );
				$users = $FunctionsObj->getUsersList();
				$crmusers[$activatedplugin] = $users;
				update_option('smack_helpdesk_users', $crmusers);
				#$user = $users[$activatedplugin];
				$content .='<h5>Assigned Users:</h5>';
				$firstname = '';
				foreach($users['first_name'] as $assignusers)
				{
					$firstname .= $assignusers."<br>";
				}
				echo $content;
				echo $firstname;die;
				break;
			default:
				break;
		}
	}



	public function doNoFieldAjaxAction()
	{
		global $wpdb;
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$module = $HelperObj->Module;
		$moduleslug = $HelperObj->ModuleSlug;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
		$SettingsConfig = get_option("smack_whi_{$activatedplugin}_settings");
		$shortcodeObj = new SmackHelpDeskDataCapture();
		switch($_REQUEST['doaction'])
		{
			case "SaveFormSettings":
				$shortcodedata['name'] = $shortcode_name;
				$shortcodedata['type'] = sanitize_text_field($_REQUEST['formtype']);
				$shortcodedata['assignto'] = sanitize_text_field($_REQUEST['assignedto']);
				$shortcodedata['errormesg'] = sanitize_text_field($_REQUEST['errormessage']);
				$shortcodedata['successmesg'] = sanitize_text_field($_REQUEST['successmessage']);
				$shortcodedata['duplicate_handling'] = sanitize_text_field($_REQUEST['duplicate_handling']);
				if( sanitize_text_field($_REQUEST['enableurlredirection']) == "true" )
				{
					$shortcodedata['isredirection'] = 1;
				}
				else
				{
					$shortcodedata['isredirection'] = 0;
				}
				$shortcodedata['urlredirection'] = sanitize_text_field($_REQUEST['redirecturl']);
				if( sanitize_text_field($_REQUEST['enablecaptcha']) == "true" )
				{
					$shortcodedata['captcha'] = 1;
				}
				else
				{
					$shortcodedata['captcha'] = 0;
				}
			
				break;
		}
	}
}

class SmackHelpDeskAjaxActions
{
	public static function adminAjaxActionsForHelpDesk()
	{
		$OverallFunctionObj = new SmackHelpDeskIntegrationCoreFunctions();
		if( isset($_REQUEST['operation']) && (sanitize_text_field($_REQUEST['operation']) == "NoFieldOperation") )
		{
			$OverallFunctionObj->doNoFieldAjaxAction( );
		}
		else
		{
			$OverallFunctionObj->doFieldAjaxAction();
		}
		die;
	}
}

add_action('wp_ajax_adminAjaxActionsForHelpDesk', array( "SmackHelpDeskAjaxActions" , 'adminAjaxActionsForHelpDesk' ));

class SmackHelpDeskUserDataCapture
{

	function CaptureFormFields( $globalvariables )
	{
		global $wpdb;
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$module = $HelperObj->Module;
		$moduleslug = $HelperObj->ModuleSlug;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
		$duplicate_inserted = $duplicate_cancelled = $duplicate_updated = 0;
		$module = $globalvariables['formattr']['module'];
		$post = $globalvariables['post'];
		$data = "failure";
		foreach($post as $key => $value) {
			if(strpos($key, 'smack_lb_') !== false) {
				$key = str_replace('smack_lb_', '', $key);
				$post[$key] = $value;
			} else {
				$post[$key] = $value;
			}
		}
		$FunctionsObj = new SmackHelpDeskIntegrations();
		if($activatedplugin == 'freshdesk')
			$emailfield = $FunctionsObj->duplicateCheckEmailField($module);
		elseif($activatedplugin == 'zendesk')
			$emailfield = $FunctionsObj->duplicateCheckEmailField($module);
		elseif($activatedplugin == 'zohodesk')
                        $emailfield = $FunctionsObj->duplicateCheckEmailField($module);
		elseif($activatedplugin == 'Vtigertickets')
                        $emailfield = $FunctionsObj->duplicateCheckEmailField($module);
		else
			$emailfield = $FunctionsObj->duplicateCheckEmailField();
		$shortcode_name = $globalvariables['attrname'];
		$enable_round_robin = $wpdb->get_var( $wpdb->prepare( "select assigned_to from wp_smackhelpdesk_shortcode_manager where shortcode_name =%s" , $shortcode_name ) );
		if( $enable_round_robin == 'Round Robin')	
		{
			$assignedto_old = $wpdb->get_var( $wpdb->prepare( "select Round_Robin from wp_smackhelpdesk_shortcode_manager where shortcode_name =%s" , $shortcode_name ) );
		}
	
		if(is_array($post))
		{
			foreach($post as $key => $value)
			{
				if(($key != 'moduleName') && ($key != 'submitcontactform') && ($key != 'submitcontactformwidget') && ($key != '') && ($key != 'submit'))
				{
					/* if($module == 'freshdesk') {

					} else { */
						$module_fields[ $key ] = $value;
					// }
					if($key == $emailfield)
					{
						$email_field_present = "yes";
						$user_email = $value;
					}
				}
			}
		}
		if( $enable_round_robin != 'Round Robin' )
		{
			$module_fields[$FunctionsObj->assignedToFieldId()] = $globalvariables['assignedto'];
		}
		else
		{
			$module_fields[$FunctionsObj->assignedToFieldId()] = $assignedto_old;
		}
		unset($module_fields['formnumber']);
		unset($module_fields['IsUnreadByOwner']);
			$record = $FunctionsObj->createRecord( $module , $module_fields);
				if($record['result'] == "success")
				{
					$duplicate_inserted++;
					$data = "/$module entry is added./";
	
				}
		return $data;
	}




	//Create data with thirdparty mapped configuration
	public static function thirdparty_mapped_submission($posted_array)
	{
		$tp_module = $posted_array['third_module'];
		$tp_active_helpdesk = $posted_array['thirdparty_helpdesk'];
		$tp_plugin_name = $posted_array['third_plugin'];
		$tp_form_title = $posted_array['form_title'];
		$tp_shortcode = $posted_array['shortcode'];
		$duplicate_option = $posted_array['duplicate_option'];

		//Code For RR
		$get_existing_option = get_option( $tp_shortcode );
		$tp_assignedto = $get_existing_option['thirdparty_assignedto_for_helpdesk'];
		//$assignedto_old = $get_existing_option['tp_roundrobin'];
                $helpdesk_active_portal = get_option( 'SmackHelpDeskIntegrationActivePortal' );

                       	//END RR

		if( isset($tp_module)  )
		{
			$module = $tp_module;
			$duplicate_cancelled = 0;
			$duplicate_inserted = 0;
			$duplicate_updated = 0;
			$successful = 0;
			$failed = 0;
			$FunctionsObj = new SmackHelpDeskIntegrations();
			$post = $posted_array['posted'];
			$Assigned_user = SmackHelpDeskUserDataCapture::wp_get_mapping_assignedto($tp_shortcode , $assignedto_old);
                	$Assigned_user_value = array_values($Assigned_user);
                	if( $Assigned_user_value[0] != "--Select--" )
                	{
                        	$post = array_merge( $post , $Assigned_user );
                	}
			$user_email = "";
				$record = $FunctionsObj->createRecord( $module , $post);
					
		}
	}

}
