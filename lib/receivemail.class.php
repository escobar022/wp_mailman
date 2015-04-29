<?php
// Main ReciveMail Class File - Version 1.1 (02-06-2009)
/*
 * File: recivemail.class.php
 * Description: Reciving mail With Attachment
 * Version: 1.1
 * Created: 01-03-2006
 * Modified: 02-06-2009
 * Author: Sunil Verma
 * Email: sanford@123789.org
 */

/***************** Changes *********************
 *
 * 1) Added feature to retrive embedded attachment
 * 2) Added SSL Supported mailbox.
 *
 **************************************************/
class receiveMail {
	var $server = '';
	var $username = '';
	var $password = '';
	var $marubox = '';
	var $email = '';
	protected $attachmentsDir;
	var $serverEncoding = 'utf-8';
	var $addAttachment = '';

	function receiveMail( $username, $password, $EmailAddress, $mailserver, $servertype, $port, $ssl ) {
		if ( $servertype == 'imap' ) {
			if ( $port == '' ) {
				$port = '143';
			}
			$strConnect = '{' . $mailserver . ':' . $port . '}INBOX';
		} else {
			$strConnect = '{' . $mailserver . ':' . $port . '/pop3/novalidate-cert' . ( $ssl ? "/ssl" : "" ) . '}INBOX';
		}
		$this->server   = $strConnect;
		$this->username = $username;
		$this->password = $password;
		$this->email    = $EmailAddress;
	}

	public function getImapStream( $forceConnection = true ) {
		static $imapStream;
		if ( $forceConnection ) {
			if ( $imapStream && ( ! is_resource( $imapStream ) || ! imap_ping( $imapStream ) ) ) {
				$this->disconnect();
				$imapStream = null;
			}
			if ( ! $imapStream ) {
				$imapStream = $this->initImapStream();
			}
		}

		return $imapStream;
	}

	protected function initImapStream() {
		$imapStream = @imap_open( $this->server, $this->username, $this->password/*, 0, 0, array( 'DISABLE_AUTHENTICATOR' => 'GSSAPI' ) */ );
		if ( ! $imapStream ) {
			throw new ImapMailboxException( 'Connection error: ' . imap_last_error() );
		}

		return $imapStream;
	}

	protected function disconnect() {
		$imapStream = $this->getImapStream( false );
		if ( $imapStream && is_resource( $imapStream ) ) {
			imap_close( $imapStream, CL_EXPUNGE );
		}
	}

	public function deleteMail( $mailId ) {
		return imap_delete( $this->getImapStream(), $mailId, FT_UID );
	}

	function get_bounced_email_address( $content ) {
		$matches = array(); /* create array */
		$pattern = '/[A-Za-z0-9_-]+@[A-Za-z0-9_-]+\.([A-Za-z0-9_-][A-Za-z0-9_]+)/'; /* regex for pattern of e-mail address */
		preg_match( $pattern, $content, $matches ); /* find matching pattern */

		return $matches[0];
	}

	function getHeaders( $mid ) /* Get Header info */ {
		if ( ! $this->getImapStream() ) {
			return false;
		}

		$mail_details   = '';
		$mail_header    = imap_header( $this->getImapStream(), $mid );
		$receiver       = $mail_header->to[0];
		$sender         = $mail_header->from[0];
		$sender_replyto = $mail_header->reply_to[0];
		if ( strtolower( $sender->mailbox ) != 'postmaster' ) {
			$mail_details = array(
				'from'      => strtolower( $sender->mailbox ) . '@' . $sender->host,
				'fromName'  => $sender->personal,
				'toOth'     => strtolower( $sender_replyto->mailbox ) . '@' . $sender_replyto->host,
				'toNameOth' => $sender_replyto->personal,
				'subject'   => $mail_header->subject,
				'to'        => $this->email,
				'toName'    => $receiver->personal,
				'date'      => date( "d/m/Y H:i", strtotime( $mail_header->Date ) ),
				'type'      => "email"
			);
			if ( strtolower( $sender->mailbox ) == 'mailer-daemon' ) {
				$mail_details['type'] = 'bounced';
			}
		}

		return $mail_details;
	}


	function getTotalMails() /* Get Total Number off Unread Email In Mailbox */ {
		if ( ! $this->getImapStream() ) {
			return false;
		}

		$headers = imap_headers( $this->getImapStream() );

		return count( $headers );
	}

