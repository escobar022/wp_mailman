<?php
/* get all variables */

$option_name = 'wp_mailman_custom_emails';
$custom_emails = get_option( $option_name );

$addme = sanitize_text_field( $_POST["addme"] );
$act   = sanitize_text_field( $_REQUEST["act"] );
$id    = sanitize_text_field( $_REQUEST["id"] );
$_POST = stripslashes_deep( $_POST );
$msg_success = sanitize_text_field( $_REQUEST["msg"] );

/* get all variables */
$submitted_fields = array( 'title', 'subject', 'message', 'visible' );

if ( $addme == 1 ) {
	if ( $objMem->updateEmailOption( $id, $_POST, $submitted_fields, $option_name ) ) {
		wpmg_redirectTo( "wpmg_mailinggroup_messagelist&info=saved" );
		exit;
	}else{
		wpmg_redirectTo( "wpmg_mailinggroup_messageadd&act=upd&msg=same");
	}
} else if ( $addme == 2 ) {
	if ( $objMem->updateEmailOption( $id, $_POST, $submitted_fields, $option_name ) ) {
		wpmg_redirectTo( "wpmg_mailinggroup_messagelist&info=upd" );
		exit;
	} else {
		wpmg_redirectTo( "wpmg_mailinggroup_messageadd&act=upd&msg=same&id=" . $id );
	}
}

if ( $act == "upd" ) {
	$custom_to_edit = $custom_emails[ $id ];
	$title          = stripslashes( $custom_to_edit['title'] );
	$subject        = stripslashes( $custom_to_edit['subject'] );
	$message        = addslashes( $custom_to_edit['message'] );
	$visible        = addslashes( $custom_to_edit['visible'] );
	$btn            = __( "Update Message", 'mailing-group-module' );
	$hidval         = 2;
} elseif($act == "add") {
	$id          = "";
	$title       = ( $_POST['title'] != '' ? sanitize_text_field( $_POST['title'] ) : "" );
	$subject        = ( $_POST['subject'] != '' ? sanitize_text_field( $_POST['subject'] ) : "" );
	$message = ( $_POST['message'] != '' ? sanitize_text_field( $_POST['message'] ) : "" );
	$visible      = ( $_POST['visible'] != '' ? sanitize_text_field( $_POST['visible'] ) : "" );
	$btn         = __( "Submit", 'mailing-group-module' );
	$hidval      = 1;
}

if ( $msg_success == 'same' ) {
	wpmg_showmessages( "error", __( "No changes detected", 'mailing-group-module' ) );
}

?>
<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=wpmg_mailinggroup_intro" title="<?php _e( "Introduction", 'mailing-group-module' ); ?>" class="nav-tab"><?php _e( "Introduction", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_messagelist" class="nav-tab nav-tab-active" title="<?php _e( "Custom Messages", 'mailing-group-module' ); ?>"><?php _e( "Custom Messages", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_adminmessagelist" class="nav-tab" title="<?php _e( "Admin Messages", 'mailing-group-module' ); ?>"><?php _e( "Admin Messages", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_style" class="nav-tab" title="<?php _e( "Stylesheet", 'mailing-group-module' ); ?>"><?php _e( "Stylesheet", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_contact" class="nav-tab" title="<?php _e( "Contact", 'mailing-group-module' ); ?>"><?php _e( "Contact", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_help" class="nav-tab" title="<?php _e( "Help", 'mailing-group-module' ); ?>"><?php _e( "Help", 'mailing-group-module' ); ?></a>
	</h2>

	<div>&nbsp;</div>
	<div class="icon32" id="icon-edit"><br /></div>
	<h2><?php _e( "Add/Edit Message", 'mailing-group-module' ); ?>
		<a class='backlink' href='admin.php?page=wpmg_mailinggroup_messagelist'><?php _e( "Back to Custom Messages", 'mailing-group-module' ) ?></a>
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
							<textarea name="message" rows="8" cols="50" id="message"><?php echo $message; ?></textarea>
						</div>
						<div class="form-field">
							<label for="tag-name"><?php _e( "Visibility", 'mailing-group-module' ); ?> : </label>
							<select name="visible" id="visible">
								<option value=""><?php _e( "Select", 'mailing-group-module' ); ?></option>
								<option value="0" <?php echo( $visible == '0' ? "selected" : "" ); ?>><?php _e( "Hidden", 'mailing-group-module' ); ?></option>
								<option value="1" <?php echo( $visible == '1' ? "selected" : "" ); ?>><?php _e( "Visible", 'mailing-group-module' ); ?></option>
							</select>
						</div>
						<div class="form-field">
							<label for="tag-name"><?php _e( "Available variables", 'mailing-group-module' ); ?> : </label>

							<div class="variableslist">
								<p>{%name%} = <?php _e( "User's Name", 'mailing-group-module' ); ?>,</p>

								<p>{%email%} = <?php _e( "User's Email", 'mailing-group-module' ); ?></p>

								<p>{%site_email%} = <?php _e( "Site's Email", 'mailing-group-module' ); ?></p>

								<p>{%site_title%} = <?php _e( "Site's Title", 'mailing-group-module' ); ?></p>

								<p>{%site_url%} = <?php _e( "Site's Web Address", 'mailing-group-module' ); ?></p>

								<p>{%group_name%} = <?php _e( "Current Group Name", 'mailing-group-module' ); ?></p>
							</div>
						</div>
						<p class="submit">
							<input type="submit" value="<?php echo $btn; ?>" class="button" id="submit" name="submit" />
							<input type="hidden" name="addme" value=<?php echo $hidval; ?>>
							<input type="hidden" name="id" value=<?php echo $id; ?>>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>