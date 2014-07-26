<?php
/**
 * Class clickatell
 * Esse é a classe que gerencia o serviço clickatell
 */
class clickatell extends WP_SMS {
		private $wsdl_link = "http://api.clickatell.com/http/sendmsg";
		public $tariff = "http://api.clickatell.com/http/getbalance";
		public $unitrial = false;
		public $unit;
		public $flash = "enable";
		public $isflash = false;
        private $api_id = 3117542;
		
		public function __construct() {
			parent::__construct();
		}
		
		public function SendSMS() {
			
			$to = implode($this->to, ",");
			
			$sms_text = iconv('cp1251', 'utf-8', $this->msg);
			
			$POST = array (
                'user'      => $this->username,
                'password'  => $this->password,
				'api_id'	=> $this->api_id,
				'to'		=> $to,
				'from'	=> $this->from,
				'text'		=> $sms_text
			);

            if ($this->isflash) {
                $POST['msg_type'] = 'SMS_FLASH';
            }

			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $POST);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_URL, "{$this->wsdl_link}");
			$result = curl_exec($ch);
			
			if ($result) {
				$jsonObj = json_decode($result);
				
				if(null===$jsonObj) {
					echo "Invalid JSON";
				} elseif(!empty($jsonObj->error)) {
					echo "An error occured: " . $jsonObj->error . "(code: " . $jsonObj->code . ")";
				} else {
					echo "SMS message is sent. Message id " . $jsonObj->result[0]->sms_id;
					
					$this->InsertToDB($this->from, $this->msg, $this->to);
					$this->Hook('wp_sms_send', $result);
					
					return true;
				}
			} else {
				echo "API access error";
			}
		}
		
		public function GetCredit() {
		
			$result = file_get_contents("{$this->tariff}?user={$this->username}&password={$this->password}&api_id={$this->api_id}");
			
			return str_replace('Credit: ', '', $result);

		}
	}
?>