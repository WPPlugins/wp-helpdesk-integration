<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

 if(!isset($_SESSION)) 
    { 
        session_start(); 
    }
if(isset($_SESSION['generated_forms']))
{
	unset($_SESSION['generated_forms']);
}
global $HelperObj;
$HelperObj = new SmackHelpDeskIntegrationHelper;
$activatedplugin = $HelperObj->ActivatedPlugin;
$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
add_filter('widget_text', 'do_shortcode');
add_shortcode( $activatedplugin."-web-form" ,'SmackHelpDeskGenerateWebForm');
global $migrationmap;
$migrationmap = get_option("smack_oldversion_shortcodes");
if( is_array($migrationmap) )
{
	foreach( $migrationmap as $key => $value )
	{
		add_shortcode($key , "SmackHelpDeskFieldMigration");
	}
}

function SmackHelpDeskFieldMigration($attr, $content , $tag)
{
	global $migrationmap;
	$migrate = $migrationmap[$tag];
	foreach ($migrate as $key => $value)
	{
		if( !isset($attr['name']) )
		{
			$name = $value['newrandomname'];
		}
		else
		{

			if($value['oldrandomname'] == $attr['name'])
			{
				$name = $value['newrandomname'];
			}
		}
	}
	return SmackHelpDeskGenerateWebForm(array('name' => $name));
}

global $plugin_dir;
$plugin_dir = WP_HELPDESK_INTEGRATION_DIRECTORY;
$plugin_url = WP_HELPDESK_INTEGRATION_DIR;
$onAction = 'onCreate';
$siteurl = site_url();
global $config;
global $post;
$config = get_option("smack_whi_{$activatedplugin}_settings");
$post = array();
global $module_options, $module , $isWidget, $assignedto, $check_duplicate, $update_record;

function SmackHelpDeskGenerateWebForm($attr , $thirdparty)
{
	global $HelperObj;
	global $module_options, $module, $isWidget, $assignedto, $check_duplicate, $update_record,$formattr, $attrname;
	$module_options = 'Leads';
	$newform = new SmackHelpDeskDataCapture();
	$newshortcode = $newform->formfields_settings( $attr['name'] );
	$FormSettings = $newform->getFormSettings( $attr['name'] );
	$formattr = array_merge( json_decode( json_encode($FormSettings) , true) , $newshortcode );
	$attrname = $attr['name'];
	$config_fields = $newshortcode['fields'];
	$module = $FormSettings->module;
	$module_options = $module;
	if(isset($shortcodes['update_record']))
	{
		$update_record = $shortcodes['update_record'];
	}

	if($FormSettings->form_type == "post")
	{
		return SmackHelpDeskNormalWebForm( $module, $config_fields, $module_options , "post" , $thirdparty);
	}
	else
	{
		return SmackHelpDeskWidgetWebForm($module, $config_fields, $module_options , "widget" , $thirdparty);
	}

}

