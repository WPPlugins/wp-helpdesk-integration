<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

include_once(WP_HELPDESK_INTEGRATION_DIRECTORY.'lib/SmackZohoSupportApi.php');

class SmackHelpDeskIntegrations{

	public $username;

	public $accesskey;

	public $authtoken;

	public $url;

	public $result_emails;

	public $result_ids;

	public $result_products;

	public function __construct()
	{
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$SettingsConfig = get_option("smack_whi_{$activateplugin}_settings");
		if(isset($_REQUEST['portal_type']))
		{
			$SettingsConfig = get_option("smack_whi_{$_REQUEST['portal_type']}_settings");
		}
		else
		{
			$SettingsConfig = get_option("smack_whi_{$activateplugin}_settings");
		}
		$this->username = $SettingsConfig['username'];
		$this->accesskey = $SettingsConfig['password'];
		$this->portalname = $SettingsConfig['portalname'];
		$this->departmentname = $SettingsConfig['departmentname'];
		$this->url = "";
		$this->authtoken = $SettingsConfig['authtoken'];
		$username=$this->username;
		$password=$this->accesskey;
		$portalname=$this->portalname;
		$departmentname=$this->departmentname;
		$authtoken=$this->authtoken;
	}

	public function login()
	{
		$client = new SmackZohoSupportApi();
		return $client;
	}

	#Get Authentication token
	public function getAuthenticationKey( $username , $password , $portalname , $departmentname)
	{
		$client = $this->login();
		$return_array = $client->getAuthenticationToken( $username , $password  );
		return $return_array;
	}



