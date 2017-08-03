<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Created by PhpStorm.
 * User: sujin
 * Date: 04/08/16
 * Time: 4:39 PM
 */
class SmackHelpDeskIntegrations {

	public $domain = null;

	public $auth_token = null;

	public $username = null;

	public $password = null;

	public $result_emails;

	public $result_ids;

	public $result_products;

	public function __construct() {
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$get_freshsales_settings_info = get_option("smack_whi_{$activateplugin}_settings");
		$this->domain = $get_freshsales_settings_info['domain_url'];
		$this->username = $get_freshsales_settings_info['username'];
		$this->password = $get_freshsales_settings_info['password'];
	}

	public function testLogin( $domain_url , $login, $password )
	{
		$domain_url = $domain_url . '/helpdesk/tickets.json';
		$process = curl_init($domain_url);

		curl_setopt($process, CURLOPT_USERPWD, "$login:$password");
		curl_setopt($process, CURLOPT_HEADER, false);
		curl_setopt($process, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
		curl_setopt($process, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($process, CURLOPT_SSL_VERIFYPEER, 0);
		$tickets = curl_exec ($process);
		return $tickets;
	}

	public function getSubFields($fieldsInfo, $config_fields, $i) {
		$L2PicklistValues = $L3PicklistValues = $L2FieldOptions = $L3FieldOptions = array();
		foreach ( $fieldsInfo['sub_fields'] as $item => $fieldInfo ) {
			$level = $fieldInfo->level;
			foreach ( $fieldsInfo['choices'] as $subFieldLevel => $subFieldInfo ) {
				if(!empty($subFieldInfo) && is_object($subFieldInfo)) {
					foreach ( $subFieldInfo as $l2options => $l3options ) {
						if(!in_array($l2options, $L2FieldOptions)) {
							$L2FieldOptions[] = $l2options;
						}
						if(!empty($l3options)) {
							foreach($l3options as $key => $val) {
								if(!in_array($val, $L3FieldOptions)) {
									$L3FieldOptions[] = $val;
								}
							}
						}
					}
				}
			}
			$i = $i + 1;
			$l2optionindex = $l3optionindex = 0;
			if($level == 2) {
				foreach($L2FieldOptions as $optionItem) {
					$L2PicklistValues[$l2optionindex]['id'] = $l2optionindex;
					$L2PicklistValues[$l2optionindex]['label'] = $optionItem;
					$L2PicklistValues[$l2optionindex]['value'] = $l2optionindex;
					$l2optionindex ++;
				}
				$config_fields['fields'][ $i ]['type'] = array(
					'name'           => 'nested',
					'picklistValues' => $L2PicklistValues,
				);
			} else {
				foreach($L3FieldOptions as $optionItem) {
					$L3PicklistValues[$l3optionindex]['id'] = $l3optionindex;
					$L3PicklistValues[$l3optionindex]['label'] = $optionItem;
					$L3PicklistValues[$l3optionindex]['value'] = $l3optionindex;
					$l3optionindex ++;
				}
				$config_fields['fields'][ $i ]['type'] = array(
					'name'           => 'nested',
					'picklistValues' => $L3PicklistValues,
				);
			}

			$config_fields['fields'][$i]['wp_mandatory']  = 0;
			$config_fields['fields'][$i]['mandatory']     = 0;
			$config_fields['fields'][$i]['name']          = str_replace( " ", "_", $fieldInfo->name );
			$config_fields['fields'][$i]['fieldname']     = $fieldInfo->name;
			$config_fields['fields'][$i]['label']         = $fieldInfo->label;
			$config_fields['fields'][$i]['display_label'] = $fieldInfo->label;
			$config_fields['fields'][$i]['publish']       = 1;
			$config_fields['fields'][$i]['order']         = $fieldsInfo['position'] . 'x' . $fieldInfo->level;
		}

		return $config_fields;
	}

	public function getCrmFields($module) {
		$this->getCompanyInfo();
		if($module == 'Tickets')
			$domain_url = $this->domain . '/api/v2/ticket_fields';
		$ch = curl_init($domain_url);
		$auth_string = "$this->username:$this->password";
		curl_setopt_array($ch, array(
			CURLOPT_HTTPGET        => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_USERPWD        => $auth_string,
			CURLOPT_SSL_VERIFYPEER => FALSE,
		));

		$response = curl_exec ($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($http_status != 200){
			throw new Exception("Freshdesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$fieldsArray = json_decode($response);
		$config_fields = array();
		$nestedFieldInfo = array();
		if(!empty($fieldsArray)) {
			$i = 0;
			foreach ( $fieldsArray as $item => $fieldInfo ) {
				if($module == 'Tickets' && ($fieldInfo->name == 'status' || $fieldInfo->name == 'priority')) {
					$config_fields['fields'][$i]['wp_mandatory'] = 1;
					$config_fields['fields'][$i]['mandatory'] = 2;
				}
				elseif($fieldInfo->required_for_customers == 1 || ($module == 'Contacts' && $config_fields['fields'][$i]['name'] == 'email')) {
					$config_fields['fields'][$i]['wp_mandatory'] = 1;
					$config_fields['fields'][$i]['mandatory'] = 2;
				} else {
					$config_fields['fields'][$i]['wp_mandatory'] = 0;
					$config_fields['fields'][$i]['mandatory'] = 0;
				}
				if($fieldInfo->type == 'nested_field') {
					$nestedFieldInfo['choices'] = $fieldInfo->choices;
					$nestedFieldInfo['sub_fields'] = $fieldInfo->nested_ticket_fields;
					$nestedFieldInfo['position'] = $fieldInfo->position;
					$optionindex    = 0;
					$picklistValues = $nestedFields = $subFieldOptions = array();
					foreach ( $fieldInfo->choices as $option_key => $option_value ) {
						$picklistValues[ $optionindex ]['id'] = $optionindex;
						$picklistValues[ $optionindex ]['label'] = $option_key;
						$picklistValues[ $optionindex ]['value'] = $optionindex;
						$optionindex ++;
					}
					$config_fields['fields'][ $i ]['type'] = array(
						'name'           => 'nested',
						'picklistValues' => $picklistValues,
					);
				} elseif($fieldInfo->type == 'custom_number' || $fieldInfo->type == 'custom_decimal' || $fieldInfo->type == 'default_phone' || $fieldInfo->type == 'default_mobile') {
					$config_fields['fields'][$i]['type'] = array("name" => 'integer');
				} elseif($fieldInfo->type == 'custom_checkbox' || $fieldInfo->type == 'default_client_manager') {
					$config_fields['fields'][$i]['type'] = array("name" => 'boolean');
				} elseif($fieldInfo->type == 'custom_date') {
					$config_fields['fields'][$i]['type'] = array("name" => 'date');
				} elseif($fieldInfo->type == 'custom_paragraph' || $fieldInfo->type == 'default_description' || $fieldInfo->type == 'default_address' || $fieldInfo->type == 'default_tag_names') {
					$config_fields['fields'][$i]['type'] = array("name" => 'text');
				} elseif($fieldInfo->type == 'custom_text' || $fieldInfo->type == 'default_subject' || $fieldInfo->type == 'default_name' || $fieldInfo->type == 'default_job_title' || $fieldInfo->type == 'default_twitter_id' || $fieldInfo->type == 'default_company_name') {
					$config_fields['fields'][$i]['type'] = array("name" => 'string');
				} elseif($fieldInfo->type == 'default_requester' || $fieldInfo->type == 'default_email') {
					$config_fields['fields'][$i]['type'] = array("name" => 'email');
				} elseif($fieldInfo->type == 'default_source' || $fieldInfo->type == 'default_priority' || $fieldInfo->type == 'default_group' || $fieldInfo->type == 'default_agent') {
					$optionindex = 0;
					$picklistValues = array();
					foreach($fieldInfo->choices as $optName => $optValue)
					{
						$picklistValues[$optionindex]['id'] = $optionindex;
						$picklistValues[$optionindex]['label'] = $optName;
						$picklistValues[$optionindex]['value'] = $optValue;
						$optionindex++;
					}
					$config_fields['fields'][$i]['type'] = array('name' => 'picklist', 'picklistValues' => $picklistValues);
				} elseif($fieldInfo->type == 'default_language' || $fieldInfo->type == 'default_time_zone') {
					$optionindex = 0;
					$picklistValues = array();
					foreach($fieldInfo->choices as $optName => $optValue)
					{
						$picklistValues[$optionindex]['id'] = $optionindex;
						$picklistValues[$optionindex]['label'] = $optValue;
						$picklistValues[$optionindex]['value'] = $optName;
						$optionindex++;
					}
					$config_fields['fields'][$i]['type'] = array('name' => 'picklist', 'picklistValues' => $picklistValues);
				} elseif($fieldInfo->type == 'default_status') {
					$optionindex = 0;
					$picklistValues = array();
					foreach($fieldInfo->choices as $optName => $optValue)
					{
						$picklistValues[$optionindex]['id'] = $optionindex;
						$picklistValues[$optionindex]['label'] = $optValue[0];
						$picklistValues[$optionindex]['value'] = $optName;
						$optionindex++;
					}
					$config_fields['fields'][$i]['type'] = array('name' => 'picklist', 'picklistValues' => $picklistValues);
				} elseif(isset($fieldInfo->choices)) {
					$optionindex = 0;
					$picklistValues = array();
					foreach($fieldInfo->choices as $optName => $optValue)
					{
						$picklistValues[$optionindex]['id'] = $optionindex;
						$picklistValues[$optionindex]['label'] = $optValue;
						$picklistValues[$optionindex]['value'] = $optValue;
						$optionindex++;
					}
					$config_fields['fields'][$i]['type'] = Array ( 'name' => 'picklist', 'picklistValues' => $picklistValues );
				} else {
					$config_fields['fields'][$i]['type'] = array("name" => $fieldInfo->type);
				}


				$config_fields['fields'][ $i ]['name']          = str_replace( " ", "_", $fieldInfo->name );
				$config_fields['fields'][ $i ]['fieldname']     = $fieldInfo->name;
				$config_fields['fields'][ $i ]['label']         = $fieldInfo->label;
				$config_fields['fields'][ $i ]['display_label'] = $fieldInfo->label;
				$config_fields['fields'][ $i ]['publish']       = 1;
				$config_fields['fields'][ $i ]['order']         = $fieldInfo->position;
				$i ++;
			}
			if(!empty($nestedFieldInfo))
				$config_fields = $this->getSubFields($nestedFieldInfo, $config_fields, $i);
			$config_fields['check_duplicate'] = 0;
			$config_fields['isWidget'] = 0;
			$users_list = $this->getUsersList();
			$config_fields['assignedto'] = $users_list['id'][0];
			$config_fields['module'] = $module;
			return $config_fields;
		}
	}

	public function getUsersList($module = 'agents') {
		$url = $this->domain . '/api/v2/' . $module;
		$ch = curl_init($url);
		$auth_string = "$this->username:$this->password";
		curl_setopt_array($ch, array(
			CURLOPT_HTTPGET        => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_USERPWD        => $auth_string,
			CURLOPT_SSL_VERIFYPEER => FALSE,
		));
		$response  = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($http_status != 200){
			throw new Exception("Freshdesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$userInfo = json_decode($response);
		$user_details = array();
		if(isset($userInfo[0])){
		$lastuserInfo=$userInfo[0];}
		else{
		$lastuserInfo=$userInfo;}
			$user_details['user_name'][] = $lastuserInfo->contact->email;
			$user_details['id'][] = $lastuserInfo->id;
			$user_details['first_name'][] = '';
			$user_details['last_name'][] = $lastuserInfo->contact->name;
		return $user_details;
	}

	public function getUsersListHtml( $shortcode = "" )
	{
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$module = $HelperObj->Module;
		$moduleslug = $HelperObj->ModuleSlug;
		$activatedplugin = $HelperObj->ActivatedPlugin;
		$activatedpluginlabel = $HelperObj->ActivatedPluginLabel;
		$formObj = new SmackHelpDeskDataCapture();
		if(isset($shortcode) && ( $shortcode != "" ))
		{
			$config_fields = $formObj->getFormSettings( $shortcode );  // Get form settings
		}
		$users_list = get_option('smack_helpdesk_users');
		$users_list = $users_list[$activatedplugin];
		$html = "";
		$html = '<select name="assignedto" id="assignedto" style="min-width:69px;">';
		$content_option = "";
		if(isset($users_list['user_name']))
			for($i = 0; $i < count($users_list['user_name']) ; $i++)
			{
				$content_option.="<option id='{$users_list['id'][$i]}' value='{$users_list['id'][$i]}'";
				if($users_list['id'][$i] == $config_fields->assigned_to)
				{
					$content_option.=" selected";
				}
				$content_option.=">{$users_list['first_name'][$i]} {$users_list['last_name'][$i]}</option>";
			}
		$content_option .= "<option id='owner_rr' value='Round Robin'";
		if( $config_fields->assigned_to == 'Round Robin' )
		{
			$content_option .= "selected";
		}
		$content_option .= "> Round Robin </option>";
		$html .= $content_option;
		$html .= "</select> <span style='padding-left:15px; color:red;' id='assignedto_status'></span>";
		return $html;
	}

	public function duplicateCheckEmailField($module = 'Contacts')
	{
		if($module == 'Tickets')
			return "requester";
	}

	public function assignedToFieldId()
	{
		return "owner_id";
	}


	public function mapUserCaptureFields( $user_firstname , $user_lastname , $user_email )
	{
		$post = array();
		$post['first_name'] = $user_firstname;
		$post['last_name'] = $user_lastname;
		$post[$this->duplicateCheckEmailField()] = $user_email;
		return $post;
	}

	public function getCompanyInfo($company_name = null) {
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activePlugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$availableCompanies = get_option('smack_' . $activePlugin . '_companies');
		$url = $this->domain . '/api/v2/companies';
		$auth_string = "$this->username:$this->password";
		$data_array = array('name' => $company_name);
		$data_array = json_encode($data_array);
		ob_flush();
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_POST           => TRUE,
			CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
			CURLOPT_HEADER         => TRUE,
			CURLOPT_USERPWD        => $auth_string,
			CURLOPT_POSTFIELDS     => $data_array,
			CURLOPT_RETURNTRANSFER => TRUE,
		));
		$response  = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($http_status != 201){
			#throw new Exception("Freshdesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$ch = curl_init($url);
		curl_setopt_array($ch, array(
			CURLOPT_HTTPGET        => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_USERPWD        => $auth_string,
			CURLOPT_SSL_VERIFYPEER => FALSE,
		));
		$response  = curl_exec($ch);
		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($http_status != 200){
			#throw new Exception("Freshdesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$records = json_decode($response);
		foreach($records as $key => $companyInfo) {
			if($company_name == $companyInfo->name) {
				return $companyInfo->id;
			}
		}
	}

	public function createRecord($module, $submittedData )
	{
		$url = $this->domain . '/api/v2/' . strtolower($module);
		$ch = curl_init($url);
		$auth_string = "$this->username:$this->password";
		unset($submittedData['company']);
		$data_array = array();

		foreach($submittedData as $key => $val) {
			if($val != '') {
				if ( strpos( $key, 'smack_lb_' ) !== false ) {
					$key = str_replace( 'smack_lb_', '', $key );
				}
				global $wpdb;
				$get_fields_info = $wpdb->get_col( $wpdb->prepare( "select field_type from wp_smackhelpdesk_field_manager where field_name = %s and crm_type = %s and module_type = %s", array( $key, 'freshdesk', $module ) ) );
				if ( strpos( $key, 'custom_' ) !== false ) {
					if ( $get_fields_info[0] === 'boolean' && $val == 'on' ) {
						$val = true;
					} elseif ( $get_fields_info[0] === 'boolean' ) {
						$val = false;
					}
					
				} 
			
				elseif ( $key == 'tag_names' ) {
					$tags = explode( ',', $val );
				} elseif ( $key == 'company_name' ) {
					#TODO: Write a separate function to find the company id
					$data_array['company_id'] = $this->getCompanyInfo( $val );
				} else {
					if ( $key == 'requester' ) {
						$key = 'email';
					} elseif ( $key == 'group' ) {
						$key = 'group_id';
					} elseif ( $key == 'agent' ) {
						$key = 'responder_id';
					} elseif ( $key == 'ticket_type' ) {
						$key = 'type';
					} elseif ( $key == 'owner_id' ) {
						$key = 'responder_id';
					}

					$data_array[ $key ] = $val;
				}
			}
		}

		
		if(!empty($tags))
			$data_array['tags'] = $tags;

		foreach($data_array as $key => $value) {
			if(strpos($key, 'smack_lb_') !== false) {
				$key = str_replace('smack_lb_', '', $key);
				$data_array[$key] = $value;
			} else {
				$data_array[$key] = $value;
			}
		}

		if($module == 'Tickets') {
			$data_array = json_encode( $data_array, JSON_NUMERIC_CHECK );
		} else {
			$data_array = json_encode( $data_array );
		}

		curl_setopt_array($ch, array(
			CURLOPT_POST           => TRUE,
			CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
			CURLOPT_HEADER         => FALSE,
			CURLOPT_USERPWD        => $auth_string,
			CURLOPT_POSTFIELDS     => $data_array,
			CURLOPT_RETURNTRANSFER => TRUE,
		));
		$response  = curl_exec($ch);
		$records = json_decode($response);
	
		if( $records->description == 'Validation failed' && $records->errors[0]->field == 'email' && $records->errors[0]->code == 'duplicate_value' ) {
			$this->checkEmailPresent( 'Contacts' , $submittedData['email'] );
			if(!empty($this->result_ids)) {
				$contact_id = $this->result_ids[0];
				$records    = $this->updateEmailPresentRecord( 'Contacts', $contact_id, $data_array );
			}
		}

		$http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);
		if ($http_status != 201){
			#throw new Exception("Freshdesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		if($http_status == 201) {
			$data['result'] = "success";
			$data['failure'] = 0;
		} else {
			$data['result'] = "failure";
			$data['failure'] = 1;
			$data['reason'] = "Freshdesk encountered an error. CODE: " . $http_status . " Response: " . $response; #"failed adding entry";
		}
		return $data;
	}

	public function updateEmailPresentRecord( $module , $contact_id , $contact_info)
	{
		$url = $this->domain . '/api/v2/' . strtolower($module) . '/' .$contact_id;
		$ch = curl_init($url);
		$auth_string = "$this->username:$this->password";

		curl_setopt_array($ch, array(
			CURLOPT_CUSTOMREQUEST  => "PUT",
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_USERPWD        => $auth_string,
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_POSTFIELDS     => $contact_info,
			CURLOPT_HTTPHEADER     => array('Content-Type: application/json'),
		));
		$response  = curl_exec($ch);
		curl_close($ch);
		$records = json_decode($response);
		return $records;
	}

}
