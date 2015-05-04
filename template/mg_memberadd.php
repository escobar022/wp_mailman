<?php
/* get all variables */
$addme = sanitize_text_field( $_POST["addme"] );
$act   = sanitize_text_field( $_REQUEST["act"] );
$gid   = sanitize_text_field( $_REQUEST["gid"] );
$recid = sanitize_text_field( $_REQUEST["id"] );
$unsid = sanitize_text_field( $_REQUEST['unsid'] );
$info  = sanitize_text_field( $_REQUEST["info"] );
$_POST = stripslashes_deep( $_POST );

$btn          = __( "Submit", 'mailing-group-module' );
$id           = "";
$name         = "";
$email        = "";
$status       = "";
$group_name   = array();
$email_format = "";
$add          = "";
$hidval       = 1;

$args  = array(
	'post_type'   => 'mg_groups',
	'post_status' => 'publish',
	'meta_key'  => 'mg_group_status',
	'meta_value' => '2'
);

$query = new WP_Query( $args );
$result_groups = $query->get_posts();

/* get all variables */
 if ( $act == "upd" ) {
	echo "update";
	$result = get_userdata( $recid );

	if ( $result ) {
		$id            = $result->ID;
		$name          = $result->first_name;
		$email         = $result->user_email;
		$username      = $result->user_login;
		$status        = get_user_meta( $id, "mg_user_status", true );
		$group_name_serial =  get_user_meta( $id, "mg_user_group_subscribed", true );
		$groups_unserialized = unserialize($group_name_serial);

		if ( count( $groups_unserialized ) > 0 ) {
			foreach ( $groups_unserialized as $group_id => $email_format ) {
				$group_name[ $group_id ] = $email_format;
			}
		} else {
			$group_name = array();
		}
		$btn    = __( "Update Member", 'mailing-group-module' );
		$hidval = 2;
	}
} else if ( $act == "uns" && $unsid != '' ) {
	echo "uns";
	$group_arr_old = unserialize( get_user_meta( $recid, "mg_user_group_subscribed", true ) );
	unset( $group_arr_old[ $unsid ] );
	$grpserial = serialize( $group_arr_old );
	update_user_meta( $recid, "mg_user_group_subscribed", $grpserial );
	$objMem->updUserGroupTaxonomy( $table_name_user_taxonomy, $recid, $group_arr_old );
	wpmg_redirectTo( "wpmg_mailinggroup_memberadd&act=upd&id=$recid&gid=$gid&info=uns" );
	exit;
}

if ( $addme == 1 ) {
	$random_password    = wp_generate_password( 12, false );
	$name               = sanitize_text_field( $_POST['name'] );
	$email              = sanitize_email( $_POST['email'] );
	$username           = sanitize_text_field( $_POST['username'] );
	$auto_generate      = sanitize_text_field( $_POST['auto_generate'] );
	$confirmation_email = sanitize_text_field( $_POST['confirmation_email'] );
	$status             = sanitize_text_field( $_POST['status'] );
	if ( $auto_generate && $username == "" ) {
		$username = $email;
	}
	$username_e = username_exists( $username );
	$email_e    = email_exists( $email );
	if ( $username != '' && $email != '' ) {
		if ( is_numeric( $username_e ) ) {
			wpmg_showmessages( "error", __( "The username entered already exists in the system.", 'mailing-group-module' ) );
			$username = "";
		} else if ( is_numeric( $email_e ) ) {
			wpmg_showmessages( "error", __( "The email entered already exists in the system.", 'mailing-group-module' ) );
		} else {
			$userdata = array(
				'user_login' => $username,
				'first_name' => $name,
				'user_pass'  => $random_password,
				'user_email' => $email,
				'role'       => 'subscriber'
			);
			$user_id  = wp_insert_user( $userdata );

			if ( $user_id ) {
				$grpsArray = $objMem->getGroupSerialized( $_POST );
				$grpserial = serialize( $grpsArray );
				add_user_meta( $user_id, "mg_user_status", $status );
				add_user_meta( $user_id, "mg_user_group_subscribed", $grpserial );
			}
			if ( $confirmation_email ) {
				wpmg_sendConfirmationtoMember( $user_id, $grpsArray );
			} else {
				wp_new_user_notification( $user_id, $random_password );
			}
			wpmg_redirectTo( "wpmg_mailinggroup_memberlist&info=saved&gid=" . $gid );
			exit;
		}
	} else {
		wpmg_showmessages( "error", __( "Please enter username or email to proceed.", 'mailing-group-module' ) );
	}
} else if ( $addme == 2 ) {
	$recid  = sanitize_text_field( $_POST['id'] );
	$name   = sanitize_text_field( $_POST['name'] );
	$status = sanitize_text_field( $_POST['status'] );

	if ( $name != '' ) {
		$userdata  = array(
			'ID'         => $recid,
			'first_name' => $name
		);
		wp_update_user( $userdata );
		$statusold = get_user_meta( $recid, "mg_user_status", true );
		update_user_meta( $recid, "mg_user_status", $status, $statusold );

		$grpsArray = $objMem->getGroupSerialized( $_POST );
		$grpserial = serialize( $grpsArray );

		update_user_meta( $recid, "mg_user_group_subscribed", $grpserial );
		wpmg_redirectTo( "wpmg_mailinggroup_memberlist&info=upd&gid=" . $gid );
		
		exit;
	} else {
		wpmg_showmessages( "error", __( "Please enter username to proceed.", 'mailing-group-module' ) );
	}
}

