<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SettingsActions extends SkinnyActions_HelpDeskIntegration {

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
		$data['HelperObj'] = new SmackHelpDeskIntegrationHelper();
		$data['module'] = $data['HelperObj']->Module;
		$data['moduleslug'] = $data['HelperObj']->ModuleSlug;
		$data['activatedplugin'] = $data['HelperObj']->ActivatedPlugin;
		$data['activatedpluginlabel'] = $data['HelperObj']->ActivatedPluginLabel;
		$data['option'] = $data['options'] = "smack_whi_{$data['activatedplugin']}_{$data['moduleslug']}_fields-tmp";
		$crmslug = str_replace( "pro" , "" , $data['activatedplugin'] );
		$crmslug = str_replace( "wp" , "" , $crmslug );
		$data['crm'] = $crmslug;
		$data['action'] = $data['activatedplugin']."Settings";
		if( isset($data['REQUEST']["posted"]) && ($data['REQUEST']["posted"] == "posted") )
		{
			$result = $this->saveSettings( $data );
			if($result['error'] == 1)
			{
				$data['display'] = "<p class='display_error'> ".$result['errormsg']." </p>";
			}
			else
			{
				$data['display'] = "<p class='display_success'> Settings Successfully Saved </p>";
			}
		}
		return $data;
	}

	public function saveSettings( $request )
	{
		update_option("SmackHelpDeskIntegrationFirstTimeWarning" , "false");
		include( WP_HELPDESK_INTEGRATION_DIRECTORY . 'modules/'.$request['action'].'/actions/actions.php');
		$SettingsActionsClass = "Wp{$request['crm']}SettingsActions";
		$SettingsActions = new $SettingsActionsClass();
		$result = $SettingsActions->saveSettings( $request['REQUEST'] );
		return $result;
	}

}
