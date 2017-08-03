<?php
/******************************
 * filename:    modules/EcommerceSettings/actions/actions.php
 * description:
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class EcommerceSettingsActions extends SkinnyActions_HelpDeskIntegration {

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
                $data['plugin_dir']= WP_HELPDESK_INTEGRATION_DIRECTORY;
                $data['plugins_url'] = WP_HELPDESK_INTEGRATION_DIR;
                $data['siteurl'] = site_url();
                if( isset($data['REQUEST']["smack-{$data['activatedplugin']}-user-capture-settings-form"]) )
                {
                        $this->saveSettingArray($data);
                }
                return $data;
        }
}
