<?php
/* get all variables */
$addme       = sanitize_text_field( $_POST["addme"] );
$act         = sanitize_text_field( $_REQUEST["act"] );
$msg_success = sanitize_text_field( $_REQUEST["msg"] );
$id          = sanitize_text_field( $_REQUEST["id"] );

$_POST = stripslashes_deep( $_POST );

$admin_emails = get_option( 'wp_mailman_admin_emails' );

$submitted_fields = array( 'title', 'message_type', 'subject', 'message' );


if ( $addme == 2 ) {
	if ( $objMem->updateAdminEmail( $id, $_POST, $submitted_fields ) ) {
		wpmg_redirectTo( "wpmg_mailinggroup_adminmessageadd&act=upd&msg=saved&id=" . $id );
	} else {
		wpmg_redirectTo( "wpmg_mailinggroup_adminmessageadd&act=upd&msg=same&id=" . $id );
	}
}

if ( $act == "upd" ) {
	$admin_to_edit = $admin_emails[ $id ];
	if ( ! empty( $admin_to_edit ) ) {
		$title        = stripslashes( $admin_to_edit['title'] );
		$message_type = stripslashes( $admin_to_edit['message_type'] );
		$subject      = stripslashes( $admin_to_edit['subject'] );
		$description  = addslashes( $admin_to_edit['message'] );
		$btn          = __( "Update Message", 'mailing-group-module' );
		$addme        = 2;
	} else {
		wpmg_showmessages( "error", __( "Please Select A Valid Email Template To Edit", 'mailing-group-module' ) );
	}
}

if ( $msg_success == 'saved' ) {
	wpmg_showmessages( "updated", __( "Email template has been updated", 'mailing-group-module' ) );
} elseif ( $msg_success == 'same' ) {
	wpmg_showmessages( "error", __( "No changes detected", 'mailing-group-module' ) );
}
?>


<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e( "Introduction", 'mailing-group-module' ); ?>" class="nav-tab"><?php _e( "Introduction", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab" title="<?php _e( "Custom Messages", 'mailing-group-module' ); ?>"><?php _e( "Custom Messages", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab nav-tab-active" title="<?php _e( "Admin Messages", 'mailing-group-module' ); ?>"><?php _e( "Admin Messages", 'mailing-group-module' ); ?></a>

		<a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e( "Stylesheet", 'mailing-group-module' ); ?>"><?php _e( "Stylesheet", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e( "Contact", 'mailing-group-module' ); ?>"><?php _e( "Contact", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e( "Help", 'mailing-group-module' ); ?>"><?php _e( "Help", 'mailing-group-module' ); ?></a>
	</h2>

	<div>&nbsp;</div>
	<div class="icon32" id="icon-edit"><br /></div>
	<h2><?php _e( "Add/Edit Message", 'mailing-group-module' ); ?>
		<a class='backlink' href='admin.php?page=wpmg_mailinggroup_adminmessagelist'><?php _e( "Back to Admin Messages", 'mailing-group-module' ) ?></a>
	</h2>

	<div id="col-left">
		<div class="col-wrap">
			<div>
				<div class="form-wrap">
					<form class="validate" action="" method="post" id="addmessage">
						<div class="form-field">
							<label for="tag-name"><?php _e( "Title", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="title" name="title" value="<?php echo $title; ?>" />
						</div>
						<div class="form-field">
							<label for="tag-name"><?php _e( "Subject", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="subject" name="subject" value="<?php echo $subject; ?>" />
						</div>
						<div class="form-field">
							<label for="tag-name"><?php _e( "Message", 'mailing-group-module' ); ?> : </label>
							<textarea name="message" rows="8" cols="50" id="message"><?php echo $description; ?></textarea>
						</div>
						<div class="form-field">
							<label for="tag-name"><?php _e( "Available variables", 'mailing-group-module' ); ?> : </label>

							<div class="variableslist">
								<p>{%displayname%} = <?php _e( "User's Display Name", 'mailing-group-module' ); ?>,</p>

								<p>{%name%} = <?php _e( "User's Name", 'mailing-group-module' ); ?>,</p>

								<p>{%email%} = <?php _e( "User's Email", 'mailing-group-module' ); ?></p>

								<p>{%password%} = <?php _e( "User's Password", 'mailing-group-module' ); ?></p>

								<p>{%activation_url%} = <?php _e( "User's Activation URL", 'mailing-group-module' ); ?></p>

								<p>{%login_url%} = <?php _e( "User's Login URL", 'mailing-group-module' ); ?></p>

								<p>{%site_email%} = <?php _e( "Site's Email", 'mailing-group-module' ); ?></p>

								<p>{%site_title%} = <?php _e( "Site's Title", 'mailing-group-module' ); ?></p>

								<p>{%site_url%} = <?php _e( "Site's Web Address", 'mailing-group-module' ); ?></p>

								<p>{%group_list%} = <?php _e( "Current Group List", 'mailing-group-module' ); ?></p>

								<p>{%group_name%} = <?php _e( "Current Group Name", 'mailing-group-module' ); ?></p>
							</div>
						</div>
						<p class="submit">
							<input type="submit" value="<?php echo $btn; ?>" class="button" id="submit" name="submit" />
							<input type="hidden" name="addme" value="<?php echo $addme; ?>">
							<input type="hidden" name="id" value=<?php echo $id; ?>>
							<input type="hidden" name="message_type" value=<?php echo $message_type; ?>>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>