<?php
defined( 'ABSPATH' ) or die( "Cannot access pages directly." );
/*
 * Description: Cron to send emails to registered users in a particular mailing group
 * Created: 08/2013
 * Author: Marcus Sorensen & netforcelabs.com
 * Website: http://www.wpmailinggroup.com
 */

function wpmg_cron_send_email() {
	global $wpdb, $objMem, $obj,$table_name_group, $table_name_message, $table_name_requestmanager, $table_name_requestmanager_taxonomy, $table_name_user_taxonomy, $table_name_parsed_emails, $table_name_emails_attachments, $table_name_sent_emails, $table_name_crons_run, $table_name_users, $table_name_usermeta;

	require_once( WPMG_PLUGIN_URL . 'lib/mailinggroupclass.php' );
	$objMem = new mailinggroupClass();

	$mailresult = $objMem->selectRows( $table_name_parsed_emails, "", " where status = '0' and type='email' order by id desc limit 0, 1" );

	if ( count( $mailresult ) > 0 ) {
		foreach ( $mailresult as $emailParsed ) {
			$receiverGroupId = $emailParsed->email_group_id;
			$receiverMailId  = $emailParsed->id;
			$senderEmail     = $emailParsed->email_from;

			/* get group details */
			$resultGroup = $objMem->selectRows( $table_name_group, "", " where id = '" . $receiverGroupId . "' order by id desc" );
			$resultGroup = $resultGroup[0];

			if ( is_numeric( $resultGroup->id ) && $resultGroup->id > 0 ) {
				/* get sender user details */
				$senderUser = $objMem->selectRows( $table_name_users, "", " where user_email='$senderEmail'" );
				/* $senderUser = get_user_by("email", $senderEmail); */

				$senderUserId = $senderUser[0]->ID;
				$senderName   = $senderUser[0]->display_name;
				$senderEmail  = $senderUser[0]->user_email;

				if ( is_numeric( $senderUserId ) ) {
					/* get other users from the sender user group */
					$membersGroup = $objMem->selectRows( $table_name_user_taxonomy, "", " where group_id = '" . $receiverGroupId . "' order by id desc" );

					if ( count( $membersGroup ) > 0 ) {
						foreach ( $membersGroup as $key => $memberstoSent ) {
							$footerText            = wpmg_nl2brformat( wpmg_dbStripslashes( $resultGroup->footer_text ) );
							$groupTitle            = $resultGroup->title;
							$groupEmail            = $resultGroup->email;
							$useinSubject          = $resultGroup->use_in_subject;
							$mail_type             = $resultGroup->mail_type;
							$sendtouserId          = $memberstoSent->user_id;
							$sendtouserEmailFormat = $memberstoSent->group_email_format;

							$sentUserDetails = $objMem->selectRows( $table_name_users, "", " where ID='$sendtouserId'" );

							$Ustatus = $objMem->selectRows( $table_name_usermeta, "", " where meta_key='User_status' and user_id='$sendtouserId'" );

							$Ustatus     = $Ustatus[0]->meta_value;
							$sendToName  = $sentUserDetails[0]->display_name;
							$sendToEmail = $sentUserDetails[0]->user_email;

							if ( $Ustatus == 1 ) {
								$body       = $emailParsed->email_content;
								$footerText = str_replace( "{%name%}", $sendToName, $footerText );
								$footerText = str_replace( "{%email%}", $sendToEmail, $footerText );
								$footerText = str_replace( "{%site_url%}", get_site_url(), $footerText );
								$footerText = str_replace( "{%archive_url%}", get_admin_url( "", "admin.php?page=mailinggroup_memberarchive" ), $footerText );
								$footerText = str_replace( "{%profile_url%}", get_admin_url( "", "profile.php" ), $footerText );
								$footerText = str_replace( "{%unsubscribe_url%}", get_bloginfo( 'wpurl' ) . '?unsubscribe=1&userid=' . $sendtouserId . '&group=' . $receiverGroupId, $footerText );
								$body .= $footerText;
								$_ARRDB['user_id']   = $sendtouserId;
								$_ARRDB['email_id']  = $receiverMailId;
								$_ARRDB['group_id']  = $receiverGroupId;
								$_ARRDB['sent_date'] = date( "Y-m-d H:i:s" );
								$_ARRDB['error_msg'] = "";

								if ( $mail_type == 'smtp' ) {
									global $phpmailer;
									if ( !is_object( $phpmailer ) || !is_a( $phpmailer, 'PHPMailer' ) ) {
										require_once ABSPATH . WPINC . '/class-phpmailer.php';
										require_once ABSPATH . WPINC . '/class-smtp.php';
										$phpmailer = new PHPMailer();
									}
									$mail = new PHPMailer();
									$mail->IsSMTP();
									$mail->SMTPDebug = 0;

									if ( $resultGroup->smtp_username != '' && $resultGroup->smtp_password != '' ) {
										$mail->Username   = $resultGroup->smtp_username;
										$mail->Password   = $resultGroup->smtp_password;
										$mail->SMTPAuth   = true;
										$mail->SMTPSecure = "ssl";

									} else {
										$mail->Username = $resultGroup->email;
										$mail->Password = $resultGroup->password;
										$mail->SMTPAuth = false;
									}
									$mail->Host   = $resultGroup->smtp_server;
									$mail->Port   = $resultGroup->smtp_port;
									$mail->Sender = $resultGroup->email;

									$mail->SetFrom( $senderEmail, $senderName );
									/* reply to */
									$mail->AddReplyTo( $groupEmail, $groupTitle );

									if ( $useinSubject ) {
										$mail->Subject = "[" . $groupTitle . "] " . $emailParsed->email_subject;
									} else {
										$mail->Subject = $emailParsed->email_subject;
									}
									if ( $sendtouserEmailFormat == '1' ) {
										$mail->IsHTML( true );
									} else {
										$mail->IsHTML( false );
									}
									$mail->MsgHTML( $body );
									$mail->AddAddress( $sendToEmail, $sendToName );

									$attachments = $objMem->selectRowsbyField( $table_name_emails_attachments, "IDEMAIL", $receiverMailId, "and AttachType='ATTACHMENT'" );

									foreach($attachments as $ATTACHMENT){
										$mail->addAttachment( $ATTACHMENT->Filedir , $ATTACHMENT->FileNameOrg);
									}
									if ( ! $mail->Send() ) {
										$_ARRDB['status']    = "0";
										$_ARRDB['error_msg'] = $mail->ErrorInfo;
									} else {
										$_ARRDB['status'] = "1";
									}
								}
								if ( $mail_type == 'php' ) {
									if ( $useinSubject ) {
										$mail_Subject = "[" . $groupTitle . "] " . $emailParsed->email_subject;
									} else {
										$mail_Subject = $emailParsed->email_subject;
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
										$mail_Subject = "[" . $groupTitle . "] " . $emailParsed->email_subject;
									} else {
										$mail_Subject = $emailParsed->email_subject;
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