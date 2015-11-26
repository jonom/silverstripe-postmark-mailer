<?php
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkAttachment;
use Postmark\Models\PostmarkException;

/**
 * A {@link Mailer} subclass to handle sending emails through the Postmark 
 * webservice API rather then send_mail(). Uses the official Postmark PHP library.
 *
 */

class PostmarkMailer extends Mailer {
	
	/**
	 * Your Postmark App API Key. Get one at https://postmarkapp.com/
	 *
	 * @config
	 * @var string
	 */
	private static $api_key = '';
	
	/**
	 * List of confirmed email addresses (sender signatures). Set them up at https://postmarkapp.com/
	 *
	 * @config
	 * @var array
	 */
	private static $sender_signatures = array();
	
	/**
	 * Send a plain-text email.
	 *
	 * @return bool
	 */
	public function sendPlain($to, $from, $subject, $plainContent, $attachedFiles = false, $customheaders = false) {
		$result = $this->sendPostmarkEmail($to, $from, $subject, false, $attachedFiles, $customheaders, $plainContent);
		if ($result === false) {
			// Fall back to regular Mailer
			$fallbackMailer = new Mailer();
			$result = $fallbackMailer->sendPlain($to, $from, $subject, $plainContent, $attachedFiles, $customheaders);
		}
		return $result;
	}
	
	/**
	 * Send an email as both HTML and plaintext
	 * 
	 * @return bool
	 */
	public function sendHTML($to, $from, $subject, $htmlContent, $attachedFiles = false, $customheaders = false, $plainContent = false) {
		$result = $this->sendPostmarkEmail($to, $from, $subject, $htmlContent, $attachedFiles, $customheaders, $plainContent);
		if ($result === false) {
			// Fall back to regular Mailer
			$fallbackMailer = new Mailer();
			$result = $fallbackMailer->sendHTML($to, $from, $subject, $htmlContent, $attachedFiles, $customheaders, $plainContent);
		}
		return $result;
	}
	
	/**
	 * Send email through Postmark's REST API
	 *
	 * @return bool (true = sent successfully)
	 */
	private function sendPostmarkEmail($to, $from, $subject, $htmlContent = NULL, $attachedFiles = NULL, $customHeaders = NULL, $plainContent = NULL) {
		
		$apiKey = $this->config()->get('api_key');
		$senderSignatures = $this->config()->get('sender_signatures');
		$cc = NULL;
		$bcc = NULL;
		$replyTo = NULL;
		
		if(!$apiKey) user_error('A Postmark App API key is required to send email', E_USER_ERROR);
		if(!($htmlContent||$plainContent)) user_error("Can't send email with no content", E_USER_ERROR);
		if(empty($senderSignatures)) user_error('At least one Postmark App sender signature is required to send email', E_USER_ERROR);
		
		// Parse out problematic custom headers
		if (is_array($customHeaders)) {
			if (array_key_exists('Cc', $customHeaders)) {
				$cc = $customHeaders['Cc'];
				unset($customHeaders['Cc']);
			}
			if (array_key_exists('Bcc', $customHeaders)) {
				$bcc = $customHeaders['Bcc'];
				unset($customHeaders['Bcc']);
			}
			if (array_key_exists('Reply-To', $customHeaders)) {
				$replyTo = $customHeaders['Reply-To'];
				unset($customHeaders['Reply-To']);
			}
			if (empty($customHeaders)) $customHeaders = NULL;
		} else {$customHeaders = NULL;}
		
		// Ensure from address is valid
		if (!in_array($from, $senderSignatures)) {
			// Fallback to first valid signature
			if (!$replyTo) $replyTo = $from;
			$from = $senderSignatures[0];
		}
		
		// Set up attachments
		$attachments = array();
		if ($attachedFiles && is_array($attachedFiles)) {
			foreach ($attachedFiles as $f) {
				$attachments[] = PostmarkAttachment::fromRawData($f['contents'], $f['filename'], $f['mimetype']);
			}
		}
		
		// Send the email
		try {
			$client = new PostmarkClient($apiKey);
			$sendResult = $client->sendEmail($from, $to, $subject, 
				$htmlContent, $plainContent, $tag = NULL, $trackOpens = true, $replyTo, 
				$cc, $bcc, $customHeaders, $attachments);
			return true;
		}
		catch(PostmarkException $ex) {
			// If client is able to communicate with the API in a timely fashion,
			// but the message data is invalid, or there's a server error,
			// a PostmarkException can be thrown.
			user_error("Postmark Exception: $ex->message (Error code: $ex->postmarkApiErrorCode)", E_USER_WARNING);
			return false;
		}
		catch(Exception $generalException) {
			// A general exception is thown if the API
			// was unreachable or times out.
			user_error('Postmark API was unreachable or timed out', E_USER_WARNING);
			return false;
		}
	}
}