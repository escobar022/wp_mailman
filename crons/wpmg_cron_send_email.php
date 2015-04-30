<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );
/*
 * Description: Cron to send emails to registered users in a particular mailing group
 * Created: 08/2013
 * Author: Marcus Sorensen & netforcelabs.com
 * Website: http://www.wpmailinggroup.com
 */

function wpmg_cron_send_email() {
	global $wpdb, $objMem, $obj, $table_name_group, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_emails_attachments, $table_name_sent_emails, $table_name_crons_run, $table_name_users, $table_name_usermeta;

	require_once( WPMG_PLUGIN_URL . 'lib/mailinggroupclass.php' );
	$objMem = new mailinggroupClass();

	$mailresult = $objMem->selectRows( $table_name_parsed_emails, "", " where status = '0' and type='email' order by id desc limit 0, 1" );

	$args  = array(
		'post_type'   => 'mg_threads',
		'post_status' => 'publish',
		'perm'        => 'readable',
		'meta_key'   => 'mg_thread_email_status',
		'meta_value' => 'Pending'

	);
	$query = new WP_Query( $args );

	$threads = $query->get_posts();

	if ( count( $threads ) > 0 )  {

		foreach ( $threads as $emailParsed ) {

			$receiverMailId  = $emailParsed->ID;
			$receiverGroupId = get_post_meta( $receiverMailId, 'mg_thread_email_group_id', true );
			$senderEmail     = get_post_meta( $receiverMailId, 'mg_thread_email_from', true );

			if ( is_numeric( $receiverGroupId ) && $receiverGroupId > 0 ) {

				/* get sender user details */
				$senderUser = get_user_by("email", $senderEmail);
				$senderUserId = $senderUser->ID;
				$senderName   = $senderUser->display_name;
				$senderEmail  = $senderUser->user_email;

				if ( is_numeric( $senderUserId ) ) {
					/* get other users from the sender user group */
					$membersGroup = $objMem->selectRows( $table_name_user_taxonomy, "", " where group_id = '" . $receiverGroupId . "' order by id desc" );

					if ( count( $membersGroup ) > 0 ) {
						foreach ( $membersGroup as $key => $memberstoSent ) {

							$footerText            = wpmg_nl2brformat( wpmg_dbStripslashes( get_post_meta( $receiverGroupId, 'mg_group_footer_text', true ) ) );
							$groupTitle            = get_the_title($receiverGroupId);
							$groupEmail            = get_post_meta( $receiverGroupId, 'mg_group_email', true );
							$useinSubject          = get_post_meta( $receiverGroupId, 'mg_group_use_in_subject', true );
							$mail_type             = get_post_meta( $receiverGroupId, 'mg_group_mail_type', true );
							$sendtouserId          = $memberstoSent->user_id;
							$sendtouserEmailFormat = $memberstoSent->group_email_format;

							$sentUserDetails = $objMem->selectRows( $table_name_users, "", " where ID='$sendtouserId'" );

							$Ustatus = $objMem->selectRows( $table_name_usermeta, "", " where meta_key='User_status' and user_id='$sendtouserId'" );

							$Ustatus     = $Ustatus[0]->meta_value;
							$sendToName  = $sentUserDetails[0]->display_name;
							$sendToEmail = $sentUserDetails[0]->user_email;

							if ( $Ustatus == 1 ) {
								$body       = get_post_meta( $receiverMailId, 'mg_thread_email_content', true );
								$footerText = str_replace( "{%name%}", $sendToName, $footerText );
								$footerText = str_replace( "{%email%}", $sendToEmail, $footerText );
								$footerText = str_replace( "{%site_url%}", get_site_url(), $footerText );
								$footerText = str_replace( "{%archive_url%}", get_admin_url( "", "admin.php?page=mailinggroup_memberarchive" ), $footerText );
								$footerText = str_replace( "{%profile_url%}", get_admin_url( "", "profile.php" ), $footerText );
								$footerText = str_replace( "{%unsubscribe_url%}", get_bloginfo( 'wpurl' ) . '?unsubscribe=1&userid=' . $sendtouserId . '&group=' . $receiverGroupId, $footerText );
								$body .= $footerText;


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
									$mail->addCustomHeader('references',get_post_meta( $receiverMailId, 'mg_thread_UID', true ));

									if ( get_post_meta( $receiverGroupId, 'mg_group_smtp_username', true ) != '' && get_post_meta( $receiverGroupId, 'mg_group_smtp_password', true ) != '' ) {
										$mail->Username   = get_post_meta( $receiverGroupId, 'mg_group_smtp_username', true );
										$mail->Password   = get_post_meta( $receiverGroupId, 'mg_group_smtp_password', true );
										$mail->SMTPAuth   = true;
										$mail->SMTPSecure = "ssl";

									} else {
										$mail->Username = $groupEmail;
										$mail->Password = get_post_meta( $receiverGroupId, 'mg_group_password', true );
										$mail->SMTPAuth = false;
									}
									$mail->Host   = get_post_meta( $receiverGroupId, 'mg_group_smtp_server', true );
									$mail->Port   = get_post_meta( $receiverGroupId, 'mg_group_smtp_port', true );
									$mail->Sender = $groupEmail;
									$mail->SetFrom( $senderEmail, $senderName );
									/* reply to */
									$mail->AddReplyTo( $groupEmail, $groupTitle );

									if ( $useinSubject ) {
										$mail->Subject = "[" . $groupTitle . "] " . get_post_meta( $receiverMailId, 'mg_thread_email_subject', true );
									} else {
										$mail->Subject =  get_post_meta( $receiverMailId, 'mg_thread_email_subject', true );
									}
									if ( $sendtouserEmailFormat == '1' ) {
										$mail->IsHTML( true );
									} else {
										$mail->IsHTML( false );
									}
									$mail->MsgHTML( $body );
									$mail->AddAddress( $sendToEmail, $sendToName );

									$args = array(
										'numberposts' => -1,
										'post_parent' => $receiverMailId,
										'post_status' => null,
										'post_type' => 'attachment',
									);

									$attachments = get_children( $args );

									if ( $attachments ) {
										foreach ( $attachments as $attachment ) {
											$fullsize_path = get_attached_file( $attachment->ID );
											$filename_only = basename($fullsize_path);
											$mail->addAttachment($fullsize_path, $filename_only );
										}
									}

									if ( ! $mail->Send() ) {
										update_post_meta($receiverMailId,'mg_thread_email_status','Error');
										update_post_meta($receiverMailId,'mg_thread_email_status_error',$mail->ErrorInfo);
									} else {
										update_post_meta($receiverMailId,'mg_thread_email_status','Sent');
									}

								}
								if ( $mail_type == 'php' ) {
									if ( $useinSubject ) {
										$mail_Subject = "[" . $groupTitle . "] " .  get_post_meta( $receiverMailId, 'mg_thread_email_subject', true );
									} else {
										$mail_Subject =  get_post_meta( $receiverMailId, 'mg_thread_email_subject', true );
									}

									$to      = $sendToEmail;
									$subject = $mail_Subject;

									$headers = 'From: ' . $groupTitle . '<' . $groupEmail . '>' . "\r\n";
									$headers .= 'Reply-To: ' . $senderName . '<' . $senderEmail . '>' . "\r\n";
									/* $headers .= 'Cc: '. $sendToName .'<'.$sendToEmail.'>'."\r\n"; */
									$headers .= 'X-Mailer: PHP' . phpversion() . "\r\n";
									$headers .= 'MIME-Version: 1.0' . "\r\n";
									$headers .= 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=\"' . get_bloginfo( 'charset' ) . '\"' . "\r\n";
									if ( $sendtouserEmailFormat == '1' ) {
										$headers .= 'Content-type: text/html' . "\r\n";
									} else {
										$headers .= 'Content-type: text/plain' . "\r\n";
									}

									$php_sent = mail( $to, $subject, $body, $headers );

									if ( $php_sent ) {
										$_ARRDB['status'] = "1";
									} else {
										$_ARRDB['status']    = "0";
										$_ARRDB['error_msg'] = $mail->ErrorInfo;
									}
								}
								if ( $mail_type == 'wp' ) {
									if ( $useinSubject ) {
										$mail_Subject = "[" . $groupTitle . "] " . get_post_meta( $receiverMailId, 'mg_thread_email_subject', true );
									} else {
										$mail_Subject =  get_post_meta( $receiverMailId, 'mg_thread_email_subject', true );
									}

									$to      = $sendToEmail;
									$subject = $mail_Subject;

									$headers[] = 'From: ' . $groupTitle . '<' . $groupEmail . '>' . "\r\n";
									$headers[] = 'Reply-To: ' . $senderName . '<' . $senderEmail . '>' . "\r\n";
									/* $headers[] = 'Cc: '. $sendToName .'<'.$sendToEmail.'>'."\r\n"; */
									$headers[] = 'X-Mailer: PHP' . phpversion() . "\r\n";
									$headers[] = 'MIME-Version: 1.0' . "\r\n";
									$headers[] = 'Content-Type: ' . get_bloginfo( 'html_type' ) . '; charset=\"' . get_bloginfo( 'charset' ) . '\"' . "\r\n";
									if ( $sendtouserEmailFormat == '1' ) {
										$headers[] = 'Content-type: text/html' . "\r\n";
									} else {
										$headers[] = 'Content-type: text/plain' . "\r\n";
									}

									$attachments = $objMem->selectRowsbyField( $table_name_emails_attachments, "IDEMAIL", $receiverMailId, "and AttachType='ATTACHMENT'" );


									$attachment_send = array();

									foreach ( $attachments as $attachment ) {
										$attachment_send[] = $attachment->Filedir;
									}

									$wp_sent = wp_mail( $to, $subject, $body, $headers, $attachment_send );

									if ( $wp_sent ) {
										$_ARRDB['status'] = "1";
									} else {
										$_ARRDB['status']    = "0";
										$_ARRDB['error_msg'] = $mail->ErrorInfo;
									}
								}
								$myFields = array(
									"id",
									"user_id",
									"email_id",
									"group_id",
									"sent_date",
									"status",
									"error_msg"
								);
								$objMem->addNewRow( $table_name_sent_emails, $_ARRDB, $myFields );
							}

						}
						$fields            = array( "id", "status" );
						$grpinfo['id']     = $receiverMailId;
						$grpinfo['status'] = "1";
						$objMem->updRow( $table_name_parsed_emails, $grpinfo, $fields );
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