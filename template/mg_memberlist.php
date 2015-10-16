<?php
/* get all variables */
$info   = sanitize_text_field( $_REQUEST["info"] );
$actreq = sanitize_text_field( $_REQUEST["act"] );
$id     = sanitize_text_field( $_REQUEST["id"] );

$gid       = sanitize_text_field( $_REQUEST["gid"] );
$groupName = get_the_title( $gid );

/* get all variables */

if ( $gid == "" ) {
	wpmg_redirectTo( "wpmg_mailinggroup_list" );
}
if ( $info == "saved" ) {
	wpmg_showmessages( "updated", __( "Member has been added successfully.", 'mailing-group-module' ) );
} else if ( $info == "upd" ) {
	wpmg_showmessages( "updated", __( "Member has been updated successfully.", 'mailing-group-module' ) );
}

if ( $actreq == 'hold' ) {
	update_user_meta( $id, "mg_user_status", '0', '1' );
	wpmg_redirectTo( "wpmg_mailinggroup_memberlist&gid=" . $gid );
	exit;
} else if ( $actreq == 'active' ) {
	update_user_meta( $id, "mg_user_status", '1', '0' );
	wpmg_redirectTo( "wpmg_mailinggroup_memberlist&gid=" . $gid );
	exit;
}

$args = array(
	'meta_query' => array(
		array(
			'key'     => 'mg_user_group_sub_arr',
			'value'   => '"' . $gid . '"',
			'compare' => 'LIKE'
		)
	)
);

$user_in_group_query = new WP_User_Query( $args );
$users_in_group      = $user_in_group_query->get_results();
$totcount            = $user_in_group_query->get_total();


