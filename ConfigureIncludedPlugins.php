<?php
/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $availableHelpDeskPortals, $defaultActiveHelpDesk, $helpDeskInformations, $ThirdPartyPlugins, $custom_plugin_for_helpdesks, $SmackHelpDeskAvailableModules;
$availableHelpDeskPortals = array(
	'Available Helpdesk' => array(
		'freshdesk' => __('Freshdesk'),
		'zendesk' => __('Zendesk'),
		'zohodesk' => __('Zohodesk'),
		'Vtigertickets' => __('Vtigertickets')
	)
);
$ThirdPartyPlugins = array(
	'none' => __("None"),
	'ninjaform' => __("Ninja Forms"),
	'contactform' => __("Contact Form"),
	'gravityform' => __("Gravity Form") ,
);

$SmackHelpDeskAvailableModules = array(
	'Contacts' => __('Contacts'),
	'Tickets'  => __('Tickets'),
);

$custom_plugin_for_helpdesks = array(
	'none' => __("None"),
	'wp-members' => __("Wp-members"),
	'acf' => __("ACF") ,
	'member-press' => __("MemberPress") ,
);

$helpDeskInformations =array(
	'freshdesk' => array("Label" => "Freshdesk" , "crmname" => "Freshdesk" , "modulename" => array("Tickets" => "Tickets", "Contacts" => "Contacts") ),
	'zendesk' => array("Label" => "Zendesk" , "crmname" => "Zendesk" , "modulename" => array("Tickets" => "Tickets", "Contacts" => "Contacts") ),
	'zohodesk' => array("Label" => "Zohodesk" , "crmname" => "Zohodesk" , "modulename" => array("Tickets" => "Tickets", "Contacts" => "Contacts") ),
	'Vtigertickets' => array("Label" => "Vtigertickets" , "crmname" => "Vtigertickets" , "modulename" => array("Tickets" => "Tickets", "Contacts" => "Contacts") ),

);

$defaultActiveHelpDesk = "freshdesk";