	public function getMail( $mailId ) {
		$head              = imap_rfc822_parse_headers( imap_fetchheader( $this->getImapStream(), $mailId, FT_UID ) );
		$mail              = new IncomingMail();
		$mail->id          = $mailId;
		$mail->UID         = $head->message_id;
		$mail->references  = $head->references;
		$mail->date        = date( 'Y-m-d H:i:s', isset( $head->date ) ? strtotime( $head->date ) : time() );
		$mail->subject     = isset( $head->subject ) ? $this->decodeMimeStr( $head->subject, $this->serverEncoding ) : null;
		$mail->fromName    = isset( $head->from[0]->personal ) ? $this->decodeMimeStr( $head->from[0]->personal, $this->serverEncoding ) : null;
		$mail->fromAddress = strtolower( $head->from[0]->mailbox . '@' . $head->from[0]->host );

		if ( isset( $head->to ) ) {
			$toStrings = array();
			foreach ( $head->to as $to ) {
				if ( ! empty( $to->mailbox ) && ! empty( $to->host ) ) {
					$toEmail              = strtolower( $to->mailbox . '@' . $to->host );
					$toName               = isset( $to->personal ) ? $this->decodeMimeStr( $to->personal, $this->serverEncoding ) : null;
					$toStrings[]          = $toName ? "$toName <$toEmail>" : $toEmail;
					$mail->to[ $toEmail ] = $toName;
				}
			}
			$mail->toString = implode( ', ', $toStrings );
		}

		if ( isset( $head->cc ) ) {
			foreach ( $head->cc as $cc ) {
				$mail->cc[ strtolower( $cc->mailbox . '@' . $cc->host ) ] = isset( $cc->personal ) ? $this->decodeMimeStr( $cc->personal, $this->serverEncoding ) : null;
			}
		}

		if ( isset( $head->reply_to ) ) {
			foreach ( $head->reply_to as $replyTo ) {
				$mail->replyTo[ strtolower( $replyTo->mailbox . '@' . $replyTo->host ) ] = isset( $replyTo->personal ) ? $this->decodeMimeStr( $replyTo->personal, $this->serverEncoding ) : null;
			}
		}

		$mailStructure = imap_fetchstructure( $this->getImapStream(), $mailId, FT_UID );


		if ( empty( $mailStructure->parts ) ) {
			$this->initMailPart( $mail, $mailStructure, 0 );
		} else {
			foreach ( $mailStructure->parts as $partNum => $partStructure ) {
				$this->initMailPart( $mail, $partStructure, $partNum + 1 );

			}
		}

		return $mail;
	}

	protected function initMailPart( IncomingMail $mail, $partStructure, $partNum ) {

		$serverEncoding = 'utf-8';

		$data = $partNum ? imap_fetchbody( $this->getImapStream(), $mail->id, $partNum, FT_UID ) : imap_body( $this->getImapStream(), $mail->id, FT_UID );

		if ( $partStructure->encoding == 1 ) {
			$data = imap_utf8( $data );
		} elseif ( $partStructure->encoding == 2 ) {
			$data = imap_binary( $data );
		} elseif ( $partStructure->encoding == 3 ) {
			$data = imap_base64( $data );
		} elseif ( $partStructure->encoding == 4 ) {
			$data = imap_qprint( $data );
		}


		$params = array();
		if ( ! empty( $partStructure->parameters ) ) {
			foreach ( $partStructure->parameters as $param ) {
				$params[ strtolower( $param->attribute ) ] = $param->value;
			}
		}
		if ( ! empty( $partStructure->dparameters ) ) {
			foreach ( $partStructure->dparameters as $param ) {
				$paramName = strtolower( preg_match( '~^(.*?)\*~', $param->attribute, $matches ) ? $matches[1] : $param->attribute );
				if ( isset( $params[ $paramName ] ) ) {
					$params[ $paramName ] .= $param->value;
				} else {
					$params[ $paramName ] = $param->value;
				}
			}
		}
		if ( ! empty( $params['charset'] ) ) {
			$data = $this->convertStringEncoding( $data, $params['charset'], $this->serverEncoding );
		}

		$attachmentId = $partStructure->ifid
			? trim( $partStructure->id, " <>" )
			: ( isset( $params['filename'] ) || isset( $params['name'] ) ? mt_rand() . mt_rand() : null );


		if ( $attachmentId ) {
			if ( empty( $params['filename'] ) && empty( $params['name'] ) ) {
				$fileName = $attachmentId . '.' . strtolower( $partStructure->subtype );
			} else {
				$fileName = ! empty( $params['filename'] ) ? $params['filename'] : $params['name'];
				$fileName = $this->decodeMimeStr( $fileName, $serverEncoding );
				$fileName = $this->decodeRFC2231( $fileName, $serverEncoding );
			}

			$attachment = new IncomingMailAttachment();

			$attachment->id          = $attachmentId;
			$attachment->name        = $fileName;
			$attachment->disposition = $partStructure->disposition;
			$attachment->wordpresdir = wp_upload_bits( $fileName, null, $data );
			$mail->addAttachment( $attachment );

		} elseif ( $partStructure->type == 0 && $data ) {
			if ( strtolower( $partStructure->subtype ) == 'plain' ) {
				$mail->textPlain .= $data;
			} else {
				$mail->textHtml .= $data;
			}
		} elseif ( $partStructure->type == 2 && $data ) {
			$mail->textPlain .= trim( $data );
		}
		if ( ! empty( $partStructure->parts ) ) {
			foreach ( $partStructure->parts as $subPartNum => $subPartStructure ) {
				if ( $partStructure->type == 2 && $partStructure->subtype == 'RFC822' ) {
					$this->initMailPart( $mail, $subPartStructure, $partNum );
				} else {
					$this->initMailPart( $mail, $subPartStructure, $partNum . '.' . ( $subPartNum + 1 ) );
				}
			}
		}
	}


