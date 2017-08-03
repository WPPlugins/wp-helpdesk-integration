<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$content1='';
$content='
	<input type="hidden" name="field-form-hidden" value="field-form" />
	<div>';
	$i=0;
	if(!isset($config_fields['fields'][0]))
	{
		$content.='<p style="color:red;font-size:20px;text-align:center;">Helpdesk fields are not yet synchronised</p>';
	}
	else
	{
		$content .='<div id="fieldtable">';
		$content.='<table style="font-size:15px;border: 1px solid #dddddd;width:24%;margin-bottom:26px;margin-left:54%;margin-top:10px"><tr class="smack_highlight smack_alt" style="border-bottom: 1px solid #dddddd;"><th align="left" style="width: 200px;"><h5>Synced Fields:</h5></th></tr>';
		$imagepath = WP_HELPDESK_INTEGRATION_DIR.'images/';
		for($i=0;$i<count($config_fields['fields']);$i++)
		{
			if(isset($config_fields['fields'][$i]['label'])) {
				$content1 .= '<tr>
				<td>' . $config_fields['fields'][ $i ]['label'] . '</td>
				<td class="smack-field-td-middleit"></td>
				</tr>';
			}
		}
		$content1.="<input type='hidden' name='no_of_rows' id='no_of_rows' value={$i} />";
		$content1.= "</table></div>";
	}
		$content.=$content1;
$content .='</div>';
echo $content;
