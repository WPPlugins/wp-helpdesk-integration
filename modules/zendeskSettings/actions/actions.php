<?php
/******************************
 * filename:    modules/zendeskSettings/actions/actions.php
 * description:
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class ZendeskSettingsActions extends SkinnyActions_HelpDeskIntegration {

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

}
