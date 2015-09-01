<?php

class mailinggroupClass {

	public $admin_emails = array(
		'1' => array(
			'title'        => 'For subscribers: Opt-in confirmation for new subscribers',
			'message_type' => 'optinAdminAdd',
			'subject'      => '',
			'message'      => ''
		),
		'2' => array(
			'title'        => 'For subscribers: Confirmation of successful group subscription',
			'message_type' => 'userApprovedRequest',
			'subject'      => '',
			'message'      => ''
		),
		'3' => array(
			'title'        => 'For admin: New subscription request alert',
			'message_type' => 'newGroupRequest',
			'subject'      => '',
			'message'      => ''
		)
	);
	public $custom_default_emails = array(
		'1' => array(
			'title'   => 'Credentials Check',
			'subject' => '',
			'message' => 'Hello {%name%},

Thank you for your subscription request to {%group_name%} at {%site_title%} ({%site_url%}).



Could you please send supporting documents to confirm your credentials for joining this list?



Thank you in advance.



The List Admin.

{%site_email%}',
			'visible' => '1'
		)
	);

	public function __construct() {
		add_action( 'wp_ajax_wpmg_reset_admin_email', array( $this, 'wpmg_reset_admin_email' ) );

		add_action( 'wp_ajax_wpmg_custom_msg_visibility', array( $this, 'wpmg_custom_msg_visibility' ) );
		add_action( 'wp_ajax_wpmg_remove_custom_email', array( $this, 'wpmg_remove_custom_email' ) );

	}

	function updateEmailOption( $id, $posted_vals, $submited_fields, $option_name ) {

		$submitted_changes = array();
		$current_emails    = get_option( $option_name );

		foreach ( $submited_fields as $field ) {
			$submitted_changes[ $field ] = $posted_vals[ $field ];
		}

		if ( empty( $id ) ) {
			$updated_list = $current_emails;
			array_push( $updated_list, $submitted_changes );
			$difference = array_diff_assoc  ($updated_list, $current_emails  );
		} else {
			$difference = array_diff_assoc( $current_emails[ $id ], $submitted_changes );
			$new_submission = array( $id => $submitted_changes );
			$updated_list   = array_replace( $current_emails, $new_submission );
		}

		if ( !empty( $difference ) ) {
			update_option( $option_name, $updated_list );
			return true;
		} else {
			return false;
		}
	}

	public function wpmg_reset_admin_email() {

		$option = 'wp_mailman_admin_emails';

		$current_emails = get_option( $option );

		$nonce = $_POST['nextNonce'];

		if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
			die ( 'Busted!' );
		}
		$email_id = $_POST['email_id'];

		$default_email = $this->admin_emails;

		$new_submission = array( $email_id => $default_email[ $email_id ] );
		$updated_list = array_replace( $current_emails, $new_submission );

		update_option( $option, $updated_list );

		$response = json_encode( $email_id );
		header( "Content-Type: application/json" );
		echo $response;

