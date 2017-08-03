<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

/******************************
 * Filename	: includes/SmackHelpDeskIntegrationHelper.php
 * Description	: Check
 * Author 	: check
 * Owner  	: smackcoders.com
 * Date   	: Mar11,2014
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SmackHelpDeskIntegrationHelper {
	public $capturedId=0;
	public $ActivatedPlugin;
	public $ActivatedPluginLabel;
	public $Action;
	public $Module;
	public $ModuleSlug;
	public $instanceurl;
	public $accesstoken;
	public function __construct()
	{
		global $availableHelpDeskPortals;
		$ContactFormPluginsObj = new SmackHelpDeskUIHelper();
		$this->ActivatedPlugin = $ContactFormPluginsObj->getActivePlugin();
		$this->ActivatedPluginLabel = array_key_exists($this->ActivatedPlugin, $availableHelpDeskPortals['Available Helpdesk']) ? $availableHelpDeskPortals['Available Helpdesk'][$this->ActivatedPlugin] : 'freshdesk';
		if(isset( $_REQUEST['action'] ))
		{
			$this->Action = sanitize_text_field($_REQUEST['action']);
		}
		else
		{
			$this->Action = "";
		}
		if(isset($_REQUEST['module']))
		{
			$this->Module = sanitize_text_field($_REQUEST['module']);
		}
		else
		{
			$this->Module = "";
		}
		$this->ModuleSlug = rtrim( strtolower($this->Module) , "s");
	}

	public static function activate(){
		#$SmackHelpDeskIntegrationHelper = new SmackHelpDeskIntegrationHelper();
		#$SmackHelpDeskIntegrationHelper->initializeMigration();
		self::create_ecom_Table();
		$sync_array = array( 'user_sync_module' => 'Contacts' , 'smack_capture_duplicates' => 'skip' );
		//Wpuser_assigned_to//new
		$wp_user_assignto = array( 'usersync_assign_leads' => '--Select--');
		update_option( 'smack_helpdesk_assignedto_settings_for_freshdesk', $wp_user_assignto );
		update_option( 'smack_helpdesk_assignedto_settings_for_zendesk', $wp_user_assignto );
		update_option( 'smack_helpdesk_assignedto_settings_for_zohodesk', $wp_user_assignto );
		//END wpuser_assigned_to
		update_option("SmackHelpDeskUserCaptureSettings_freshdesk", $sync_array);
		update_option("SmackHelpDeskUserCaptureSettings_zendesk", $sync_array);
		update_option("SmackHelpDeskUserCaptureSettings_zohodesk", $sync_array);
		update_option( "SmackHelpDeskConfiguredThirdPartyPlugin" , "none" );
		update_option( "SmackHelpDeskAvailableModules" , "Leads" );
		update_option( "SmackHelpDeskCustomPlugin" , "none" );
		update_option( "SmackHelpDeskUserSyncOption" , "On" );
		update_option( "SmackHelpDesk_ConfiguredModule_eCommerce" , "Not Enabled" );
		global $availableHelpDeskPortals , $defaultActiveHelpDesk ;
		$index = 0;
		$i = 0;
		foreach($availableHelpDeskPortals as $groupName => $availableConfigurations) {
			foreach ( $availableConfigurations as $key => $value ) {
				if ( $defaultActiveHelpDesk == $key ) {
					update_option( 'SmackHelpDeskIntegrationActivePortal', $defaultActiveHelpDesk );
					$index = 1;
				}
				if ( $i == 0 ) {
					$firstplugin = $key;
				}
				$i ++;
			}
		}
		update_option("SmackHelpDeskIntegrationFirstTimeWarning" , "true");
		if($index == 0)
		{
			update_option( 'SmackHelpDeskIntegrationActivePortal' , $firstplugin );
		}
		self::createPluginTables();
	}

	public static function deactivate(){

		//VTiger deactivation code
		global $availableHelpDeskPortals;
		foreach($availableHelpDeskPortals as $groupName => $availableConfigurations) {
			foreach ( $availableConfigurations as $key => $value ) {
				delete_option( "smack_{$key}_lead_post_field_settings" );
				delete_option( "smack_{$key}_lead_widget_field_settings" );

				delete_option( "smack_whi_{$key}_lead_fields-tmp" );
				delete_option( "smack_whi_{$key}_contact_fields-tmp" );

				delete_option( "smack_whi_{$key}_settings" );
				delete_option( "SmackHelpDeskShortCodeFields" );
			}
		}
		delete_option("smack_oldversion_shortcodes");
		delete_option("SmackHelpDeskIntegrationFirstTimeWarning");
	}

	public static function checkVersion()
	{
		$wp_helpdesk_integration_version = get_option( "SmackHelpDeskIntegrationVersion" );
		update_option( 'SmackHelpDeskIntegrationVersion' , WP_HELPDESK_INTEGRATION_VERSION );
		if( $wp_helpdesk_integration_version == NULL || $wp_helpdesk_integration_version == "" || !$wp_helpdesk_integration_version )
		{
			self::createPluginTables();
		}
		if( $wp_helpdesk_integration_version == NULL || $wp_helpdesk_integration_version == "" || !$wp_helpdesk_integration_version || $wp_helpdesk_integration_version <= 1.1)
		{
			self::createPluginTablesNew();
		}
	}

	public static function createPluginTables()
	{
		global $wpdb;
		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `wp_smackhelpdesk_shortcode_manager` (
			  `shortcode_id` int(11) NOT NULL AUTO_INCREMENT,
			  `shortcode_name` varchar(10) NOT NULL,
			  `old_shortcode_name` varchar(255) DEFAULT NULL,
			  `form_type` varchar(10) NOT NULL,
			  `assigned_to` varchar(200) NOT NULL,
			  `error_message` text NOT NULL,
			  `success_message` text NOT NULL,
			  `submit_count` int(10) NOT NULL DEFAULT '0',
			  `success_count` int(10) NOT NULL DEFAULT '0',
			  `failure_count` int(10) NOT NULL DEFAULT '0',
			  `is_redirection` tinyint(1) NOT NULL,
			  `url_redirection` varchar(255) NOT NULL,
			  `duplicate_handling` varchar(10) NOT NULL DEFAULT 'none',
			  `google_captcha` tinyint(1) NOT NULL,
			  `module` varchar(25) NOT NULL,
			  `Round_Robin` varchar(50) NOT NULL,
			  `crm_type` varchar(25) NOT NULL,
			  PRIMARY KEY (`shortcode_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		");

		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `wp_smackhelpdesk_field_manager` (
			  `field_id` int(11) NOT NULL AUTO_INCREMENT,
			  `field_name` varchar(50) NOT NULL,
			  `field_label` varchar(50) NOT NULL,
			  `field_ref_id` INT(10) NULL,
			  `field_type` varchar(20) NOT NULL,
			  `field_values` longtext NOT NULL,
			  `field_default` text NOT NULL,
			  `module_type` varchar(20) NOT NULL,
			  `field_mandatory` varchar(10) NOT NULL,
			  `crm_type` varchar(25) NOT NULL,
			  `field_sequence` int(10) NOT NULL,
			  `base_model` varchar(20) NOT NULL,
			  PRIMARY KEY (`field_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		");

		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `wp_smackhelpdesk_form_field_manager` (
			  `rel_id` int(11) NOT NULL AUTO_INCREMENT,
			  `shortcode_id` int(11) NOT NULL,
			  `field_id` int(11) NOT NULL,
			  `wp_field_mandatory` varchar(10) NOT NULL,
			  `state` varchar(10) NOT NULL,
			  `custom_field_type` varchar(20) NOT NULL,
			  `custom_field_values` longtext NOT NULL,
			  `custom_field_default` text NOT NULL,
			  `form_field_sequence` int(3) NOT NULL,
			  `display_label` varchar(50) NOT NULL,
			  PRIMARY KEY (`rel_id`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1
		");
	}

	public static function createPluginTablesNew()
	{
		global $wpdb;
		//new table for form relation with third party plugins
		$wpdb->query("CREATE TABLE IF NOT EXISTS `wp_smackhelpdeskformrelation` (
				  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
				  `shortcode` varchar(30) NOT NULL,
				  `thirdparty` varchar(30) NOT NULL,
				  `thirdpartyid` int(50) DEFAULT NULL,
				  PRIMARY KEY (`id`)
				) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=latin1
		");

		//create table for form field relations
		$wpdb->query("
			CREATE TABLE IF NOT EXISTS `wp_smackthirdpartyhelpdeskformfieldrelation` (
			  `id` int(6) unsigned NOT NULL AUTO_INCREMENT,
			  `smackshortcodename` varchar(30) NOT NULL,
			  `smackfieldid` int(20) DEFAULT NULL,
			  `smackfieldslable` varchar(100) NOT NULL,
			  `thirdpartypluginname` varchar(30) NOT NULL,
			  `thirdpartyformid` int(50) DEFAULT NULL,
			  `thirdpartyfieldids` varchar(100) DEFAULT NULL,
			  PRIMARY KEY (`id`)
			) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=latin1
		");
	}

	public static function create_ecom_Table()
	{
		global $wpdb;
		$wpdb->query("CREATE TABLE IF NOT EXISTS `wp_smackhelpdesk_ecom_info` (
                          id int(6) unsigned NOT NULL AUTO_INCREMENT,
                          crmid varchar(100) DEFAULT NULL,
                          crm_name varchar(100) NOT NULL,
                          wp_user_id varchar(100) NOT NULL,
                          is_user int(30) NOT NULL,
                          lead_no varchar(100) DEFAULT NULL,
			  product_id varchar(100) DEFAULT NULL,
                          contact_no varchar(100) DEFAULT NULL,
			  order_id varchar(100) DEFAULT NULL,
			  sales_orderid varchar(100) DEFAULT NULL,		
                          PRIMARY KEY (`id`)
                        ) ENGINE=InnoDB AUTO_INCREMENT=142 DEFAULT CHARSET=latin1
                ");
	}

	public function CreateNewFieldShortcode( $portal_type , $module ){
		global $crmdetails;
		$module = $module;
		$moduleslug = rtrim( strtolower($module) , "s");
		$tmp_option = "smack_whi_{$portal_type}_{$moduleslug}_fields-tmp";
		if(!function_exists("generateRandomStringActivate"))
		{
			function generateRandomStringActivate($length = 10) {
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
			$random_string = generateRandomStringActivate(5);
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

	public static function output_fd_page()
	{
		require_once(WP_HELPDESK_INTEGRATION_DIRECTORY.'config/settings.php');
		require_once(WP_HELPDESK_INTEGRATION_DIRECTORY.'lib/skinnymvc/controller/SkinnyController.php');
		if (!isset($_REQUEST['__module']))
		{
			$admin_page = get_admin_url() . 'admin.php';
			$index_page = add_query_arg( array( 'page' => WP_HELPDESK_INTEGRATION_SLUG . '/index.php', '__module' => 'Settings', '__action' => 'view' ) , $admin_page );
			wp_safe_redirect( $index_page );
			exit;
		}
		$c = new SkinnyControllerHelpDeskIntegration;
		$c->main();
	}

	public function renderMenu()
	{
		include(plugin_dir_path(__FILE__) . '../templates/menu.php');
	}

	public function renderContent()
	{
		if($this->Action == "Settings" || $this->Action=="")
		{
			if($this->Action=="")
			{
				$this->Action = "Settings";
			}
			$action = $this->ActivatedPlugin.$this->Action;
			$module = $this->Module;
		}
		else
		{
			$action = $this->Action;
			$module = $this->Module;
		}
		include(plugin_dir_path(__FILE__) . '../modules/'.$action.'/actions/actions.php');
		include(plugin_dir_path(__FILE__) . '../modules/'.$action.'/templates/view.php');
	}
	
	public static function CreateStaticShortCodes($formType) {
		error_reporting(E_ALL);
		ini_set('display_errors', 'On');
		global $wpdb;
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$crmType = $HelperObj->ActivatedPlugin;
		$module = 'Tickets';
		// Function call
		$get_shortCodes = $wpdb->get_results($wpdb->prepare("select *from wp_smackhelpdesk_shortcode_manager where form_type = '%s' and module = '%s' and crm_type = '%s'", array($formType, $module, $crmType)));
		if(empty($get_shortCodes)) {
			$shortCodeObj                  = new SmackHelpDeskDataCapture();
			$OverallFunctions              = new SmackHelpDeskIntegrationCoreFunctions();
			$randomString                  = $OverallFunctions->CreateNewFieldShortcode( $crmType, $module );
			$config_fields['crm']          = $crmType;
			$users_list                    = get_option( 'crm_users' );
			$assignee                      = $users_list[ $crmType ]['id'][0];
			$shortCode_details['name']     = $randomString;
			$shortCode_details['type']     = $formType;
			$shortCode_details['assignto'] = $assignee;
			$shortCode_details['crm_type'] = $crmType;
			$shortCode_details['module']   = $module;
			$shortCode_id                  = $shortCodeObj->formShortcodeManager( $shortCode_details );
			$config_fields                 = $shortCodeObj->get_crmfields_by_settings( $crmType, $module );
			foreach ( $config_fields as $field ) {
				$shortCodeObj->insertFormFieldManager( $shortCode_id, $field->field_id, $field->field_mandatory, '1', $field->field_type, $field->field_values, $field->field_sequence, $field->field_label );
			}
			#self::CreateStaticShortCodes('widget');
			wp_redirect("".WP_HELPDESK_INTEGRATION_PLUG_URL."&__module=ManageShortcodes&__action=view");
		}
		return $formType;
	}

 }


class CallSmackHelpDeskDataCaptureObj extends SmackHelpDeskIntegrationHelper
{
	private static $_instance = null;
	public static function getInstance()
	{
		if( !is_object(self::$_instance) )
			self::$_instance = new SmackHelpDeskIntegrationHelper();
		return self::$_instance;
	}
}// CallWPCaptureObj Class Ends
