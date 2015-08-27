<?php
/* get all variables */
$addme = sanitize_text_field( $_POST["addme"] );
$info  = sanitize_text_field( $_REQUEST["info"] );
$_POST = stripslashes_deep( $_POST );

$btn          = __( "Submit", 'mailing-group-module' );
$id           = "";
$name         = "";
$email        = "";
$status       = "";
$group_name   = array();
$email_format = "";
$add          = 1;
$hidval       = 1;

print_r( $_POST['group_name'] );


if ( $addme == 1 ) {

	$random_password    = wp_generate_password( 12, true );
	$name               = sanitize_text_field( $_POST['first_name'] );
	$last_name          = sanitize_text_field( $_POST['last_name'] );
	$username           = sanitize_text_field( $_POST['username'] );
	$email              = sanitize_email( $_POST['email'] );
	$confirmation_email = sanitize_text_field( $_POST['confirmation_email'] );

	$status = sanitize_text_field( $_POST['status'] );


	$new_groups_subscribed = $_POST['group_name'];


	if ( $username != '' && $email != '' ) {

		if ( is_numeric( username_exists( $username ) ) ) {
			wpmg_showmessages( "error", __( "The username entered already exists in the system.", 'mailing-group-module' ) );
			$username = "";
		} else if ( is_numeric( email_exists( $email ) ) ) {
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
				foreach ( $new_groups_subscribed as $group_ID ) {

					$groups_subscribed[ $group_ID ] = $_POST[ 'email_format_' . $group_ID ];
				}
				foreach ( $groups_subscribed as $group => $format ) {
					$groups_array[] = (string) $group;
				}


				update_user_meta( $userID, 'mg_user_group_subscribed', $groups_subscribed );
				update_user_meta( $userID, 'mg_user_group_sub_arr', $groups_array );

			}


		}
	} else {
		wpmg_showmessages( "error", __( "Please enter username or email to proceed.", 'mailing-group-module' ) );
	}


//			if ( $confirmation_email ) {
//				wpmg_sendConfirmationtoMember( $user_id, $grpsArray );
//			} else {
//				wp_new_user_notification( $user_id, $random_password );
//			}
//			wpmg_redirectTo( "wpmg_mailinggroup_memberlist&info=saved&gid=" . $gid );
//			exit;
//		}

}

//if ( $info == "userexists" ) {
//	wpmg_showmessages( "error", __( "The email entered already exists in the system.", 'mailing-group-module' ) );
//}

$email_format = "";

$args = array(
	'post_type'   => 'mg_groups',
	'post_status' => 'publish',
	'order_by'    => 'title',
	'order'       => 'DESC',
);

$query         = new WP_Query( $args );
$result_groups = $query->get_posts();
?>

