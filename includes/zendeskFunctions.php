<?php

/**
 * Created by PhpStorm.
 * User: sujin
 * Date: 16/08/16
 * Time: 11:52 AM
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

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
		$domain_url = $domain_url . '/api/v2/users.json';
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


	public function coreTicketFields() {
		return array(
			0 => array(
				'name' => 'requester_name',
				'fieldname' => 'requester_name',
				'label' => 'Requester Name',
				'display_label' => 'Requester Name',
				'type'  => array("name" => 'string'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			1 => array(
				'name' => 'requester',
				'fieldname' => 'requester',
				'label' => 'Requester Email',
				'display_label' => 'Requester Email',
				'type'  => array("name" => 'email'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			2 => array(
				'name' => 'comment',
				'fieldname' => 'comment',
				'label' => 'Comment',
				'display_label' => 'Comment',
				'type'  => array("name" => 'text'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
		);
	}

	public function getCrmFields($module) {
		if($module == 'Tickets')
			$domain_url = $this->domain . '/api/v2/ticket_fields.json';
		
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
			throw new Exception("Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$fieldsArray = json_decode($response);
		$config_fields = array();
		$nestedFieldInfo = array();
		$i = 0;
			if($module == 'Tickets') {
			foreach($this->coreTicketFields() as $fieldKey => $coreFields) {
				foreach($coreFields as $key => $value) {
					$config_fields['fields'][$i][$key] = $value;
					$config_fields['fields'][ $i ]['publish']       = 1;
					$config_fields['fields'][ $i ]['order']         = $i;
				}
				$i++;
			}
		}
		if(!empty($fieldsArray)) {
			foreach ( $fieldsArray as $fields_group => $fields_list) {
				if(is_array($fields_list) && !empty($fields_list)) {
					foreach ( $fields_list as $item => $fieldInfo ) {
						if ( isset($fieldInfo->required) && $fieldInfo->required == 1 ) {
							$config_fields['fields'][ $i ]['wp_mandatory'] = 1;
							$config_fields['fields'][ $i ]['mandatory']    = 2;
						} else {
							$config_fields['fields'][ $i ]['wp_mandatory'] = 0;
							$config_fields['fields'][ $i ]['mandatory']    = 0;
						}
						if ( ! empty( $fieldInfo->system_field_options ) ) {
							$optionindex    = 0;
							$picklistValues = array();
							foreach ( $fieldInfo->system_field_options as $option_key => $option_value ) {
								$picklistValues[ $optionindex ]['id']    = $optionindex;
								$picklistValues[ $optionindex ]['label'] = $option_value->name;
								$picklistValues[ $optionindex ]['value'] = $option_value->value;
								$optionindex ++;
							}
							$config_fields['fields'][ $i ]['type'] = array(
								'name'           => 'picklist',
								'picklistValues' => $picklistValues,
							);
						} 
					 
						elseif ( $fieldInfo->type == 'integer' || $fieldInfo->type == 'decimal' ) {
							$config_fields['fields'][ $i ]['type'] = array( "name" => 'integer' );
						} elseif ( $fieldInfo->type == 'date' ) {
							$config_fields['fields'][ $i ]['type'] = array( "name" => 'date' );
						} elseif($fieldInfo->type == 'checkbox' ) {
							$config_fields['fields'][$i]['type'] = array("name" => 'boolean');
						} elseif ( $fieldInfo->type == 'description' || $fieldInfo->type == 'textarea' ) {
							$config_fields['fields'][ $i ]['type'] = array( "name" => 'text' );
						} elseif ( $fieldInfo->type == 'text' ) {
							$config_fields['fields'][ $i ]['type'] = array( "name" => 'string' );
						} else {
							$config_fields['fields'][ $i ]['type'] = array( "name" => 'string' );
						}

						$core_fields = array('subject', 'description', 'status', 'tickettype', 'priority');
						if( in_array($fieldInfo->type, $core_fields) ) {
							$config_fields['fields'][ $i ]['name'] = $fieldInfo->type;
						} else {
							if($module == 'Tickets')
								$config_fields['fields'][ $i ]['name'] = $fieldInfo->id;
							else
								$config_fields['fields'][$i]['name'] = $fieldInfo->key;
						}
						$config_fields['fields'][ $i ]['fieldname']     = $fieldInfo->type;
						$config_fields['fields'][ $i ]['label']         = $fieldInfo->title;
						$config_fields['fields'][ $i ]['field_ref_id']  = $fieldInfo->id;
						$config_fields['fields'][ $i ]['display_label'] = $fieldInfo->raw_title;
						$config_fields['fields'][ $i ]['publish']       = 1;
						$config_fields['fields'][ $i ]['order']         = $i;
						if($fieldInfo->type == 'assignee' || $fieldInfo->type == 'group') {
							unset($config_fields['fields'][$i]);
						}
						$i++;
					}
				}
			}

			$config_fields['check_duplicate'] = 0;
			$config_fields['isWidget'] = 0;
			$users_list = $this->getUsersList();
			$config_fields['assignedto'] = $users_list['id'][0];
			$config_fields['module'] = $module;			
			return $config_fields;
		}
	}

	public function extractFields($fieldInfo, $i, $module, $config_fields) {
		if ( isset($fieldInfo->required) && $fieldInfo->required == 1 ) {
			$config_fields['fields'][ $i ]['wp_mandatory'] = 1;
			$config_fields['fields'][ $i ]['mandatory']    = 2;
		} else {
			$config_fields['fields'][ $i ]['wp_mandatory'] = 0;
			$config_fields['fields'][ $i ]['mandatory']    = 0;
		}
		if ( ! empty( $fieldInfo->system_field_options ) ) {
			$optionindex    = 0;
			$picklistValues = array();
			foreach ( $fieldInfo->system_field_options as $option_key => $option_value ) {
				$picklistValues[ $optionindex ]['id']    = $optionindex;
				$picklistValues[ $optionindex ]['label'] = $option_value->name;
				$picklistValues[ $optionindex ]['value'] = $option_value->value;
				$optionindex ++;
			}
			$config_fields['fields'][ $i ]['type'] = array(
				'name'           => 'picklist',
				'picklistValues' => $picklistValues,
			);
		} 
	 
		elseif ( $fieldInfo->type == 'integer' || $fieldInfo->type == 'decimal' ) {
			$config_fields['fields'][ $i ]['type'] = array( "name" => 'integer' );
		} elseif ( $fieldInfo->type == 'date' ) {
			$config_fields['fields'][ $i ]['type'] = array( "name" => 'date' );
		} elseif ( $fieldInfo->type == 'description' || $fieldInfo->type == 'textarea' ) {
			$config_fields['fields'][ $i ]['type'] = array( "name" => 'text' );
		} elseif ( $fieldInfo->type == 'text' ) {
			$config_fields['fields'][ $i ]['type'] = array( "name" => 'string' );
		} else {
			$config_fields['fields'][ $i ]['type'] = array( "name" => $fieldInfo->type );
		}

		$config_fields['fields'][ $i ]['name']          = str_replace( " ", "_", $fieldInfo->type );
	
			$config_fields['fields'][ $i ]['fieldname']     = $fieldInfo->id;
		$config_fields['fields'][ $i ]['label']         = $fieldInfo->title;
		$config_fields['fields'][ $i ]['display_label'] = $fieldInfo->raw_title;
		$config_fields['fields'][ $i ]['publish']       = 1;
		$config_fields['fields'][ $i ]['order']         = $i;
		$i++;
		return array('count' => $i, 'fields' => $config_fields);
	}

	public function getUsersList($module = 'users') {
		$url = $this->domain . '/api/v2/' . $module . '.json';
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
			throw new Exception("Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$userInfo = json_decode($response);
		if(isset($userInfo->users[0])) {
		$lastuserInfo=$userInfo->users[0];}
		else {
		$lastuserInfo=$userInfo->users;}
		$user_details = array();
			if($lastuserInfo->role == 'admin' || $lastuserInfo->role == 'agent') {
				$user_details['user_name'][]  = $lastuserInfo->email;
				$user_details['id'][]         = $lastuserInfo->id;
				$user_details['first_name'][] = '';
				$user_details['last_name'][]  = $lastuserInfo->name;
			}
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

	public function getCompanyInfo($orgInfo) {
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activePlugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$availableCompanies = get_option('smack_' . $activePlugin . '_companies');
		$url = $this->domain . '/api/v2/organizations.json';
		$auth_string = "$this->username:$this->password";
		$domain_names = isset($orgInfo['domain_names']) ? explode(',', $orgInfo['domain_names']) : '';
		$orgName = isset($orgInfo['organization']) ? $orgInfo['organization'] : '';
		$data_array = array(
			'organization' => array(
				'name' => $orgName,
				'domain_names' => $domain_names
			)
		);
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
			#throw new Exception("Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response);
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
			#throw new Exception("Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$records = json_decode($response);
		$companyId = '';
		foreach($records->organizations as $key => $companyInfo) {
			if(isset($orgInfo['organization']) && $orgInfo['organization'] == $companyInfo->name) {
				$companyId = $companyInfo->id;
			}
		}
		return $companyId;
	}

	public function getRequesterId($requesterInfo) {
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activePlugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$availableCompanies = get_option('smack_' . $activePlugin . '_requester');
		$url = $this->domain . '/api/v2/users.json';
		$auth_string = "$this->username:$this->password";

		$data_array = array(
			'user' => array(
				'name' => $requesterInfo['requester_name'],
				'email' => $requesterInfo['requester']
			)
		);
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
			#throw new Exception("Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response);
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
			#throw new Exception("Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		$records = json_decode($response);
		foreach($records->users as $key => $customerInfo) {
			if($requesterInfo['requester'] == $customerInfo->email) {
				return $customerInfo->id;
			}
		}
	}
	
	public function createRecord($module, $submittedData )
	{
		$module = strtolower($module);
		if($module == 'tickets') {
			$slug = 'ticket';
			$url = $this->domain . '/api/v2/' . $module . '.json';
		} 
		$ch = curl_init($url);
		$auth_string = "$this->username:$this->password";

		$data_array  = array();

		if($module == 'users') {
			unset( $submittedData['user_fields']['owner_id'] );
		}
		foreach($submittedData as $key => $val) {
			if($val != '') {
				global $wpdb;
				$get_fields_info = $wpdb->get_col( $wpdb->prepare( "select field_type from wp_smackhelpdesk_field_manager where field_name = %s and field_type = %s and crm_type = %s and module_type = %s", array( $key, 'boolean', 'zendesk', $module ) ) );
				if ( !empty($get_fields_info) ) {
					if ( $get_fields_info[0] === 'boolean' && $val == 'on' ) {
						$val = true;
					} elseif ( $get_fields_info[0] === 'boolean' ) {
						$val = false;
					}
					
				} elseif ( strpos( $key, 'customer_' ) !== false ) {
					$key = str_replace( 'customer_', '', $key );
					if ( $key == 'tags' ) {
						$tags = explode( ',', $val );
					} elseif ( $key == 'organization' || $key == 'domain_names' ) {
						$organizationInfo[$key] = $val;
					} else {
						$data_array[ $slug ][ $key ] = $val;
					}
				} 
			 
				else {
					if ( $key == 'requester' || $key == 'requester_name' ) {
						$requesterInfo[$key] = $val;
					}
					if($module == 'tickets') {
						if($key == 'comment')
							$data_array[ $slug ][ $key ] = array('body' => $val);
						else
							$data_array[ $slug ][ $key ] = $val;
					} 
									}
			}
		}

		if(!empty($requesterInfo)) {
			$data_array[ $slug ]['requester'] = array(
				'name' => $requesterInfo['requester_name'],
				'email' => $requesterInfo['requester'],
				);
		}

		if(!empty($organizationInfo)) {
			$orgId = $this->getCompanyInfo( $organizationInfo );
			if($orgId)
				$data_array[ $slug ]['organization_id'] = $orgId;
		}

		if(!empty($custom_fields) && $module == 'contacts') {
			$data_array[ $slug ]['user_fields'] = $custom_fields;
		} elseif(!empty($custom_fields) && $module == 'tickets') {
			$data_array[ $slug ]['custom_fields'] = $this->reformatCustomFields($custom_fields, $module);
		}

		if(!empty($tags))
			$data_array[ $slug ]['tags'] = $tags;

		
		if($module == 'tickets') {
			unset($data_array[ $slug ]['requester_name']);
			unset($data_array[ $slug ]['owner_id']);
			$data_array[ $slug ]['type'] = $data_array[ $slug ]['tickettype'];
			unset($data_array[ $slug ]['tickettype']);
			$data_array = json_encode( $data_array, JSON_NUMERIC_CHECK );
		} else {
			unset($data_array[ $slug ]['user_fields']['owner_id']);
			$data_array = json_encode( $data_array );
		}

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
		if ($http_status != 201 && $http_status != 200){
			#throw new Exception("Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response);
		}
		if($http_status == 201 || $http_status == 200) {
			$data['result'] = "success";
			$data['failure'] = 0;
		} else {
			$data['result'] = "failure";
			$data['failure'] = 1;
			$data['reason'] = "Zendesk encountered an error. CODE: " . $http_status . " Response: " . $response; #"failed adding entry";
		}
		return $data;
	}
}