function SmackHelpDeskCaptureWebFormData( $formtype )
{
	global $HelperObj;
	global $plugin_dir;
	global $plugin_url;
	global $config;
	global $post;
	global $formattr;
	global $attrname;
	global $module_options, $module , $isWidget, $assignedto, $check_duplicate, $update_record;
	$plugin_dir=WP_HELPDESK_INTEGRATION_DIRECTORY;
	$globalvariables = Array( 'plugin_dir' => $plugin_dir , 'plugin_url' => $plugin_url , 'post' => $post , 'module_options' => $module_options , 'module' => $module , 'isWidget' => $isWidget , 'assignedto' => $assignedto , 'check_duplicate' => $check_duplicate , 'update_record' => $update_record , 'HelperObj' => $HelperObj , 'formattr' => $formattr , 'attrname' => $attrname);
	$CapturingProcessClass = new SmackHelpDeskUserDataCapture();
	$data = $CapturingProcessClass->CaptureFormFields($globalvariables);
	$smacklog='';
	$HelperObj = new SmackHelpDeskIntegrationHelper();
	$module = $HelperObj->Module;
	$moduleslug = $HelperObj->ModuleSlug;
	$activatedplugin = $HelperObj->ActivatedPlugin;
	$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
	$pageurl = SmackHelpDeskGetCurrentPageURL();
	$newform = new SmackHelpDeskDataCapture();
	$newshortcode = $newform->formfields_settings( $attrname );
	$FormSettings = $newform->getFormSettings( $attrname );
	$config_fields = array_merge( json_decode( json_encode($FormSettings) , true) , $newshortcode );
	$submitcontactform = '';
	if(isset($data) && $data) {
		if(isset($_REQUEST['submitcontactform']))
		{
			$form_no = sanitize_text_field( $_REQUEST['formnumber'] );
			$submitcontactform = "smackLogMsg{$form_no}";
		}
		if(isset($_REQUEST['submitcontactformwidget']))
		{
			$submitcontactform = "widgetSmackLogMsg{$_REQUEST['formnumber']}";
		}
		$successfulAttemptsOption['total'] =  $config_fields['submit_count'];
		$successfulAttemptsOption['success'] = $config_fields['success_count'];
		$total=0;
		$success=0;
		if(!isset($successfulAttemptsOption['total']) && ($successfulAttemptsOption['success'] ))
		{
			$successfulAttemptsOption['total'] = 0;
			$successfulAttemptsOption['success'] = 0;
		}
		else{
			$total= $successfulAttemptsOption['total'];
			$success= $successfulAttemptsOption['success'];
		}
		$total++;
		$contenttype = "\n";
		foreach($config_fields['fields'] as $key => $value)
		{
			$config_field_label[$value['name']] = $value['display_label'];
		}
		
		foreach( $post as $key => $value )
		{
			if(($key != 'formnumber') && ($key != 'submitcontactformwidget') && ($key != 'moduleName') && ($key != "submit" ) && ( $key != "") && ($key != 'submitcontactform') && ($key != "g-recaptcha-response") )
				if(isset($config_field_label[$key]))
				{
					if(is_array($value))
						$value = $value[0];
					$contenttype.= "{$config_field_label[$key]} : $value"."\n";
				}
				else
				{
					if(is_array($value))
						$value = $value[0];
					$contenttype.= "$key : $value"."\n";
				}
		}
		$config = get_option("SmackHelpDeskCaptchaSettings");
		if(preg_match("/{$config_fields['module']} entry is added./",$data)) {
			$success++;
			$successfulAttemptsOption['total'] = $total;
			$successfulAttemptsOption['success'] = $success;
			if( isset($config['emailcondition']) && ( ($config['emailcondition'] == 'success') || ($config['emailcondition'] == 'both') ) )
			{
				$sendmail = SmackHelpDeskSendMail( $config,$activatedplugin,$formtype, $pageurl, "Success" , $contenttype );
			}
			$successfulAttemptsOption['success'] = $success;
			$successfulAttemptsOption['total'] = $total;
			$total_config_fields[$attrname] = $config_fields;
			$newform->updateFormSubmitStatuses( $successfulAttemptsOption , $attrname );
			if( isset($config_fields['is_redirection']) && ($config_fields['is_redirection'] == "1") && isset($config_fields['url_redirection']) && ( $config_fields['url_redirection'] !== "" ) && is_numeric($config_fields['url_redirection']) )
			{
				wp_redirect(get_permalink($config_fields['url_redirection']));
			}
			$smacklog.="<script>";
			if(isset( $config_fields['success_message'] ) && ($config_fields['success_message'] != "") )
			{
				$smacklog.="document.getElementById('{$submitcontactform}').innerHTML=\"<p class='smack_logmsg' style='color:green;'>{$config_fields['success_message']}</p>\"";
			}
			else
			{
				if(isset($submitcontactform))
				$smacklog.="document.getElementById('{$submitcontactform}').innerHTML=\"<p class='smack_logmsg' style='color:green;'>Thank you for submitting</p>\"";
			}
			$smacklog.="</script>";
			return $smacklog;
		}
		else
		{
			$successfulAttemptsOption['total'] = $total;
			$successfulAttemptsOption['success'] = $success;
			if( isset($config['emailcondition']) )
			{
				$sendmail = SmackHelpDeskSendMail( $config,$activatedplugin,$formtype, $pageurl, "Failure" , $contenttype );
			}
			$config_fields['success'] = $success;
			$config_fields['total'] = $total;
			$total_config_fields[$attrname] = $config_fields;
			update_option( "SmackHelpDeskShortCodeFields", $total_config_fields);
			$smacklog.="<script>";
			if( isset( $config_fields['error_message'] ) && ($config_fields['error_message'] != "") )
			{
				$smacklog.="document.getElementById('{$submitcontactform}').innerHTML=\"<p class='smack_logmsg' style='color:red;'>{$config_fields['error_message']}</p>\"";
			}
			else
			{
				$smacklog.="document.getElementById('{$submitcontactform}').innerHTML=\"<p class='smack_logmsg' style='color:red;'>Submitting Failed</p>\"";
			}
			$smacklog.="</script>";
			$successfulAttemptsOption['total'] = $total;
			$successfulAttemptsOption['success'] = $success;
			$failure = $total -  $success;
			$newform->updateFormSubmitStatuses( $successfulAttemptsOption , $attrname );
			return $smacklog;
		}
	}
}

