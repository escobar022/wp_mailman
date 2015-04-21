<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );
/*
 * Description: cron to parse emails to db from various groups
 * Created: 8/2013
 * Author: Marcus Sorensen & netforcelabs.com
 * Website: http://www.wpmailinggroup.com
 */
/**
 *
 */
function wpmg_cron_parse_email() {
	global $wpdb, $objMem, $obj, $table_name_group, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails,$table_name_emails_attachments, $table_name_sent_emails, $table_name_crons_run, $table_name_users, $table_name_usermeta;

	require_once( WPMG_PLUGIN_URL . 'lib/mailinggroupclass.php' );
	$objMem = new mailinggroupClass();

	/* get all groups one by one */
	$groupresult = $objMem->selectRows( $table_name_group, "", " where status = '1' order by id desc" );
	if ( count( $groupresult ) > 0 ) {
		foreach ( $groupresult as $row ) {
			$id              = $row->id;
			$email           = $row->email;
			$password        = $row->password;
			$pop_server_type = $row->pop_server_type;
			$pop_server      = $row->pop_server;
			$pop_port        = $row->pop_port;
			$pop_ssl         = $row->pop_ssl;
			$pop_username    = $row->pop_username;
			$pop_password    = $row->pop_password;

			if ( $pop_ssl == 1 ) {
				$ssl = true;
			} else {
				$ssl = false;
			}

			if ( $pop_username != '' && $pop_password != '' ) {
				$obj->receiveMail( $pop_username, $pop_password, $email, $pop_server, $pop_server_type, $pop_port, $ssl );
			} else {
				$obj->receiveMail( $email, $password, $email, $pop_server, $pop_server_type, $pop_port, false );
			}
			/* Connect to the Mail Box */
			$obj->getImapStream(); /* If connection fails give error message and exit */

			/* Get Total Number of Unread Email in mail box */
			$tot = $obj->getTotalMails(); /* Total Mails in Inbox Return integer value */

			$myFields = array(
				"id",
				"type",
				"email_bounced",
				"email_from",
				"email_from_name",
				"email_to",
				"email_to_name",
				"email_subject",
				"email_content",
				"email_group_id",
				"status"
			);

			$myFieldsAttachment = array(
				"id",
				"IDEmail",
				"FileNameOrg",
				"Filedir",
				"AttachType"
			);

			if ( $tot > 0 ) {
				for ( $i = $tot; $i > 0; $i -- ) {
					$head         = $obj->getHeaders( $i );  /*  Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName) */
					$mail = $obj->getMail( $i );
					$emailContent =$mail->fetch_html_body();

					/* get bounced email if any */
					$bounced_email = "";
					if ( $head['type'] == 'bounced' ) {
						$bounced_email = $obj->get_bounced_email_address( $emailContent );
					}
					/* Insert into database and delete from server */
					$_ARRDB['type']            = $head['type'];
					$_ARRDB['email_from']      = $head['from'];
					$_ARRDB['email_from_name'] = $head['fromName'];
					$_ARRDB['email_to']        = $head['to'];
					$_ARRDB['email_to_name']   = $head['toName'];
					$_ARRDB['email_subject']   = $head['subject'];
					$_ARRDB['email_content']   = $emailContent;
					$_ARRDB['email_group_id']  = $id;
					$_ARRDB['status']          = "0";
					if ( $bounced_email != '' ) {
						$_ARRDB['email_bounced'] = $bounced_email;
					}
					$newid = $objMem->addNewRow( $table_name_parsed_emails, $_ARRDB, $myFields );

					$attachments = $mail->getAttachments();

					foreach ( $attachments as $attachment ) {
						$_ARRDB2['IDEmail']            = $newid;
						$_ARRDB2['FileNameOrg']      = $attachment->name;
						$_ARRDB2['Filedir'] = $attachment->filePath;
						$_ARRDB2['AttachType'] = $attachment->disposition;
						$objMem->addNewRow($table_name_emails_attachments, $_ARRDB2, $myFieldsAttachment );
					}

					$obj->deleteMail( $i);

				}
			} else {
				echo "No Email Found.";
			}
			$obj->close_mailbox();   /* Close Mail Box */
		}
	}
}