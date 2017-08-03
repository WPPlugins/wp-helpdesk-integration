<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

$SmackHelpDeskIntegrationCoreFunctionsObj = new SmackHelpDeskIntegrationCoreFunctions();
$result = $SmackHelpDeskIntegrationCoreFunctionsObj->CheckFetchedDetails();

if( !$result['status'] )
{
	echo "<div style='font-weight:bold; padding-left:20px; color:red;'> {$result['content']} </div>";
}
else
{
	$siteurl = $skinnyData['siteurl'];
	$plug_url = WP_HELPDESK_INTEGRATION_PLUG_URL;
	$field_form_action = add_query_arg( array( '__module' => 'ManageShortcodes' , '__action' => 'ManageFields', 'portal_type' => $skinnyData['REQUEST']['portal_type'], 'module' => $skinnyData['REQUEST']['module'], 'EditShortcode' => $skinnyData['REQUEST']['EditShortcode']), $plug_url );
	?>
	<form id="field-form" action="<?php echo $field_form_action .''; ?>" method="post">

		<h3 style="height: 42px;">

			<?php
			global $helpDeskInformations;
			$content = $select_option = "";
			if(isset($skinnyData['REQUEST']['EditShortcode']))
			{
				$content .= "<span id='inneroptions' style='position:relative;left:5px;margin-left:15px;'>Helpdesk Type  : ";
				$content .= "<span> ";
				foreach( $helpDeskInformations as $crm_key => $crm_value )
				{
					if(isset($skinnyData['REQUEST']['portal_type']) && ($crm_key == $skinnyData['REQUEST']['portal_type'])){
						$select_option = " {$crm_value['crmname']} ";
					}
				}
				$content .= $select_option;
				$content .= "</span>";
				$content .= "</span>";

				echo $content;
			}
			?>
			<?php
			global $helpDeskInformations;
			global $defaultActiveHelpDesk;

			$content = "";
			if(isset($skinnyData['REQUEST']['EditShortcode']))
			{
				$content .= "<span id='inneroptions' style='position:relative;left:40px;'>Module Type  : ";
				$content .= "<span> ";
				foreach( $helpDeskInformations[$skinnyData['activatedplugin']]['modulename'] as $key => $value )
				{
					if(isset($skinnyData['REQUEST']['module']) && ($skinnyData['REQUEST']['module'] == $key ) ){
						$select_option = " {$value} ";
					}
				}
				$content .= $select_option;
				$content .= "</span>";
				$content .= "</span>";
				echo $content;
			}
			?>
		</h3>
		<?php
		if(isset($skinnyData['REQUEST']['EditShortcode']) )
		{
			$skinnyData['onAction']='onEditShortCode';
			?>
			<h3 id="innerheader" style="margin-bottom: 0px;">[<?php echo sanitize_text_field($skinnyData['activatedplugin']);?>-web-form name='<?php echo $skinnyData['REQUEST']['EditShortcode'];?>']</h3>
			<?php
			$skinnyData['onAction']='onEditShortCode';
		}
		else
		{
			$skinnyData['onAction']='onCreate';
		}
		?>


		<div class="wp-common-crm-content" style="background-color: white;" >
			<div class="content" style="padding: 20px 0px;">
				<div id="settingsavedmessage" style="height: 42px; display:none; color:red;">	</div>
				<div id="savedetails" style="height: 90px; display:none; color:blue;">   </div>
				<div id="url_post_id" style="display:none; color:blue;">  </div>
				<h3 id="formtext" style=" margin:0px; padding: 10px 0px; "> <?php echo esc_html__('Form Settings' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?> :</h3>
				<table>
					<tbody>

					<tr>
					<?php
                                        $formtype= $skinnyData['formtype'];
					$content = "";
                                        $content.= "<td style='width:200px;'><b>".__("Form Type")."</b> <div style='float:right'>:</div> </td> <td><span> </span>";
                                        $content.= $formtype;
                                        $content.= "</td>";
                                        echo $content;
                                        ?>

				</tr>
					</tbody>
				</table>

				<table><tbody>
					<tr><td><br></td></tr>
					<tr>
						<td>
			<label><?php echo ("Duplicate Handling");?></label><div style='float:right'>:</div></td><td>
        <div class="tooltipd"><img style="padding-bottom:10px;"src="<?php echo esc_url(WP_HELPDESK_INTEGRATION_DIR)."images/duplicate.png"; ?>"> <span class="tooltiptext"> <a href="https://www.smackcoders.com/wp-helpdesk-integration.html" target="_blank"><?php echo esc_html__("Upgrade To Pro");?></a> </span> </div>
</td></tr>
											</tbody></table>

				<table><tbody>
					<br>
					</tr>
					<tr>
						<td>
							<label id='innertext'><?php echo esc_html__("Error Message Submission");?></label><div style='float:right;'> : </div>
						</td>
						<td style="padding-left:10px;">
							<input type="text" name="errormessage" value="<?php if(isset($config_fields->error_message)) echo $config_fields->error_message; ?>" placeholder ="<?php echo esc_html__("Sorry, submission failed. Kindly try again after some time" , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>" />
						</td>
						<td>
							<div style ="position:relative;top:-9px;">
								<a class="tooltip"  href="#" style="padding-left:8px">
									<img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/help.png">
                        <span class="tooltipPostStatus">
                        <img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/callout.gif" class="callout">
	                        <?php echo esc_html__("Message Displayed For Failed Submission." ,WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>
                        </span> </a>
							</div>
						</td>
					</tr><tr><td><br></td></tr>
					<tr>
						<td>
							<label id='innertext'><?php echo esc_html__('Success Message Submission', WP_HELPDESK_INTEGRATION_SLUG); ?></label><div style='float:right;'> : </div>
						</td>
						<td style="padding-left:10px;">
							<input type="text" name="successmessage" value="<?php if(isset($config_fields->success_message)) echo $config_fields->success_message; ?>" placeholder ="Thanks for Submitting"/>
						</td>
						<td>
							<div style ="position:relative;top:-9px;">
								<a class="tooltip" href="#" style="padding-left:8px">
									<img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/help.png">
                        <span class="tooltipPostStatus">
                        <img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/callout.gif" class="callout">
	                        <?php echo esc_html__('Message Displayed For Successful Submission.' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>
                        </span> </a>
							</div>
						</td>
					</tr>
					<tr><td><br></td></tr>
					</tbody></table>
				<table><tbody>
					<tr>
						<td style="width:199px">
							<label id='innertext'><?php echo esc_html__('Enable URL Redirection' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?> </label><div style='float:right;'> : </div>
						</td><td style="width: 90px;padding-left: 10px;">
							<div class="switch">
								<input type="checkbox" id='enableurlredirection' name="enableurlredirection" class="cmn-toggle cmn-toggle-yes-no" onclick="enableredirecturl(this.id);" value="on" <?php if(isset($config_fields->is_redirection) && ($config_fields->is_redirection == '1')){ echo "checked=checked"; } ?> />
								<label for="enableurlredirection" data-on="Yes" data-off="No"></label>
							</div>
						</td>
						<td style="padding-left:30px;">
							<input id="redirecturl" type="text" name="redirecturl" <?php if(!isset($config_fields->is_redirection) == '1'){ echo "disabled=disabled";} ?> value="<?php if(isset($config_fields->url_redirection)) echo $config_fields->url_redirection; ?>" placeholder = "<?php echo esc_attr__('Page id or Post id' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>"/>
						</td>
						<td style="padding-left:10px;">
							<div style ="position:relative;top:-9px;">
								<a class="tooltip" href="#">
									<img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/help.png">
                        <span class="tooltipPostStatus">
                        <img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/callout.gif" class="callout">
	                        <?php echo esc_html__("(Give your custom success page url post id to redirect leads)." , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>
                        </span> </a>
							</div>

						</td>
					</tr>
					<tr><td><br></td></tr>
					<tr>
						<td>
							<label id='innertext'><?php echo esc_html__("Enable Google Captcha", WP_HELPDESK_INTEGRATION_SLUG); ?> </label><div style='float:right;'> : </div>
						</td>
						<td style="padding-left:10px;">
							<div class="switch">
								<input type="checkbox" name="enablecaptcha" id="enablecaptcha"  class="cmn-toggle cmn-toggle-yes-no" value="on" <?php if(isset($config_fields->google_captcha ) && ($config_fields->google_captcha == '1'))  { echo "checked=checked"; } ?> />
								<label for="enablecaptcha" data-on="Yes" data-off="No"></label>
							</div>
						</td>
						<td style="padding-left:36px;">
							<div style="margin-left:-25px;margin-top:-11px">
								<a class="tooltip" href="#">
									<img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/help.png">
                        <span class="tooltipPostStatus">
                        <img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/callout.gif" class="callout">
	                        <?php echo esc_html__('(Enable google recaptcha feature).' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>
                        </span> </a>
							</div>

						</td>

						<!-- kjkkjkj -->
					<tr><td><br></td></tr>
					<?php
					$thirdparty_form = get_option( 'SmackHelpDeskThirdparty_'.$skinnyData['REQUEST']['EditShortcode'] );
					?>
					<tr>
						<td>
							<label><?php echo esc_html__("Choose Thirdparty Form");?> </label><div style='float:right;'> : </div>
						</td>

						<td> <select id='thirdparty_form_type_for_helpdesk' name='thirdparty_form_type_for_helpdesk' style='margin-left:10px;width:180px;' >";
								<option value='none'><?php echo esc_html__("None");?></option>
								<option value='ninjaform' disabled><?php echo esc_html__("Ninja Form");?></option>
								<option value='contactform' disabled><?php echo esc_html__("Contact Form");?></option>
								<option value='gravityform' disabled ><?php echo esc_html__("Gravity Forms");?></option>
							</select></td>

						<td style="padding-left:36px;">
							<div style="margin-left:-25px;margin-top:15px">
								<a class="tooltip" href="#">
									<img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/help.png">
                        <span class="tooltipPostStatus">
                        <img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/callout.gif" class="callout">
	                        <?php echo esc_html__('(To Enable ninja form,Contact form and Gravity form Please click Upgrade to Pro link).' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>
                        </a>
			 <a href ="https://www.smackcoders.com/wp-helpdesk-integration.html" class="free-notice" style="float:left;margin-left:10%;text-color:red;margin-top:-2%" target="_blank"><h6 style="text-color:red"><?php echo esc_html__("Upgrade To Pro");?> </h6>
</a></span>
							</div>

						</td>
						<!-- lllklkll -->
					<tr>
						<td><br></td>
					</tr>
					<tr>
						<td>
							<label><?php echo esc_html__("Thirdparty Form Title");?></label><div style='float:right;'> : </div>
						</td>
						<td style="padding-left:10px;">
							<div class="switch">
								<input type="text" visibility="hidden" disabled ></td><td>
								

 <a href ="https://www.smackcoders.com/wp-helpdesk-integration.html" class="free-notice" style="float:left;margin-left:15%;text-color:red;margin-top:-2%" target="_blank"><h6 style="text-color:red"><?php echo esc_html__("Upgrade To Pro");?> </h6>
</a></td>
							</div>
						</td>
						<td style="padding-left:36px;">
							<div style="margin-left:-250px;margin-top:-11px">
								<a class="tooltip" href="#">
									<img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/help.png">
                        <span class="tooltipPostStatus">
                        <img src="<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/callout.gif" class="callout">
	                        <?php echo esc_html__('(To Enable Thirdparty Form Title Please Click Upgrade To Pro Link).' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>
                        </span> </a>
							</div>
						</td>
					</tr>
					</tr>
					<tr>
						<td>
							<br>
							<input class="button button-primary" type="button" onclick="saveFormSettings('<?php echo esc_js($_REQUEST['EditShortcode']); ?>');" value="<?php echo esc_attr__("Save Form Settings" , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>" name="SaveFormSettings" />
						</td>
					</tr>

					</tbody>
				</table>
			</div>
		</div>

		<span style="padding:10px; color:#FFFFFF; background-color: #37707D; text-align:center; float:right; font-weight:bold; cursor:pointer;" id ="showmore"><?php echo esc_html__("Form Options" , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?> <i class="dashicons dashicons-arrow-down"></i></span>
		<span style="padding:10px; color:#FFFFFF; background-color: #37707D; text-align:center; float:right; font-weight:bold; cursor:pointer;" id ="showless"><?php echo esc_html__("Form Options" , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?> <i class="dashicons dashicons-arrow-up"></i></span>
		<br>
		<br>
		<br>

		<div class="wp-common-crm-content" style="background-color: white;" >

			<div>
				<h3 id="formtext" style=" margin:0px; padding: 10px 0px; "> <?php echo esc_html__('Field Settings' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?> :</h3>

				<select id="bulk-action-selector-top" name="bulkaction" style="margin: 0px 0px -67px;">
					<option selected="selected" value="-1"><?php echo __('Bulk Actions' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?></option>
					<option value="enable_field"><?php echo __('Enable Field' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?></option>
					<option value="disable_field"><?php echo __('Disable Field' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?></option>
					<option value="update_order"><?php echo __('Update Order' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?></option>
					<option value="enable_mandatory"disabled><?php echo esc_html__("Enable Mandatory");?></option>
					<option value="disable_mandatory" disabled><?php echo esc_html("Disable Mandatory");?></option>
					<option value="save_field_label_display" disabled><?php echo esc_html("Save Display Label");?></option>
				</select>


				<input type="hidden" id="savefields" name="savefields" value="<?php echo esc_attr__('Apply' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>"/>
				<?php
				if(isset($skinnyData['REQUEST']['EditShortcode']))
				{
					$content = "";
					$content .= "<input class='button-primary' id='field_settings' type='submit' value='".__("Apply" , WP_HELPDESK_INTEGRATION_PLUG_URL )."' style='background-color: #0074A2; border-color: #CCCCCC; color: #FFFFFF; font-weight:bold; margin-top: 30px; margin-right:78%; float:right;'  onclick =  \" return applyChangesOnHelpDeskShortCode('{$skinnyData['siteurl']}','{$skinnyData['module']}','{$skinnyData['options']}', '{$skinnyData['onAction']}')\" />";
					echo $content;
				}
				?>

				<div class="form-group"> <?php #echo esc_html($error); ?> </div>
				<?php #echo $pagination; ?>

			</div>
			<script>
				jQuery(document).ready(function() {
					jQuery( ".content" ).hide();
					jQuery( "#showless" ).hide();
					jQuery( "#showmore" ).click(function() {
						jQuery( ".content" ).show( 500 );
						jQuery( "#showless" ).show();
						jQuery( "#showmore" ).hide();
					});
					jQuery( "#showless" ).click(function() {
						jQuery( ".content" ).hide( 500 );
						jQuery( "#showless" ).hide();
						jQuery( "#showmore" ).show();
					});
				});
			</script>

			<br>
			<br>
			<div id="fieldtable">
				<?php
				$SmackHelpDeskIntegrationApplyBulkActions = new SmackHelpDeskIntegrationApplyBulkActions();
				if(isset($skinnyData['REQUEST']['EditShortcode']))
					echo $SmackHelpDeskIntegrationApplyBulkActions->HelpDeskFormFields( "SmackHelpDeskShortCodeFields" , "onEditShortCode" , $skinnyData['REQUEST']['EditShortcode'] , $skinnyData['formtype'] );
				else
					echo $SmackHelpDeskIntegrationApplyBulkActions->HelpDeskFormFields( $skinnyData['option'] , $skinnyData['onAction'] , '' , $skinnyData['formtype'] );
				?>
			</div>
		</div>
		<br>
	</form>
	<div id="loading-image" style="display: none; background:url(<?php echo WP_HELPDESK_INTEGRATION_DIR; ?>images/ajax-loaders.gif) no-repeat center #fff;"><?php echo esc_html__('Please Wait' , WP_HELPDESK_INTEGRATION_PLUG_URL ); ?>...</div>
	<?php
}
?>