function SmackHelpDeskNormalWebForm($module, $config_fields, $module_options , $formtype , $thirdparty)
{
	global $plugin_dir;
	global $plugin_url;
	$siteurl=site_url();
	global $config;
	global $post;
	global $formattr;
	$HelperObj = new SmackHelpDeskIntegrationHelper();
	$captcha_error = false;
	$activatedplugin = $HelperObj->ActivatedPlugin;
	$script='';
	$post = array();
	$post=$_POST;
	if( !isset( $_SESSION["generated_forms"] ) )
	{
		$_SESSION["generated_forms"] = 1;
	}
	else
	{
		$_SESSION["generated_forms"]++;
	}

	if(isset($_POST['submitcontactform']) && (sanitize_text_field($_POST['formnumber']) == sanitize_text_field($_SESSION['generated_forms'])) )
	{
		$count_error=0;
		for($i=0; $i<count($config_fields); $i++)
		{
			if(array_key_exists($config_fields[$i]['name'],$_POST))
			{

				if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "" )
				{

					$count_error++;
				}
				elseif($config_fields[$i]['type']['name'] == 'integer' && !preg_match('/^[\d]*$/', sanitize_text_field($_POST[$config_fields[$i]['name']])) && (sanitize_text_field($_POST[$config_fields[$i]['name']]) != ""))
				{
					$count_error++;
				}
				elseif($config_fields[$i]['type']['name'] == 'double'  && !preg_match('/^([\d]{1,8}.?[\d]{1,2})?$/', sanitize_text_field($_POST[$config_fields[$i]['name']])) && ($_POST[$config_fields[$i]['name']] != ""))
				{
					$count_error++;
				}
				elseif($config_fields[$i]['type']['name'] == 'currency'  && !preg_match('/^([\d]{1,8}.?[\d]{1,2})?$/', sanitize_text_field($_POST[$config_fields[$i]['name']])) && (sanitize_text_field($_POST[$config_fields[$i]['name']] != "")))
				{
					$count_error++;
				}
				elseif($config_fields[$i]['type']['name'] == 'email' && (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',sanitize_text_field($_POST[$config_fields[$i]['name']])) && (sanitize_text_field($_POST[$config_fields[$i]['name']]) != "")))
				{
					$count_error++;
				}
				elseif($config_fields[$i]['type']['name'] == 'url' && (!preg_match('/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-=#]+\.([a-zA-Z0-9\.\/\?\:@\-=#])*/',sanitize_text_field($_POST[$config_fields[$i]['name']])) && (sanitize_text_field($_POST[$config_fields[$i]['name']]) != "")))
				{
					if($_POST[$config_fields[$i]['name']] == "")
					{
					}
					else
					{
						$count_error++;
					}

				}
				elseif($config_fields[$i]['type']['name'] == 'multipicklist' )
				{
					$concat = "";
					for( $index=0; $index<count($_POST[$config_fields[$i]['name']]); $index++)
					{
						$concat.=$_POST[$config_fields[$i]['name']][$index]." |##| ";

					}
					$concat=substr($concat,0,-6);
					$post[$config_fields[$i]['name']]=$concat;

				}
				elseif($config_fields[$i]['type']['name'] == 'phone' && !preg_match('/^[2-9]{1}[0-9]{2}-[0-9]{3}-[0-9]{4}$/', $_POST[$config_fields[$i]['name']]))
				{

				}
			}
		}
		$captcha_config = get_option("SmackHelpDeskCaptchaSettings");
		$save_field_config = $formattr;
		if(isset($captcha_config['smack_recaptcha'])) {
		if(($captcha_config['smack_recaptcha']=='yes') && (isset($save_field_config['google_captcha']) && (sanitize_text_field($save_field_config['google_captcha']) == 1 )))
		{
			$privatekey = $captcha_config['recaptcha_private_key'];
			if( !isset( $_REQUEST['g-recaptcha-response'] ) || ( sanitize_text_field($_REQUEST['g-recaptcha-response']) == NULL ) || ( sanitize_text_field($_REQUEST['g-recaptcha-response']) == "" ) ) {
				$captcha_error = true;
			} else {
				$botcheck_url = "https://www.google.com/recaptcha/api/siteverify?secret=$privatekey&response={$_REQUEST['g-recaptcha-response']}";
				$ch  = curl_init ($botcheck_url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$google_bot_check_result = curl_exec ($ch);
				$decoded_result = json_decode( $google_bot_check_result );
				if( $decoded_result->success ) {
					$captcha_error = false;
				}
				else {
					$count_error++;
					$captcha_error = true;
				}
			}
		}}
	}

	#error_reporting(E_ALL); ini_set('display_errors', 'On');
	$content = "<form id='contactform{$_SESSION["generated_forms"]}' name='contactform{$_SESSION["generated_forms"]}' method='post'>";
	$content.= "<table>";
	$content.= "<div id='smackLogMsg{$_SESSION["generated_forms"]}'></div>";
	$content1="";
	$count_selected=0;
	for($i=0; $i<count($config_fields); $i++) {
		$content2 = "";
		$fieldtype = $config_fields[$i]['type']['name'];
		$config_field_name = $config_fields[$i]['name'];
		if($config_fields[$i]['name'] == 'name')
			$config_field_name = 'smack_lb_' . $config_fields[$i]['name'];
		if( $config_fields[$i]['publish']==1) {
			if($config_fields[$i]['wp_mandatory']==1) {
				$content1.="<tr><td>".$config_fields[$i]['display_label']." *</td>";
				$M=' mandatory';
			} else {
				$content1.="<tr><td>".$config_fields[$i]['display_label']."</td>";
				$M='';
			}
			if($fieldtype == "string") {
				$content1.="<td><input type='text' class='string{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_field_name]) && (sanitize_text_field($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';
				$content1 .= "'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if(isset($_POST['submitcontactform']) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) ) {
					if(isset($_POST[$config_field_name])&& $config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_field_name]) == "" ) {
						$content1 .="This field is mandatory";
					}
				}
				$content1 .="</span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == "text") {
				$content1.="<td><textarea class='textarea{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}'></textarea><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if(isset($_POST['submitcontactform']) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) ) {
					if(isset($_POST[$config_field_name])&& $config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_field_name]) == "" ) {
						$content1 .="This field is mandatory";
					}
				}
				$content1 .="</span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == 'radioenum') {
				$content1 .= "<td>";
				$picklist_count = count($config_fields[$i]['type']['picklistValues']);
				for($j=0 ; $j<$picklist_count ; $j++) {
					$content2.="<input type='radio' name='{$config_field_name}' value='{$config_fields[$i]['type']['picklistValues'][$j]['label']}'>{$config_fields[$i]['type']['picklistValues'][$j]['value']}";
				}
				$content1.=$content2;
				$content1.="<script>document.getElementById('{$config_fields[$i]['name']}').value='{$_POST[$config_field_name]}'</script>";
				$content1 .= "<br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span>";
				$content1 .= "</td>";
				$count_selected++;
			} elseif($fieldtype == 'multipicklist') {
				$picklist_count = count($config_fields[$i]['type']['picklistValues']);
				$content1.="<td><select class='multipicklist{$M}' name='{$config_field_name}[]' multiple='multiple' id='{$module_options}_{$config_fields[$i]['name']}' >";
				for($j=0 ; $j<$picklist_count ; $j++) {
					$content2.="<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['value']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
				}
				$content1.=$content2;
				$content1.="</select><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == 'picklist') {
				$picklist_count = count($config_fields[$i]['type']['picklistValues']);
				$content1.="<td><select class='picklist{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}'  value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';

				$content1.="'>";
				for($j=0 ; $j<$picklist_count ; $j++) {
					if($activatedplugin == 'freshsales') {
						$content2 .= "<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['id']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
					} else {
						$content2 .= "<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['value']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
					}
				}
				$content1.=$content2;
				$content1.="</select><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></tr>";
				$count_selected++;
			} elseif($fieldtype == 'nested') {
				$picklist_count = count($config_fields[$i]['type']['picklistValues']);
				$content1.="<td><select class='picklist{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}'  value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';

				$content1.="'>";
				for($j=0 ; $j<$picklist_count ; $j++) {
					$content2 .= "<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['label']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
				}
				$content1.=$content2;
				$content1.="</select><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></tr>";
				$count_selected++;
			} elseif($fieldtype == 'integer') {
				$content1.="<td><input type='text' class='integer{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';
				$content1.="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_field_name]) == "" ) {
					$content1 .="This field is mandatory";
				} elseif( isset($_POST[$config_field_name]) && sanitize_text_field($config_fields[$i]['type']['name']) == 'integer' && !preg_match('/^[\d]*$/', sanitize_text_field($_POST[$config_field_name])) && (sanitize_text_field($_POST[$config_field_name]) != "")) {
					$content1 .="This field is integer";
				}
				$content1 .= "</span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == 'double') {
				$content1.="<td><input type='text' class='double{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='{".sanitize_text_field($_POST[$config_field_name])."}'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == 'currency') {
				$content1.="<td><input type='text' class='currency{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';
				$content1.="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_field_name]) == "" ) {
					$content1 .="This field is mandatory";
				} elseif(  isset($_POST[$config_field_name]) && $config_fields[$i]['type']['name'] == 'currency'  && !preg_match('/^([\d]{1,8}.?[\d]{1,2})?$/', sanitize_text_field($_POST[$config_field_name]))&& (sanitize_text_field($_POST[$config_field_name]) != "")) {
					$content1 .="This field is integer";
				}
				$content1 .= "</span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == 'email') {
				$content1.="<td><input type='text' class='email{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';

				$content1.="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";

				if($config_fields[$i]['wp_mandatory'] == 1 && isset($_POST[$config_field_name]) && sanitize_text_field($_POST[$config_field_name]) == "" ) {
					$content1 .="This field is mandatory";
				} elseif( isset($_POST[$config_field_name]) && $config_fields[$i]['type']['name'] == 'email' && (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$/',sanitize_text_field($_POST[$config_field_name])) && (sanitize_text_field($_POST[$config_field_name]) != ""))) {
					$content1 .="Invalid Email";
				}
				$content1 .="</span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == 'date') {
				if( $thirdparty != "thirdparty" ) {
					?>
					<script>
						jQuery(function() {
							jQuery( "#<?php echo esc_js($module_options.'_'.$config_fields[$i]['name'].'_'.$_SESSION['generated_forms']);?>" ).datepicker({
								dateFormat: "yy-mm-dd",
								changeMonth: true,
								changeYear: true,
								showOn: "button",
								buttonImage: "<?php echo $plugin_url; ?>/images/calendar.gif",
								buttonImageOnly: true,
								yearRange: '1900:2050'
							});
						});
					</script>
					<?php
				}
				$content1.='<td><input type="text" class="date'.$M.'" name='.$config_field_name.' id="'.$module_options.'_'.$config_fields[$i]['name'].'_'.$_SESSION['generated_forms'].'" value="';
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';

				$content1.='" readonly="readonly" /> <span class="smack_field_error" id="'.$config_fields[$i]['name'].'error'.$_SESSION["generated_forms"].'"></span></td></tr>';

				$count_selected++;
			} elseif($fieldtype == 'boolean') {
				$content1.='<td><input type="checkbox'.$M.'" class="boolean" name='.$config_field_name.' id="'.$module_options.'_'.$config_fields[$i]['name'].'" value="on"/><br/><span class="smack_field_error" id="'.$config_fields[$i]['name'].'error'.$_SESSION["generated_forms"].'"></span></td></tr>';
				$count_selected++;
			} elseif($fieldtype == 'url') {
				$content1.="<td><input type='text' class='url{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';
				$content1.="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if(isset($_POST['submitcontactform']) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) ) {
					if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_field_name]) == "" ) {
						$content1 .="This field is mandatory";
					}
					elseif( isset($_POST[$config_field_name]) && $config_fields[$i]['type']['name'] == 'url' && (!preg_match('/((http|https)\:\/\/)?[a-zA-Z0-9\.\/\?\:@\-=#]+\.([a-zA-Z0-9\.\/\?\:@\-=#])*/',sanitize_text_field($_POST[$config_field_name])) && (sanitize_text_field($_POST[$config_field_name]) != ""))) {
						$content1 .="Invalid URL";
					}
				}
				$content1 .="</span></td></tr>";
				$count_selected++;
			} elseif($fieldtype == 'phone') {
				$content1.="<td><input type='text' class='phone{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';
				$content1.="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if(isset($_POST['submitcontactform']) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) ) {
					if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_field_name]) == "" ) {
						$content1 .="This field is mandatory";
					}
				}
				$content1 .="</span></td></tr>";
				$count_selected++;
			} else {
				$content1.="<td><input type='text' class='others{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}' value='{".sanitize_text_field($_POST[$config_field_name])."}'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></td></tr>";
				$count_selected++;
			}
		}
	}

	if($count_selected==0) {
		$content.="<h3>You have selected no fields</h3>";
	} else {
		$content.=$content1;
	}
	$content.="<tr><td></td><td>";
	if($count_selected==0) {

	} else {
		$config = get_option("SmackHelpDeskCaptchaSettings");
		$save_field_config = $formattr;
		if(isset($config['smack_recaptcha'])) {
		if(($config['smack_recaptcha']=='yes') && (isset($save_field_config['google_captcha']) && ($save_field_config['google_captcha'] == "1"))) {
			$publickey = $config['recaptcha_public_key'];
			if(isset($captcha_error) && ($captcha_error == true)) {
				$content.="<div style='color:red' id='recaptcha_response_field_error{$_SESSION["generated_forms"]}'>Captcha Error</div>";
				$count_error++;
			}
			$content .= '<br><div class="g-recaptcha" data-sitekey="'.$publickey.'"></div>';
		}}
		$content.="<p class='contact-form-comment'>
		<p class='form-submit'>";
		$content.="<input type='hidden' name='formnumber' value='{$_SESSION['generated_forms']}'>";
		$content.="<input type='hidden' name='submitcontactform' value='submitcontactform{$_SESSION['generated_forms']}'/>";
		$content.='<input type="submit" value="Submit" id="submit" name="submit"></p>';
	}
	$content.="</td></tr></table>";
	$content.="<input type='hidden' value='".$module."' name='moduleName' /></p></form>";
	if(isset($_POST['submitcontactform']) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) ) {
		if($count_error==0) {
			$content.= SmackHelpDeskCaptureWebFormData( $formtype );
		}
	}
	return $content;
}

