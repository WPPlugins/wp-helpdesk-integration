<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SmackHelpDeskIntegrationApplyBulkActions
{
	public $nonceKey = null;
	public function __construct() {
		$helperObj = new SmackHelpDeskIntegrationCoreFunctions();
	}
	function saveHelpDeskFormFields( $options , $onAction , $editShortCodes , $formtype = "post" )
	{
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$module = $HelperObj->Module;
		$moduleslug = $HelperObj->ModuleSlug;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
		$save_field_config = array();
		$portal_type = sanitize_text_field($_REQUEST['portal_type']);
		$module = sanitize_text_field($_REQUEST['module']);
		$moduleslug = rtrim( strtolower($module) , "s");
		$options = "SmackHelpDeskShortCodeFields";
		if( isset($_POST ['savefields'] ) && (sanitize_text_field($_POST ['savefields']) == "GenerateShortcode"))
		{
			$config_fields = get_option("smack_whi_{$portal_type}_{$moduleslug}_fields-tmp");
			$config_contact_shortcodes = get_option($options);
		}
		else
		{
			$options = "SmackHelpDeskShortCodeFields";
			$config_contact_shortcodes = get_option($options);
			$config_fields = $config_contact_shortcodes[sanitize_text_field($_REQUEST['EditShortcode'])];
		}
		foreach( $config_fields as $shortcode_attributes => $fields )
		{
			if($shortcode_attributes == "fields")
			{
				foreach( $fields as $key => $field )
				{
					$save_field_config["fields"][$key] = $field;

					if( !isset($field['mandatory']) || $field['mandatory'] != 2 )
					{
						if(isset($_POST['select'.$key]))
						{
							$save_field_config['fields'][$key]['publish'] = 1;
						}
						else
						{
							$save_field_config['fields'][$key]['publish'] = 0;
						}
					}
					else
					{
						$save_field_config['fields'][$key]['publish'] = 1;
					}

					if( !isset($field['mandatory']) || $field['mandatory'] != 2 )
					{
						if(isset($_POST['mandatory'.$key]))
						{
							$save_field_config['fields'][$key]['wp_mandatory'] = 1;
							$save_field_config['fields'][$key]['publish'] = 1;
						}
						else
						{
							$save_field_config['fields'][$key]['wp_mandatory'] = 0;
						}
					}
					else
					{

						$save_field_config['fields'][$key]['wp_mandatory'] = 1;
					}

					$save_field_config['fields'][$key]['display_label'] = sanitize_text_field($_POST['fieldlabel'.$key]);
				}
			}
			else
			{
				$save_field_config[$shortcode_attributes] = $fields;
			}
		}
		if(!isset($save_fields_config["check_duplicate"]))
		{
			$save_fields_config["check_duplicate"] = 'none';
		}
		else if(isset($save_fields_config["check_duplicate"]) && ($save_fields_config["check_duplicate"] === 1))
		{
			$save_fields_config["check_duplicate"] === 'skip';
		}
		else if(isset($save_fields_config["check_duplicate"]) && ($save_fields_config["check_duplicate"] === 0))
		{
			$save_fields_config["check_duplicate"] = 'none';
		}

		$extra_fields = array( "formtype" , "enableurlredirection" , "redirecturl" , "errormessage" , "successmessage" , "assignedto" , "check_duplicate" , "enablecaptcha");

		foreach( $extra_fields as $extra_field )
		{
			if(isset( $_POST[$extra_field]))
			{
				$save_field_config[$extra_field] = sanitize_text_field($_POST[$extra_field]);
			}
			else
			{
				unset($save_field_config[$extra_field]);
			}
		}
		for( $i = 0; $i < $_REQUEST['no_of_rows']; $i++ )
		{
			$REQUEST_DATA[$i] = sanitize_text_field($_REQUEST['position'.$i]);
		}

		asort($REQUEST_DATA);

		$i = 0;
		foreach( $REQUEST_DATA as $key => $value )
		{
			$Ordered_field_config['fields'][$i] = $save_field_config['fields'][$key];
			$i++;
		}
		$save_field_config['fields'] = $Ordered_field_config['fields'];
		$save_field_config['crm'] = sanitize_text_field($_REQUEST['portal_type']);
		if( isset($_POST ['savefields'] ) && (sanitize_text_field($_POST ['savefields']) == "GenerateShortcode"))
		{
			$OverallFunctionObj = new SmackHelpDeskIntegrationCoreFunctions();
			$random_string = $OverallFunctionObj->CreateNewFieldShortcode( $_REQUEST['portal_type'] , $_REQUEST['module'] );
			$config_contact_shortcodes[$random_string] = $config_fields;
			update_option("SmackHelpDeskShortCodeFields", $config_contact_shortcodes);
			update_option("smack_whi_{$portal_type}_{$moduleslug}_fields-tmp" , $save_field_config);
			wp_redirect("".WP_HELPDESK_INTEGRATION_PLUG_URL."&__module=ManageShortcodes&__action=ManageFields&portal_type=$portal_type&module=$module&EditShortcode=$random_string&nonce_key=$this->nonceKey");
			exit;
		}
		else
		{
			$config_contact_shortcodes[$_REQUEST['EditShortcode']] = $save_field_config;
			update_option("SmackHelpDeskShortCodeFields", $config_contact_shortcodes);
			update_option("smack_whi_{$portal_type}_{$moduleslug}_fields-tmp" , $save_field_config);
		}
		$data['display'] = "";
		return $data;
	}

	function HelpDeskFormFields( $options, $onAction, $editShortCodes , $formtype = "post" )
	{
		$SmackHelpDeskDataCapture = new SmackHelpDeskDataCapture();
		$module =$module_options ='Leads';
		$content1='';
		$config_leads_fields = $SmackHelpDeskDataCapture->formfields_settings( $editShortCodes );
		$imagepath = WP_HELPDESK_INTEGRATION_DIR . "images/";
		$imagepath = esc_url( $imagepath );
		$content='
		<input type="hidden" name="field-form-hidden" value="field-form" />
		<div>';
		$i = 0;
		if(!isset($config_leads_fields['fields'][0]))
		{
			$content.='<p style="color:red;font-size:20px;text-align:center;margin-top:-22px;margin-bottom:20px;">'.__("Helpdesk fields are not yet synchronised", WP_HELPDESK_INTEGRATION_SLUG).' </p>';
		}
		else
		{
			$content.='<table style="background-color: #F1F1F1; border: 1px solid #dddddd;width:98%;margin-bottom:26px;margin-top:0px"><tr class="smack_highlight smack_alt" style="border-bottom: 1px solid #dddddd;"><th class="smack-field-td-middleit" align="left" style="width: 50px;"><input type="checkbox" name="selectall" id="selectall" onclick="SmackHelpDeskSelectAllFields'."('field-form','".$module."')".';" style="margin-top:-3px"/></th><th align="left" style="width: 200px;"><h5>'.__('Field Name', WP_HELPDESK_INTEGRATION_SLUG).'</h5></th><th class="smack-field-td-middleit" align="left" style="width: 100px;"><h5>'.__('Show Field', WP_HELPDESK_INTEGRATION_SLUG).'</h5></th><th class="smack-field-td-middleit" align="left" style="width: 100px;"><h5>'.__('Order', WP_HELPDESK_INTEGRATION_SLUG).'</h5></th><th class="smack-field-td-middleit" style="width: 100px;" align="left"><h5>'.__('Mandatory', WP_HELPDESK_INTEGRATION_SLUG).'</h5></th><th class="smack-field-td-middleit" style="width: 100px;" align="left"><h5>'.__('Field Label Display', WP_HELPDESK_INTEGRATION_SLUG).'</h5></th></tr>';

			for($i=0;$i<count($config_leads_fields['fields']);$i++)
			{
				if( $config_leads_fields['fields'][$i]['wp_mandatory'] == 1 )
				{
					$madantory_checked = 'checked="checked"';
				}
				else
				{
					$madantory_checked = "";
				}

				if( isset($config_leads_fields['fields'][$i]['mandatory']) && $config_leads_fields['fields'][$i]['mandatory'] == 2)
				{

					if($i % 2 == 1)
						$content1.='<tr class="smack_highlight smack_alt">';
					else
						$content1.='<tr class="smack_highlight">';

					$content1.='
					<td class="smack-field-td-middleit"><input type="checkbox" name="select'.$i.'" id="select'.$i.'" disabled=disabled checked=checked ></td>
					<td>'.$config_leads_fields['fields'][$i]['label'].' *</td>
					<td class="smack-field-td-middleit">';
					{
						$content1.='<a name="publish'.$i.'" id="publish'.$i.'" onclick="'."alert('".__('This field is mandatory, cannot hide', WP_HELPDESK_INTEGRATION_SLUG)."')".'">
						<img src="'.$imagepath.'tick_strict.png"/>
						</a>';
					}
					$content1.='</td>
					<td class="smack-field-td-middleit">';
					$content1.= "<input class='position-text-box' type='textbox' name='position{$i}' value='".($i+1)."' >";
					$content1.='</td> 
					<td class="smack-field-td-middleit"><input type="checkbox" name="mandatory'.$i.'" id="mandatory'.$i.'" disabled=disabled checked=checked ></td>';
					$content1.='<td class="smack-field-td-middleit" id="field_label_display'.$i.'"><input type="text" id="field_label_display'.$i.'" name="fieldlabel'.$i.'" value="'.$config_leads_fields['fields'][$i]['display_label'].'"></td>

							</tr>';
				}
				else
				{
					if($i % 2 == 1)
						$content1.='<tr class="smack_highlight smack_alt">';
					else
						$content1.='<tr class="smack_highlight">';

					$content1.='<td class="smack-field-td-middleit">';
					if($config_leads_fields['fields'][$i]['publish'] == 1){
						$content1.= '<input type="checkbox" name="select'.$i.'" id="select'.$i.'">';
					}
					else
					{
						$content1.= '<input type="checkbox" name="select'.$i.'" id="select'.$i.'">';
					}
					$content1.='</td>
					<td>'.$config_leads_fields['fields'][$i]['label'].'</td>
					<td class="smack-field-td-middleit">';

					if($config_leads_fields['fields'][$i]['publish'] == 1){
						$content1.='<p name="publish'.$i.'" id="publish'.$i.'" >
						
						<span class="is_show_widget" style="color: #019E5A;">Yes</span>
						</p>';
					}
					else{
						$content1.='<p name="publish'.$i.'" id="publish'.$i.'" >
						
						<span class="not_show_widget" style="color: #FF0000;">No</span>
						</p>';
					}
					$content1.='</td>
					<td class="smack-field-td-middleit">';
					$content1.= "<input class='position-text-box' type='textbox' name='position{$i}' value='".($i+1)."' ></td>";
					$content1.=' <td class="smack-field-td-middleit">';
					if($config_leads_fields['fields'][$i]["wp_mandatory"] == 1)
					{
						$content1 .= '<p name="mandatory'.$i.'" id="mandatory'.$i.'" >
						
						<span class="is_show_widget" style="color: #019E5A;">'.__("Yes", WP_HELPDESK_INTEGRATION_SLUG).'</span>
						</p>';
					}
					else
					{
						$content1 .= '<p name="mandatory'.$i.'" id="mandatory'.$i.'" >
						<span class="not_show_widget" style="color: #FF0000;">'.__("No", WP_HELPDESK_INTEGRATION_SLUG).'</span>
						</p>';
					}
					$content1 .= '</td>';

					$content1.='<td class="smack-field-td-middleit" id="field_label_display'.$i.'"><input type="text" id="field_label_display_'.$i.'" name ="fieldlabel'.$i.'" value="'.$config_leads_fields['fields'][$i]['display_label'].'"></td>
			
	</tr>';
				}
			}
		}
		$content1.="<input type='hidden' name='no_of_rows' id='no_of_rows' value={$i} />";
		$content.=$content1;
		$content.= '</table>
		</div>';
		return $content;
	}


	function enableFields( $selectedfields , $shortcode_name )
	{
		global $wpdb;
		$string2 = "";
		$enable_showfields = $wpdb->get_results($wpdb->prepare("select ffm.form_field_sequence , ffm.rel_id , sm.shortcode_id from wp_smackhelpdesk_form_field_manager as ffm inner join wp_smackhelpdesk_shortcode_manager as sm on ffm.shortcode_id = sm.shortcode_id where sm.shortcode_name = %s order by ffm.form_field_sequence", $shortcode_name));
		if( isset( $selectedfields ) ) {
			foreach($selectedfields as $fields)
			{
				$string2 .= "'" . $enable_showfields[$fields]->rel_id . "',";
			}
		}
		$trim2 = rtrim($string2, ',');
		$wps_enablefields = $enable_showfields[0]->shortcode_id;
		if($trim2 != "")
		$wpdb->query($wpdb->prepare("update wp_smackhelpdesk_form_field_manager set state = %d where rel_id in ($trim2) and shortcode_id = %d", 1, $wps_enablefields));
	}

	function disableFields( $selectedfields , $shortcode_name )
	{
		global $wpdb;
		$string3 = "";
		$disable_showfields = $wpdb->get_results( $wpdb->prepare("select ffm.form_field_sequence , ffm.rel_id , sm.shortcode_id from wp_smackhelpdesk_form_field_manager as ffm inner join wp_smackhelpdesk_shortcode_manager as sm on ffm.shortcode_id = sm.shortcode_id where sm.shortcode_name = %s order by ffm.form_field_sequence", $shortcode_name) );
		if( isset( $selectedfields ) ) {
			foreach($selectedfields as $fields)
			{
				$string3 .= "'" . $disable_showfields[$fields]->rel_id . "',";
			}
		}
		$trim3 = rtrim($string3, ',');
		$wps_disablefields = $disable_showfields[0]->shortcode_id;
		if($trim3 != "")
		$wpdb->query($wpdb->prepare("update wp_smackhelpdesk_form_field_manager set state = %d where rel_id in ($trim3) and shortcode_id = %d", 0, $wps_disablefields));
	}

	function updateFieldsOrder( $selectedfields , $shortcode_name )
	{
		global $wpdb;
		for( $i = 0; $i < count($selectedfields); $i++ )
		{
			$REQUEST_DATA[$i+1] = $selectedfields[$i];
		}
		asort($REQUEST_DATA);
		$i = 1;
		foreach( $REQUEST_DATA as $key => $value )
		{
			$REQUEST_DATA_1[$key] = $i;
			$i++;
		}
		$update_field_order = $wpdb->get_results($wpdb->prepare("select ffm.rel_id , ffm.form_field_sequence from wp_smackhelpdesk_form_field_manager as ffm inner join wp_smackhelpdesk_shortcode_manager as sm on sm.shortcode_id = ffm.shortcode_id  where sm.shortcode_name = %s order by ffm.form_field_sequence", $shortcode_name));
		$newarray = array();
		$i = 1;
		foreach( $update_field_order as $fieldkey => $fieldvalue)
		{
			$newarray[$fieldvalue->rel_id] = $REQUEST_DATA_1[$i]; //$REQUEST_DATA[$fieldvalue->form_field_sequence];
			$i++;
		}
		foreach( $newarray as $key => $value )
		{
			$update_order = $wpdb->query($wpdb->prepare("update wp_smackhelpdesk_form_field_manager set form_field_sequence = %d where rel_id = %d", $value, $key));
		}
	}
}
class ManageShortcodesActions extends SkinnyActions_HelpDeskIntegration {

