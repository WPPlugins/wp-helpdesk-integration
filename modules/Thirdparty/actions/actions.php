<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ThirdpartyActions extends SkinnyActions_HelpDeskIntegration {

	public function __construct()
	{
	}

	/**
	 * The actions index method
	 * @param array $request
	 * @return array
	 */
	public function executeIndex($request)
	{
		// return an array of name value pairs to send data to the template
		$data = array();
		return $data;
	}

	public function executeView($request)
	{
		// return an array of name value pairs to send data to the template
		$data = array();
		foreach( $request as $key => $REQUESTS )
		{
			foreach( $REQUESTS as $REQUESTS_KEY => $REQUESTS_VALUE )
			{
				$data['REQUEST'][$REQUESTS_KEY] = $REQUESTS_VALUE;
			}
		}
		$data['activatedplugin'] = 'Thirdparty';
		$data['action'] = $data['activatedplugin']."Settings";
		$data['HelperObj'] = new SmackHelpDeskIntegrationHelper();
		$data['module'] = $data['HelperObj']->Module;
		$data['moduleslug'] = $data['HelperObj']->ModuleSlug;
		$data['activatedpluginlabel'] = $data['HelperObj']->ActivatedPluginLabel;
		$data['plugin_dir']= WP_HELPDESK_INTEGRATION_DIRECTORY;
		$data['plugins_url'] = WP_HELPDESK_INTEGRATION_DIR;
		$data['siteurl'] = site_url();
		if( isset($data['REQUEST']["posted"]) && ($data['REQUEST']["posted"] == "posted") )
		{
			$result = $this->saveSettings( $data );
			if($result['error'] == 1)
			{
				$data['display'] = "<p class='display_failure'> Please Fill all details </p>";
			}
			else
			{
				$data['display'] = "<p class='display_success'> Settings Successfully Saved </p>";
			}
		}
		return $data;
	}

	public function saveSettings( $data )
	{
		update_option("SmackHelpDeskIntegrationFirstTimeWarning" , "false");
		$request['action'] = 'ThirdParty';
		$HelperObj = $data['HelperObj'];
		$module = $HelperObj->Module;
		$moduleslug = $HelperObj->ModuleSlug;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
		$fieldNames = array(
			'smack_recaptcha' => __('Recaptcha', WP_HELPDESK_INTEGRATION_SLUG ),
			'email'           => __( 'Email', WP_HELPDESK_INTEGRATION_SLUG) ,
			'emailcondition'  => __( 'Email Condition', WP_HELPDESK_INTEGRATION_SLUG ),
			'debugmode'       => __( 'Debug Mode', WP_HELPDESK_INTEGRATION_SLUG )
		);
		foreach ($fieldNames as $field=>$value){
			if(isset($data['REQUEST'][$field]))
			{
				$config[$field] = $data["REQUEST"][$field];
			}
			else
			{
				$config[$field] = "";
			}
		}

			if( $config['smack_recaptcha'] == 'yes' ) {
				$result['error'] = "1";
			}
			else
			{
				$result['error'] = "0";
			}
			$result['error'] = "0";
		update_option("SmackHelpDeskCaptchaSettings", $config );
		return $result ;
	}
}