if ( $info == "userexists" ) {
	wpmg_showmessages( "error", __( "The email entered already exists in the system.", 'mailing-group-module' ) );
} else if ( $info == "uns" ) {
	wpmg_showmessages( "updated", __( "Member has been unsubcribed from the group.", 'mailing-group-module' ) );
}
$email_format = "";

?>


<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<div class="icon32" id="icon-edit"><br /></div>
	<h2><?php _e( "Add/Edit Member", 'mailing-group-module' ); ?></h2>

	<div id="col-left-2">
		<div class="col-wrap">
			<div>
				<p><?php _e( "To add a new Mailing Group subscriber, please fill in the form below. This will automatically create a basic user account on your WordPress site, which will enable the subscriber to log in and update their mailing preferences later on. They will not be able to make any changes to the rest of your website or settings.", 'mailing-group-module' ); ?></p>

				<p><?php _e( "If the user you wish to add already exists on your WordPress site, please use the Import User page to add them to your Mailing Group. You can also import a list of names and email addresses from a CSV file (and VCF for Premium plugin users), or create a Subscription Request to add a new user to multiple Mailing Groups at the same time (multiple Mailing Groups available in Premium plugin only).", 'mailing-group-module' ); ?></p>

				<div class="form-wrap">
					<form class="validate" action="" method="post" id="addmember">
						<div class="form-field">
							<label for="tag-name"><?php _e( "Name", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="name" name="name" value="<?php echo $name; ?>" />
						</div>
						<?php if ( $act != 'upd' ) { ?>
							<div class="form-field">
								<label for="tag-name">&nbsp;</label>
								<input type="checkbox" name="auto_generate" <?php echo( $auto_generate == '1' ? "checked" : "" ); ?> value="1" id="auto_generate" />&nbsp;<?php _e( "Auto-generate WordPress username", 'mailing-group-module' ); ?>
							</div>
						<?php } ?>
						<div class="form-field" id="gen_username">
							<label for="tag-name"><?php _e( "Username", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="username" name="username" <?php echo( $act == 'upd' ? 'disabled="disabled"' : "" ) ?> value="<?php echo $username; ?>" /><?php if ( $act != 'upd' ) { ?>&nbsp;<a href="#" id="check_username" name="check_username"/><?php _e( "Check Availability", 'mailing-group-module' ); ?></a><?php } ?><?php echo( $act == 'upd' ? '&nbsp;' . __( "Username cannot be edited.", 'mailing-group-module' ) : "" ) ?>
						</div>
						<div class="form-field">
							<label for="tag-name"><?php _e( "Email Address", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="email" name="email" <?php echo( $act == 'upd' ? 'disabled="disabled"' : "" ) ?> value="<?php echo $email; ?>" /><?php echo( $act == 'upd' ? '&nbsp;' . __( "Email cannot be edited.", 'mailing-group-module' ) : "" ) ?>
						</div>
						<?php if ( $act != 'upd' ) { ?>
							<div class="form-field">
								<label for="tag-name">&nbsp;</label>

								<div><p class="ltfloat">
										<input type="radio" name="confirmation_email" <?php echo( $confirmation_email == '1' ? "checked" : ( ! isset( $confirmation_email ) ? "checked" : "" ) ); ?> class="confirmation_email" id="confirmation_email_1" value="1" /><?php _e( "Send opt-in confirmation link", 'mailing-group-module' ); ?>
									</p>

									<p class="ltfloat">
										<input type="radio" <?php echo( $confirmation_email == '0' ? "checked" : "" ); ?> name="confirmation_email" class="confirmation_email" id="confirmation_email_0" value="0" />  <?php _e( "Skip opt-in confirmation", 'mailing-group-module' ); ?>
									</p></div>
							</div>
						<?php } ?>
						<div class="form-field">
							<label for="tag-name"><?php _e( "Group Name", 'mailing-group-module' ); ?> : </label>

							<div class="check_div">
								<table class="wp-list-table widefat fixed" id="memberaddedit">
									<thead>
									<tr role="row" class="topRow">
										<th class="sort topRow_messagelist"><?php _e( "Mailing Group Name", 'mailing-group-module' ); ?></th>
										<th><?php _e( "Subscription Status", 'mailing-group-module' ); ?></th>
										<th><?php _e( "Email Format", 'mailing-group-module' ); ?></th>
									</tr>
									</thead>
									<tbody>
									<?php
									foreach ( $result_groups as $group ) {
										$checkSelected = false;
										if ( array_key_exists( $group->ID, $group_name ) ) {
											$checkSelected = true;
										}
										?>
										<tr>
											<td>

												<input type="checkbox" name="group_name[]" id="selector" value="<?php echo $group->ID; ?>" <?php echo( $checkSelected ? "checked" : ( $gid == $group->ID ? "checked" : "" ) ) ?> /><?php echo  $group->post_title; ?>
											</td>
											<td>
												<?php if ( $checkSelected ) {
													echo "Yes";
												} else {
													echo "No";
												} ?>
											</td>
											<td>
												<div class="check_div">
													<input type="radio" name="email_format_<?php echo $group->ID; ?>" <?php echo( $group_name[ $group->ID ] == '1' ? "checked" : "" ) ?> value="1" />&nbsp;<?php _e( "HTML", 'mailing-group-module' ); ?>
													<br />
													<input type="radio" <?php echo( $group_name[ $group->ID ] == '2' ? "checked" : ( count( $group_name ) == '0' ? "checked" : ( ! isset( $group_name[ $group->ID ] ) ? "checked" : "" ) ) ) ?> name="email_format_<?php echo $group->ID; ?>" value="2" />&nbsp;<?php _e( "Plain Text", 'mailing-group-module' ); ?>
												</div>
											</td>
										</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
						</div>
						<div class="form-field">
							<label for="status">&nbsp;</label>

							<div>
								<p class="ltfloat">
									<input type="radio" name="status" <?php echo( $status == '0' ? "checked" : "" ); ?> <?php echo( $status == '' ? "checked" : "" ) ?> id="status_0" value="0" />&nbsp;<?php _e( "On Hold", 'mailing-group-module' ); ?>
								</p>

								<p class="ltfloat">
									<input type="radio" <?php echo( $status == '1' ? "checked" : "" ); ?> name="status" id="status_1" value="1" <?php if ($act != "upd") { ?>disabled="disabled"<?php } ?> />&nbsp;<?php _e( "Active", 'mailing-group-module' ); ?>
								</p>
							</div>
						</div>
						<div class="clearbth"></div>
						<p>
							<i><?php _e( "NB: On Hold is the default status for new members until they confirm their subscription by clicking the opt-in link sent by email. To make a new member active immediately, select the `Skip opt-in confirmation` option above. Please ONLY do this if you have received permission from the new member, or you may be contravening laws on Unsolicited Email.", 'mailing-group-module' ); ?></i>
						</p>

						<div class="clearbth"></div>
						<?php if ( $act == 'upd' ) { ?>
							<p>
								<i><?php _e( "NB: To unsubscribe a member from a Mailing Group, de-select the checkbox next to the group name and click Update Member.", 'mailing-group-module' ); ?></i>
							</p>
						<?php } ?>
						<div class="clearbth"></div>
						<p class="submit">
							<input type="submit" value="<?php echo $btn; ?>" class="button" id="submit" name="submit" />
							<input type="hidden" name="addme" value="<?php echo $hidval;?>">
							<input type="hidden" name="id" value="<?php echo $id; ?>">
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>