?>
<div class="wrap">
	<h2><?php _e( "Current Members", 'mailing-group-module' ); ?> <?php echo( $groupName != '' ? "($groupName) <a class='backlink' href='admin.php?page=wpmg_mailinggroup_list'>" . __( "Back", 'mailing-group-module' ) . "</a>" : "" ) ?>
		<a class="button add-new-h2" href="admin.php?page=wpmg_mailinggroup_requestmanageradd&act=add"><?php _e( "Add New User", 'mailing-group-module' ); ?></a>
	</h2>

	<div id="message"></div>

	<div class="search_all">
		<label for="search_all">Search:</label>
		<input type="text" id="search_all">
		<br>
	</div>


	<table class="wp-list-table widefat memberlist">
		<thead>
		<tr role="row" class="topRow">
			<th class="sort topRow_messagelist"><a href="#"><?php _e( "Name", 'mailing-group-module' ); ?></a></th>
			<th><a href="#"><?php _e( "Email Address", 'mailing-group-module' ); ?></a></th>
			<th><?php _e( "Bounced Emails", 'mailing-group-module' ); ?></th>
			<th><?php _e( "Status", 'mailing-group-module' ); ?></th>
			<th width="10%"><?php _e( "Actions", 'mailing-group-module' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( $totcount > 0 ) {
			foreach ( $users_in_group as $user ) {

				$userId            = $user->ID;
				$group_name_serial = get_user_meta( $userId, 'mg_user_group_subscribed', true );

				if ( count( $group_name_serial ) > 0 ) {
					foreach ( $group_name_serial as $group_id => $email_format ) {
						if ( $group_id == $gid ) {
							$Userrow      = get_user_by( "id", $userId );
							$user_login   = $Userrow->user_login;
							$user_email   = $Userrow->user_email;
							$display_name = $Userrow->user_firstname;
							$lastname     = $Userrow->user_lastname;
							$status       = get_user_meta( $userId, "mg_user_status", true );

							/*TODO: add email bounce table*/
//                          $mailbounceresult = $objMem->selectRows( $table_name_sent_emails, "", " where user_id = '" . $userId . "' and status='2'" );
							$mailbounceresult = "";
							$noofemailb       = 0;/*count( $mailbounceresult )*/

							$act         = "hold";
							$lablestatus = __( "Active", 'mailing-group-module' );
							$labledetail = __( "click to put On Hold", 'mailing-group-module' );
							if ( $status == 0 ) {
								$act         = "active";
								$lablestatus = __( "On Hold", 'mailing-group-module' );
								$labledetail = __( "click to Activate", 'mailing-group-module' );
							} ?>
							<tr>
								<td><?php echo $display_name . ' ' . $lastname; ?></td>
								<td><?php echo $user_email; ?></td>
								<td><?php echo $noofemailb; ?></td>
								<td><?php echo $lablestatus; ?> (<a href="admin.php?page=wpmg_mailinggroup_memberlist&act=<?php echo $act; ?>&id=<?php echo $userId; ?>&gid=<?php echo $gid; ?>"><?php echo $labledetail; ?></a>)
								</td>
								<td class="last">
									<a href="admin.php?page=wpmg_mailinggroup_memberadd&id=<?php echo $userId; ?>&gid=<?php echo $gid; ?>" class="edit_record" title="<?php _e( "Edit", 'mailing-group-module' ); ?>"></a>|<a class="delete_record remove_user" data-group_id="<?php echo $group_id; ?>" data-user_id="<?php echo $userId; ?>" title="Remove"></a>
								</td>
							</tr>
						<?php }
					}
				}
			}

		} else { ?>
		<tr>
			<td colspan="5" align="center"><?php _e( "No members found.", 'mailing-group-module' ); ?></td>
		<tr>
			<?php } ?>
		</tbody>
	</table>

</div>


<?php

$args_available = array(
	'meta_query' => array(
		'relation' => 'OR',
		array(
			'key'     => 'mg_user_group_sub_arr',
			'value'   => '"' . $gid . '"',
			'compare' => 'NOT EXISTS'
		),
		array(
			'key'     => 'mg_user_group_sub_arr',
			'value'   => '"' . $gid . '"',
			'compare' => 'NOT LIKE'
		)
	)
);

$available_user_query = new WP_User_Query( $args_available );

$available_users = $available_user_query->get_results();

$tot_available = $available_user_query->get_total();

?>

<div class="wrap">
	<h2>Available Users to add</h2>
	<table class="wp-list-table widefat fixed memberlist">
		<thead>
		<tr role="row" class="topRow">
			<th class="sort topRow_messagelist"><a href="#"><?php _e( "Name", 'mailing-group-module' ); ?></a></th>
			<th><a href="#"><?php _e( "Email Address", 'mailing-group-module' ); ?></a></th>
			<th><?php _e( "Bounced Emails", 'mailing-group-module' ); ?></th>
			<th><?php _e( "Status", 'mailing-group-module' ); ?></th>
			<th width="10%"><?php _e( "Actions", 'mailing-group-module' ); ?></th>
		</tr>
		</thead>
		<tbody>
		<?php
		if ( $tot_available > 0 ) {
			foreach ( $available_users as $user ) {

				$userId       = $user->ID;
				$Userrow      = get_user_by( "id", $userId );
				$user_login   = $Userrow->user_login;
				$user_email   = $Userrow->user_email;
				$display_name = $Userrow->user_firstname;
				$lastname     = $Userrow->user_lastname;
				$status       = get_user_meta( $userId, 'mg_user_status', true );

				/*TODO: add email bounce table*/
//                  $mailbounceresult = $objMem->selectRows( $table_name_sent_emails, "", " where user_id = '" . $userId . "' and status='2'" );
				$mailbounceresult = "";
				$noofemailb       = 0;/*count( $mailbounceresult )*/

				$act         = "hold";
				$lablestatus = __( "Active", 'mailing-group-module' );
				$labledetail = __( "click to put On Hold", 'mailing-group-module' );
				if ( $status == 0 ) {
					$act         = "active";
					$lablestatus = __( "On Hold", 'mailing-group-module' );
					$labledetail = __( "click to Activate", 'mailing-group-module' );
				} ?>
				<tr>
					<td><?php echo $display_name . ' ' . $lastname; ?></td>
					<td><?php echo $user_email; ?></td>
					<td><?php echo $noofemailb; ?></td>
					<td><?php echo $lablestatus; ?> (<a href="admin.php?page=wpmg_mailinggroup_memberlist&act=<?php echo $act; ?>&id=<?php echo $userId; ?>&gid=<?php echo $gid; ?>"><?php echo $labledetail; ?></a>)
					</td>
					<td class="last">
						<a href="admin.php?page=wpmg_mailinggroup_memberadd&id=<?php echo $userId; ?>&gid=<?php echo $gid; ?>" class="edit_record" title="<?php _e( "Edit", 'mailing-group-module' ); ?>"></a>|<a class="add_subscriber add_user" data-group_id="<?php echo $gid; ?>" data-user_id="<?php echo $userId; ?>" title="Add User"></a>
					</td>
				</tr>
				<?php
			}

		} else { ?>
		<?php } ?>
		</tbody>
	</table>
</div>