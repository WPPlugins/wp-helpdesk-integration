<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class CaptchaActions extends SkinnyActions_HelpDeskIntegration {

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
                $data = array();
                foreach( $request as $key => $REQUESTS )
                {
                        foreach( $REQUESTS as $REQUESTS_KEY => $REQUESTS_VALUE )
                        {
                                $data['REQUEST'][$REQUESTS_KEY] = $REQUESTS_VALUE;
                        }
                }
                $data['ok'] = "Captcha";
                $data['HelperObj'] = new SmackHelpDeskIntegrationHelper();
                $data['module'] = $data['HelperObj']->Module;
                $data['moduleslug'] = $data['HelperObj']->ModuleSlug;
                $data['activatedplugin'] = $data['HelperObj']->ActivatedPlugin;
                $data['activatedpluginlabel'] = $data['HelperObj']->ActivatedPluginLabel;
                $data['plugin_dir']= WP_HELPDESK_INTEGRATION_DIRECTORY;
                $data['plugins_url'] = WP_HELPDESK_INTEGRATION_DIR;
                $data['siteurl'] = site_url();
                if( isset($data['REQUEST']["smack-{$data['activatedplugin']}-captcha-form"]) )
                {
                        $this->saveSettingArray($data);
                }
                return $data;
        }
        public function saveSettingArray($data)
        {
                $HelperObj = $data['HelperObj'];
                $module = $HelperObj->Module;
                $moduleslug = $HelperObj->ModuleSlug;
                $activatedplugin = $HelperObj->ActivatedPlugin;
                $activatedpluginlabel = $HelperObj->ActivatedPluginLabel;

                $fieldNames = array(
                    'smack_recaptcha' => __('Recaptcha', WP_HELPDESK_INTEGRATION_SLUG ),
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
                update_option("SmackHelpDeskCaptchaSettings", $config);
        }
}