function SmackHelpDeskWidgetWebForm($module, $config_fields, $module_options , $formtype) {
	global $plugin_dir;
	global $plugin_url;
	$siteurl=site_url();
	global $config;
	global $post;
	global $formattr;
	$captcha_error = false;
	$HelperObj = new SmackHelpDeskIntegrationHelper();
	$activatedplugin = $HelperObj->ActivatedPlugin;
	$post=array();
	$post=$_POST;

	if( !isset( $_SESSION["generated_forms"] ) ) {
		$_SESSION["generated_forms"] = 1;
	} else {
		$_SESSION["generated_forms"]++;
	}

	if(isset($_POST['submitcontactformwidget']) && (sanitize_text_field($_POST['submitcontactformwidget']) == 'submitwidgetcontactform'.$_SESSION['generated_forms'])  && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) ) {
		$content = "";
		$script = "";
		$count_error=0;
		for($i=0; $i<count($config_fields); $i++) {
			if(array_key_exists($config_fields[$i]['name'],$_POST)) {
				if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "") {
					$script="<script> oFormObject = document.forms['contactform{$_SESSION["generated_forms"]}']; oformElement = oFormObject.elements['".$config_fields[$i]['name']."']; document.getElementById('".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}').innerHTML= '<div style=\'color:red;\'>This field is mandatory</div>'; </script>";
					$content .= $script;
					$script="";
					$count_error++;
				} elseif(  isset($_POST[$config_fields[$i]['name']]) && $config_fields[$i]['type']['name'] == 'integer' && !preg_match('/^[\d]*$/', sanitize_text_field($_POST[$config_fields[$i]['name']]))) {
					$script="<script>oFormObject = document.forms['contactform{$_SESSION["generated_forms"]}'];oformElement = oFormObject.elements['".$config_fields[$i]['name']."']; document.getElementById('".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}').innerHTML='enter valid ".$config_fields[$i]['name']."'; </script>";
					$content .= $script;
					$script="";
					$count_error++;
				} elseif($config_fields[$i]['type']['name'] == 'double'  && !preg_match('/^([\d]{1,8}.?[\d]{1,2})?$/', sanitize_text_field($_POST[$config_fields[$i]['name']])) ) {
					$script="<script>oFormObject = document.forms['contactform{$_SESSION["generated_forms"]}'];oformElement = oFormObject.elements['".$config_fields[$i]['name']."']; document.getElementById('".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}').innerHTML='enter valid ".$config_fields[$i]['name']."';</script>";
					$content .= $script;
					$count_error++;
				} elseif($config_fields[$i]['type']['name'] == 'currency'  && !preg_match('/^([\d]{1,8}.?[\d]{1,2})?$/', sanitize_text_field($_POST[$config_fields[$i]['name']])) ) {
					$script="<script>oFormObject = document.forms['contactform{$_SESSION["generated_forms"]}'];oformElement = oFormObject.elements['".$config_fields[$i]['name']."']; document.getElementById('".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}').innerHTML='enter valid ".$config_fields[$i]['name']."';</script>";
					$content .= $script;
					$count_error++;
				} elseif($config_fields[$i]['type']['name'] == 'email' && (!preg_match('/^([a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,4}))?$/',sanitize_text_field($_POST[$config_fields[$i]['name']])))) {
					$script="<script>oFormObject = document.forms['contactform{$_SESSION["generated_forms"]}'];oformElement = oFormObject.elements['".$config_fields[$i]['name']."']; document.getElementById('".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}').innerHTML='<font color=\'red\'>Enter valid ".$config_fields[$i]['name']."</font>';</script>";
					$content .= $script;
					$count_error++;
				} elseif($config_fields[$i]['type']['name'] == 'phone' && !preg_match('/^[2-9]{1}[0-9]{2}-[0-9]{3}-[0-9]{4}$/', sanitize_text_field($_POST[$config_fields[$i]['name']])))
				{

				} elseif($config_fields[$i]['type']['name'] == 'multipicklist' ) {
					$concat ="";
					for( $index=0; $index<count(sanitize_text_field($_POST[$config_fields[$i]['name']])); $index++) {
						$concat.=$_POST[$config_fields[$i]['name']][$index]." |##| ";
					}
					$concat=substr($concat,0,-6);
					$post[$config_fields[$i]['name']]=$concat;
				} elseif($config_fields[$i]['type']['name'] == 'url' && (!preg_match('/^((http:|ftp:|https:)\/\/[a-z0-9A-Z]+\.[a-z0-9-]+\.[a-z0-9-]{2,4})/',sanitize_text_field($_POST[$config_fields[$i]['name']])))) {
					if($_POST[$config_fields[$i]['name']] == "") {

					} else {
						$script="<script>oFormObject = document.forms['contactform{$_SESSION["generated_forms"]}'];oformElement = oFormObject.elements['".$config_fields[$i]['name']."']; document.getElementById('".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}').innerHTML='enter valid ".$config_fields[$i]['name']."'</script>";
						$count_error++;
					}
					$content .= $script;
				}
			}
		}
		$captcha_config = get_option("SmackHelpDeskCaptchaSettings");
		$save_field_config = $formattr;
		if(($captcha_config['smack_recaptcha']=='yes') && (isset($save_field_config['google_captcha']) && ($save_field_config['google_captcha'] == 1 ))) {
			$privatekey = $captcha_config['recaptcha_private_key'];
			if( !isset( $_REQUEST['g-recaptcha-response'] ) || ( sanitize_text_field($_REQUEST['g-recaptcha-response']) == NULL ) || ( sanitize_text_field($_REQUEST['g-recaptcha-response']) == "" ) )
			{
				$captcha_error = true;
			} else {
				$botcheck_url = "https://www.google.com/recaptcha/api/siteverify?secret=$privatekey&response={$_REQUEST['g-recaptcha-response']}";
				$ch  = curl_init ($botcheck_url);
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$google_bot_check_result = curl_exec ($ch);
				$decoded_result = json_decode( $google_bot_check_result );
				if( $decoded_result->success ) {
					$captcha_error = false;
				} else {
					$count_error++;
					$captcha_error = true;
				}
			}
		}
	}
	$content = "<form id='contactform{$_SESSION["generated_forms"]}' name='contactform{$_SESSION["generated_forms"]}' method='post'>";
	$content.= "<div id='widgetSmackLogMsg{$_SESSION["generated_forms"]}'></div>";
	$content1="";
	$count_selected=0;
	for($i=0; $i<count($config_fields);$i++) {
		if(isset( $_POST[$config_fields[$i]['name']] ) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) ) {
			$field_value = sanitize_text_field($_POST[$config_fields[$i]['name']]);
		} else {
			$field_value = "";
		}
		$content2 = "";
		$fieldtype = $config_fields[$i]['type']['name'];
		if($config_fields[$i]['publish']==1) {
			if($config_fields[$i]['wp_mandatory']==1) {
				$content1.=$config_fields[$i]['display_label']." *";
				$M=' mandatory';
			} else {
				$content1.="<label for='".$config_fields[$i]['display_label']."'>".$config_fields[$i]['display_label']."</label>";
				$M='';
			} if($fieldtype == "string") {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='string{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_fields[$i]['name']]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= $field_value;
				else
					$content1 .= '';
				$content1 .= "'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if(isset($_POST['submitcontactformwidget']) && (sanitize_text_field($_POST['submitcontactformwidget']) == 'submitwidgetcontactform'.$_SESSION['generated_forms'])  && (sanitize_text_field($_POST['formnumber']) == $_SESSION['generated_forms']) )
				{
					if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "")
					{
						$content1 .="This field is mandatory";
					}
				}
				$content1 .="</span></div>";
				$count_selected++;
			} elseif($fieldtype == "text") {
				$content1.='<div class="div_texbox">'."<textarea class='textarea{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}'></textarea><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></div>";
				$count_selected++;
			} elseif($fieldtype == 'radioenum') {
				$content1 .= '<div class="div_texbox">';
				$picklist_count = count($config_leads_fields[$i]['type']['picklistValues']);
				for($j=0 ; $j<$picklist_count ; $j++) {
					$content2.="<input type='radio' name='{$config_leads_fields[$i]['name']}' value='{$config_leads_fields[$i]['type']['picklistValues'][$j]['label']}'>{$config_leads_fields[$i]['type']['picklistValues'][$j]['value']}<br/>";
				}
				$content1.=$content2;
				$content1 .= "<br/><span class='smack-field_error' id='".$config_leads_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span>";
				$content1 .= "</div>";
				$count_selected++;
			} elseif($fieldtype == 'multipicklist') {
				$picklist_count = count($config_fields[$i]['type']['picklistValues']);
				$content1.='<div class="div_texbox">'."<select class='multipicklist{$M}' name='{$config_fields[$i]['name']}[]' multiple='multiple' id='{$module_options}_{$config_fields[$i]['name']}'  value='{$field_value}'>";
				for($j=0 ; $j<$picklist_count ; $j++) {
					$content2.="<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['value']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
				}
				$content1.=$content2;
				$content1.="</select><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></div>";
				$count_selected++;
			} elseif($fieldtype == 'picklist') {
				$picklist_count = count($config_fields[$i]['type']['picklistValues']);
				$content1.='<div class="div_texbox">'."<select class='picklist{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}'  value='";
				if(isset($_POST[$config_fields[$i]['name']]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= $field_value;
				else
					$content1 .= '';

				$content1.="'>";
				for($j=0 ; $j<$picklist_count ; $j++) {
					if($activatedplugin == 'freshsales') {
						$content2 .= "<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['id']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
					} else {
						$content2 .= "<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['value']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
					}
				}
				$content1.=$content2;
				$content1.="</select><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></div>";
				$count_selected++;
			} elseif($fieldtype == 'nested') {
				$picklist_count = count($config_fields[$i]['type']['picklistValues']);
				$content1.="<td><select class='picklist{$M}' name='{$config_field_name}' id='{$module_options}_{$config_fields[$i]['name']}'  value='";
				if(isset($_POST[$config_field_name]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_field_name]);
				else
					$content1 .= '';

				$content1.="'>";
				for($j=0 ; $j<$picklist_count ; $j++) {
					$content2 .= "<option id='{$config_fields[$i]['name']}' value='{$config_fields[$i]['type']['picklistValues'][$j]['label']}'>{$config_fields[$i]['type']['picklistValues'][$j]['label']}</option>";
				}
				$content1.=$content2;
				$content1.="</select><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></tr>";
				$count_selected++;
			} elseif($fieldtype == 'integer') {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='integer{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_fields[$i]['name']]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= $field_value;
				else
					$content1 .= '';
				$content1 .="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "")
				{
					$content1 .="This field is mandatory";
				} elseif( isset($_POST[$config_fields[$i]['name']]) && $config_fields[$i]['type']['name'] == 'integer' && !preg_match('/^[\d]*$/', sanitize_text_field($_POST[$config_fields[$i]['name']])))
				{
					$content1 .="This field is integer";
				}
				$content1 .="</span></div>";
				$count_selected++;
			}
			elseif($fieldtype == 'double') {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='double{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='{$field_value}'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></div>";
				$count_selected++;
			}
			elseif($fieldtype == 'currency') {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='currency{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_fields[$i]['name']]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= $field_value;
				else
					$content1 .= '';
				$content1 .="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "")
				{
					$content1 .="This field is mandatory";
				}
				elseif( isset($_POST[$config_fields[$i]['name']]) && sanitize_text_field($config_fields[$i]['type']['name']) == 'currency'  && !preg_match('/^([\d]{1,8}.?[\d]{1,2})?$/', sanitize_text_field($_POST[$config_fields[$i]['name']])) )
				{
					$content1 .="This field is integer";
				}
				$content1 .="</span></div>";
				$count_selected++;
			}
			elseif($fieldtype == 'email') {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='email{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_fields[$i]['name']]) && (sanitize_text_field($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= $field_value;
				else
					$content1 .= '';
				$content1 .="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if($config_fields[$i]['wp_mandatory'] == 1 &&  isset($_POST[$config_fields[$i]['name']]) && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "") {
					$content1 .="This field is mandatory";
				}
				elseif(  isset($_POST[$config_fields[$i]['name']]) && $config_fields[$i]['type']['name'] == 'email' && (!preg_match('/^([a-z0-9_\+-]+(\.[a-z0-9_\+-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*\.([a-z]{2,4}))?$/',sanitize_text_field($_POST[$config_fields[$i]['name']])) && (sanitize_text_field($_POST[$config_fields[$i]['name']]) != "") )) {
					$content1 .="Invalid Email";
				}
				$content1 .="</span></div>";
				$count_selected++;
			}
			elseif($fieldtype == 'date') {
				?>
				<script>
					jQuery(function() {
						jQuery( "#<?php echo $module_options.'_'.$config_fields[$i]['name'].'_'.$_SESSION['generated_forms'];?>" ).datepicker({
							dateFormat: "yy-mm-dd",
							changeMonth: true,
							changeYear: true,
							showOn: "button",
							buttonImage: "<?php echo $plugin_url; ?>/images/calendar.gif",
							buttonImageOnly: true
						});
					});
				</script>
				<?php
				$content1.='<div class="div_texbox">'.'<input type="text" class="date'.$M.' smack_widget_textbox_date_picker" name='.$config_fields[$i]['name'].' id="'.$module_options.'_'.$config_fields[$i]['name'].'_'.$_SESSION['generated_forms'].'" value="';
				if(isset($_POST[$config_fields[$i]['name']]) && (sanitize_text_field($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= sanitize_text_field($_POST[$config_fields[$i]['name']]);
				else
					$content1 .= '';
				$content1 .='" readonly="readonly" /> <span class="smack_field_error" id="'.$config_fields[$i]['name'].'error'.$_SESSION["generated_forms"].'"></span></div>';
				$count_selected++;
			}
			elseif($fieldtype == 'boolean') {
				$content1.='<div class="div_texbox">'.'<input type="checkbox'.$M.'" class="boolean" name='.$config_fields[$i]['name'].' id="'.$module_options.'_'.$config_fields[$i]['name'].'" value="on"/><br/><span class="smack_field_error" id="'.$config_fields[$i]['name'].'error'.$_SESSION["generated_forms"].'"></span><div>';
				$count_selected++;
			}
			elseif($fieldtype == 'url') {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='url{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_fields[$i]['name']]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= $field_value;
				else
					$content1 .= '';
				$content1 .="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if(isset($_POST['submitcontactformwidget']) && ($_POST['submitcontactformwidget'] == 'submitwidgetcontactform'.$_SESSION['generated_forms'])  && ($_POST['formnumber'] == $_SESSION['generated_forms']) )
				{
					if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "")
					{
						$content1 .="This field is mandatory";
					}

					elseif( isset($_POST[$config_fields[$i]['name']]) && $config_fields[$i]['type']['name'] == 'url' && (!preg_match('/^((http:|ftp:|https:)\/\/[a-z0-9A-Z]+\.[a-z0-9-]+\.[a-z0-9-]{2,4})/',sanitize_text_field($_POST[$config_fields[$i]['name']])))  && (sanitize_text_field($_POST[$config_fields[$i]['name']]) != "") )
					{
						$content1 .="Invalid URL";
					}
				}
				$content1 .="</span></div>";
				$count_selected++;
			}
			elseif($fieldtype == 'phone') {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='phone{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='";
				if(isset($_POST[$config_fields[$i]['name']]) && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) && $count_error!=0)
					$content1 .= $field_value;
				else
					$content1 .= '';
				$content1 .="'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'>";
				if(isset($_POST['submitcontactformwidget']) && (sanitize_text_field($_POST['submitcontactformwidget']) == 'submitwidgetcontactform'.$_SESSION['generated_forms'])  && (sanitize_text_field($_POST['formnumber']) == $_SESSION['generated_forms']) )
				{
					if($config_fields[$i]['wp_mandatory'] == 1 && sanitize_text_field($_POST[$config_fields[$i]['name']]) == "")
					{
						$content1 .="This field is mandatory";
					}
				}
				$content1 .="</span></div>";
				$count_selected++;
			} else {
				$content1.='<div class="div_texbox">'."<input class='smack_widget_textbox' type='text' class='others{$M}' name='{$config_fields[$i]['name']}' id='{$module_options}_{$config_fields[$i]['name']}' value='{$field_value}'/><br/><span class='smack_field_error' id='".$config_fields[$i]['name']."error{$_SESSION["generated_forms"]}'></span></div>";
				$count_selected++;
			}
		}
	}

	if($count_selected==0) {
		$content.="<h3>You have selected no fields</h3>";
	} else {
		$content.=$content1;
	}
	if($count_selected==0)
	{
	}
	else {
		$config = get_option("SmackHelpDeskCaptchaSettings");
		$save_field_config = $formattr;
		if(($config['smack_recaptcha']=='yes') && (isset($save_field_config['google_captcha']) && ($save_field_config['google_captcha'] == 1 )))
		{
			$publickey = $config['recaptcha_public_key'];
			if(isset($captcha_error) && ($captcha_error == true)) {
				$content.="<div style='color:red' id='recaptcha_response_field_error{$_SESSION["generated_forms"]}'> Captcha Error </div>";
				$count_error++;
			}
			$content .= '<br><div class="g-recaptcha" data-sitekey="'.$publickey.'"></div>';
		}
		$content.="<p class='contact-form-comment'>
		<p class='form-submit'>";
		$content.="<input type='hidden' name='formnumber' value='{$_SESSION['generated_forms']}'>";
		$content.="<input type='hidden' name='submitcontactformwidget' value='submitwidgetcontactform{$_SESSION["generated_forms"]}'/>";
		$content.='<input class="smack_widget_buttons" type="submit" value="Submit" id="submit" name="submit"></p>';
	}
	if(isset($_POST['submitcontactformwidget']) && (sanitize_text_field($_POST['submitcontactformwidget']) == 'submitwidgetcontactform'.$_SESSION['generated_forms'])  && (intval($_POST['formnumber']) == $_SESSION['generated_forms']) )
	{
		if($count_error==0) {
			$content .= SmackHelpDeskCaptureWebFormData( $formtype );
		}
	}
	$content.="<input type='hidden' value='".$module."' name='moduleName' /></p></form>";
	return $content;
}