	public $nonceKey = null;
	public function __construct()
	{
		$helperObj = new SmackHelpDeskIntegrationCoreFunctions();
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
		$data['plugin_url']= WP_HELPDESK_INTEGRATION_DIRECTORY;
		$data['onAction'] = 'onCreate';
		$data['siteurl'] = site_url();
		$data['nonce_key'] = $this->nonceKey;
		return $data;
	}

	public function executeManageFields1($request)
	{
		$data = $request;
		return $data;
	}
	public function executeManageFields($request)
	{
		$FieldOperation = new SmackHelpDeskIntegrationApplyBulkActions();
		$selectedfields = array();
		if(!empty($request['POST']['no_of_rows'])) {
			for ( $i = 0; $i < $request['POST']['no_of_rows']; $i ++ ) {
				if ( isset( $request['POST'][ 'select' . $i ] ) && ( $request['POST'][ 'select' . $i ] == 'on' ) ) {
					$selectedfields[] = $i;
				}
				$fieldpostions[]     = $request['POST'][ 'position' . $i ];
				$fieldLabelDisplay[] = $request['POST'][ 'fieldlabel' . $i ];
			}
		}
		$bulkaction = isset($request['POST']['bulkaction']) ? $request['POST']['bulkaction'] : 'enable_field';
		$shortcode_name = $request['GET']['EditShortcode'];
		switch( $bulkaction )
		{
			case 'enable_field':
				$FieldOperation->enableFields( $selectedfields , $shortcode_name );
				break;
			case 'disable_field':
				$FieldOperation->disableFields( $selectedfields , $shortcode_name );
				break;
			case 'update_order':
				$FieldOperation->updateFieldsOrder( $fieldpostions , $shortcode_name );
				break;
			
		}

		//Action 1
		//support for Ninja forms 
		//first create the  ninjs form title  in wp_ninja_forms table

		//check the selected Third party plugin

		$get_edit_shortcode = $request['GET']['EditShortcode'];
		$thirdPartyPlugin = get_option('SmackHelpDeskThirdparty_'.$get_edit_shortcode);
		$get_thirdparty_title_for_helpdesk = get_option( $get_edit_shortcode );

		$data = array();

		foreach( $request as $key => $REQUESTS )
		{
			foreach( $REQUESTS as $REQUESTS_KEY => $REQUESTS_VALUE )
			{
				$data['REQUEST'][$REQUESTS_KEY] = $REQUESTS_VALUE;
			}
		}


		$data['HelperObj'] = new SmackHelpDeskIntegrationHelper();
		$data['module'] = $data["HelperObj"]->Module;
		$data['moduleslug'] = $data['HelperObj']->ModuleSlug;
		$data['activatedplugin'] = $data["HelperObj"]->ActivatedPlugin;
		$data['activatedpluginlabel'] = $data["HelperObj"]->ActivatedPluginLabel;
		$data['plugin_url']= WP_HELPDESK_INTEGRATION_DIRECTORY;
		$data['onAction'] = 'onCreate';
		$data['siteurl'] = site_url();
		$data['nonce_key'] = $this->nonceKey;
		if(isset($data['REQUEST']['formtype']))
		{
			$data['formtype'] = $data['REQUEST']['formtype'];
		}
		else
		{
			$data['formtype'] = "post";
		}

		if(isset($data['REQUEST']['EditShortcode']) && ( $data['REQUEST']['EditShortcode'] != "" ))
		{
			$data['option'] = $data['options'] = "SmackHelpDeskShortCodeFields";
		}
		else
		{
			$data['option'] = $data['options'] = "smack_whi_{$data['activatedplugin']}_{$data['moduleslug']}_fields-tmp";
		}

		if(isset($data['REQUEST']['EditShortcode']) && ( $data['REQUEST']['EditShortcode'] != "" ) )
		{
			$data['onAction'] = 'onEditShortCode';
		}
		else
		{
			$data['onAction'] = 'onCreate';
		}

		return $data;
	}

