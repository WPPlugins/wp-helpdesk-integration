<?php if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly ?>
<script>
	alert("<?php echo WP_HELPDESK_INTEGRATION_PLUG_URL; ?>"+'&__module=Settings&__action=view');
	window.href.location = "<?php echo WP_HELPDESK_INTEGRATION_PLUG_URL; ?>"+'&__module=Settings&__action=view';
</script>

<?php

/******************************************************************************************
 * Copyright (C) Smackcoders 2016 - All Rights Reserved
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * You can contact Smackcoders at email address info@smackcoders.com.
 *******************************************************************************************/

	echo WP_HELPDESK_INTEGRATION_PLUG_URL . '&__module=Settings&__action=view';

header('Location : '. WP_HELPDESK_INTEGRATION_PLUG_URL . '&__module=Settings&__action=view');