function SmackHelpDeskCaptureUserIP() {
	$ip = $_SERVER['REMOTE_ADDR'];
	return $ip;
}

function SmackHelpDeskSendMail( $config,$activatedplugin,$formtype, $pageurl,$data,$contenttype ) {
	$subject = 'Form Details';
	$message = "Shortcode: " . "[$activatedplugin-web-form type='$formtype']" ."\n" . "URL: " . $pageurl ."\n" . "Type:".$formtype ."\n". "Form Status:".$data . "\n" . "FormFields and Values:"."\n".$contenttype ."\n"."User IP: " . SmackHelpDeskCaptureUserIP();
	$current_user = wp_get_current_user();
	$admin_email = $current_user->user_email;
	$headers = "From: Administrator <$admin_email>" . "\r\n\\";
	if(isset($config['email']) && ($config['email'] == "")) {
		$to = "{$admin_email}";
	} else {
		$to = "{$config['email']}";
	}
	if(isset($config['emailcondition']) && $config['emailcondition'] != 'none') {
		wp_mail( $to, $subject, $message,$headers );
	}
}

function SmackHelpDeskGetCurrentPageURL() {
	$pageURL = 'http';
	if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on") {$pageURL .= "s";}
	$pageURL .= "://";
	if ($_SERVER["SERVER_PORT"] != "80") {
		$pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
	} else {
		$pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
	}
	return $pageURL;
}
?>