	protected function convertStringEncoding( $string, $fromEncoding, $toEncoding ) {
		$convertedString = false;
		if ( $string && $fromEncoding !== $toEncoding ) {
			if ( extension_loaded( 'mbstring' ) ) {
				$convertedString = mb_convert_encoding( $string, $toEncoding, $fromEncoding );
			} else {
				$convertedString = @iconv( $fromEncoding, $toEncoding . '//IGNORE', $string );
			}
		}

		// If conversion does not occur or is not successful, return the original string
		return ( $convertedString !== false ) ? $convertedString : $string;
	}

	protected function decodeMimeStr( $string, $charset = 'utf-8' ) {
		$newString = '';
		$elements  = imap_mime_header_decode( $string );
		for ( $i = 0; $i < count( $elements ); $i ++ ) {
			if ( $elements[ $i ]->charset == 'default' ) {
				$elements[ $i ]->charset = 'iso-8859-1';
			}
			$newString .= $this->convertStringEncoding( $elements[ $i ]->text, $elements[ $i ]->charset, $charset );
		}

		return $newString;
	}

	protected function decodeRFC2231( $string, $charset = 'utf-8' ) {
		if ( preg_match( "/^(.*?)'.*?'(.*?)$/", $string, $matches ) ) {
			$encoding = $matches[1];
			$data     = $matches[2];
			if ( $this->isUrlEncoded( $data ) ) {
				$string = $this->convertStringEncoding( urldecode( $data ), $encoding, $charset );
			}
		}

		return $string;
	}

	function isUrlEncoded( $string ) {
		$hasInvalidChars = preg_match( '#[^%a-zA-Z0-9\-_\.\+]#', $string );
		$hasEscapedChars = preg_match( '#%[a-zA-Z0-9]{2}#', $string );

		return ! $hasInvalidChars && $hasEscapedChars;
	}

	function close_mailbox() /* Close Mail Box */ {
		if ( ! $this->getImapStream() ) {
			return false;
		}

		return imap_close( $this->getImapStream(), CL_EXPUNGE );
	}
}

class IncomingMail {

	public $id;
	public $UID;
	public $references;
	public $newid;
	public $date;
	public $subject;

	public $fromName;
	public $fromAddress;

	public $to = array();
	public $toString;
	public $cc = array();
	public $replyTo = array();

	public $textPlain;
	public $textHtml;
	/** @var IncomingMailAttachment[] */
	protected $attachments = array();

	public function addAttachment( IncomingMailAttachment $attachment ) {
		$this->attachments[ $attachment->id ] = $attachment;
	}

	/**
	 * @return IncomingMailAttachment[]
	 */
	public function getAttachments() {
		return $this->attachments;
	}

	/**
	 * Get array of internal HTML links placeholders
	 * @return array attachmentId => link placeholder
	 */
	public function getInternalLinksPlaceholders() {
		return preg_match_all( '/=["\'](cid:([\w\.%*@-]+))["\']/i', $this->textHtml, $matches ) ? array_combine( $matches[2], $matches[1] ) : array();
	}

	function fetch_html_body() {
		$baseUri     = get_site_url();
		$baseUri     = rtrim( $baseUri, '\\/' ) . '/wp-content/uploads/wp_mailinggroup/';
		$fetchedHtml = $this->textHtml;

		foreach ( $this->getInternalLinksPlaceholders() as $attachmentId => $placeholder ) {
			if ( isset( $this->attachments[ $attachmentId ] ) ) {
				$fetchedHtml = str_replace( $placeholder, $baseUri . basename( $this->attachments[ $attachmentId ]->filePath ), $fetchedHtml );
			}
		}

		return $fetchedHtml;
	}
}


class IncomingMailAttachment {
	public $id;
	public $name;
	public $filePath;
	public $disposition;
	public $wordpresdir = array();
}


class ImapMailboxException extends Exception {

}