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
		$content.='<p style="color:red;font-size:20px;text-align:center;">Crm fields are not yet synchronised</p>';
	}
	else
	{
		$content .='<div id="fieldtable">';
		$content.='<table style="border: 1px solid #dddddd;width:98%;margin-bottom:26px;margin-top:-20px"><tr class="smack_highlight smack_alt" style="border-bottom: 1px solid #dddddd;"><th class="smack-field-td-middleit" align="left" style="width: 50px;"><input type="checkbox" name="selectall" id="selectall" onclick="SmackHelpDeskSelectAllFields'."('field-form','".$module."')".';" style="margin-top:-3px"/></th><th align="left" style="width: 200px;"><h5>Field Name</h5></th><th class="smack-field-td-middleit" align="left" style="width: 100px;"><h5>Show Field</h5></th><th class="smack-field-td-middleit" align="left" style="width: 100px;"><h5>Order</h5></th><th class="smack-field-td-middleit" style="width: 100px;" align="left"><h5>Mandatory</h5></th><th class="smack-field-td-middleit" style="width: 100px;" align="left"><h5>Field Label Display</h5></th> </tr>';
		$imagepath = WP_HELPDESK_INTEGRATION_DIR.'images/';
		$imagepath = esc_url( $imagepath );
		for($i=0;$i<count($config_fields['fields']);$i++)
		{
			if( isset( $config_fields['fields'][$i]['wp_mandatory'] ) && ( $config_fields['fields'][$i]['wp_mandatory']==1 ))
			{
				$madantory_checked='checked="checked"';
			}
			else
			{
				$madantory_checked="";
			}
			if(isset( $config_fields['fields'][$i]['mandatory'] ) && ($config_fields['fields'][$i]['mandatory'] == 2 ))
			{
				if($i % 2 == 1)
				$content1.='<tr class="smack_highlight smack_alt">';
				else
				$content1.='<tr class="smack_highlight">';
				$content1.='
				<td class="smack-field-td-middleit"><input type="checkbox" name="select'.$i.'" id="select'.$i.'" disabled="disabled" ></td>
				<td>'.$config_fields['fields'][$i]['label'].' *</td>
				<td class="smack-field-td-middleit">';
				if($config_fields['fields'][$i]['publish'] == 1){
					$content1.='<a class="smack_pointer" name="publish'.$i.'" id="publish'.$i.'" onclick="'."alert('This field is mandotory, cannot hide')".'">
					<img src="'.$imagepath.'tick_strict.png"/>
					</a>';
				}
				$content1.='</td>
				<td class="smack-field-td-middleit">';
				$content1.= "<input class='position-text-box' type='textbox' name='position{$i}' value='".($i+1)."' >";
				$content1.='</td>';
 				$content1.='</td> 
                                <td class="smack-field-td-middleit"><input type="checkbox" name="mandatory'.$i.'" id="mandatory'.$i.'" disabled=disabled checked=checked ></td>';
                                $content1.='<td class="smack-field-td-middleit" id="field_label_display'.$i.'"><input type="text" id="field_label_display'.$i.'" name="fieldlabel'.$i.'" value="'.$config_fields['fields'][$i]['display_label'].'"><img src="'.$imagepath.'delete-icon.png"/></td>

</tr>';
			}
			else
			{
				if($i % 2 == 1)
				$content1.='<tr class="smack_highlight smack_alt">';
				else
				$content1.='<tr class="smack_highlight">';
				$content1.='<td class="smack-field-td-middleit">';
                                if($config_fields['fields'][$i]['publish'] == 1){
                                        $content1.= '<input type="checkbox" name="select'.$i.'" id="select'.$i.'" checked=checked >';
                                }
                                else
                                {
                                        $content1.= '<input type="checkbox" name="select'.$i.'" id="select'.$i.'">';
                                }
				$content1.= '</td>
				<td>'.$config_fields['fields'][$i]['label'].'</td>
				<td class="smack-field-td-middleit">';
				if($config_fields['fields'][$i]['publish'] == 1){
					$content1.='<a class="smack_pointer" name="publish'.$i.'" id="publish'.$i.'" onclick="published('.$i.',0,'."'$siteurl'".','."'$module'".','."'$options'".','."'$onAction'".');">
					<span class="is_show_widget" style="color: #019E5A;">Yes</span>
					</a>';
				}
				else{
					$content1.='<a class="smack_pointer" name="publish'.$i.'" id="publish'.$i.'" onclick="published('.$i.',1,'."'$siteurl'".','."'$module'".','."'$options'".','."'$onAction'".');">
					<span class="not_show_widget" style="color: #FF0000;">No</span>
					</a>';
				}
				$content1.='</td>
				<td class="smack-field-td-middleit">';
				$content1.= "<input class='position-text-box' type='textbox' name='position{$i}' value='".($i+1)."' >";
				$content1.='</td>';
				$content1.=' <td class="smack-field-td-middleit">';
                                if($config_fields['fields'][$i]["wp_mandatory"] == 1)
                                {
                                        $content1 .= '<input type="checkbox" name="mandatory'.$i.'" id="mandatory'.$i.'"  checked=checked >';
                                }
                                else
                                {
                                        $content1 .= '<input type="checkbox" name="mandatory'.$i.'" id="mandatory'.$i.'" >';
                                }
                                $content1 .= '</td>';
                                $content1.='<td class="smack-field-td-middleit" id="field_label_display'.$i.'"><input type="text" id="field_label_display_'.$i.'" name ="fieldlabel'.$i.'" value="'.$config_fields['fields'][$i]['display_label'].'"> <img src="'.$imagepath.'delete-icon.png"/></td>

</tr>';
			}
		}
		$content1.="<input type='hidden' name='no_of_rows' id='no_of_rows' value={$i} />";
		$content1.= "</table></div>";
	}
		$content.=$content1;
$content .='</div>';
echo $content;