	#Construct fields for tickets
	public function coreTicketFields() {
		return array(
			0 => array(
				'name' => 'Contact Name',
				'fieldname' => 'Contact Name',
				'label' => 'Contact Name',
				'display_label' => 'Contact Name',
				'type'  => array("name" => 'string'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			1 => array(
				'name' => 'Account Name',
				'fieldname' => 'Account Name',
				'label' => 'Account Name',
				'display_label' => 'Account Name',
				'type'  => array("name" => 'string'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			2 => array(
				'name' => 'Email',
				'fieldname' => 'Email',
				'label' => 'Email',
				'display_label' => 'Email',
				'type'  => array("name" => 'email'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			3 => array(
				'name' => 'Phone',
				'fieldname' => 'Phone',
				'label' => 'Phone',
				'display_label' => 'Phone',
				'type'  => array("name" => 'text'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			4 => array(
				'name' => 'Subject',
				'fieldname' => 'Subject',
				'label' => 'Subject',
				'display_label' => 'Subject',
				'type'  => array("name" => 'text'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			5 => array(
				'name' => 'Description',
				'fieldname' => 'Description',
				'label' => 'Description',
				'display_label' => 'Description',
				'type'  => array("name" => 'text'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			6 => array(
				'name' => 'Status',
				'fieldname' => 'Status',
				'label' => 'Status',
				'display_label' => 'Status',
				'type'  => array("name" => 'picklist'),
				'choices' => array( array("name" =>'Open',"value" =>'open'),array("name" =>'On Hold',"value" => 'on hold'),array("name" =>'Escalated',"value" => 'escalated'),array("name" =>'Closed',"value" => 'closed')),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			7 => array(
				'name' => 'Product Name',
				'fieldname' => 'Product Name',
				'label' => 'Product Name',
				'display_label' => 'Product Name',
				'type'  => array("name" => 'text'),
				'wp_mandatory' => 1,
				'mandatory' => 2,
			),
			8 => array(
				'name' => 'date',
				'fieldname' => 'date',
				'label' => 'Due Date',
				'display_label' => 'Due Date',
				'type'  => array("name" => 'date'),
				'wp_mandatory' => 0,
				'mandatory' => 0,
			),
			9 => array(
				'name' => 'Priority',
				'fieldname' => 'Priority',
				'label' => 'Priority',
				'display_label' => 'Priority',
				'type'  => array("name" => 'picklist'),
				'choices' => array( array("name" =>'High',"value" =>'high'),array("name" =>'Medium',"value" => 'medium'),array("name" =>'Low',"value" => 'low')),
				'wp_mandatory' => 0,
				'mandatory' => 0,
			),
			10 => array(
				'name' => 'Channel',
				'fieldname' => 'Channel',
				'label' => 'Channel',
				'display_label' => 'Channel',
				'type'  => array("name" => 'picklist'),
				'choices' => array( array("name" =>'Phone',"value" =>'phone'),array("name" =>'Twitter',"value" => 'twitter'),array("name" =>'Email',"value" => 'email'),array("name" =>'Facebook',"value" => 'facebook'),array("name" =>'Web',"value" => 'web'),array("name" =>'Chat',"value" => 'chat'),array("name" =>'Forums',"value" => 'forums')),
				'wp_mandatory' => 0,
				'mandatory' => 0,
			),
			11 => array(
				'name' => 'Classifications',
				'fieldname' => 'Classifications',
				'label' => 'Classifications',
				'display_label' => 'Classifications',
				'type'  => array("name" => 'picklist'),
				'choices' =>array( array("name" =>'Question',"value" =>'question'),array("name" =>'Problem',"value" => 'problem'),array("name" =>'Feature',"value" => 'feature'),array("name" =>'Others',"value" => 'others')),
				'wp_mandatory' => 0,
				'mandatory' => 0,
			),
		);
	}

	#Get Helpdesk fields
	public function getCrmFields($module) {
		#Fetch all fields based on the module
		$client = $this->login();
		$config_fields = array();
		$nestedFieldInfo = array();
		$i = 0;
	
		if($module == 'Tickets') {
			$Fieldinfo = $this->coreTicketFields();}

		if($module = 'Tickets')
		{
			if ( isset($Fieldinfo['mandatory']) && $Fieldinfo['mandatory'] == 2 ) {
				$config_fields['fields'][ $i ]['wp_mandatory'] = 1;
				$config_fields['fields'][ $i ]['mandatory']    = 2;
			} else {
				$config_fields['fields'][ $i ]['wp_mandatory'] = 0;
				$config_fields['fields'][ $i ]['mandatory']    = 0;
			}

			foreach($Fieldinfo as $key => $coreFields)
			{
				if ( isset($coreFields['mandatory']) && $coreFields['mandatory'] == 2 ) {
					$config_fields['fields'][ $i ]['wp_mandatory'] = 1;
					$config_fields['fields'][ $i ]['mandatory']    = 2;
				} else {
					$config_fields['fields'][ $i ]['wp_mandatory'] = 0;
					$config_fields['fields'][ $i ]['mandatory']    = 0;
				}

				foreach($coreFields['type'] as $name => $value)
				{
					if($value == 'picklist')
					{
						$picklistValues = array();
						$optionindex    = 0;
						$picklistValues = $nestedFields = $subFieldOptions = array();
						if(isset($coreFields['choices']) ? $coreFields['choices'] : ''){
							foreach ( $coreFields['choices'] as $option_key => $option_value ) {
								$picklistValues[ $optionindex ]['id'] = $optionindex;
								$picklistValues[ $optionindex ]['label'] = $option_value['name'];
								$picklistValues[ $optionindex ]['value'] = $option_value['value'];
								$optionindex ++;
							}}
						$config_fields['fields'][ $i ]['type'] = array(
							'name'           => 'picklist',
							'picklistValues' => $picklistValues,
						);
					}
					elseif ( $value == 'integer' || $value == 'decimal' ) {
						$config_fields['fields'][ $i ]['type'] = array( "name" => 'integer' );
					} elseif ( $value == 'date' ) {
						$config_fields['fields'][ $i ]['type'] = array( "name" => 'date' );
						$config_fields['fields'][ $i ]['name']          = $value;
						$config_fields['fields'][ $i ]['fieldname']     = $value;
					} elseif($value == 'checkbox' ) {
						$config_fields['fields'][$i]['type'] = array("name" => 'boolean');
					} elseif ( $value == 'description' || $value == 'textarea' ) {
						$config_fields['fields'][ $i ]['type'] = array( "name" => 'text' );
					} elseif ( $value == 'text' ) {
						$config_fields['fields'][ $i ]['type'] = array( "name" => 'string' );
					} else {
						$config_fields['fields'][ $i ]['type'] = array( "name" => 'string' );
					}
					# If field type is date
					if($value == 'date') {
						$config_fields['fields'][ $i ]['name']          = $value;
						$config_fields['fields'][ $i ]['fieldname']     = $value;
						$config_fields['fields'][ $i ]['label']         = $coreFields['label'];
						$config_fields['fields'][ $i ]['field_ref_id']  = $coreFields['name'];
						$config_fields['fields'][ $i ]['display_label'] = $coreFields['label'];
						$config_fields['fields'][ $i ]['publish']       = 1;
						$config_fields['fields'][ $i ]['order']         = $i;
					}
					else {
						$config_fields['fields'][ $i ]['name'] = $coreFields['label'];
						if($module == 'Tickets')
							$config_fields['fields'][ $i ]['name'] = $coreFields['label'];
						else
							$config_fields['fields'][$i]['name'] = $coreFields['label'];
						if( $module == 'Contacts' )
							$config_fields['fields'][ $i ]['fieldname']     = $coreFields['label'];
						else
							$config_fields['fields'][ $i ]['fieldname']     = $coreFields['label'];
						$config_fields['fields'][ $i ]['label']         = $coreFields['label'];
						$config_fields['fields'][ $i ]['field_ref_id']  = $coreFields['name'];
						$config_fields['fields'][ $i ]['display_label'] = $coreFields['label'];
						$config_fields['fields'][ $i ]['publish']       = 1;
						$config_fields['fields'][ $i ]['order']         = $i;}
					$i++;

				}
			}
		}
		$config_fields['check_duplicate'] = 0;
		$config_fields['isWidget'] = 0;
		$config_fields['module'] = $module;
		$users_list = $this->getUsersList();
		$config_fields['assignedto'] = $users_list['id'][0];
		return $config_fields;
	}

	public function extractFields($coreFields, $i, $module, $config_fields) {
		if($module = 'Tickets')
		{
			if ( isset($coreFields['mandatory']) && $coreFields['mandatory'] == 2 ) {
				$config_fields['fields'][ $i ]['wp_mandatory'] = 1;
				$config_fields['fields'][ $i ]['mandatory']    = 2;
			} else {
				$config_fields['fields'][ $i ]['wp_mandatory'] = 0;
				$config_fields['fields'][ $i ]['mandatory']    = 0;
			}

			foreach($coreFields['type'] as $name => $value)
			{
				if($value == 'picklist')
				{
					$picklistValues = array();
					$optionindex    = 0;
					$picklistValues = $nestedFields = $subFieldOptions = array();
					if(isset($coreFields['choices']) ? $coreFields['choices'] : ''){
						foreach ( $coreFields['choices'] as $option_key => $option_value ) {
							$picklistValues[ $optionindex ]['id'] = $optionindex;
							$picklistValues[ $optionindex ]['label'] = $option_value['name'];
							$picklistValues[ $optionindex ]['value'] = $option_value['value'];
							$optionindex ++;
						}}
					$config_fields['fields'][ $i ]['type'] = array(
						'name'           => 'picklist',
						'picklistValues' => $picklistValues,
					);

				}elseif ( $value == 'integer' || $value == 'decimal' ) {
					$config_fields['fields'][ $i ]['type'] = array( "name" => 'integer' );
				} elseif ( $value == 'date' ) {
					$config_fields['fields'][ $i ]['type'] = array( "name" => 'date' );
					$config_fields['fields'][ $i ]['name']          = $value;
					$config_fields['fields'][ $i ]['fieldname']     = $value;
				} elseif ( $value == 'description' || $value == 'textarea' ) {
					$config_fields['fields'][ $i ]['type'] = array( "name" => 'text' );
				} elseif ( $value == 'text' ) {
					$config_fields['fields'][ $i ]['type'] = array( "name" => 'string' );
				} else {
					$config_fields['fields'][ $i ]['type'] = array( "name" => $value );
				}
				if($value == 'date') {
					$config_fields['fields'][ $i ]['name']          = $value;
					$config_fields['fields'][ $i ]['fieldname']     = $value;
					$config_fields['fields'][ $i ]['label']         = $coreFields['label'];
					$config_fields['fields'][ $i ]['field_ref_id']  = $coreFields['name'];
					$config_fields['fields'][ $i ]['display_label'] = $coreFields['label'];
					$config_fields['fields'][ $i ]['publish']       = 1;
					$config_fields['fields'][ $i ]['order']         = $i;
				}
				else {
					$config_fields['fields'][ $i ]['name']          = $coreFields['label'];
					if( $module == 'Contacts' )
						$config_fields['fields'][ $i ]['fieldname']     = $coreFields['label'];
					else
						$config_fields['fields'][ $i ]['fieldname']     = $coreFields['label'];
					$config_fields['fields'][ $i ]['label']         = $coreFields['label'];
					$config_fields['fields'][ $i ]['display_label'] = $coreFields['label'];
					$config_fields['fields'][ $i ]['publish']       = 1;
					$config_fields['fields'][ $i ]['order']         = $i;
					$i++;
					return array('count' => $i, 'fields' => $config_fields);
				}
			}
		}
	}

	#Get the Assignee user field
	public function getUsersList($module = 'users') {
		$client=$this->login();
		$extraparams = "&type=Activeaccounts";
		$records = $client->getrecords( "accounts" , "getrecords" , $this->authtoken , $this->portalname , $this->departmentname , "" , $extraparams );		
		if(is_array($records))
		{
			foreach($records as $key=>$value) {
				foreach($value as $key1=>$value1){
				}
			}
		}
		$user_details = array();
		if(isset($value1['row'][0])) {
		$lastuserInfo=$value1['row'][0];}
		else {
		$lastuserInfo=$value1['row']; }
		if( isset( $lastuserInfo ) ) {
				$user_details['user_name'][] = $lastuserInfo['fl'][3];
				$user_details['id'][] = $lastuserInfo['fl'][0];
				$user_details['first_name'][] = $lastuserInfo['fl'][1];
				$user_details['last_name'][] = $lastuserInfo['fl'][1];
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
				$html .= "</select> <span style='padding-left:15px; color:red;' id='assignedto_status'></span>";
		return $html;
	}

	public function duplicateCheckEmailField($module = 'Contacts')
	{
		if($module == 'Tickets')
			return "Email";
	}

	public function assignedToFieldId()
	{
		return "owner_id";
	}

	public function getAssignedToList()
	{
		$users_list = $this->getUsersList();
		for($i = 0; $i < count($users_list['user_name']) ; $i++)
		{
			$user_list_array[$users_list['user_name'][$i]] = $users_list['user_name'][$i];
		}
		return $user_list_array;
	}



	public function mapUserCaptureFields( $user_firstname , $user_lastname , $user_email )
	{
		$post = array();
		$post['first_name'] = $user_firstname;
		$post['last_name'] = $user_lastname;
		$post[$this->duplicateCheckEmailField()] = $user_email;
		return $post;
	}

	public function SmackWHIZohoSupportKeyReplacer($module_fields, $key1, $key2)
	{
		$keys = array_keys($module_fields);
		$index = array_search($key1, $keys);
		if ($index !== false) {
			$keys[$index] = $key2;
			$module_fields = array_combine($keys, $module_fields);
		}
		return $module_fields;
	}

	#create records based on the module
	public function createRecord( $module , $module_fields )
	{
		$client = $this->login();
		global $HelperObj;
		$SmackHelpDeskIntegrationHelper_Obj = new SmackHelpDeskIntegrationHelper();
		$activateplugin = $SmackHelpDeskIntegrationHelper_Obj->ActivatedPlugin;
		$moduleslug = $this->ModuleSlug = rtrim( strtolower($module) , "s");
		$config_fields = get_option("smack_whi_{$activateplugin}_{$moduleslug}_fields-tmp");
		$underscored_field = "";
		if(is_array($config_fields))
		{
			foreach($config_fields['fields'] as $key => $fields)         //To add _ for field with spaces to capture the REQUEST
			{
				if(count($exploded_fields = explode(' ', $fields['fieldname'] )) > 0) {
					foreach( $exploded_fields as $exploded_field )
					{
						$underscored_field .= $exploded_field."_";
					}
					$underscored_field = rtrim($underscored_field, "_");
				} else {
					$underscored_field = $fields['fieldname'];
				}
				$config_underscored_fields[$underscored_field] = $fields['fieldname'];
				$underscored_field = "";
			}
		}
		foreach($module_fields as $field => $value)
		{
			if( array_key_exists($field , $config_underscored_fields) )
			{
				$post_fields[$config_underscored_fields[$field]]=$value;//urlencode($value);
			}
		}
		foreach($module_fields as $key => $value)
		{
			$key = preg_replace('/_/',' ',$key);
			$module_field=array();
			$module_field[$key] = $value;
			$module_fields = $module_field;
		}
		$postfields = "<?xml version='1.0' encoding='UTF-8'?>";
		$postfields .= "<{$module}><row no='1'>";
		if( isset( $post_fields ) )
		{
			foreach( $config_fields['fields'] as $conf_key => $conf_val )
			{
				foreach( $post_fields as $post_key => $post_val )
				{
					if( $post_key == $conf_val['fieldname'])
					{
						unset( $post_fields[$post_key] );
						$post_fields[$conf_val['label']] = $post_val;
					}
				}
			}
		}
		else {
			if(is_array($config_fields)) {
				foreach( $config_fields["fields"] as $conf_key => $conf_val )
				{
					foreach( $module_fields as $module_key => $module_val )
					{
						if( $module_key == $conf_val['fieldname'])
						{
							unset( $module_fields[$module_key] );
							$module_fields[$conf_val['label']] = $module_val;
						}
					}
				}
			}
		}
		if(isset($post_fields)) {
			foreach($post_fields as $key => $value) {
				if($value != '')
					$postfields .= '<FL val="'.$key.'">'.$value.'</FL>';
			}
		} else {
			if(is_array($module_fields))
			{
				foreach($module_fields as $key => $value)
				{
					if($value != '')
						$postfields .= '<FL val="'.$key.'">'.$value.'</FL>';
				}
			}
		}
		$postfields .= "</row></{$module}>";
		$extraparams="";
		if($module == "Tickets") {
			$module = "requests";
		}
	
		$record = $client->addrecords( $module , "addrecords" , $this->authtoken , $this->portalname, $this->departmentname , $postfields , $extraparams);

		if( isset($record['result']['responsecode']) && ( $record['result']['responsecode'] == "2001" ) )
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

}
