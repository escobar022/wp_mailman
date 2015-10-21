<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );


function wpmg_cron_parse_email() {
	global $obj;

	//Gets groups that are active
	$args  = array(
		'post_type'   => 'mg_groups',
		'post_status' => array( 'publish', 'private' ),
		'meta_key'    => 'mg_group_status',
		'meta_value'  => '2',
		'posts_per_page' => - 1
	);
	$query = new WP_Query( $args );

	//All Active Groups
	$groups = $query->get_posts();

	if ( count( $groups ) > 0 ) {
		//For each active group, get email.
		foreach ( $groups as $row ) {

			//Set connection settings for group. (See mg_groups post type)
			$group_id        = $row->ID;
			$group_email     = get_post_meta( $group_id, 'mg_group_email', true );
			$group_password  = get_post_meta( $group_id, 'mg_group_password', true );
			$server_type     = get_post_meta( $group_id, 'mg_group_server_type', true );
			$server_address  = get_post_meta( $group_id, 'mg_group_server', true );
			$server_port     = get_post_meta( $group_id, 'mg_group_server_port', true );
			$pop_ssl         = get_post_meta( $group_id, 'mg_group_pop_ssl', true );
			$server_username = get_post_meta( $group_id, 'mg_group_mail_username', true );
			$server_password = get_post_meta( $group_id, 'mg_group_password', true );

			if ( $pop_ssl != 'on' ) {
				$ssl = false;
			} else {
				$ssl = true;
			}

			if ( $server_username != '' && $server_password != '' ) {
				$obj->receiveMail( $server_username, $server_password, $group_email, $server_address, $server_type, $server_port, $ssl );
			} else {
				$obj->receiveMail( $group_email, $group_password, $group_email, $server_address, $server_type, $server_port, false );
			}

			/* Connect to the Mail Box */
			$obj->getImapStream(); /* If connection fails give error message and exit */

			/* Get Total Number of Unread Email in mail box */
			$tot = $obj->getTotalMails(); /* Total Mails in Inbox Return integer value */

			if ( $tot > 0 ) {
				for ( $i = $tot; $i > 0; $i -- ) {

					//Gets headers for current email
					$head = $obj->getHeaders( $i );

					//Checks to see if email is on behalf of group(from itself)
					if ( $head['sender'] == $group_email ) {

						$obj->deleteMail( $i );

					} else {
						//Get body and parts/attachements
						$mail         = $obj->getMail( $i );

						//Get email html body, if not use plain text received
						$emailContent = $mail->fetch_html_body();
						if ( empty( $emailContent ) ) {
							$emailContent = nl2br( $mail->textPlain );
						}

						//Looks for ID in References Header, This is the Parent ID of the Thread
						preg_match( '#\[(.*)\]#', $mail->references, $match );
						$parent_ID = $match[1];

						//If not parent, add group title to subject
						if ( empty( $parent_ID ) ) {
							$subject_head = "[" . get_the_title( $group_id ) . "] " . $head['subject'];
						} else {
							//If child dont add title
							$subject_head = $head['subject'];
						}

						$hashed_title = hash( 'crc32b', $subject_head );

						/* get bounced email if any */
						$bounced_email = "";
						if ( $head['type'] == 'bounced' ) {
							$bounced_email = $obj->get_bounced_email_address( $emailContent );
						}

						// Create post object
						$thread = array(
							'post_title'  => $subject_head,
							'post_type'   => 'mg_threads',
							'post_status' => 'draft',
							'tags_input'  => get_the_title( $group_id ),
							'post_parent' => $parent_ID,
							'post_name'   => $hashed_title
						);

						// Insert the post into the database
						$pid = wp_insert_post( $thread );

						//ADD OUR CUSTOM FIELDS
						add_post_meta( $pid, 'mg_thread_type', $head['type'], true );
						add_post_meta( $pid, 'mg_thread_UID', $mail->UID, true );
						add_post_meta( $pid, 'mg_thread_references', $mail->references, true );
						add_post_meta( $pid, 'mg_thread_parent_id', $parent_ID, true );
						if ( $bounced_email != '' ) {
							add_post_meta( $pid, 'mg_thread_email_bounced', $bounced_email, true );
						}
						add_post_meta( $pid, 'mg_thread_email_from', $head['from'], true );
						add_post_meta( $pid, 'mg_thread_email_from_name', $head['fromName'], true );
						add_post_meta( $pid, 'mg_thread_email_to', $head['to'], true );
						add_post_meta( $pid, 'mg_thread_email_to_name', $head['toName'], true );
						add_post_meta( $pid, 'mg_thread_email_subject', $subject_head, true );
						add_post_meta( $pid, 'mg_thread_email_content', addslashes( $emailContent ), true );
						add_post_meta( $pid, 'mg_thread_email_group_id', $group_id, true );
						add_post_meta( $pid, 'mg_thread_email_status', 'Pending', true );
						add_post_meta( $pid, 'mg_thread_date', $mail->date, true );

						//Get attachments from email and add them to WP Media
						$attachments = $mail->getAttachments();

						foreach ( $attachments as $attachment ) {

							$wp_res = $attachment->wordpresdir;

							if ( ! $wp_res['error'] ) {
								$wp_filetype     = wp_check_filetype( $attachment->name, null );
								$attached_insert = array(
									'post_mime_type' => $wp_filetype['type'],
									'post_parent'    => $pid,
									'post_title'     => preg_replace( '/\.[^.]+$/', '', $attachment->name ),
									'post_content'   => '',
									'post_status'    => 'inherit'
								);
								$attachment_id   = wp_insert_attachment( $attached_insert, $wp_res['file'], $pid );

								if ( ! is_wp_error( $attachment_id ) ) {
									add_post_meta( $attachment_id, '_wp_attachment_image_alt', $attachment->disposition, true );
									require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
									$attachment_data = wp_generate_attachment_metadata( $attachment_id, $wp_res['file'] );
									wp_update_attachment_metadata( $attachment_id, $attachment_data );
								}
							}
						}
						//debug
						$obj->deleteMail( $i );
					}
				}
			} else {
				echo "No Email Found.";
			}
			$obj->close_mailbox();   /* Close Mail Box */
		}
	}
}