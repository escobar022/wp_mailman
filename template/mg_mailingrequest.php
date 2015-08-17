<?php
$args = array(
	'post_type'   => 'mg_requests',
	'post_status' => 'publish'
);

$query         = new WP_Query( $args );
$result_groups = $query->get_posts();
$totcount      = count( $result_groups );

?>
<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<a href="admin.php?page=wpmg_mailinggroup_requestmanagerlist" title="<?php _e( "Subscription Request Manager", 'mailing-group-module' ); ?>" class="nav-tab nav-tab-active"><?php _e( "Subscription Request Manager", 'mailing-group-module' ); ?></a>
			<a href="admin.php?page=wpmg_mailinggroup_requestmanageradd&act=add" class="nav-tab" title="<?php _e( "Add New Subscriber", 'mailing-group-module' ); ?>"><?php _e( "Add New Subscriber", 'mailing-group-module' ); ?></a>
		</h2>

		<div class="description">
			<h4><?php _e( "Any new subscriber requests submitted via your website, or via the Add New Subscriber panel, will appear below. You need to use the plugin’s shortcode to display the subscription request form on your website - see the Help tab in the General Settings for more information.", 'mailing-group-module' ); ?></h4>
		</div>
		<form name="approvedeleterequest" id="approvedeleterequest" method="post">

			<table class="wp-list-table widefat fixed" id="mailingrequestmanager">
				<thead>
				<tr role="row" class="topRow">
					<th><?php _e( "User Name", 'mailing-group-module' ); ?></th>
					<th><?php _e( "Current Groups", 'mailing-group-module' ); ?></th>
					<th><?php _e( "Requested Groups", 'mailing-group-module' ); ?></th>
					<th width="10%"><?php _e( "Actions", 'mailing-group-module' ); ?></th>
				</tr>
				</thead>
				<tbody>
				<?php
				if ($totcount > 0){
					foreach ( $result_groups as $row ) {
						$id        = $row->ID;
						$user_id   = get_post_meta( $id, 'mg_request_user_id', true );
						$user_info = get_userdata( $user_id );
						$username  = $user_info->user_login;

						$current_groups     = get_user_meta( $user_id, 'mg_user_group_subscribed', true );
						$requested_groups   = get_user_meta( $user_id, 'mg_user_requested_groups', true );
						$group_requested_id = get_post_meta( $id, 'mg_request_group_id', true );
//						$denied_request     = get_post_meta( $id, 'mg_requested_denied', true );

						?>
						<tr>
							<td ><?php echo $username; ?></td>
							<td>
								<?php
								if ( ! empty( $current_groups ) ) {
									foreach ( $current_groups as $group => $group_format ) { ?>
										<p><?php echo get_the_title( $group ) ?></p>
									<?php
									}
								} else {
									?>
									<p>No Current Groups</p>
								<?php
								}
								?>
							</td>
							<td>
								<?php
								if ( ! empty( $requested_groups ) ) {
									?>
									<p><?php echo get_the_title( $group_requested_id ) ?></p>
								<?php
								} else { ?>
									<p>Error in request</p>
								<?php
								}
								?>
							</td>
							<td width="25%" class="last">
								<?php
								if ( ! empty( $requested_groups ) /*&& empty($denied_request) */ ) {
									?>
									<div>
										<input type="button" class="approve_request" data-request_id="<?php echo $id; ?>" data-user_id="<?php echo $user_id; ?>" value="Approve" />
                                        <input type="button" class="deny_request" data-request_id="<?php echo $id; ?>" data-user_id="<?php echo $user_id; ?>" value="Deny" />
                                        <a href="admin.php?page=wpmg_mailinggroup_sendmessage&act=upd&id=<?php echo $id;?>&gid=<?php echo $groups->group_id;?>&TB_iframe=true&width=550&height=530" title="<?php _e("Send Message", 'mailing-group-module'); ?>" class="send_mail thickbox"></a>
									</div>
								<?php
								} else {
									?>
									<p>Request Has been Denied</p>
								<?php }
								?>
							</td>
						</tr>
					<?php }
				} else { ?>
				<tr>
					<td colspan="5" align="center"><?php _e( "No new subscription requests", 'mailing-group-module' ); ?></td>
				<tr>
					<?php } ?>
				</tbody>
			</table>
		</form>
	</div>

<?php add_thickbox(); ?>