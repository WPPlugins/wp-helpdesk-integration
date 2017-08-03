<?php
/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	$captObj = CallSmackHelpDeskDataCaptureObj::getInstance();
	$captObj->renderMenu();
?>

<div class="wp-common-crmwrapper">
<?php
	echo $skinny_content;
?>
</div>
