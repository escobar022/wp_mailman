<?php
/* get all variables */
$actreq = sanitize_text_field( $_REQUEST["act"] );
$info   = sanitize_text_field( $_REQUEST["info"] );

if ( $info == "upd" ) {
	wpmg_showmessages( "updated", __( "Message has been updated successfully.", 'mailing-group-module' ) );
}

$admin_emails = get_option( 'wp_mailman_admin_emails' );

$totcount = count( $admin_emails );
?>

<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e( "Introduction", 'mailing-group-module' ); ?>" class="nav-tab"><?php _e( "Introduction", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e( "Custom Messages", 'mailing-group-module' ); ?>"><?php _e( "Custom Messages", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab nav-tab-active" title="<?php _e( "Admin Messages", 'mailing-group-module' ); ?>"><?php _e( "Admin Messages", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e( "Stylesheet", 'mailing-group-module' ); ?>"><?php _e( "Stylesheet", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e( "Contact", 'mailing-group-module' ); ?>"><?php _e( "Support", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e( "Help", 'mailing-group-module' ); ?>"><?php _e( "Help", 'mailing-group-module' ); ?></a>
	</h2>

	<div>&nbsp;</div>

	<p><?php _e( "The following emails are sent out automatically by the plugin when the events described in the title column occur. You are welcome to customise the text according to your preference. The available dynamic variables are shown when you click to edit a message.", 'mailing-group-module' ); ?></p>
	<table class="wp-list-table widefat fixed">
		<thead>
		<tr role="row" class="topRow">
			<th width="35%" class="sort topRow_messagelist"><?php _e( "Title", 'mailing-group-module' ); ?></th>
			<th width="35%"><?php _e( "Message", 'mailing-group-module' ); ?></th>
			<th width="8%"><?php _e( "Actions", 'mailing-group-module' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( $totcount > 0 ){
			foreach ( $admin_emails as $email_id => $email_info ) {
				$title = stripslashes( $email_info['title'] );
				$desc  = wpmg_stringlength( nl2br( stripslashes( $email_info['message'] ) ), 50 );
				?>
				<tr>
					<td width="40%"><?php echo $title; ?></td>
					<td width="40%"><?php echo $desc; ?></td>
					<td width="10%" class="last">
						<a href="admin.php?page=wpmg_mailinggroup_adminmessageadd&act=upd&id=<?php echo $email_id; ?>" class="edit_record" title="<?php _e( "Edit", 'mailing-group-module' ); ?>"></a>|<a class="reject_record reset_admin_email" data-email_id="<?php echo $email_id; ?>" title="<?php _e( "Reset", 'mailing-group-module' ); ?>"></a>
					</td>
				</tr>
			<?php }

		} else { ?>
		<tr>
			<td colspan="3" align="center"><?php _e( "No Message Found!", 'mailing-group-module' ); ?></td>
		<tr>
			<?php } ?>
		</tbody>
	</table>
</div>