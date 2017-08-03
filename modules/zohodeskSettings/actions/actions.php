<?php
/******************************
 * filename:    modules/zohodeskSettings/actions/actions.php
 * description:
 */

if ( ! defined( 'ABSPATH' ) ) exit;

class ZohodeskSettingsActions extends SkinnyActions_HelpDeskIntegration {

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
