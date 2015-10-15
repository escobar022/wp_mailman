<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );
/**
 * Send Email:
 * Looks for pending threads, sends to memebers if member has sent email
 *
 * @throws Exception
 * @throws phpmailerException
 */
function wpmg_cron_send_email() {

	//To debug, adjust settings here.
	$args = array(
		'post_type'   => 'mg_threads',
		'post_status' => 'draft',
		'perm'        => 'readable',
		'meta_key'    => 'mg_thread_email_status',
		'meta_value'  => 'Pending'
	);

	//All pending emails
	$query = new WP_Query( $args );

	$threads = $query->get_posts();

	if ( count( $threads ) > 0 ) {

		foreach ( $threads as $emailParsed ) {

			//Single Thread Information generated
			$thread_id       = $emailParsed->ID;
			$group_id        = get_post_meta( $thread_id, 'mg_thread_email_group_id', true );
			$senderEmail     = get_post_meta( $thread_id, 'mg_thread_email_from', true );
			$is_active_group = get_post_meta( $group_id, 'mg_group_status', true );

			$thread_subject = get_post_meta( $thread_id, 'mg_thread_email_subject', true );

			//Filter Out specific automated emails
			$test_out_office = "/out of the office/i";

			if ( preg_match( $test_out_office, $thread_subject ) ) {
				update_post_meta( $thread_id, 'mg_thread_email_status', 'Out of Office' );
				break;
			}
			$test_auto = "/automatic reply/i";

			if ( preg_match( $test_auto, $thread_subject ) ) {
				update_post_meta( $thread_id, 'mg_thread_email_status', 'Automatic Reply' );
				break;
			}

			//Checks to see if group is active and valid
			if ( $is_active_group == 2 && is_numeric( $group_id ) && $group_id > 0 ) {

				/* get sender user details */
				$senderUser   = get_user_by( "email", $senderEmail );
				$senderUserId = $senderUser->ID;
				$senderEmail  = $senderUser->user_email;

				//Checks if user is valid
				if ( is_numeric( $senderUserId ) ) {

					/* get other users from the sender user group */
					$args = array(
						'meta_query' => array(
							'relation' => 'AND',
							array(
								'key'     => 'mg_user_group_sub_arr',
								'value'   => '"' . $group_id . '"',
								'compare' => 'LIKE'
							),
							array(
								'key'   => 'mg_user_status',
								'value' => 1
							)
						)
					);

					$user_query = new WP_User_Query( $args );

					//Checks to see if sender is in group
					$in_group = false;

					foreach ( $user_query->get_results() as $memberstoSent ) {

						if ( $senderUserId == $memberstoSent->ID ) {
							$in_group = true;
							error_log(print_r($in_group,true));
							break;
						} else {
							$in_group = false;
						}

					}

					if ( $user_query->get_total() > 0 && $in_group == true ) {

						//Get group information to build email
						$groupTitle = get_the_title( $group_id );
						$groupEmail = get_post_meta( $group_id, 'mg_group_email', true );
						$mail_type  = get_post_meta( $group_id, 'mg_group_mail_type', true );

						//Email information
						$has_parent = get_post_meta( $thread_id, 'mg_thread_parent_id', true );
						$body       = get_post_meta( $thread_id, 'mg_thread_email_content', true );

						//Generates footer for email from group listing
						$footerText = nl2br( stripslashes( get_post_meta( $group_id, 'mg_group_footer_text', true ) ) );

						if ( empty( $has_parent ) ) {
							$footerText = str_replace( "{%grouptitle%}", $groupTitle, $footerText );
							$footerText = str_replace( "{%site_url%}", get_site_url(), $footerText );
							$footerText = str_replace( "{%archive_url%}", get_permalink( $group_id ), $footerText );
							$footerText = str_replace( "{%profile_url%}", get_admin_url( "", "profile.php" ), $footerText );
							//Needs development to add unsusbscribe to footer
//							$footerText = str_replace( "{%unsubscribe_url%}", get_bloginfo( 'wpurl' ) . '?unsubscribe=1&userid=' . $sendtouserId . '&group=' . $group_id, $footerText );
							$body .= $footerText;
						}

						if ( $mail_type == 'smtp' ) {
							global $phpmailer;
							if ( ! is_object( $phpmailer ) || ! is_a( $phpmailer, 'PHPMailer' ) ) {
								require_once ABSPATH . WPINC . '/class-phpmailer.php';
								require_once ABSPATH . WPINC . '/class-smtp.php';
								$phpmailer = new PHPMailer();
							}
							$mail = new PHPMailer();
							$mail->IsSMTP();
							$mail->SMTPDebug = 0;

							/*
							 * Connect to SMTP
							 */
							//Add Thread ID to references, if replied to, becomes parent of reply
							if ( get_post_meta( $group_id, 'mg_group_smtp_username', true ) != '' && get_post_meta( $group_id, 'mg_group_smtp_password', true ) != '' ) {
								$mail->Username   = get_post_meta( $group_id, 'mg_group_smtp_username', true );
								$mail->Password   = get_post_meta( $group_id, 'mg_group_smtp_password', true );
								$mail->SMTPAuth   = true;
								$mail->SMTPSecure = "ssl";
							} else {
								$mail->Username = $groupEmail;
								$mail->Password = get_post_meta( $group_id, 'mg_group_password', true );
								$mail->SMTPAuth = false;
							}

							$mail->Host = get_post_meta( $group_id, 'mg_group_smtp_server', true );
							$mail->Port = get_post_meta( $group_id, 'mg_group_smtp_port', true );

							/* Custom Headers

							$mail->addCustomHeader( 'Errors-To', 'no-reply@domain' );
							$mail->addCustomHeader( 'Return-Path', 'no-reply-bounces@domain' );
							*/
							$mail->addCustomHeader( 'references', '[' . $thread_id . ']' );
							$mail->addCustomHeader( 'sender', $groupEmail );

							//Set top level addresses
							$mail->SetFrom( $senderEmail );
							$mail->AddReplyTo( $groupEmail, $groupTitle );
							$mail->AddAddress( $groupEmail, $groupTitle );

							//Add each user in group to bcc email
							foreach ( $user_query->get_results() as $memberstoSent ) {

								$sendtouserId = $memberstoSent->ID;
								$Userrow      = get_user_by( "id", $sendtouserId );
								$sendToEmail  = $Userrow->user_email;

								$mail->addBCC( $sendToEmail );
							}

							//Subject fron thread
							$mail->Subject = get_post_meta( $thread_id, 'mg_thread_email_subject', true );

							//Email formating for body
//							$mail->IsHTML( true );
							$mail->MsgHTML( $body );
							$mail->CharSet = 'utf-8';
//							$mail->Body = $body;
							$alt_body      = nl2br( $mail->html2text( $body ) );
							$mail->AltBody = $alt_body;


							//Add attachments to email from thread
							$args = array(
								'numberposts' => - 1,
								'post_parent' => $thread_id,
								'post_status' => null,
								'post_type'   => 'attachment',
							);

							$attachments = get_children( $args );

							if ( $attachments ) {
								foreach ( $attachments as $attachment ) {
									if ( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) == 'ATTACHMENT' ) {
										$fullsize_path = get_attached_file( $attachment->ID );
										$filename_only = basename( $fullsize_path );

										//If is attached .eml(needs 8bit encoding)
										if ( $attachment->post_mime_type == 'message/rfc822' ) {
											$mail->addAttachment( $fullsize_path, $filename_only, '8bit' );
										} else {
											$mail->addAttachment( $fullsize_path, $filename_only );
										}
									}
								}
							}

							//If email is sent, update status and post
							if ( $mail->Send() ) {
								update_post_meta( $thread_id, 'mg_thread_email_status', 'Sent' );
								$thread_update = array(
									'ID'          => $thread_id,
									'post_status' => 'publish',
								);
								wp_update_post( $thread_update );
							} else {
								update_post_meta( $thread_id, 'mg_thread_email_status', 'Error' );
								update_post_meta( $thread_id, 'mg_thread_email_status_error', $mail->ErrorInfo );
							}
						}

						//Needs to be updated to SMTP format
						if ( $mail_type == 'php' ) {

							$mail_Subject = get_post_meta( $thread_id, 'mg_thread_email_subject', true );

							$to = array();

							foreach ( $user_query->get_results() as $memberstoSent ) {
								$sendtouserId = $memberstoSent->ID;
								$Userrow      = get_user_by( "id", $sendtouserId );
								$sendToEmail  = $Userrow->user_email;
								$to[]         = $sendToEmail;
							}

							$subject = $mail_Subject;

							$headers = 'From: ' . $groupTitle . '<' . $groupEmail . '>' . "\r\n";
							$headers .= 'Reply-To: <' . $senderEmail . '>' . "\r\n";
							$headers .= 'X-Mailer: PHP' . phpversion() . "\r\n";
							$headers .= 'MIME-Version: 1.0' . "\r\n";
							$headers .= 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=\"' . get_bloginfo( 'charset' ) . '\"' . "\r\n";
							$headers .= 'references: [' . $thread_id . ']' . "\r\n";

							$headers .= 'Content-type: text/html' . "\r\n";

							$php_sent = mail( $to, $subject, $body, $headers );

							if ( $php_sent ) {
								update_post_meta( $thread_id, 'mg_thread_email_status', 'Sent' );
								$thread_update = array(
									'ID'          => $thread_id,
									'post_status' => 'publish',
								);
								wp_update_post( $thread_update );
							} else {
								update_post_meta( $thread_id, 'mg_thread_email_status', 'Error' );
							}
						}

						//Needs to be updated to SMTP format
						if ( $mail_type == 'wp' ) {

							$mail_Subject = get_post_meta( $thread_id, 'mg_thread_email_subject', true );

							$to = array();

							foreach ( $user_query->get_results() as $memberstoSent ) {
								$sendtouserId = $memberstoSent->ID;
								$Userrow      = get_user_by( "id", $sendtouserId );
								$sendToEmail  = $Userrow->user_email;
								$to[]         = $sendToEmail;
							}

							$subject = $mail_Subject;

							$headers[] = 'From: ' . $groupTitle . '<' . $groupEmail . '>' . "\r\n";
							$headers[] = 'Reply-To: <' . $senderEmail . '>' . "\r\n";
							/* $headers[] = 'Cc: '. $sendToName .'<'.$sendToEmail.'>'."\r\n"; */
							$headers[] = 'X-Mailer: PHP' . phpversion() . "\r\n";
							$headers[] = 'MIME-Version: 1.0' . "\r\n";
							$headers[] = 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=\"' . get_bloginfo( 'charset' ) . '\"' . "\r\n";

							$headers[] = 'Content-type: text/html' . "\r\n";

							$args = array(
								'numberposts' => - 1,
								'post_parent' => $thread_id,
								'post_status' => null,
								'post_type'   => 'attachment',
							);

							$attachments = get_children( $args );

							$attachment_send = array();

							foreach ( $attachments as $attachment ) {
								if ( get_post_meta( $attachment->ID, '_wp_attachment_image_alt', true ) === 'ATTACHMENT' ) {
									$fullsize_path     = get_attached_file( $attachment->ID );
									$attachment_send[] = $fullsize_path;
								}
							}

							$wp_sent = wp_mail( $to, $subject, $body, $headers, $attachment_send );

							if ( $wp_sent ) {
								update_post_meta( $thread_id, 'mg_thread_email_status', 'Sent' );
								$thread_update = array(
									'ID'          => $thread_id,
									'post_status' => 'publish',
								);
								wp_update_post( $thread_update );
							} else {
								update_post_meta( $thread_id, 'mg_thread_email_status', 'Error' );
							}
						}

					} else {
						echo "No other user subscribed in this group!";
					}
				} else {
					echo "No Valid Sender Found in DB!";
				}
			} else {
				echo "No Valid Mailing Group Found!";
			}
		}
	} else {
		echo "No Parsed Email found!";
	}
}