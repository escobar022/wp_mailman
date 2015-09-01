<?php

/* get all variables */
$custom_emails = get_option( 'wp_mailman_custom_emails' );

$act = sanitize_text_field( $_REQUEST["act"] );
$info   = sanitize_text_field( $_REQUEST["info"] );
$delid  = sanitize_text_field( $_GET["did"] );
$id     = sanitize_text_field( $_GET["id"] );

/* get all variables */
if ( $act == 'vis' ) {
//	$myFields       = array( "status" );
//	$_ARR['id']     = $id;
//	$_ARR['status'] = '1';
//	$objMem->updRow( $table_name_message, $_ARR, $myFields );
	wpmg_redirectTo( "wpmg_mailinggroup_messagelist&info=vis" );
	exit;
} else if ( $act == 'hid' ) {
//	$myFields       = array( "status" );
//	$_ARR['id']     = $id;
//	$_ARR['status'] = '0';
//	$objMem->updRow( $table_name_message, $_ARR, $myFields );
	wpmg_redirectTo( "wpmg_mailinggroup_messagelist&info=hid" );
	exit;
}


if ( $info == "saved" ) {
	wpmg_showmessages( "updated", __( "Message has been added successfully.", 'mailing-group-module' ) );
} else if ( $info == "upd" ) {
	wpmg_showmessages( "updated", __( "Message has been updated successfully.", 'mailing-group-module' ) );
} else if ( $info == "vis" ) {
	wpmg_showmessages( "updated", __( "Message has been set to visible successfully.", 'mailing-group-module' ) );
} else if ( $info == "hid" ) {
	wpmg_showmessages( "updated", __( "Message has been  set to hidden successfully.", 'mailing-group-module' ) );
} else if ( $info == "del" ) {
	wpmg_showmessages( "updated", __( "Message has been deleted successfully.", 'mailing-group-module' ) );
}

$totcount      = count( $custom_emails );
?>

<div class="wrap">
	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e( "Introduction", 'mailing-group-module' ); ?>" class="nav-tab"><?php _e( "Introduction", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab nav-tab-active" title="<?php _e( "Custom Messages", 'mailing-group-module' ); ?>"><?php _e( "Custom Messages", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab" title="<?php _e( "Admin Messages", 'mailing-group-module' ); ?>"><?php _e( "Admin Messages", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e( "Stylesheet", 'mailing-group-module' ); ?>"><?php _e( "Stylesheet", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e( "Contact", 'mailing-group-module' ); ?>"><?php _e( "Support", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e( "Help", 'mailing-group-module' ); ?>"><?php _e( "Help", 'mailing-group-module' ); ?></a>
	</h2>

	<div>&nbsp;</div>
	<a class="button add-new-h2" href="admin.php?page=wpmg_mailinggroup_messageadd&act=add"><?php _e( "New custom message", 'mailing-group-module' ); ?></a></h2>
	<p><?php _e( "When a user sends a request to join a mailing group, you can send them a customised response, for example if you would like more information from them before approving their request. Any custom messages you save when responding to a subscription request appear in the list below.", 'mailing-group-module' ); ?></p>
	<table class="wp-list-table widefat fixed" id="messagelist">
		<thead>
		<tr role="row" class="topRow">
			<th width="35%" class="sort topRow_messagelist">
				<a href="#"><?php _e( "Title", 'mailing-group-module' ); ?></a></th>
			<th width="35%"><?php _e( "Message", 'mailing-group-module' ); ?></th>
			<th width="20%"><?php _e( "Hidden/Visible", 'mailing-group-module' ); ?></th>
			<th width="8%"><?php _e( "Actions", 'mailing-group-module' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( $totcount > 0 ){
			foreach ( $custom_emails as $email_id => $custom_email_info ) {
				$title = stripslashes( $custom_email_info['title'] );

				if ( strlen( $custom_email_info['message']) > 100 ) {
					$message = wpmg_stringlength( nl2br( stripslashes( $custom_email_info['message'] ) ), 100 );
				} else {
					$message = nl2br( stripslashes($custom_email_info['message'] ) );
				}
				$visible     = $custom_email_info['visible'];
				$act         = "hid";
				$lablestatus = __( "Visible", 'mailing-group-module' );
				if ( $visible == 0 ) {
					$act         = "vis";
					$lablestatus = __( "Hidden", 'mailing-group-module' );
				}
				?>
				<tr>
					<td width="40%"><?php echo $title; ?></td>
					<td width="40%"><?php echo $message; ?></td>
					<td width="15%">
						<a class="custom_msg_visibility" data-email_id="<?php echo $email_id; ?>" data-visibility="<?php echo $visible; ?>" href="#"><?php echo $lablestatus; ?></a>

					</td>
					<td width="10%" class="last">
						<a href="admin.php?page=wpmg_mailinggroup_messageadd&act=upd&id=<?php echo $email_id; ?>" class="edit_record" title="<?php _e( "Edit", 'mailing-group-module' ); ?>"></a>|<a class="delete_record remove_custom_email" title="<?php _e( "Delete", 'mailing-group-module' ); ?>" href="#" data-email_id="<?php echo $email_id; ?>"></a>
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