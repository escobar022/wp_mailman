<?php
$WPMG_SETTINGS  = get_option("WPMG_SETTINGS");
$websiteurl     = $WPMG_SETTINGS["MG_WEBSITE_URL"];
$contactaddress = $WPMG_SETTINGS["MG_CONTACT_ADDRESS"];
$supportemail   = $WPMG_SETTINGS["MG_SUPPORT_EMAIL"];
$contactphone   = $WPMG_SETTINGS["MG_SUPPORT_PHONE"];

?>
<div class="wrap">
	<h2 class="nav-tab-wrapper">
        <a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e("Introduction", 'mailing-group-module'); ?>" class="nav-tab"><?php _e("Introduction", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e("Custom Messages", 'mailing-group-module'); ?>"><?php _e("Custom Messages", 'mailing-group-module'); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab" title="<?php _e("Admin Messages", 'mailing-group-module'); ?>"><?php _e("Admin Messages", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e("Stylesheet", 'mailing-group-module'); ?>"><?php _e("Stylesheet", 'mailing-group-module'); ?></a>

		<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab nav-tab-active" title="<?php _e("Contact", 'mailing-group-module'); ?>"><?php _e("Support", 'mailing-group-module'); ?></a>
        <a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e("Help", 'mailing-group-module'); ?>"><?php _e("Help", 'mailing-group-module'); ?></a>
    </h2>
    <div>
    	<h3><?php _e("<i>WordPress Mailing Group</i> &rsaquo; Support", 'mailing-group-module'); ?></h3>
    </div>
	<div>
    	<p>
		<?php _e("If you encounter any problems with the installation or configuration of the WordPress Mailing Group plugin, please visit", 'mailing-group-module'); ?> <a target="_blank" href="<?php echo $websiteurl; ?>">Andre</a>
		<?php _e("and check the FAQ section.", 'mailing-group-module'); ?><br><br>
		<?php _e("If you should NOT find the answer to your question there, please visit:", 'mailing-group-module'); ?> <a target="_blank" href="<?php echo $websiteurl; ?>/ticket">www.wordpressmailinggroup.com/ticket</a> 
		<?php _e("and log your enquiry there, with as many details about the issue as you can provide.", 'mailing-group-module'); ?><br>
		    <br>
		<?php _e("We will get back to you as soon as possible.", 'mailing-group-module'); ?> <br><br>
		<b><?php _e("NB: Please ensure you have the latest version of the plugin installed.", 'mailing-group-module'); ?></b></p>
 	</div>
</div>