<?php
if ( is_user_logged_in() ) {

	$addme = sanitize_text_field( $_POST["addme"] );
	$_POST = stripslashes_deep( $_POST );

	$btn    = __( "Submit", 'mailing-group-module' );
	$add    = "";
	$hidval = 2;

	$args = array(
		'post_type'   => 'mg_groups',
		'post_status' => 'publish',
		'order_by' =>'title',
		'order' => 'DESC',
		'meta_query'  => array(
			'relation' => 'AND',
			array(
				'key'   => 'mg_group_status',
				'value' => '2'
			),
			array(
				'key'   => 'mg_group_visibility',
				'value' => '1'
			),
		)
	);

	$query         = new WP_Query( $args );
	$result_groups = $query->get_posts();

	$current_user = wp_get_current_user();
	$recid        = $current_user->ID;

	if ( $current_user ) {
		$fname               = $current_user->user_firstname;
		$lname               = $current_user->user_lastname;
		$email               = $current_user->user_email;
		$username            = $result->user_login;
		$status              = get_user_meta( $recid, "mg_user_status", true );
		$group_name_serial   = get_user_meta( $recid, "mg_user_group_subscribed", true );
		$groups_unserialized = unserialize( $group_name_serial );

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


	if ( $addme == 2 ) {

		$recid  = sanitize_text_field( $_POST['id'] );
		$fname  = sanitize_text_field( $_POST['fname'] );
		$lname  = sanitize_text_field( $_POST['lname'] );
		$status = sanitize_text_field( $_POST['status'] );
		$email = sanitize_email( $_POST['email'] );

		if ( $fname != '' && $lname != '' ) {

			if ( !get_page_by_title( $email,'OBJECT', 'mg_requests' ) ) {

				$request = array(
					'post_title'  => $email,
					'post_type'   => 'mg_requests',
					'post_status' => 'publish'
				);

				$pid = wp_insert_post( $request );

				$grpsArray = $objMem->getGroupSerialized( $_POST );
				$grpserial = serialize( $grpsArray );

				$subs_old = get_user_meta( $recid, "mg_user_group_subscribed", true );
				$subs_arr_old = get_user_meta( $recid, "mg_user_group_sub_arr", true );


				add_post_meta( $pid, 'mg_request_user_id', $recid, true );
				add_post_meta( $pid, 'mg_request_user_first_name_new', $fname, true );
				add_post_meta( $pid, 'mg_request_user_last_name_new', $lname, true );

				add_post_meta( $pid, 'mg_request_user_id', $recid, true );
				add_post_meta( $pid, 'mg_request_email', $email, true );
				add_post_meta( $pid, "mg_user_group_subscribed_old", $subs_old );
				add_post_meta( $pid, "mg_user_group_sub_arr_old",$subs_arr_old );

				add_post_meta( $pid, "mg_user_group_subscribed_new", $grpserial );
				add_post_meta( $pid, "mg_user_group_sub_arr_new",$_POST['group_name'] );
				add_post_meta( $pid, 'mg_request_status', 0, true );
				add_post_meta( $pid, 'mg_request_message_sent', 0, true );

				/*$userdata = array(
					'ID'         => $recid,
					'first_name' => $fname,
					'last_name'  => $lname
				);

				wp_update_user( $userdata );
				$statusold = get_user_meta( $recid, "mg_user_status", true );
				update_user_meta( $recid, "mg_user_status", $status, $statusold );

				$grpsArray = $objMem->getGroupSerialized( $_POST );
				$grpserial = serialize( $grpsArray );

				update_user_meta( $recid, "mg_user_group_subscribed", $grpserial );
				update_user_meta( $recid, "mg_user_group_sub_arr", $_POST['group_name'] );


				if ( $subscriptioncheck == '1' ) {
					wpmg_sendmessagetoAdmin( sanitize_text_field( $_POST['fname'] ), sanitize_email( $_POST['email'] ), implode( ",", sanitize_text_field( $_POST['group_name'] ) ) );
				}*/

				wpmg_showmessages( "error", __( "You're request is being processed", 'mailing-group-module' ) );
				exit;
			} else {
				wpmg_showmessages( "error", __( "User request with email address already exists, please allow previous request to be processed.", 'mailing-group-module' ) );
			}

		} else {
			wpmg_showmessages( "error", __( "Please enter First and Last Name.", 'mailing-group-module' ) );
		}
	}

	?>
	<style>
		<?php echo $custom_style; ?>
	</style>
	<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
		<div id="col-left">
			<div class="col-wrap">
				<div class="user_form_div">
					<div class="form-wrap">
						<form class="validate" action="" method="post" id="mailingrequestform">
							<div class="form-field">
								<label for="tag-name"><?php _e( "First Name", 'mailing-group-module' ); ?> : </label>
								<input type="text" size="40" id="fname" name="fname" value="<?php echo $fname; ?>" />
							</div>
							<div class="form-field">
								<label for="tag-name"><?php _e( "Last Name", 'mailing-group-module' ); ?> : </label>
								<input type="text" size="40" id="lname" name="lname" value="<?php echo $lname; ?>" />
							</div>
							<div class="form-field">
								<label for="tag-name"><?php _e( "Email Address", 'mailing-group-module' ); ?> : </label>
								<input type="text" size="40" id="email" name="email" value="<?php echo $email; ?>" />
							</div>

							<div class="form-field">
								<label for="tag-name"><?php _e( "Group Name", 'mailing-group-module' ); ?> : </label>
								<br>
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
								<!--		<label for="tag-name"><?php /*_e( "Captcha", 'mailing-group-module' ); */ ?> : </label>
							<img src="<?php /*echo WPMG_PLUGIN_URL . '/lib/captcha.php'; */ ?>">
							<input type="text" size="40" id="c_captcha" name="c_captcha" value="" />-->
							</div>
							<div class="form-field">
								<p class="submit">
									<input type="submit" value="<?php _e( "Subscribe", 'mailing-group-module' ); ?>" class="button" id="submit" name="submit" />
									<input type="hidden" name="addme" value="<?php echo $hidval; ?>">
									<input type="hidden" name="id" value="<?php echo $recid; ?>">
									<input type="hidden" name="status" value="0">
								</p>
							</div>
							<div class="clearbth"></div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php } else {

}