	public function executeCreateShortcode($request)
	{
		$data['HelperObj'] = new SmackHelpDeskIntegrationHelper();
		$portal_type = $data["HelperObj"]->ActivatedPlugin;
		$module = $request['GET']['moduletype'];
		$moduleslug = rtrim( strtolower($module) , "s");
		$tmp_option = "smack_whi_{$portal_type}_{$moduleslug}_fields-tmp";
		// Function call
		$shortcodeObj = new SmackHelpDeskDataCapture();
		$OverallFunctions = new SmackHelpDeskIntegrationCoreFunctions();
		$randomstring = $OverallFunctions->CreateNewFieldShortcode( $portal_type , $module );
		$config_fields['crm'] = $portal_type;
		$users_list = get_option('smack_helpdesk_users');
		$assignee = $users_list[$portal_type]['id'][0];
		$shortcode_details['name'] = $randomstring;
		$shortcode_details['type'] = 'post';
		$shortcode_details['assignto'] = $assignee;
		$shortcode_details['isredirection'] = $is_redirection;
		$shortcode_details['urlredirection'] = $url_redirection;
		$shortcode_details['captcha'] = $google_captcha;
		$shortcode_details['crm_type'] = $portal_type;
		$shortcode_details['module'] = $module;
		$shortcode_id = $shortcodeObj->formShortCodeManager($shortcode_details);
		$config_fields = $shortcodeObj->get_crmfields_by_settings($portal_type, $module);
		foreach( $config_fields as $field )
		{
			$shortcodeObj->insertFormFieldManager( $shortcode_id, $field->field_id, $field->field_mandatory, '1', $field->field_type, $field->field_values, $field->field_sequence, $field->field_label );
		}

		$config_shortcodes = get_option("SmackHelpDeskShortCodeFields");
		$config_shortcodes[$randomstring] = $config_fields;
		wp_redirect("".WP_HELPDESK_INTEGRATION_PLUG_URL."&__module=ManageShortcodes&__action=ManageFields&portal_type=$portal_type&module=$module&EditShortcode=$randomstring&nonce_key=$this->nonceKey");
		exit;
	}

