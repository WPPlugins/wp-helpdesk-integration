<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once( plugin_dir_path( __FILE__ ) . '../../../lib/SmackZohoSupportApi.php' );

class SmackHelpDeskSaveConfiguration
{

    public function CheckPortalType( $config )
    {
        $whi_active_support_portal = $config['action'];
        switch( $whi_active_support_portal )
        {
	        case 'freshdeskSettings':
		        $save_result = $this->freshdeskSettings($config);
		        return $save_result;
		        break;
	        case 'zendeskSettings':
		        $save_result = $this->zendeskSettings($config);
		        return $save_result;
		        break;
	        case 'zohodeskSettings':
		        $save_result = $this->zohodeskSettings($config);
		        return $save_result;
		        break;
		case 'VtigerticketsSettings':
                        $save_result = $this->VtigerticketsSettings($config);
                        return $save_result;
                        break;
        }
    }

	public function zohodeskSettings( $configData )
	{
		$zohodesk_config_array = $configData['REQUEST'];
		$fieldNames = array(
			'username' => __('Zoho Username' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'password' => __('Zoho Password' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'portalname' => __('Zoho Portalname' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'departmentname' => __('Zoho Departmentname' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'TFA_check'      => __('Two Factor Authentication' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'smack_email' => __('Smack Email' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'email' => __('Email id' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'emailcondition' => __('Emailcondition' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'debugmode' => __('Debug Mode' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
		);

		foreach ($fieldNames as $field=>$value){
			if(isset($zohodesk_config_array[$field]))
			{
				$config[$field] = $zohodesk_config_array[$field];
			}
		}
		$FunctionsObj = new SmackHelpDeskIntegrations( );
		$jsonData = $FunctionsObj->getAuthenticationKey( $zohodesk_config_array['username'] , $zohodesk_config_array['password'] , $zohodesk_config_array['portalname'] , $zohodesk_config_array['departmentname'] );
		if($jsonData['result'] == "TRUE")
		{
			$successresult = "<p class='display_success' style='color: green;'> Settings Saved </p>";
			$result['error'] = 0;
			$result['success'] = $successresult;
			$config['authtoken'] = $jsonData['authToken'];
			$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
			$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
			update_option("smack_whi_{$activateplugin}_settings", $config);
		}
		else if( $jsonData['result'] == "FALSE" && $jsonData['cause'] == 'WEB_LOGIN_REQUIRED'){
			$TFA_get_authtoken = get_option('SmackHelpDeskTFA_zoho_authtoken' );
			$uri = "https://support.zoho.com/api/xml/Info/getModules?"; // Check Auth token present in Zoho //ONLY FOR TFA CHECK
			$postContent = "scope=supportapi";
			$postContent .= "&authtoken={$TFA_get_authtoken}";
			$ch = curl_init($uri );
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postContent);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			$curl_result = curl_exec($ch);
			$xml = simplexml_load_string($curl_result);
			$json = json_encode($xml);
			$result_array = json_decode($json,TRUE);
			curl_close($ch);
			$TFA_result_array = $result_array['error'];
			if( $TFA_result_array['code'] = "4834" && $TFA_result_array['message'] == "Invalid Ticket Id" )
			{
				$successresult = "<p class='display_failure' style='color:red;' >TFA is enabled in ZOHO Support. Please Enter Valid Authtoken Below. <a target='_blank' href='https://support.zoho.com/support/ShowSetup.do?tab=developerSpace&subTab=api'>To Generate Authtoken</a></p>";
				$result['error'] = 11;
				$result['errormsg'] = $successresult;
			}
			else
			{

				$successresult = "<p class='display_success' style='color: green;'> Settings Saved </p>";
				$result['error'] = 0;
				$result['success'] = $successresult;
			}
			$config['authtoken'] = get_option( "SmackHelpDeskTFA_zoho_authtoken" );
			$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
			$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
			update_option("smack_whi_{$activateplugin}_settings", $config);
		}
		else
		{
			if($jsonData['cause'] == 'EXCEEDED_MAXIMUM_ALLOWED_AUTHTOKENS') {
				$zohocrmerror = "<p style='color:red; '>Please log in to <a target='_blank' href='https://accounts.zoho.com'>https://accounts.Zoho.com</a> - Click Active Authtoken - Remove unwanted Authtoken, so that you could generate new authtoken..</p>";
			}
			else{
				$zohocrmerror = "<p class='display_failure' style='color:red;' >Please Verify your Zohodesk Credentials.</p>";
			}
			$result['error'] = 1;
			$result['errormsg'] = $zohocrmerror ;
			$result['success'] = 0;
		}
		return $result;
	}



	public function zendeskSettings( $configData ) {
		$zendesk_config_array = $configData['REQUEST'];
		$fieldNames = array(
			'username' => __('Zendesk Username' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'password' => __('Zendesk Password' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'domain_url' => __('Zendesk Domain URL' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'smack_email' => __('Smack Email' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'email' => __('Email id' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'emailcondition' => __('Emailcondition' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'debugmode' => __('Debug Mode' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
		);

		foreach ($fieldNames as $field=>$value){
			if(isset($zendesk_config_array[$field]))
			{
				$config[$field] = trim($zendesk_config_array[$field]);
			}
		}
		$FunctionsObj = new SmackHelpDeskIntegrations();
		$testlogin_result = $FunctionsObj->testLogin( $zendesk_config_array['domain_url'], $zendesk_config_array['username'], $zendesk_config_array['password'] );
		$check_is_valid_login = json_decode($testlogin_result);
		if(!empty($check_is_valid_login) && isset($check_is_valid_login->users) && $check_is_valid_login->users[0]->id) {
			$successresult = "<p class='display_success' style='color: green;'> Settings Saved </p>";
			$result['error'] = 0;
			$result['success'] = $successresult;
			$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
			$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
			#$config['auth_token'] = $check_is_valid_login->auth_token;
			update_option("smack_whi_{$activateplugin}_settings", $config);
		}
		else
		{
			$zendesk_crm_config_error = "<p class='display_failure' style='color:red;'>Please Verify your Zendesk Credentials.</p>";

			$result['error'] = 1;
			$result['errormsg'] = $zendesk_crm_config_error;
			$result['success'] = 0;
		}
		return $result;
	}
public function VtigerticketsSettings( $configData )
    {
        $HelpDesk_config_array = $configData['REQUEST'];
        $fieldNames = array(

            'domain_url' => __('Vtigertickets Domain Url' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
            'username' => __('Vtigertickets User Name' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
            'accesskey' => __('Vtigertickets Access Key' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
            'smack_email' => __('Smack Email' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
            'email' => __('Email id' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
            'emailcondition' => __('Emailcondition' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
            'debugmode' => __('Debug Mode' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
        );

        foreach ($fieldNames as $field=>$value){
            if(isset($HelpDesk_config_array[$field]))
            {
                $config[$field] = trim($HelpDesk_config_array[$field]);
            }
        }
        $FunctionsObj = new SmackHelpDeskIntegrations();

        $testlogin_result = $FunctionsObj->testLogin( $HelpDesk_config_array['domain_url'] , $HelpDesk_config_array['username'] , $HelpDesk_config_array['accesskey'] );

        if($testlogin_result == 1)
        {
            $successresult = "<p  class='display_success' style='color: green;'> Settings Saved </p>";
            $result['error'] = 0;
            $result['success'] = $successresult;
            $SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
            $activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
            update_option("smack_whi_{$activateplugin}_settings", $config);
        }
        else
        {
            $HelpDeskcrmerror = "<p  class='display_failure' style='color:red;' >Please Verify your HelpDesk Credentials.</p>";

            $result['error'] = 1;
            $result['errormsg'] = $HelpDeskcrmerror ;
            $result['success'] = 0;
	        }
        return $result;
    }

	public function freshdeskSettings( $configData ) {
		$freshdesk_config_array = $configData['REQUEST'];
		$fieldNames = array(
			'username' => __('Freshdesk Username' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'password' => __('Freshdesk Password' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'domain_url' => __('Freshdesk Domain URL' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'smack_email' => __('Smack Email' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'email' => __('Email id' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'emailcondition' => __('Emailcondition' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
			'debugmode' => __('Debug Mode' , WP_HELPDESK_INTEGRATION_PLUG_URL ),
		);

		foreach ($fieldNames as $field=>$value){
			if(isset($freshdesk_config_array[$field]))
			{
				$config[$field] = trim($freshdesk_config_array[$field]);
			}
		}
		$FunctionsObj = new SmackHelpDeskIntegrations();
		$testlogin_result = $FunctionsObj->testLogin( $freshdesk_config_array['domain_url'], $freshdesk_config_array['username'], $freshdesk_config_array['password'] );
		$check_is_valid_login = json_decode($testlogin_result);
		if(is_array($check_is_valid_login)) {
			$successresult = "<p  class='display_success' style='color: green;'> Settings Saved </p>";
			$result['error'] = 0;
			$result['success'] = $successresult;
			$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
			$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
			#$config['auth_token'] = $check_is_valid_login->auth_token;
			update_option("smack_whi_{$activateplugin}_settings", $config);
		}
		else
		{
			$freshdesk_crm_config_error = "<p class='display_failure' style='color:red;'>Please Verify your Freshdesk Credentials.</p>";

			$result['error'] = 1;
			$result['errormsg'] = $freshdesk_crm_config_error;
			$result['success'] = 0;
		}
		return $result;
	}

	
}
