<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

include_once(WP_HELPDESK_INTEGRATION_DIRECTORY.'lib/vtwsclib/Vtiger/WSClient.php');
class SmackHelpDeskIntegrations {
	public $username;
	public $accesskey;
	public $url;
	public $result_emails;
	public $result_ids;
	public $result_products;
	public function __construct()
	{
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$SettingsConfig = get_option("smack_whi_{$activateplugin}_settings");

		$this->username = $SettingsConfig['username'];
		$this->accesskey = $SettingsConfig['accesskey'];
		$this->url = $SettingsConfig['domain_url'];

	}

	public function login()
	{
		$client = new Vtiger_WSClient($this->url);
		$login = $client->doLogin($this->username, $this->accesskey);
		return $client;
	}

	public function testLogin( $url , $username , $accesskey )
	{	

		$client = new Vtiger_WSClient($url);
		$login = $client->doLogin($username, $accesskey);

		return $login;
	}

	public function coreTicketFields() {
		return array(
	0 => array(
				'name'=>'contact_id',
				
				'fieldname' => 'Customer_name',
				'label'=>'ContactID',
				'display_label' => 'Name',
				'type'  => array("name" => 'string'),
				'wp_mandatory' => 0,
				'mandatory' => 0,
			),
	1 => array(
				'name' => 'email',
				'fieldname' => 'email',
				'label' => 'Primary Email',
				'display_label' => 'Email',
				'type'  => array("name" => 'email'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),


		);
	}

	public function getCrmFields( $module )
	{
		if($module=='Tickets')
		{
		$module='HelpDesk';
		}
			$client = $this->login();
			$recordInfo = $client->doDescribe($module);
			$config_fields = array();
			$i = 0;

			if($module == 'HelpDesk') {

				foreach($this->coreTicketFields() as $fieldKey => $coreFields) {
					foreach($coreFields as $key => $value) {
						$config_fields['fields'][$i][$key] = $value;
						$config_fields['fields'][ $i ]['publish']       = 1;
						$config_fields['fields'][ $i ]['order']         = $i;
					}
					$i++;
				}
			}
			
		if($module=='HelpDesk')
		{
			if($recordInfo)
			{

				$j=2;
				for($i=0;$i<count($recordInfo['fields']);$i++)
				{
					if($recordInfo['fields'][$i]['nullable']=="" && $recordInfo['fields'][$i]['editable']=="" ){
					}
					elseif($recordInfo['fields'][$i]['type']['name'] == 'reference'){
					}
					
					elseif($recordInfo['fields'][$i]['name'] == 'modifiedby' || $recordInfo['fields'][$i]['name'] == 'assigned_user_id' ){
					}
					else{
						if($recordInfo['fields'][$i]['type']['name'] == 'double' ){
						$recordInfo['fields'][$i]['type']['name']='text';
						}
						$config_fields['fields'][$j] = $recordInfo['fields'][$i];
						$config_fields['fields'][$j]['order'] = $j;
						$config_fields['fields'][$j]['publish'] = 1;
						$config_fields['fields'][$j]['display_label'] = $recordInfo['fields'][$i]['label'];
						if($recordInfo['fields'][$i]['mandatory']==1)
						{
							$config_fields['fields'][$j]['wp_mandatory'] = 1;
							$config_fields['fields'][$j]['mandatory'] = 2;
						}
						else
						{
							$config_fields['fields'][$j]['wp_mandatory'] = 0;
						}
						$j++;
					}
				}
				$config_fields['check_duplicate'] = 0;
				$config_fields['isWidget'] = 0;
				$config_fields['update_record'] = 0;
				$users_list = $this->getUsersList();
				$config_fields['assignedto'] = $users_list['id'][0];
				$config_fields['module'] = $module;
			}
		}
		return $config_fields;
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
		} elseif ( ! empty( $fieldInfo->custom_field_options ) ) {
			$optionindex    = 0;
			$picklistValues = array();
			foreach ( $fieldInfo->custom_field_options as $option_key => $option_value ) {
				$picklistValues[ $optionindex ]['id']    = $optionindex;
				$picklistValues[ $optionindex ]['label'] = $option_value->name;
				$picklistValues[ $optionindex ]['value'] = $option_value->value;
				$optionindex ++;
			}
			$config_fields['fields'][ $i ]['type'] = array(
				'name'           => 'picklist',
				'picklistValues' => $picklistValues,
			);
		} elseif ( $fieldInfo->type == 'integer' || $fieldInfo->type == 'decimal' ) {
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
		if( $module == 'Contacts' )
			$config_fields['fields'][ $i ]['fieldname']     = $fieldInfo->id;
		else
			$config_fields['fields'][ $i ]['fieldname']     = $fieldInfo->id;
		$config_fields['fields'][ $i ]['label']         = $fieldInfo->title;
		$config_fields['fields'][ $i ]['display_label'] = $fieldInfo->raw_title;
		$config_fields['fields'][ $i ]['publish']       = 1;
		$config_fields['fields'][ $i ]['order']         = $i;
		$i++;
		return array('count' => $i, 'fields' => $config_fields);
	}

	public function getUsersList()
	{
		$query = "select user_name, id, first_name, last_name  from Users";
		$client = $this->login();
		$records = $client->doQuery($query);
		if($records) {
			$columns = $client->getResultColumns($records);
			foreach($records as $record) {
				$user_details['user_name'][] = $record['user_name'];
				$user_details['id'][] = $record['id'];
				$user_details['first_name'][] = $record['first_name'];
				$user_details['last_name'][] = $record['last_name'];
			}
		}
		return $user_details;
	}

	public function getUsersListHtml( $shortcode = "" )
	{
		$HelperObj = new SmackHelpDeskIntegrationHelper();
		$module = $HelperObj->Module;
		$moduleslug = $HelperObj->ModuleSlug;
		if($module=='Tickets')
		{
			$module='HelpDesk';
		}
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

	public function getAssignedToList()
	{
		$users_list = $this->getUsersList();
		for($i = 0; $i < count($users_list['user_name']) ; $i++)
		{
			$user_list_array[$users_list['id'][$i]] = $users_list['first_name'][$i] ." ". $users_list['last_name'][$i];
		}
		return $user_list_array;
	}

	public function assignedToFieldId()
	{
		return "assigned_user_id";
	}

	public function mapUserCaptureFields( $user_firstname , $user_lastname , $user_email )
	{
		$post = array();
		$post['firstname'] = $user_firstname;
		$post['lastname'] = $user_lastname;
		$post[$this->duplicateCheckEmailField()] = $user_email;
		return $post;
	}

	public function createRecordOnUserCapture( $module , $module_fields )
	{
		return $this->createRecord( $module , $module_fields );
	}

	public function createRecord($module, $module_fields )
	{

	if($module=='Tickets')
	{
		$module='HelpDesk';
	}

		$client = $this->login();
		$client->debug = true;
		$record = $client->docreate( $module , $module_fields );
		if($record)
		{
			$data['result'] = "success";
			$data['failure'] = 0;
		
		}
		else
		{
			$data['result'] = "failure";
			$data['failure'] = 1;
			$data['reason'] = "failed adding entry";
		}
		return $data;
	}
	
	public function checkEmailPresent( $module , $email )
	{
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$result_emails = array();
		$result_ids = array();
		$client = $this->login();
		$email_present = "no";
			$query = "SELECT lastname, email FROM $module where email like '$email'";
			$records = $client->doQuery($query);
			if($records) {
				$columns = $client->getResultColumns($records);
				if(is_array($records))
				{
					foreach($records as $record) {
						$result_lastnames[] = $record['lastname'];
						$result_emails[] = $record['email'];
						$result_ids[] = $record['id'];

						if($email == $record['email'])
						{
							$code = $record['id'];
							$email_present = "yes";
						}
					}
				}
			}
		$this->result_emails = $result_emails;
		$this->result_ids = $result_ids;
		if($email_present == 'yes')
			return $this->result_ids[0];
		else
			return '';
	}

	function duplicateCheckEmailField()
	{
		return "email";
	}
}
