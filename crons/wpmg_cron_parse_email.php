<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );
/*
 * Description: cron to parse emails to db from various groups
 * Created: 8/2013
 * Author: Marcus Sorensen & netforcelabs.com
 * Website: http://www.wpmailinggroup.com
 */

function wpmg_cron_parse_email() {
	global $wpdb, $obj, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_emails_attachments, $table_name_sent_emails, $table_name_crons_run, $table_name_users, $table_name_usermeta;

	$args  = array(
		'post_type'   => 'mg_groups',
		'post_status' => 'publish',
		'perm'        => 'readable',
	);
	$query = new WP_Query( $args );

	$groups = $query->get_posts();

	if ( count( $groups ) > 0 ) {
		foreach ( $groups as $row ) {

			$id              = $row->ID;
			$email           = get_post_meta( $id, 'mg_group_email', true );
			$password        = get_post_meta( $id, 'mg_group_password', true );
			$pop_server_type = get_post_meta( $id, 'mg_group_server_type', true );
			$pop_server      = get_post_meta( $id, 'mg_group_server', true );
			$pop_port        = get_post_meta( $id, 'mg_group_server_port', true );
			$pop_ssl         = get_post_meta( $id, 'pop_ssl', true );
			$pop_username    = get_post_meta( $id, 'mg_group_mail_username', true );
			$pop_password    = get_post_meta( $id, 'mg_group_password', true );

			if ( $pop_ssl != 'on' ) {
				$ssl = false;
			} else {
				$ssl = true;
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

			if ( $tot > 0 ) {
				for ( $i = $tot; $i > 0; $i -- ) {
					$head         = $obj->getHeaders( $i );  /*  Get Header Info Return Array Of Headers **Array Keys are (subject,to,toOth,toNameOth,from,fromName) */
					$mail         = $obj->getMail( $i );
					$emailContent = $mail->fetch_html_body();

					/* get bounced email if any */
					$bounced_email = "";
					if ( $head['type'] == 'bounced' ) {
						$bounced_email = $obj->get_bounced_email_address( $emailContent );
					}

					// Create post object
					$thread = array(
						'post_title'  => $head['subject'],
						'post_type'   => 'mg_threads',
						'post_status' => 'publish',
					);

// Insert the post into the database
					$pid = wp_insert_post( $thread );

					//ADD OUR CUSTOM FIELDS
					add_post_meta( $pid, 'mg_thread_type', $head['type'], true );
					add_post_meta( $pid, 'mg_thread_UID', $mail->UID, true );
					add_post_meta( $pid, 'mg_thread_references', $mail->references, true );
					if ( $bounced_email != '' ) {
						add_post_meta( $pid, 'mg_thread_email_bounced', $bounced_email, true );
					}
					add_post_meta( $pid, 'mg_thread_email_from', $head['from'], true );
					add_post_meta( $pid, 'mg_thread_email_from_name', $head['fromName'], true );
					add_post_meta( $pid, 'mg_thread_email_to', $head['to'], true );
					add_post_meta( $pid, 'mg_thread_email_to_name', $head['toName'], true );
					add_post_meta( $pid, 'mg_thread_email_subject', $head['subject'], true );
					add_post_meta( $pid, 'mg_thread_email_content', $emailContent, true );
					add_post_meta( $pid, 'mg_thread_email_group_id', $id, true );
					add_post_meta( $pid, 'mg_thread_email_status', 0, true );
					add_post_meta( $pid, 'mg_thread_date', $mail->date, true );

					$attachments = $mail->getAttachments();

					foreach ( $attachments as $attachment ) {

						$wp_res = $attachment->wordpresdir;

						if ( ! $wp_res['error'] ) {
							$wp_filetype   = wp_check_filetype( $attachment->name, null );
							$attachment    = array(
								'post_mime_type' => $wp_filetype['type'],
								'post_parent'    => $pid,
								'post_title'     => preg_replace( '/\.[^.]+$/', '', $attachment->name ),
								'post_content'   => '',
								'post_excerpt'   => $attachment->disposition,
								'post_status'    => 'inherit'
							);
							$attachment_id = wp_insert_attachment( $attachment, $wp_res['file'], $pid );
							if ( ! is_wp_error( $attachment_id ) ) {
								require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
								$attachment_data = wp_generate_attachment_metadata( $attachment_id, $wp_res['file'] );
								wp_update_attachment_metadata( $attachment_id, $attachment_data );
							}
						}
					}
					$obj->deleteMail( $i );
				}
			} else {
				echo "No Email Found.";
			}
			$obj->close_mailbox();   /* Close Mail Box */
		}
	}
}