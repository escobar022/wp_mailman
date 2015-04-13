<?php
$status = get_option( 'wpmg_mailing_license_status' );	
if($status == 'invalid'){die();}
$WPMG_SETTINGS  = get_option("WPMG_SETTINGS");
$websiteurl     = $WPMG_SETTINGS["MG_WEBSITE_URL"];
$contactaddress = $WPMG_SETTINGS["MG_CONTACT_ADDRESS"];
$supportemail   = $WPMG_SETTINGS["MG_SUPPORT_EMAIL"];
$contactphone   = $WPMG_SETTINGS["MG_SUPPORT_PHONE"];
$plugintype     = $WPMG_SETTINGS["MG_PLUGIN_TYPE"];

?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Introduction", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e("Custom Messages", 'mailing-group-module'); ?>"><?php _e("Custom Messages", 'mailing-group-module'); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab" title="<?php _e("Admin Messages", 'mailing-group-module'); ?>"><?php _e("Admin Messages", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e("Stylesheet", 'mailing-group-module'); ?>"><?php _e("Stylesheet", 'mailing-group-module'); ?></a>
        <?php if($plugintype=='PAID') { ?>
        	<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab nav-tab-active" title="<?php _e("Contact", 'mailing-group-module'); ?>"><?php _e("Premium Support", 'mailing-group-module'); ?></a>
        <?php } ?>
        <a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e("Help", 'mailing-group-module'); ?>"><?php _e("Help", 'mailing-group-module'); ?></a>
    </h2>
    <div>
    	<h3><?php _e("<i>WordPress Mailing Group</i> &rsaquo; Premium Support", 'mailing-group-module'); ?></h3>
    </div>
	<div>
    	<?php /* echo sprintf( __('<p>If you encounter any problems with the installation or configuration of the WordPress Mailing Group Premium plugin, please visit <a target="_blank" href="%s">www.wordpressmailinggroup.com</a> and check the FAQ section.<br><br>If you should NOT find the answer to your question there, please visit: <a target="_blank" href="%s">www.wordpressmailinggroup.com/ticket</a> and log your enquiry there, with as many details about the issue as you can provide.<br>Please ensure you provide your plugin serial number in the Serial Number text field, so that support can be given, as the support ticket system is exclusively for Premium plugin users.<br><br>We will get back to you as soon as possible. <br><br><b>NB: Please ensure you have the latest version of the plugin installed.</b></p>', 'mailing-group-module'), $websiteurl, $websiteurl."/ticket" ); */ ?>
    	<p>
		<?php _e("If you encounter any problems with the installation or configuration of the WordPress Mailing Group Premium plugin, please visit", 'mailing-group-module'); ?> <a target="_blank" href="<?php echo $websiteurl; ?>">www.wordpressmailinggroup.com</a> 
		<?php _e("and check the FAQ section.", 'mailing-group-module'); ?><br><br>
		<?php _e("If you should NOT find the answer to your question there, please visit:", 'mailing-group-module'); ?> <a target="_blank" href="<?php echo $websiteurl; ?>/ticket">www.wordpressmailinggroup.com/ticket</a> 
		<?php _e("and log your enquiry there, with as many details about the issue as you can provide.", 'mailing-group-module'); ?><br><br>
		<?php _e("Please ensure you provide your plugin serial number in the Serial Number text field, so that support can be given, as the support ticket system is exclusively for Premium plugin users.", 'mailing-group-module'); ?><br><br>
		<?php _e("We will get back to you as soon as possible.", 'mailing-group-module'); ?> <br><br>
		<b><?php _e("NB: Please ensure you have the latest version of the plugin installed.", 'mailing-group-module'); ?></b></p>
 	</div>
</div>