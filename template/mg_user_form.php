<?php
if ( is_user_logged_in() ) {

	$WPMG_SETTINGS = get_option( "WPMG_SETTINGS" );
	$custom_style  = $WPMG_SETTINGS["MG_CUSTOM_STYLESHEET"];

	$args = array(
		'post_type'   => 'mg_groups',
		'post_status' => 'publish',
		'orderby'    => 'title',
		'order'       => 'DESC',
		'posts_per_page' => - 1,
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
	$user_id      = $current_user->ID;

	$fname             = $current_user->user_firstname;
	$lname             = $current_user->user_lastname;
	$email             = $current_user->user_email;
	$groups_subscribed = get_user_meta( $user_id, 'mg_user_group_subscribed', true );
	if ( empty( $groups_subscribed ) ) {
		$groups_subscribed = array();
	}

	$requested_groups = get_user_meta( $user_id, 'mg_user_requested_groups', true );
	$denied_requests  = get_user_meta( $user_id, 'mg_user_denied_request', true );

	?>

	<style>
		<?php echo $custom_style; ?>
	</style>

	<div xmlns="http://www.w3.org/1999/xhtml" class="wrap nosubsub">
		<div id="col-left">
			<div class="col-wrap">
				<div class="user_form_div">
					<div class="form-wrap">
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
										$subscribed   = false;
										$request_id   = false;
										$group_format = '1';
										if ( array_key_exists( $group->ID, $groups_subscribed ) ) {
											$subscribed   = true;
											$group_format = $groups_subscribed[ $group->ID ];
										}
										if ( ! empty( $requested_groups ) && array_key_exists( $group->ID, $requested_groups ) ) {
											$request_id   = $requested_groups[ $group->ID ]['request_id'];
											$group_format = $requested_groups[ $group->ID ]['group_format'];
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
													<input type="button" class="req_leave_group" value="Leave Group" data-group_id="<?php echo $group->ID; ?>" />
													<p class="confirm_message" data-group_id="<?php echo $group->ID; ?>">You will have to request to be added back into the mailing group, are you sure you want to continue?</p>
													<input type="button" class="confirm_leave_group" value="Confrim" data-group_id="<?php echo $group->ID; ?>" />
													<input type="button" class="cancel_leave_group" value="Cancel" data-group_id="<?php echo $group->ID; ?>" />
												<?php
												} else {
													if ( $request_id ) {
														?>
														<p>Pending</p>
														<input type="button" class="cancel_request" value="Cancel Request" data-group_id="<?php echo $group->ID; ?>" data-request_id="<?php echo $request_id; ?>" />
													<?php
													} else { ?>
														<p>No</p>
														<input type="button" class="request_group" value="Request" data-group_id="<?php echo $group->ID; ?>" />
													<?php
													}
												} ?>
											</td>
											<td width="30%">
												<div class="check_div">
													<input type="hidden" class="current_format" value="<?php echo $group_format; ?>" data-group_id="<?php echo $group->ID; ?>" />

													<div class="select_format">
														<input type="radio" class="email_format" name="email_format_<?php echo $group->ID; ?>" data-group_id="<?php echo $group->ID; ?>" value="1" <?php echo( $group_format == '1' ? "checked" : "" ) ?>/><?php _e( "HTML", 'mailing-group-module' ); ?>
													</div>
													<div class="select_format">
														<input type="radio" class="email_format" name="email_format_<?php echo $group->ID; ?>" data-group_id="<?php echo $group->ID; ?>" value="2" <?php echo( $group_format == '2' ? "checked" : "" ) ?>/><?php _e( "Plain Text", 'mailing-group-module' ); ?>
													</div>
													<?php if ( $subscribed OR $request_id ) {
														?>
														<input type="button" class="update_group_format" value="Update Format" data-group_id="<?php echo $group->ID; ?>" data-request_id="<?php echo $request_id; ?>" />
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
						<div class="form-field">
							<input type="hidden" id="user_id" name="user_id" value="<?php echo $user_id; ?>">
						</div>
						</form>
					</div>
				</div>
			</div>
		</div>
	</div>

<?php } else {
	wp_login_form();
}
