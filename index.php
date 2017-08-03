<?php
/********************************************************************************************
 * Plugin Name: WP Helpdesk Integration
 * Plugin URI: https://www.smackcoders.com/wp-helpdesk-integration.html
 * Description: Send contact, support and WP User informations captured with simple Webforms & Contact Form 7 in WordPress to Freshdesk, Zoho Support & Zendesk and Vtiger Tickets module. Embed forms in Posts, Pages & Widgets.
 * Version: 1.1
 * Author: smackcoders
 * Author URI: https://www.smackcoders.com/wp-helpdesk-integration.html
 * Contributors: smackcoders
 */
 /* Text Domain: wp-helpdesk-integration
    Domain Path: /languages
*/
 /*******************************************************************************************
 * WP HelpDesk Integration is a Tool for importing CSV for the Wordpress
 * plugin developed by Smackcoders. Copyright (C) 2016 Smackcoders.
 *
 * WP HelpDesk Integration is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Affero General Public License version 3
 * as published by the Free Software Foundation with the addition of the
 * following permission added to Section 15 as permitted in Section 7(a): FOR
 * ANY PART OF THE COVERED WORK IN WHICH THE COPYRIGHT IS OWNED BY WP Ultimate
 * CSV Importer, WP HelpDesk Integration DISCLAIMS THE WARRANTY OF NON
 * INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * WP HelpDesk Integration is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public
 * License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program; if not, see http://www.gnu.org/licenses or write
 * to the Free Software Foundation, Inc., 51 Franklin Street, Fifth Floor,
 * Boston, MA 02110-1301 USA.
 *
 * You can contact Smackcoders at email address info@smackcoders.com.
 *
 * The interactive user interfaces in original and modified versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * WP HelpDesk Integration copyright notice. If the display of the logo is
 * not reasonably feasible for technical reasons, the Appropriate Legal
 * Notices must display the words
 * "Copyright Smackcoders. 2016. All rights reserved".
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
ob_start();
define('WP_HELPDESK_INTEGRATION_URL', 'http://www.smackcoders.com/store/');
define('WP_HELPDESK_INTEGRATION_NAME', 'Helpdesk Integration');
define('WP_HELPDESK_INTEGRATION_SLUG', 'wp-helpdesk-integration');
define('WP_HELPDESK_INTEGRATION_SETTINGS', 'WP Helpdesk Integration');
define('WP_HELPDESK_INTEGRATION_VERSION', '1.1');
define('WP_HELPDESK_INTEGRATION_DIR', WP_PLUGIN_URL . '/' . WP_HELPDESK_INTEGRATION_SLUG . '/');
define('WP_HELPDESK_INTEGRATION_DIRECTORY', plugin_dir_path( __FILE__ ));
define('WP_HELPDESK_INTEGRATION_PLUG_URL',site_url().'/wp-admin/admin.php?page='.WP_HELPDESK_INTEGRATION_SLUG.'/index.php');

add_action('plugins_loaded','SmackHelpDeskIntegrationLoadLanguages');
function SmackHelpDeskIntegrationLoadLanguages(){
	$wp_helpdesk_integration_pro_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
	load_plugin_textdomain( WP_HELPDESK_INTEGRATION_SLUG , false, $wp_helpdesk_integration_pro_lang_dir );
}
if (!class_exists('SkinnyControllerHelpDeskIntegration')) {
	require_once('lib/skinnymvc/controller/SkinnyController.php');
}

require_once("includes/Data.php");
require_once("includes/ContactFormPlugins.php");
$ContactFormPlugins = new SmackHelpDeskUIHelper();
$ActivePlugin = $ContactFormPlugins->getActivePlugin();
$get_debug_option = get_option("smack_whi_{$ActivePlugin}_settings");

if(!isset($get_debug_option['debugmode'])) {
	error_reporting(0);
	ini_set('display_errors', 'Off');
}


if(isset( $_REQUEST['portal_type'] ))
{
	require_once("includes/{$_REQUEST['portal_type']}Functions.php");
}
else
{
	require_once("includes/{$ActivePlugin}Functions.php");
}

require_once('lib/skinnymvc/controller/SkinnyController.php');
require_once('includes/SmackHelpDeskIntegrationHelper.php');
require_once("templates/SmackContactFormGenerator.php");
require_once('includes/Functions.php');

# Activation & Deactivation
register_activation_hook(__FILE__, array('SmackHelpDeskIntegrationHelper', 'activate') );
register_deactivation_hook(__FILE__, array('SmackHelpDeskIntegrationHelper', 'deactivate') );

function SmackHelpDeskIntegrationMenu()
{
	add_menu_page(WP_HELPDESK_INTEGRATION_SETTINGS, WP_HELPDESK_INTEGRATION_NAME, 'manage_options',  __FILE__, array('SmackHelpDeskIntegrationHelper','output_fd_page'), WP_HELPDESK_INTEGRATION_DIR . "/images/WPHelpDeskIntegration.png");
}
add_action ( "admin_menu", "SmackHelpDeskIntegrationMenu" );

function SmackHelpDeskIntegrationInitActions()
{
	if (isset($_REQUEST['page']) && (sanitize_text_field($_REQUEST['page']) == WP_HELPDESK_INTEGRATION_SLUG.'/index.php' || $_REQUEST['page'] == 'page')) {
		wp_enqueue_script('jquery');
		wp_enqueue_style('main-style', plugins_url('css/mainstyle.css', __FILE__));
		wp_enqueue_style('jquery-ui', plugins_url('css/jquery-ui.css', __FILE__));
		wp_enqueue_style('helpdesk-integration-bootstrap-css', plugins_url('css/bootstrap.css', __FILE__));
		wp_enqueue_style('helpdesk-integration-font-awesome-css', plugins_url('css/font-awesome/css/font-awesome.css', __FILE__));
		wp_enqueue_style('magnific-popup.css', plugins_url('css/magnific-popup.css', __FILE__));
		wp_enqueue_style('jquery-confirm.min.css', plugins_url('css/jquery-confirm.min.css', __FILE__));
		wp_register_script('helpdesk-jquery', plugins_url('js/basicaction.js', __FILE__));
		wp_enqueue_script('helpdesk-jquery');
		wp_register_script('bootstrap-modal-min-js', plugins_url('js/bootstrap-modal.min.js', __FILE__));
		wp_enqueue_script('bootstrap-modal-min-js');
		wp_register_script('magnific-popup', plugins_url('js/jquery.magnific-popup.js', __FILE__));
		wp_enqueue_script('magnific-popup');
		wp_register_script('jquery-confirm.min.js', plugins_url('js/jquery-confirm.min.js', __FILE__));
		wp_enqueue_script('jquery-confirm.min.js');
	}
}

function SmackHelpDeskIntegrationFrontEndInitActions()
{
	if(!is_admin())
	{
		global $HelperObj;
		$HelperObj = new SmackHelpDeskIntegrationHelper;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$config = get_option("SmackHelpDeskCaptchaSettings");
		wp_enqueue_script('jquery-ui-datepicker');
		wp_enqueue_style('jquery-ui' , plugins_url('css/jquery-ui.css', __FILE__) );
		wp_enqueue_style('front-end-styles' , plugins_url('css/frontendstyles.css', __FILE__) );
		wp_enqueue_style('datepicker' , plugins_url('css/datepicker.css', __FILE__) );
	
	}
}

add_action('init' , 'SmackHelpDeskIntegrationFrontEndInitActions');
add_action('admin_init', 'SmackHelpDeskIntegrationInitActions');
$check_sync_value = get_option( 'SmackHelpDeskUserSyncOption' );

if( $check_sync_value == "On" ){
	add_action( 'profile_update', array( 'SmackHelpDeskUserDataCapture' , 'capture_updating_users' ) );
	add_action( 'user_register', array( 'SmackHelpDeskUserDataCapture' , 'capture_registering_users' ) );
}
$active_plugins = get_option( "active_plugins" );

function smack_whi_TFA_auth_save()
{
	$TFA_Authtoken_value = sanitize_text_field( $_REQUEST['authtoken']);
	update_option("SmackHelpDeskTFA_zoho_authtoken" , $TFA_Authtoken_value );
	print_r( $TFA_Authtoken_value);
	die;
}
add_action('wp_ajax_smack_whi_TFA_auth_save' , 'smack_whi_TFA_auth_save' );

function SmackHelpDeskSaveConfiguration( )
{
	require_once( 'modules/Settings/actions/SmackHelpDeskSaveConfiguration.php' );
	die;
}
add_action( 'wp_ajax_SmackHelpDeskSaveConfiguration' , 'SmackHelpDeskSaveConfiguration' );

function SmackHelpDeskUserAutoSyncSettings()
{
	require_once( 'templates/save-sync-value.php' );
	die;
}
add_action( 'wp_ajax_SmackHelpDeskUserAutoSyncSettings' , 'SmackHelpDeskUserAutoSyncSettings' );

function SmackHelpDeskChangeActivePortal()
{
	require_once("templates/plugin-select.php");
	die;
}


function SmackHelpDeskCustomMenuOrder( $menu_order ) {
	return array(
		'index.php',
		'edit.php',
		'edit.php?post_type=page',
		'upload.php',
		WP_HELPDESK_INTEGRATION_SLUG . '/index.php',
		'wp-leads-builder-any-crm-pro/index.php',
	);
}
add_filter( 'custom_menu_order', '__return_true' );
add_filter( 'menu_order', 'SmackHelpDeskCustomMenuOrder' );

add_action('wp_ajax_SmackHelpDeskChangeActivePortal', 'SmackHelpDeskChangeActivePortal');

SmackHelpDeskIntegrationHelper::checkVersion();