		wp_die();
	}

	public function wpmg_remove_custom_email() {

		$option = 'wp_mailman_custom_emails';

		$current_emails = get_option( $option );

		$nonce = $_POST['nextNonce'];

		if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
			die ( 'Busted!' );
		}

		$email_id = $_POST['email_id'];

		if (array_key_exists( $email_id, $current_emails ) ) {

			$updated_list = $current_emails;
			unset( $updated_list[ $email_id ] );
			update_option( $option, $updated_list );
		} else {
			error_log( 'Email template does not exist' );
		}

		$response = json_encode( $email_id );
		header( "Content-Type: application/json" );
		echo $response;

		wp_die();
	}

	public function wpmg_custom_msg_visibility() {

		$option = 'wp_mailman_custom_emails';
		$current_emails = get_option( $option );

		$nonce = $_POST['nextNonce'];

		if ( ! wp_verify_nonce( $nonce, 'myajax-next-nonce' ) ) {
			die ( 'Busted!' );
		}
		$email_id   = $_POST['email_id'];
		$visibility = $_POST['visibility'];

		$updated_custom_emails = $current_emails;

		if ( $visibility == 1 ) {
			$updated_custom_emails[ $email_id ]['visible'] = 0;
		} else {
			$updated_custom_emails[ $email_id ]['visible'] = 1;
		}

		$updated_custom_emails_arr = array_replace( $current_emails, $updated_custom_emails );

		update_option( $option, $updated_custom_emails_arr );

		$response = json_encode( $email_id );
		header( "Content-Type: application/json" );
		echo $response;

		wp_die();
	}


	function addNewRow( $tblname, $grpinfo, $fields ) {
		global $wpdb;
		$count = sizeof( $grpinfo );
		if ( $count > 0 ) {
			$id    = 0;
			$field = "";
			$vals  = "";

			foreach ( $fields as $key ) {
				if ( is_array( $grpinfo[ $key ] ) ) {
					$exp = implode( ",", $grpinfo[ $key ] );
					if ( $field == "" ) {
						$field = "`" . $key . "`";
						$vals  = $vals . ",'" . addslashes( $exp ) . "'";
					} else {
						$field = $field . ",`" . $key . "`";
						$vals  = $vals . ",'" . addslashes( $exp ) . "'";
					}
				} else {
					if ( $field == "" ) {
						$field = "`" . $key . "`";
						$vals  = "'" . addslashes( wpmg_trimVal( $grpinfo[ $key ] ) ) . "'";
					} else {
						$field = $field . ",`" . $key . "`";
						$vals  = $vals . ",'" . addslashes( wpmg_trimVal( $grpinfo[ $key ] ) ) . "'";
					}
				}
			}

			$sSQL = "INSERT INTO " . $tblname . " ($field) values ($vals)";
			/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */
			$wpdb->query( $sSQL );

			return $lastid = $wpdb->insert_id;
		} else {
			return false;
		}
	}

	function updRow( $tblname, $grpinfo, $fields ) {
		global $wpdb;
		$count = sizeof( $grpinfo );
		if ( $count > 0 ) {
			$field = "";
			$vals  = "";
			foreach ( $fields as $key ) {
				if ( is_array( $grpinfo[ $key ] ) ) {
					$exp = implode( ",", $grpinfo[ $key ] );
					if ( $field == "" && $key != "id" ) {
						$field = "`" . $key . "` = '" . addslashes( wpmg_trimVal( $exp ) ) . "'";
					} else if ( $key != "id" ) {
						$field = $field . ",`" . $key . "` = '" . addslashes( wpmg_trimVal( $exp ) ) . "'";
					}
				} else {
					if ( $field == "" && $key != "id" ) {
						$field = "`" . $key . "` = '" . addslashes( wpmg_trimVal( $grpinfo[ $key ] ) ) . "'";
					} else if ( $key != "id" ) {
						$field = $field . ",`" . $key . "` = '" . addslashes( wpmg_trimVal( $grpinfo[ $key ] ) ) . "'";
					}
				}
			}

			$sSQL = "update " . $tblname . " set $field where id='" . $grpinfo["id"] . "'";
			/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */
			$wpdb->query( $sSQL );

			return true;
		} else {
			return false;
		}
	}

	function selectRows( $tblname, $id = "", $extra = "" ) {
		global $wpdb;
		$subStr = "";
		if ( $id > 0 ) {
			$subStr = " where id='$id'";
		}
		$sSQL = "select * from " . $tblname . $subStr . $extra;
		$res  = $wpdb->get_results( $sSQL );

		return $res;
	}

	function selectRowsCompleteQuery( $query ) {
		global $wpdb;
		$res = $wpdb->get_results( $query );

		return $res;
	}

	function selectRowsbyField( $tblname, $by, $id = "", $extra = "" ) {
		global $wpdb;
		$subStr = "";
		if ( $id != '' ) {
			$subStr = " where $by='$id'";
		}
		$sSQL = "select * from " . $tblname . $subStr . $extra;
		$res  = $wpdb->get_results( $sSQL );

		return $res;
	}

	function checkRowExists( $tblname, $field, $grpinfo, $extracheck = "" ) {
		global $wpdb;
		if ( $field != "" ) {
			$substr = "";
			if ( $extracheck = "idCheck" ) {
				$substr = " and id!='" . $grpinfo['id'] . "'";
			}
			$sSQL = "select * from " . $tblname . " where " . $field . "='" . addslashes( wpmg_trimVal( $grpinfo[ $field ] ) ) . "' $substr";
			$res  = $wpdb->get_results( $sSQL );
			if ( sizeof( $res ) ) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}

	function getUserGroup( $tblname, $id, $type = '0' ) {
		global $wpdb;
		$sSQL = "select * from " . $tblname . " where user_id='" . $id . "'";
		$res  = $wpdb->get_results( $sSQL );
		if ( count( $res ) > 0 ) {
			foreach ( $res as $resg ) {
				$arrresult[ $resg->group_id ] = $resg->group_email_format;
			}

			return $arrresult;
		}
	}

	function getGroupUserCount( $tblname, $id ) {
		global $wpdb;
		$sSQL = "select * from " . $tblname . " where group_id='" . $id . "'";

		return $res = $wpdb->get_results( $sSQL );
	}

	function getCompleteUserGroups( $tblname, $tblnameuser, $id ) {
		global $wpdb;
		$sSQL = "select t1.*,t2.* from " . $tblname . " t1 inner join " . $tblnameuser . " t2 on t1.group_id = t2.id and t1.user_id='" . $id . "'";
		$res  = $wpdb->get_results( $sSQL );
		if ( count( $res ) > 0 ) {
			foreach ( $res as $resg ) {
				$arrresult[] = $resg;
			}

			return $arrresult;
		}
	}

	function addUserGroup( $tblname, $id, $grpinfo ) {
		global $wpdb;
		$myFields = "id,user_id,group_id,group_email_format";
		if ( count( $grpinfo['group_name'] ) > 0 ) {
			foreach ( $grpinfo['group_name'] as $key => $group_id ) {
				$emailformat = $grpinfo[ 'email_format_' . $group_id ];
				$sSQL        = "INSERT INTO " . $tblname . " ($myFields) VALUES ('',$id,'$group_id','$emailformat')";
				/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */
				$wpdb->query( $sSQL );
			}
		}

		return true;
	}

	function getGroupSerialized( $grpinfo ) {
		$arrresult = array();
		if ( count( $grpinfo['group_name'] ) > 0 ) {
			foreach ( $grpinfo['group_name'] as $key => $group_id ) {
				$emailformat            = $grpinfo[ 'email_format_' . $group_id ];
				$arrresult[ $group_id ] = $emailformat;
			}
		}

		return $arrresult;
	}

	function deleteUserGroup( $tblname, $groupid, $userid ) {
		global $wpdb;
		if ( $groupid != '' && $userid != '' ) {
			$sSQL = "DELETE FROM " . $tblname . " WHERE user_id = '" . $userid . "' and group_id = '" . $groupid . "'";
			/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */
			$wpdb->query( $sSQL );
		}

		return true;
	}

	function updUserGroup( $tblname, $id, $grpinfo ) {
		global $wpdb;
		$myFields         = "id,user_id,group_id,group_email_format";
		$getCurrentGroups = $this->getUserGroup( $tblname, $id, '1' );
		if ( count( $grpinfo['group_name'] ) > 0 && $getCurrentGroups ) {
			foreach ( $grpinfo['group_name'] as $key => $group_id ) {
				$emailformat = $grpinfo[ 'email_format_' . $group_id ];
				if ( ! in_array( $group_id, $getCurrentGroups ) ) {
					$sSQL = "INSERT INTO " . $tblname . " ($myFields) values ('',$id,'$group_id','$emailformat')";
					/* mysql_query($sSQL) or die (mysql_error().'Error, query failed'); */
					$wpdb->query( $sSQL );
				}
			}
		} else {
			$this->addUserGroup( $tblname, $id, $grpinfo );
		}

		return true;
	}

	function addUserGroupTaxonomy( $tblname, $id, $arrtoInsert ) {
		global $wpdb;
		if ( count( $arrtoInsert ) > 0 ) {
			$myFields = "id,user_id,group_id,group_email_format";
			foreach ( $arrtoInsert as $group_id => $emailformat ) {
				$sSQL = "INSERT INTO " . $tblname . " ($myFields) values ('',$id,'$group_id','$emailformat')";
				$wpdb->query( $sSQL );
			}
		}
	}

	function updUserGroupTaxonomy( $tblname, $id, $arrtoInsert ) {
		global $wpdb;
		$sSQLdel = "DELETE FROM " . $tblname . " WHERE user_id = '" . $id . "'";
		$wpdb->query( $sSQLdel );
		if ( count( $arrtoInsert ) > 0 ) {
			$myFields = "id,user_id,group_id,group_email_format";
			foreach ( $arrtoInsert as $group_id => $emailformat ) {
				$sSQL = "INSERT INTO " . $tblname . " ($myFields) values ('',$id,'$group_id','$emailformat')";
				$wpdb->query( $sSQL );
			}
		}
	}
}