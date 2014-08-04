<?php
/**
 * Class clickatell
 * Esse é a classe que gerencia o serviço clickatell
 */
class clickatell extends WP_SMS {
		private $wsdl_link = "http://api.clickatell.com/http/sendmsg";
		public $tariff = "http://api.clickatell.com/http/getbalance";

		public function SendSMS() {

			$to = implode($this->to, ",");

			$sms_text = iconv('cp1251', 'utf-8', $this->msg);

			$POST = array (
				'user'	  => $this->username,
				'password'  => $this->password,
				'to'		=> $to,
				'api_id'	=> $this->from,
				'text'		=> $sms_text
			);

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_URL, "{$this->wsdl_link}");
			$result = curl_exec($ch);

			if ($result) {
				if (strstr($result, 'ERR')) {
					echo $result;
				}
				else {
					echo "SMS message sent. $result";

					$this->InsertToDB($this->from, $this->msg, $this->to);
					$this->Hook('wp_sms_send', $result);

					return true;
				}
			} else {
				echo "API access error";
			}
		}

		public function GetCredit() {
			$result = file_get_contents("{$this->tariff}?user={$this->username}"
					. "&password={$this->password}&api_id={$this->from}");

			return (float) str_replace('Credit: ', '', $result);

		}
	}
?>
