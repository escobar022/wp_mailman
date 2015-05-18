<?php
if ( is_user_logged_in() ) {

	$args = array(
		'post_type'   => 'mg_groups',
		'post_status' => 'publish',
		'order_by'    => 'title',
		'order'       => 'DESC',
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
		$fname = $current_user->user_firstname;
		$lname = $current_user->user_lastname;
		$email = $current_user->user_email;

		$requested_groups = get_user_meta( $recid, "mg_user_requested_groups", true );


		/*foreach ( $requested_groups as $request_id => $request_info ) {
			$request_ids[] = $request_id;
			$groups_requested[] = $request_info['group_id'];
		}*/

		/*error_log(print_r($request_ids,true));
		error_log(print_r($groups_requested,true));*/


		$group_name_serial   = get_user_meta( $recid, "mg_user_group_subscribed", true );
		$groups_unserialized = unserialize( $group_name_serial );

		if ( count( $groups_unserialized ) > 0 ) {
			foreach ( $groups_unserialized as $group_id => $email_format ) {
				$group_name[ $group_id ] = $email_format;
			}
			$btn = __( "Update Subscribtions", 'mailing-group-module' );
		} else {
			$btn        = __( "Subcribe to Mailing Groups", 'mailing-group-module' );
			$group_name = array();
		}
	}


	/*	wpmg_showmessages( "error", __( "User request with email address already exists, please allow previous request to be processed.", 'mailing-group-module' ) );*/

	?>

	<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
		<div id="col-left">
			<div class="col-wrap">
				<div class="user_form_div">
					<div class="form-wrap">
						<form class="validate" action="" method="post" id="mailingrequestform">
							<div class="form-field">
								<label for="tag-name"><?php _e( "First Name", 'mailing-group-module' ); ?> : </label>
								<input type="text" size="40" id="fname" name="fname" value="<?php echo $fname; ?>" readonly />
							</div>
							<div class="form-field">
								<label for="tag-name"><?php _e( "Last Name", 'mailing-group-module' ); ?> : </label>
								<input type="text" size="40" id="lname" name="lname" value="<?php echo $lname; ?>" readonly />
							</div>
							<div class="form-field">
								<label for="tag-name"><?php _e( "Email Address", 'mailing-group-module' ); ?> : </label>
								<input type="text" size="40" id="email" name="email" value="<?php echo $email; ?>" readonly />
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
											$subscribed = false;
											if ( array_key_exists( $group->ID, $group_name ) ) {
												$subscribed = true;
											}
											?>
											<tr>
												<td>
													<?php echo $group->post_title; ?>
												</td>
												<td>
													<?php if ( $subscribed ) {
														?>
														<p class="current_status" data-group_id="<?php echo $group->ID; ?>">Yes</p>
														<input type="button" class="req_leave_group" value="Leave Group" data-group_id="<?php echo $group->ID; ?>" />
														<p class="confirm_message" data-group_id="<?php echo $group->ID; ?>">You will have to request to be added back into the mailing group, are you sure you want to continue?</p>
														<input type="button" class="confirm_leave_group" value="Confrim" data-group_id="<?php echo $group->ID; ?>" />
														<input type="button" class="cancel_leave_group" value="Cancel" data-group_id="<?php echo $group->ID; ?>" />

													<?php
													} else {
														if ( array_key_exists( $group->ID, $requested_groups ) ) { ?>
															<p>Pending</p>
															<input type="button" class="cancel_request" value="Cancel Request" data-group_id="<?php echo $group->ID; ?>" data-request_id="<?php echo $requested_groups[ $group->ID ]['request_id']; ?>" />
														<?php
														} else { ?>
															<p>No</p>
															<input type="button" class="request_group" value="Request" data-group_id="<?php echo $group->ID; ?>" />
														<?php
														}
													} ?>
												</td>
												<td>
													<div class="check_div">
														<input type="radio" name="email_format_<?php echo $group->ID; ?>" <?php echo( $group_name[ $group->ID ] == '1' ? "checked" : "" ) ?> value="1" /><?php _e( "HTML", 'mailing-group-module' ); ?>
														<br />
														<input type="radio" <?php echo( $group_name[ $group->ID ] == '2' ? "checked" : ( count( $group_name ) == '0' ? "checked" : ( ! isset( $group_name[ $group->ID ] ) ? "checked" : "" ) ) ) ?> name="email_format_<?php echo $group->ID; ?>" value="2" /><?php _e( "Plain Text", 'mailing-group-module' ); ?>
													</div>
												</td>
											</tr>
										<?php } ?>
										</tbody>
									</table>
								</div>
							</div>
							<div class="form-field">
								<input type="hidden" id="user_id" name="user_id" value="<?php echo $recid; ?>">
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
