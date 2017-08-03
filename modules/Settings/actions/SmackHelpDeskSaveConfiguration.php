<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SmackHelpDeskSaveConfigurationActions {

    public function __construct()
    {
    }
    public function saveConfigAjax()
    {
        $data['REQUEST'] = $_REQUEST['posted_data'] ;
        $data['HelperObj'] = new SmackHelpDeskIntegrationHelper();
        $data['module'] = $data['HelperObj']->Module;
        $data['moduleslug'] = $data['HelperObj']->ModuleSlug;
        $data['activatedplugin'] = $data['HelperObj']->ActivatedPlugin;
        $data['activatedpluginlabel'] = $data['HelperObj']->ActivatedPluginLabel;
        $data['option'] = $data['options'] = "smack_{$data['activatedplugin']}_{$data['moduleslug']}_fields-tmp";
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
	    else if( $result['error'] == 11 )
	    {
		$data['display'] = "<p class='display_error'>". $result['errormsg']." </p>";
	    }
            else
            {
                $data['display'] = "<p class='display_success'> Settings Successfully Saved </p>";
            }

            $final_result['display'] = $data['display'];
            $final_result['error'] = $result['error'];
            $final_result = json_encode( $final_result );
            print_r( $final_result);
            die;
        }
    }

    public function saveSettings( $request )
    {
        update_option("SmackHelpDeskIntegrationFirstTimeWarning" , "false");
        include( 'SmackHelpDeskSaveConfigurationHelper.php');
        $saveCall = new SmackHelpDeskSaveConfiguration();
        $result = $saveCall->CheckPortalType( $request );
        return $result;
    }
}
$saveObj = new SmackHelpDeskSaveConfigurationActions();
$call = $saveObj->saveConfigAjax();
