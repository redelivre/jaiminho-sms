<?php
/**
 * Class clickatell
 * Esse é a classe que gerencia o serviço clickatell
 */
class clickatell extends WP_SMS {
		private $wsdl_link = "http://api.clickatell.com/http/sendmsg";
		public $tariff = "http://api.clickatell.com/http/getbalance";
		const SMS_SEND_EMAIL = 'sms@messaging.clickatell.com';
		const BATCH_SIZE = 50000;

		public function __construct() {
			parent::__construct();

			$this->custom_fields = array(
					__('HTTP API ID', 'wp-sms'),
					__('SMTP API ID', 'wp-sms'));
		}

		public function SendSMS() {
			$text = str_replace('\r', '', $this->msg);
			$encoding = mb_detect_encoding($text);

			if ($encoding === false) {
				return 0;
			}

			$http_id = $this->custom_values[0];
			$smtp_id = $this->custom_values[1];

			$message = "user:{$this->username}\r\n";
			$message = "api_id:{$smtp_id}\r\n";
			$message .= "password:{$this->password}\r\n";

			if ($encoding === 'ASCII') {
				if (strlen($text) > 160) {
					return 0;
				}
				$text = urlencode($text);
				$message .= "urltext:$text\r\n";
			}
			else {
				if (mb_strlen($text, $encoding) > 70) {
					return 0;
				}

				$text = iconv($encoding, 'UCS-2', $text);
				$hex = '';
				$len = strlen($text);
				for ($i = 0; $i < $len; $i++) {
					$hex .= dechex(ord($text[$i]));
				}
				$message .= "unicode:1\r\n";
				$message .= "data:$hex\r\n";
			}

			$sent = 0;
			foreach (array_chunk($this->to, self::BATCH_SIZE) as $to) {
				$recipients = '';
				foreach ($to as $t) {
					$recipients .=  "to:$t\r\n";
				}

				if (mail(self::SMS_SEND_EMAIL, '', $message . $recipients)) {
					$this->InsertToDB($this->from, $this->msg, $this->to);
					$this->Hook('wp_sms_send', true);
					$sent += sizeof($to);
				}
			}

			return $sent;
		}

		public function GetCredit() {
			$http_id = $this->custom_values[0];

			$result = file_get_contents("{$this->tariff}?user={$this->username}"
					. "&password={$this->password}&api_id={$http_id}");

			return (float) str_replace('Credit: ', '', $result);
		}
	}
?>
