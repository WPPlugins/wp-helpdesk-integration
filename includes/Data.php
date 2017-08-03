<?php 
/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class SmackHelpDeskDataCapture {
	function selectFieldManager( $portal_type = "" , $module = "" )
	{
		global $wpdb;
		$sql = "select *from wp_smackhelpdesk_field_manager";
		$fields = $wpdb->get_results($wpdb->prepare( " $sql where crm_type =%s and module_type =%s" , $portal_type,$module ) );
		if( count( $fields ) > 0 )
		{
			return true;
		}
		else
		{
			return false;
		}
	}

	function fieldManager($data , $module )
	{
		global $wpdb;
		$field_name = $data['name'];
		$field_label = $data['label'];
		$field_type = $data['type'];
		$module_type = $data['module'];
		$field_mandatory = $data['mandatory'];
		$crm_type = $data['portal_type'];
		$base_model = $data['base_model'];
		$field_ref_id = $data['field_ref_id'];
		$field_sequence = $data['sequence'];
		$field_values = $data['field_values'];

		
		$fields = $wpdb->get_results( $wpdb->prepare( "select *from wp_smackhelpdesk_field_manager where field_name=%s and module_type=%s and crm_type=%s and base_model=%s", $field_name, $module, $crm_type , $base_model ) );
		if(count($fields) == 0  )
		{
			
			$fields = $wpdb->insert( 'wp_smackhelpdesk_field_manager' , array( 'field_name' => "$field_name", 'field_label' => "$field_label", 'field_ref_id' => $field_ref_id, 'field_type' => "$field_type", 'field_values' => "$field_values", 'module_type' => "$module_type", 'field_mandatory' => $field_mandatory, 'crm_type' => "$crm_type", 'field_sequence' => $field_sequence, 'base_model' => "$base_model") );
		}
		else {
			$fields = $wpdb->update( 'wp_smackhelpdesk_field_manager' , array( 'field_label' => "$field_label", 'field_ref_id' => $field_ref_id, 'field_type' => "$field_type", 'field_values' => "$field_values", 'field_mandatory' => "$field_mandatory", 'field_sequence' => "$field_sequence", 'base_model' => "$base_model") , array( 'field_name' => "$field_name" , 'module_type' => "$module_type" , 'crm_type' => "$crm_type" ) );
		}
	}

	function updateFormSubmitStatuses( $submit_parameters , $shortcodename )
	{
		global $wpdb;
		$submit_parameters['failure_count'] = $submit_parameters['total'] - $submit_parameters['success'];

		$update_form_submits = $wpdb->query( $wpdb->prepare("update wp_smackhelpdesk_shortcode_manager set submit_count = %d, success_count = %d, failure_count = %d where shortcode_name = %s", $submit_parameters['total'], $submit_parameters['success'], $submit_parameters['failure_count'], $shortcodename));
	}

	function updateShortcodeFields( $data , $module  )
	{
		global $wpdb;
		$field_name = $data['name'];
		$field_label = $data['label'];
		$field_type = $data['type'];
		$module_type = $data['module'];
		$field_mandatory = $data['mandatory'];

		$publish = 0;
		if( $field_mandatory == 1 )
		{
			$publish = 1;
		}

		$crm_type = $data['portal_type'];
		$field_sequence = $data['sequence'];
		$field_values = $data['field_values'];

		$get_shortcodes = array();

		$get_shortcodes = $wpdb->get_results($wpdb->prepare("select * from wp_smackhelpdesk_shortcode_manager where module =%s and crm_type =%s" , $module , $crm_type) );

		$get_field_manager = $wpdb->get_results( $wpdb->prepare("select * from wp_smackhelpdesk_field_manager where module_type =%s and field_name =%s and crm_type =%s" , $module , $field_name , $crm_type) );

		foreach( $get_shortcodes as $key => $shortcodedata )
		{
			$fields = array();
			$shortcodename = $shortcodedata->shortcode_name;
			$shortcode_id = $shortcodedata->shortcode_id;

			$fields = $wpdb->get_results($wpdb->prepare("select ffm.* , sm.*  from wp_smackhelpdesk_form_field_manager as ffm inner join wp_smackhelpdesk_field_manager as fm on fm.field_id = ffm.field_id inner join wp_smackhelpdesk_shortcode_manager as sm on sm.shortcode_id = ffm.shortcode_id where fm.field_name = %s and fm.module_type = %s and shortcode_name = %s and sm.crm_type = %s", $field_name, $module, $shortcodename, $crm_type));

			$rel_id = !empty($fields) ? $fields[0]->rel_id : '';
			$field_id = !empty($get_field_manager) ? $get_field_manager[0]->field_id : '';

			if( $crm_type == $shortcodedata->crm_type && $module_type == $module )
			{
				if(count($fields) == 0)
				{

					$query = $wpdb->get_results($wpdb->prepare("insert into wp_smackhelpdesk_form_field_manager( field_id , shortcode_id , display_label , custom_field_type , custom_field_values , wp_field_mandatory , form_field_sequence , state ) VALUES (%d, %d, %s, %s, %s, %s, %d, %s)", $field_id, $shortcode_id, $field_label, $field_type, $field_values, $field_mandatory, $field_sequence, $publish));
				}
				else
				{
					$state = "";
					if( $field_mandatory == 1 )
					{
						$field_mandatory = 1;
						$state = ", state = '1'";
					}

					$query = $wpdb->get_results($wpdb->prepare("update wp_smackhelpdesk_form_field_manager set wp_field_mandatory = %s {$state} , custom_field_values = %s where rel_id = %d", $field_mandatory, $field_values, $rel_id));

					if( $field_type == 'picklist' || $field_type == 'multipicklist')
					{
						$wpdb->update( 'wp_smackhelpdesk_form_field_manager' , array( 'custom_field_values' => $field_values ) , array( 'rel_id' => $rel_id ) );
					}
				}
			}
		}
	}

	function formShortCodeManager($shortcodedata , $mode = "create")
	{
		global $wpdb;
		$shortcode_name = $shortcodedata['name'];
		$form_type = $shortcodedata['type'];
		$assigned_to = $shortcodedata['assignto'];
		$error_message = $shortcodedata['errormesg'];
		$success_message = $shortcodedata['successmesg'];
		$is_redirection = $shortcodedata['isredirection'];
		$url_redirection = $shortcodedata['urlredirection'];
		$google_captcha = $shortcodedata['captcha'];
		$module = isset($shortcodedata['module']) ? $shortcodedata['module'] : '';
		$crm_type = isset($shortcodedata['crm_type']) ? $shortcodedata['crm_type'] : '';
		$duplicate_handling = $shortcodedata['duplicate_handling'];

		$FunctionsObj = new SmackHelpDeskIntegrations();
		$get_userslist = $FunctionsObj->getUsersList();
        	$first_userid = $get_userslist['id'][0];

		if( $mode == "create" )
		{
			$shortcodemanager = $wpdb->insert( 'wp_smackhelpdesk_shortcode_manager' , array( 'shortcode_name' => "$shortcode_name" , 'form_type' => "$form_type" , 'assigned_to' => "$assigned_to" , 'error_message' => "$error_message" , 'success_message' => "$success_message" , 'is_redirection' => "$is_redirection" , 'url_redirection' => "$url_redirection" , 'google_captcha' => "$google_captcha" , 'module' => "$module" , 'crm_type' => "$crm_type" , 'Round_Robin' => "$first_userid") );
		}
		else
		{
			$shortcodemanager = $wpdb->update( 'wp_smackhelpdesk_shortcode_manager' , array( 'form_type' => "$form_type" , 'assigned_to' => "$assigned_to", 'error_message' => "$error_message" , 'success_message' => "$success_message" , 'is_redirection' => $is_redirection , 'url_redirection' => $url_redirection, 'google_captcha' => $google_captcha , 'duplicate_handling' => "$duplicate_handling") , array( 'shortcode_name' => "$shortcode_name" ) );

		}
		$lastid = $wpdb->insert_id;
		return $lastid;	
	}

	function insertFormFieldManager( $shortcode_id, $field_id, $wp_field_mandatory, $state, $custom_field_type, $custom_field_values , $form_field_sequence, $display_label)
	{
		global $wpdb;

		$forms = $wpdb->insert( 'wp_smackhelpdesk_form_field_manager' , array( 'shortcode_id' => "$shortcode_id" , 'field_id' => "$field_id" , 'wp_field_mandatory' => "$wp_field_mandatory" , 'state' => "$state" , 'custom_field_type' => "$custom_field_type" , 'custom_field_values' => "$custom_field_values" , 'form_field_sequence' => "$form_field_sequence" , 'display_label' => "$display_label") );
	}

	function get_crmfields_by_settings($portal_type, $module)
	{
		global $wpdb;
		$fields = $wpdb->get_results($wpdb->prepare("select *from wp_smackhelpdesk_field_manager where crm_type = %s and module_type = %s " , $portal_type , $module ) );
		return $fields;
	}

	function formfields_settings($shortcode_name)
	{

		global $wpdb;
		$field = $wpdb->get_results($wpdb->prepare("select a.field_type , a.field_name , a.field_label , a.field_mandatory , b.* , c.* from wp_smackhelpdesk_field_manager as a inner join wp_smackhelpdesk_form_field_manager as b on a.field_id = b.field_id inner join wp_smackhelpdesk_shortcode_manager as c on c.shortcode_id = b.shortcode_id where c.shortcode_name = %s order by b.form_field_sequence", $shortcode_name));
		$i = 0;
		$crmFields = array();
		foreach($field as $newfields) 
		{
			$crmFields['fields'][$i]['name'] = $newfields->field_name;
			if( $newfields->field_mandatory == 1 )
				$crmFields['fields'][$i]['mandatory'] = 2;//$newfields->wp_field_mandatory;
			else
				$crmFields['fields'][$i]['mandatory'] = 0;

			$crmFields['fields'][$i]['wp_mandatory'] = $newfields->wp_field_mandatory;
			$crmFields['fields'][$i]['order'] = $newfields->form_field_sequence;
			$crmFields['fields'][$i]['publish'] = $newfields->state;
			$crmFields['fields'][$i]['display_label'] = $newfields->display_label;
			$crmFields['fields'][$i]['label'] = $newfields->field_label;
			$crmFields['fields'][$i]['type'] = array( 'picklistValues' => unserialize($newfields->custom_field_values) , 'name' => $newfields->custom_field_type , 'defaultValue' => $newfields->custom_field_values );
			$i++;
		}
		return $crmFields;
	} 

	function getFormSettings( $shortcodename = "" )
	{
		global $wpdb;
		$query = "";
		$where = "";
		if( $shortcodename != "" )
		{
			$where = " where shortcode_name = '$shortcodename'";
		}
		$query = "select * from wp_smackhelpdesk_shortcode_manager";
		$sql = $query.$where;
		$results = $wpdb->get_results($sql);
		if( ( $shortcodename != "" ) && ( count( $results ) > 0 ) )
		{
			$return_results = $results[0];
			return $return_results;
		}
		else
		{
			return $results;
		}
	}
}