	public function executeDelete($request)
	{
		global $wpdb;
		// return an array of name value pairs to send data to the template
		$data =array();
		$delete_short = $request['GET']['DeleteShortcode'];
		$deletedata = $wpdb->get_results($wpdb->prepare("select shortcode_id from wp_smackhelpdesk_shortcode_manager where shortcode_name = %s", $delete_short));
		$deleteid = $deletedata[0]->shortcode_id;
		$delete_shortcode = $wpdb->query($wpdb->prepare("delete from wp_smackhelpdesk_shortcode_manager where shortcode_id = %d", $deleteid));
		$delete_shortcode_fields = $wpdb->query( $wpdb->prepare("delete from wp_smackhelpdesk_form_field_manager where shortcode_id = %d", $deleteid) );
		unset( $deletedata[$request["GET"]['DeleteShortcode']] );
		wp_redirect(WP_HELPDESK_INTEGRATION_PLUG_URL."&__module=ManageShortcodes&__action=view&nonce_key=$this->nonceKey");
		exit;
	}
}

class SmackHelpDeskManageShortCodeObj extends ManageShortcodesActions
{
	private static $_instance = null;
	public static function getInstance()
	{
		if( !is_object(self::$_instance) )
			self::$_instance = new SmackHelpDeskManageShortCodeObj();
		return self::$_instance;
	}


}