<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
	<h2 class="nav-tab-wrapper">
		<a href="admin.php?page=wpmg_mailinggroup_requestmanagerlist" title="<?php _e( "Subscription Request Manager", 'mailing-group-module' ); ?>" class="nav-tab"><?php _e( "Subscription Request Manager", 'mailing-group-module' ); ?></a>
		<a href="admin.php?page=wpmg_mailinggroup_requestmanageradd" class="nav-tab nav-tab-active" title="<?php _e( "Add New Subscriber/User", 'mailing-group-module' ); ?>"><?php _e( "Add New Subscriber/User", 'mailing-group-module' ); ?></a>
	</h2>

	<div id="col-left-2">
		<div class="col-wrap">
			<div class="div800">
				<p><?php _e( "Fill out the form below to add a subscriber to a mailing group.<br>They will then be sent an email to confirm that they are now a subscriber.<br>NB: Please only add subscribers here if you have their permission already.", 'mailing-group-module' ); ?></p>

				<p>If the user you wish to add already exists on your WordPress site, add via
					<a href="admin.php?page=wpmg_mailinggroup_list">Mailing Groups</a></p>

				<div class="form-wrap">
					<form class="validate" action="" method="post" id="addmember">

						<div class="form-field">
							<label for="first_name"><?php _e( "First Name", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="first_name" name="first_name" value="<?php echo $first_name; ?>" />
						</div>

						<div class="form-field">
							<label for="last_name"><?php _e( "Last Name", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="last_name" name="last_name" value="<?php echo $last_name; ?>" />
						</div>

						<div class="form-field" id="gen_username">
							<label for="tag-name"><?php _e( "Username", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="username" name="username" value="" /><a href="#" id="check_username" name="check_username" /><?php _e( "Check Availability", 'mailing-group-module' ); ?></a>
						</div>

						<div class="form-field">
							<label for="tag-name"><?php _e( "Email Address", 'mailing-group-module' ); ?> : </label>
							<input type="text" size="40" id="email" name="email" value="<?php echo $email; ?>" />
						</div>

						<div class="form-field">
							<label for="tag-name">Opt In:</label>

							<div>
								<p class="ltfloat">
									<input type="radio" name="confirmation_email" <?php echo( $confirmation_email == '1' ? "checked" : ( ! isset( $confirmation_email ) ? "checked" : "" ) ); ?> class="confirmation_email" id="confirmation_email_1" value="1" /><?php _e( "Send opt-in confirmation link", 'mailing-group-module' ); ?>
								</p>

								<p class="ltfloat">
									<input type="radio" <?php echo( $confirmation_email == '0' ? "checked" : "" ); ?> name="confirmation_email" class="confirmation_email" id="confirmation_email_0" value="0" /> <?php _e( "Skip opt-in confirmation", 'mailing-group-module' ); ?>
								</p>
							</div>
						</div>
						<div class="form-field">
							<label for="status">Status:</label>

							<div>
								<p class="ltfloat">
									<input type="radio" name="status" <?php echo( $status == '0' ? "checked" : "" ); ?> <?php echo( $status == '' ? "checked" : "" ) ?> id="status_0" value="0" />&nbsp;<?php _e( "On Hold", 'mailing-group-module' ); ?>
								</p>

								<p class="ltfloat">
									<input type="radio" <?php echo( $status == '1' ? "checked" : "" ); ?> name="status" id="status_1" value="1" disabled="disabled" />&nbsp;<?php _e( "Active", 'mailing-group-module' ); ?>
								</p>
							</div>
						</div>
						<div class="clearbth"></div>
						<p>
							<i><?php _e( "NB: On Hold is the default status for new members until they confirm their subscription by clicking the opt-in link sent by email. To make a new member active immediately, select the `Skip opt-in confirmation` option above. Please ONLY do this if you have received permission from the new member, or you may be contravening laws on Unsolicited Email.", 'mailing-group-module' ); ?></i>
						</p>

						<div class="form-field">
							<div class="check_div">
								<table class="wp-list-table widefat fixed mg_groups" id="memberaddedit">
									<thead>
									<tr>
										<th class="sort topRow_messagelist"><?php _e( "Mailing Group Name", 'mailing-group-module' ); ?></th>
										<th><?php _e( "Email Format", 'mailing-group-module' ); ?></th>
									</tr>
									</thead>
									<tbody>
									<?php
									foreach ( $result_groups as $group ) {
										$checkSelected = false;
										?>
										<tr>
											<td>
												<input type="checkbox" name="group_name[]" id="selector" value="<?php echo $group->ID; ?>" /><?php echo $group->post_title; ?>
											</td>
											<td>
												<div class="check_div">
													<input type="radio" name="email_format_<?php echo $group->ID; ?>" value="1" />&nbsp;<?php _e( "HTML", 'mailing-group-module' ); ?>
													<br />
													<input type="radio" name="email_format_<?php echo $group->ID; ?>" value="2" checked />&nbsp;<?php _e( "Plain Text", 'mailing-group-module' ); ?>
												</div>
											</td>
										</tr>
									<?php } ?>
									</tbody>
								</table>
							</div>
						</div>

						<div class="clearbth"></div>
						<p class="submit">
							<input type="submit" value="<?php echo $btn; ?>" class="button" id="submit" name="submit" />
							<input type="hidden" name="addme" value="<?php echo $hidval; ?>">
							<input type="hidden" name="id" value="<?php echo $id; ?>">
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

