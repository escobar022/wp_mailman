<?php
/* get all variables */
$actreq = sanitize_text_field( $_REQUEST["act"] );
$UpdId  = sanitize_text_field( $_GET['id'] );
$gid    = sanitize_text_field( $_GET["gid"] );
$delid  = sanitize_text_field( $_GET["did"] );
$info   = sanitize_text_field( $_REQUEST["info"] );
$type   = sanitize_text_field( $_REQUEST["type"] );


/* get all variables */
if ( $_POST['Save'] && is_array( $_POST['selectusers'] ) ) {
	foreach ( $_POST['selectusers'] as $key => $val ) {
		$user_id = '';
		$getIds  = explode( "_", $val );
		$UpdId   = $delid = $getIds[0];
		$gid     = $getIds[1];
		$mact    = ( $_POST['massaction'] ? sanitize_text_field( $_POST['massaction'] ) : sanitize_text_field( $_POST['massaction2'] ) );
		if ( $mact == '1' ) {
			$addRequesttodb  = $objMem->selectRows( $table_name_requestmanager, "", " where id = '" . $UpdId . "'" );
			$random_password = wp_generate_password( 12, false );
			$name            = $addRequesttodb[0]->name;
			$email           = $addRequesttodb[0]->email;
			$username        = $addRequesttodb[0]->username;
			$group_name      = $objMem->getUserGroup( $table_name_requestmanager_taxonomy, $UpdId );
			if ( trim( $username ) == "" ) {
				$username = $email;
			}
			$status     = "1";
			$username_e = username_exists( $username );
			$email_e    = email_exists( $email );
			if ( ! $user_id and email_exists( $email ) == false ) {
				$userdata = array(
					'user_login' => $username,
					'first_name' => $name,
					'user_pass'  => $random_password,
					'user_email' => $email,
					'role'       => 'subscriber'
				);
				/* //print_r($userdata); */
				$user_id = wp_insert_user( $userdata );
				$msg     = "";
				if ( is_numeric( $user_id ) ) {
					/* //echo "<br>---".$user_id; */
					wp_new_user_notification( $user_id, $random_password );
					add_user_meta( $user_id, "Plugin", "groupmailing" );
					add_user_meta( $user_id, "mg_user_status", $status );
					$gropArray = array( $gid => $group_name[ $gid ] );
					add_user_meta( $user_id, "mg_user_group_subscribed", serialize( $gropArray ) );
					$objMem->addUserGroupTaxonomy( $table_name_user_taxonomy, $user_id, $gropArray );
					if ( count( $group_name ) > 1 ) {
						$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $UpdId );
					} else {
						$myFields       = array( "status" );
						$_ARR['id']     = $UpdId;
						$_ARR['status'] = $status;
						$objMem->updRow( $table_name_requestmanager, $_ARR, $myFields );
					}
				}
				wpmg_sendGroupConfirmationtoMember( $user_id, $gropArray );
			} else {
				if ( $username_e || $email_e ) {
					$userId = ( is_numeric( $username_e ) ? $username_e : $email_e );
					if ( is_numeric( $userId ) ) {
						$usergroupnames = get_user_meta( $userId, "mg_user_group_subscribed", true );
						$group_name_new = unserialize( $usergroupnames );
						if ( ! in_array( $gid, $group_name_new ) ) {
							$group_name_new[ $gid ] = $group_name[ $gid ];
						}
						update_user_meta( $userId, "mg_user_group_subscribed", serialize( $group_name_new ) );
						$objMem->updUserGroupTaxonomy( $table_name_user_taxonomy, $userId, $group_name_new );
						if ( count( $group_name ) > 1 ) {
							$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $UpdId );
						} else {
							$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $UpdId );
							$myFields       = array( "status" );
							$_ARR['id']     = $UpdId;
							$_ARR['status'] = $status;
							$objMem->updRow( $table_name_requestmanager, $_ARR, $myFields );
						}
					}
				}
			}
		} else if ( $mact == '2' ) {
			$addRequesttodb = $objMem->selectRows( $table_name_requestmanager, "", " where id = '" . $delid . "'" );
			$groupArr       = $objMem->getUserGroup( $table_name_requestmanager_taxonomy, $delid );
			if ( count( $groupArr ) > 1 ) {
				$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $delid );
			} else {
				$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $delid );
				$wpdb->query( "delete from " . $table_name_requestmanager . " where id=" . $delid );
			}
		}
	}
	wpmg_redirectTo( "wpmg_mailinggroup_requestmanagerlist&info=mass&type=" . $mact );
	exit;
}

