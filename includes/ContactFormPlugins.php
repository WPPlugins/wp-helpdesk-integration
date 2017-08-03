<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

require_once(plugin_dir_path(__FILE__).'/../ConfigureIncludedPlugins.php');
class SmackHelpDeskUIHelper
{
	public function getActivePlugin()
	{
		return get_option('SmackHelpDeskIntegrationActivePortal');
	}



	public function getPluginActivationHtml( )
	{
		global $availableHelpDeskPortals;
		global $helpDeskInformations;
		$html = '<select name = "pluginselect" id ="pluginselect" onchange="SmackActiveHelpDesk( this )">';
		$select_option = "";
		foreach($availableHelpDeskPortals as $groupName => $availableConfigurations) {
			$select_option .= "<optgroup label='" . $groupName . "'>";
			foreach ( $availableConfigurations as $pluginslug => $pluginlabel ) {
				if ( $this->getActivePlugin() == $pluginslug ) {
					$select_option .= "<option value='{$pluginslug}' selected=selected > {$pluginlabel} </option>";
				} else {
					$select_option .= "<option value='{$pluginslug}' > {$pluginlabel} </option>";
				}
			}
			$select_option .= "</optgroup>";
		}
		$html .= $select_option;
		$html .= "</select>";
		return $html;
	}
}
