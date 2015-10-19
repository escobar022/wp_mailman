<?php
/* get all variables */

$gid       = sanitize_text_field( $_REQUEST["gid"] );
$groupName = get_the_title( $gid );
$recid = sanitize_text_field( $_REQUEST["id"] );
$status_change = sanitize_text_field( $_REQUEST["stat"] );

if ( $gid == "" ) {
	wpmg_redirectTo( "wpmg_mailinggroup_list" );
}

$user_id    = "";
$name       = "";
$email      = "";
$status     = "";
$group_name = array();

$args = array(
	'post_type'   => 'mg_groups',
	'post_status' => array('publish','private'),
	'order_by'    => 'title',
	'order'       => 'DESC',
	'posts_per_page' => - 1

);

$result_groups = get_posts( $args );

$result = get_userdata( $recid );
if ( $result ) {
	$user_id  = $result->ID;
	$name     = $result->user_firstname . ' ' . $result->user_lastname;
	$email    = $result->user_email;
	$username = $result->user_login;
	$status   = get_user_meta( $user_id, "mg_user_status", true );

	$group_name_serial = get_user_meta( $user_id, "mg_user_group_subscribed", true );
	if ( empty( $group_name_serial ) ) {
		$groups_subscribed = array();
	} else {
		$groups_subscribed = $group_name_serial;
	}
}


if ( $status_change == 'hold' ) {
	update_user_meta( $recid, "mg_user_status", '0', '1' );
	wpmg_redirectTo( "wpmg_mailinggroup_memberadd&id=" . $recid . "&gid=" . $gid );
	exit;
} else if ( $status_change == 'active' ) {
	update_user_meta( $recid, "mg_user_status", '1', '0' );
	wpmg_redirectTo( "wpmg_mailinggroup_memberadd&id=" . $recid . "&gid=" . $gid );
	exit;
}

?>

<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<div class="icon32" id="icon-edit"></div>
	<h2><?php _e( "Edit Member", 'mailing-group-module' ); ?>
		<a class="button add-new-h2" href="admin.php?page=wpmg_mailinggroup_memberlist&gid=<?php echo $gid; ?>"><?php _e( "Back to ", 'mailing-group-module' ); ?><?php echo $groupName; ?></a>
	</h2>

	<div id="col-left-2">
		<div class="col-wrap">
			<div>
				<p><?php _e( "To add a new Mailing Group subscriber, please fill in the form below. This will automatically create a basic user account on your WordPress site, which will enable the subscriber to log in and update their mailing preferences later on. They will not be able to make any changes to the rest of your website or settings.", 'mailing-group-module' ); ?></p>

				<p><?php _e( "If the user you wish to add already exists on your WordPress site, please use the .", 'mailing-group-module' ); ?></p>

				<div class="form-wrap">
					<div class="form-field">
						<label for="tag-name"><?php _e( "Name", 'mailing-group-module' ); ?> : </label>
						<input type="text" size="40" id="name" name="name" value="<?php echo $name; ?>" disabled="disabled"/>
					</div>
					<div class="form-field" id="gen_username">
						<label for="tag-name"><?php _e( "Username", 'mailing-group-module' ); ?> : </label>
						<input type="text" size="40" id="username" name="username" disabled="disabled" value="<?php echo $username; ?>" />
					</div>
					<div class="form-field">
						<label for="tag-name"><?php _e( "Email Address", 'mailing-group-module' ); ?> : </label>
						<input type="text" size="40" id="email" name="email" disabled="disabled" value="<?php echo $email; ?>" /><?php echo( $act == 'upd' ? '&nbsp;' . __( "Email cannot be edited.", 'mailing-group-module' ) : "" ) ?>
					</div>

					<div class="form-field">
						<label for="tag-name">Status:</label>
						<?php
						$act         = "hold";
						$lablestatus = __( "Active", 'mailing-group-module' );
						$labledetail = __( "click to put On Hold", 'mailing-group-module' );
						if ( $status == 0 ) {
							$act         = "active";
							$lablestatus = __( "On Hold", 'mailing-group-module' );
							$labledetail = __( "click to Activate", 'mailing-group-module' );
						} ?>
						<p class="ltfloat"><?php echo $lablestatus; ?> (<a href="admin.php?page=wpmg_mailinggroup_memberadd&stat=<?php echo $act; ?>&id=<?php echo $user_id; ?>&gid=<?php echo $gid; ?>"><?php echo $labledetail; ?></a>)
						</p>

						<div class="clearbth"></div>
					</div>
					<div class="clearbth"></div>
					<div class="form-field">
						<div class="check_div">
							<input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
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
									$subscribed   = false;
									$group_format = '1';

									if ( array_key_exists( $group->ID, $groups_subscribed ) ) {
										$subscribed   = true;
										$group_format = $groups_subscribed[ $group->ID ];
									}

									?>
									<tr>
										<td width="30%">
											<?php echo $group->post_title; ?>
										</td>
										<td width="40%">
											<?php if ( $subscribed ) {
												?>
												<p class="current_status" data-group_id="<?php echo $group->ID; ?>">Yes</p>
												<input type="button" class="remove_from_group" value="Remove From Group" data-group_id="<?php echo $group->ID; ?>" />
												<p class="confirm_message" data-group_id="<?php echo $group->ID; ?>">Are you sure you want to remove user?</p>
												<input type="button" class="confirm_leave_group" value="Confirm" data-group_id="<?php echo $group->ID; ?>" />
												<input type="button" class="cancel_leave_group" value="Cancel" data-group_id="<?php echo $group->ID; ?>" />
												<?php
											} else { ?>
												<p>No</p>
												<input type="button" class="add_user_to_group" value="Add To Group" data-group_id="<?php echo $group->ID; ?>" />
												<?php
											} ?>
										</td>
										<td width="30%">
											<div class="check_div">
												<input type="hidden" class="current_format" value="<?php echo $group_format; ?>" data-group_id="<?php echo $group->ID; ?>" />

												<div class="select_format">
													<input type="radio" class="email_format_edit" name="email_format_edit_<?php echo $group->ID; ?>" data-group_id="<?php echo $group->ID; ?>" value="1" <?php echo( $group_format == '1' ? "checked" : "" ) ?>/><?php _e( "HTML", 'mailing-group-module' ); ?>
												</div>
												<div class="select_format">
													<input type="radio" class="email_format_edit" name="email_format_edit_<?php echo $group->ID; ?>" data-group_id="<?php echo $group->ID; ?>" value="2" <?php echo( $group_format == '2' ? "checked" : "" ) ?>/><?php _e( "Plain Text", 'mailing-group-module' ); ?>
												</div>

												<?php if ( $subscribed ) {
													?>
													<input type="button" class="update_group_format" value="Update Format" data-group_id="<?php echo $group->ID; ?>" />
													<?php
												} ?>

											</div>
										</td>
									</tr>
								<?php } ?>
								</tbody>
							</table>
						</div>
					</div>

					<div class="clearbth"></div>
					<p>
						<i><?php _e( "NB: On Hold is the default status for new members until they confirm their subscription by clicking the opt-in link sent by email. To make a new member active immediately, select the `Skip opt-in confirmation` option above. Please ONLY do this if you have received permission from the new member, or you may be contravening laws on Unsolicited Email.", 'mailing-group-module' ); ?></i>
					</p>

					<div class="clearbth"></div>
				</div>
			</div>
		</div>
	</div>
</div>