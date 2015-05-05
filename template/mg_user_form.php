<?php

if ( is_user_logged_in() ) {
	$current_user = wp_get_current_user();
	$cu_id = $current_user->ID;
	$fname = $current_user->user_firstname;
	$lname = $current_user->user_lastname;
	$email =  $current_user->user_email ;
} else {
	auth_redirect();
	exit;
}

$WPMG_SETTINGS = get_option( "WPMG_SETTINGS" );
/* get all variables */
$addme             = sanitize_text_field( $_POST["addme"] );
$info              = sanitize_text_field( $_REQUEST["info"] );
$_POST             = stripslashes_deep( $_POST );
$subscriptioncheck = $WPMG_SETTINGS["MG_SUBSCRIPTION_REQUEST_CHECK"];

$substr     = "";
$add        = "";
$group_name = ( $_POST['group_name'] != '' ? sanitize_text_field( $_POST['group_name'] ) : array() );
$hidval     = 1;

if ( $group_name == "" ) {
	$group_name = array();
}
$custom_style = $WPMG_SETTINGS["MG_CUSTOM_STYLESHEET"];

$args = array(
	'post_type'   => 'mg_groups',
	'post_status' => 'publish',
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
$query = new WP_Query( $args );
$result_groups = $query->get_posts();

if ( isset( $_POST['submit'] ) ) {
	$_POST['fname'] = sanitize_text_field( $_POST['fname'] );
	$_POST['lname'] = sanitize_text_field( $_POST['lname'] );

	if ( ! $objMem->checkRowExists( $table_name_requestmanager, "email", $_POST, "" ) ) {
		// Create request
		$request = array(
			'post_title'  => $_POST['name'].' '.$_POST['lname'],
			'post_type'   => 'mg_requests',
			'post_status' => 'publish'
		);

		$pid = wp_insert_post( $request );

		add_post_meta( $pid, 'mg_request_email', sanitize_email( $_POST['email'] ), true );
		add_post_meta( $pid, 'mg_request_groups', $_POST['group_name'], true );
		add_post_meta( $pid, 'mg_request_current_groups', $_POST['group_name'], true );
		add_post_meta( $pid, 'mg_request_status', 0, true );
		add_post_meta( $pid, 'mg_request_message_sent', 0, true );

		if ( $subscriptioncheck == '1' ) {
			wpmg_sendmessagetoAdmin( sanitize_text_field( $_POST['fname'] ), sanitize_email( $_POST['email'] ), implode( ",", sanitize_text_field( $_POST['group_name'] ) ) );
		}

		wpmg_redirectTo( "?info=saved", "front" );
		exit;
	} else {
		wpmg_showmessages( "error", __( "User with email address already exists, please contact administrator for more info.", 'mailing-group-module' ) );
	}

} elseif ( $info == "saved" ) {
	wpmg_showmessages( "error", __( "You are successfully registered for the group(s) selected pending approval.", 'mailing-group-module' ) );
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
						<div class="outer_group_div">
							<div class="check_div_fir">
								<p class="inner_check_imp"><?php _e( "Mailing Group", 'mailing-group-module' ); ?> :</p>
							</div>
							<div class="check_div_imp">
								<?php $groupCount = count( $result_groups );
								if ( $groupCount > 0 ) {
									foreach ( $result_groups as $group ) { ?>
										<p class="inner_check_imp_group">
											<input type="checkbox" name="group_name[]" id="selector" value="<?php echo $group->ID; ?>" />&nbsp;<?php echo $group->post_title; ?>
										</p>
									<?php }
								} else {
									_e( "No group available", 'mailing-group-module' );
								} ?>
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