if ( $actreq == 'app' ) {
	$addRequesttodb  = $objMem->selectRows( $table_name_requestmanager, "", " where id = '" . $UpdId . "'" );
	$random_password = wp_generate_password( 12, false );
	$name            = $addRequesttodb[0]->name;
	$email           = $addRequesttodb[0]->email;
	$username        = $addRequesttodb[0]->username;
	$group_name      = $objMem->getUserGroup( $table_name_requestmanager_taxonomy, $UpdId );
	/* //echo "<pre>";
	//print_r($group_name); */
	if ( trim( $username ) == "" ) {
		$username = $email;
	}
	$status     = "1";
	$username_e = username_exists( $username );
	$email_e    = email_exists( $email );
	if ( ! $user_id and email_exists( $email ) == false ) {
		$userdata = array(
			'user_login' => $username,
			'first_name' => $name,
			'user_pass'  => $random_password,
			'user_email' => $email,
			'role'       => 'subscriber'
		);
		/* //print_r($userdata); */
		$user_id = wp_insert_user( $userdata );
		/* //wp_new_user_notification($user_id, $random_password); */
		$msg = "";
		if ( is_numeric( $user_id ) ) {
			/* //echo "<br>---".$user_id; */
			wp_new_user_notification( $user_id, $random_password );
			add_user_meta( $user_id, "Plugin", "groupmailing" );
			add_user_meta( $user_id, "mg_user_status", $status );
			$gropArray = array( $gid => $group_name[ $gid ] );
			add_user_meta( $user_id, "mg_user_group_subscribed", serialize( $gropArray ) );
			$objMem->addUserGroupTaxonomy( $table_name_user_taxonomy, $user_id, $gropArray );
			if ( count( $group_name ) > 1 ) {
				/* //echo "<br> in delete---"; */
				$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $UpdId );
			} else {
				$myFields       = array( "status" );
				$_ARR['id']     = $UpdId;
				$_ARR['status'] = $status;
				$objMem->updRow( $table_name_requestmanager, $_ARR, $myFields );
			}
			wpmg_sendGroupConfirmationtoMember( $user_id, $gropArray );
			wpmg_redirectTo( "wpmg_mailinggroup_requestmanagerlist&info=app" );
			exit;
		} else {
			foreach ( $user_id->errors as $errr ) {
				$msg .= $errr[0];
			}
			wpmg_showmessages( "error", $msg );
		}
	} else {
		if ( $username_e || $email_e ) {
			$userId = ( is_numeric( $username_e ) ? $username_e : $email_e );
			if ( is_numeric( $userId ) ) {
				$usergroupnames = get_user_meta( $userId, "mg_user_group_subscribed", true );
				$group_name_new = unserialize( $usergroupnames );
				if ( ! in_array( $gid, $group_name_new ) ) {
					$group_name_new[ $gid ] = $group_name[ $gid ];
				}
				update_user_meta( $userId, "mg_user_group_subscribed", serialize( $group_name_new ) );
				$objMem->updUserGroupTaxonomy( $table_name_user_taxonomy, $userId, $group_name_new );
				if ( count( $group_name ) > 1 ) {
					$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $UpdId );
				} else {
					$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $UpdId );
					$myFields       = array( "status" );
					$_ARR['id']     = $UpdId;
					$_ARR['status'] = $status;
					$objMem->updRow( $table_name_requestmanager, $_ARR, $myFields );
				}
			}
			wpmg_sendGroupConfirmationtoMember( $user_id, $gropArray );
			wpmg_redirectTo( "wpmg_mailinggroup_requestmanagerlist&info=upd2" );
			exit;
		}
	}
} else if ( $actreq == 'del' ) {
	$addRequesttodb = $objMem->selectRows( $table_name_requestmanager, "", " where id = '" . $delid . "'" );
	$groupArr       = $objMem->getUserGroup( $table_name_requestmanager_taxonomy, $delid );
	if ( count( $groupArr ) > 1 ) {
		$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $delid );
		wpmg_redirectTo( "wpmg_mailinggroup_requestmanagerlist&info=upd" );
		exit;
	} else {
		$objMem->deleteUserGroup( $table_name_requestmanager_taxonomy, $gid, $delid );
		$wpdb->query( "delete from " . $table_name_requestmanager . " where id=" . $delid );
		wpmg_redirectTo( "wpmg_mailinggroup_requestmanagerlist&info=del" );
		exit;
	}
} else if ( $actreq == 'delsubs' ) {
	$wpdb->query( "delete from " . $table_name_requestmanager . " where id=" . $delid );
	wpmg_redirectTo( "wpmg_mailinggroup_requestmanagerlist&info=delsubs" );
	exit;
}
if ( $info == "app" ) {
	wpmg_showmessages( "updated", __( "Subscription request has been approved successfully.", 'mailing-group-module' ) );
} else if ( $info == "upd" ) {
	wpmg_showmessages( "updated", __( "Subscription request has been updated successfully.", 'mailing-group-module' ) );
} else if ( $info == "upd2" ) {
	wpmg_showmessages( "updated", __( "Subscription request was already registered, groups updated successfully.", 'mailing-group-module' ) );
} else if ( $info == "del" ) {
	wpmg_showmessages( "updated", __( "Subscription request has been rejected successfully.", 'mailing-group-module' ) );
} else if ( $info == "delsubs" ) {
	wpmg_showmessages( "updated", __( "Subscription request has been deleted successfully.", 'mailing-group-module' ) );
} else if ( $info == "saved" ) {
	wpmg_showmessages( "updated", __( "Subscription request has been added successfully. Now approve the request to activate the membership.", 'mailing-group-module' ) );
} else if ( $info == "free" ) {
	wpmg_showmessages( "error", __( "You can only add 20 member(s) per group, Please upgrade to paid version for more features.", 'mailing-group-module' ) );
} else if ( $info == "mass" ) {
	if ( $type == '1' ) {
		wpmg_showmessages( "updated", __( "Subscription request(s) has been added successfully.", 'mailing-group-module' ) );
	} else if ( $type == '2' ) {
		wpmg_showmessages( "updated", __( "Subscription request(s) has been rejected successfully.", 'mailing-group-module' ) );
	} else if ( $type == '3' ) {
		wpmg_showmessages( "error", __( "You can only add 20 member(s) per group, Please upgrade to paid version for more features.", 'mailing-group-module' ) );
	}
}


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
			<a href="admin.php?page=wpmg_mailinggroup_importuser" class="nav-tab" title="<?php _e( "Import Users", 'mailing-group-module' ); ?>"><?php _e( "Import Users", 'mailing-group-module' ); ?></a>
		</h2>

		<div class="description">
			<h4><?php _e( "Any new subscriber requests submitted via your website, or via the Add New Subscriber panel, will appear below. You need to use the plugin’s shortcode to display the subscription request form on your website - see the Help tab in the General Settings for more information.", 'mailing-group-module' ); ?></h4>
		</div>
		<form name="approvedeleterequest" id="approvedeleterequest" method="post">
			<div class="tablenav top">
				<div class="alignleft actions">
					<select name="massaction" id="massaction">
						<option selected="selected" value=""><?php _e( "Bulk actions", 'mailing-group-module' ); ?></option>
						<option value="1"><?php _e( "Approve Selected", 'mailing-group-module' ); ?></option>
						<option value="2"><?php _e( "Reject Selected", 'mailing-group-module' ); ?></option>
					</select>
					<input type="submit" id="doaction" name="Save" value="<?php _e( "Apply", 'mailing-group-module' ); ?>" />
				</div>
				<br class="clear">
			</div>
			<table class="wp-list-table widefat fixed" id="mailingrequestmanager">
				<thead>
				<tr role="row" class="topRow">
					<th width="25%" class="sort" style="cursor:pointer;">
						<a href="#"><?php _e( "User Name", 'mailing-group-module' ); ?></a>
					</th>
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
							<td width="25%"><?php echo $username; ?></td>
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
									</div>
								<?php
								} else {
									?>
									<p>Request is already </p>
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
			<div class="tablenav bottom">
				<div class="alignleft actions">
					<select name="massaction2" id="massaction2">
						<option selected="selected" value=""><?php _e( "Bulk actions", 'mailing-group-module' ); ?></option>
						<option value="1"><?php _e( "Approve Selected", 'mailing-group-module' ); ?></option>
						<option value="2"><?php _e( "Reject Selected", 'mailing-group-module' ); ?></option>
					</select>
					<input type="submit" id="doaction2" name="Save" value="Apply" />
				</div>
				<br class="clear">
			</div>
		</form>
	</div>
<?php add_thickbox(); ?>