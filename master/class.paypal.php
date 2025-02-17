<?php

class paypal
{
	var $paypal_post_vars;
	var $paypal_response;
	var $timeout;
	var $host = "www.sandbox.paypal.com";
	var $sitename = "";

	var $error_email;
	
	function paypal($paypal_post_vars) {
		$this->paypal_post_vars = $paypal_post_vars;
		$this->timeout = 120;
	}

	function send_response_curl() {
		//$fp = @fsockopen($this->host, 80, &$errno, &$errstr, 30 ); 
		//$fp = @fsockopen('ssl://www.sandbox.paypal.com', 443, &$errno, &$errstr, 30 ); 
		
		$target_post = 'https://' . $this->host . '/cgi-bin/webscr';

		foreach($this->paypal_post_vars AS $key => $value) {
			if (@get_magic_quotes_gpc()) {
				$value = stripslashes($value);
			}
			$values[] = "$key" . "=" . urlencode($value);
		}
		$req = @implode("&", $values);
		$req .= "&cmd=_notify-validate";
		
		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $target_post);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		$this->paypal_response = curl_exec($ch);
		curl_close($ch);
	}

	function send_response() {
		$fp = @fsockopen($this->host, 80, &$errno, &$errstr, 30 ); 
		//$fp = @fsockopen('ssl://www.sandbox.paypal.com', 443, &$errno, &$errstr, 30 ); 

		if (!$fp) { 
			$this->error_out("PHP fsockopen() error: " . $errstr,$em_headers );
		} else {
			foreach($this->paypal_post_vars AS $key => $value) {
				if (@get_magic_quotes_gpc()) {
					$value = stripslashes($value);
				}
				$values[] = "$key" . "=" . urlencode($value);
			}
			$response = @implode("&", $values);
			$response .= "&cmd=_notify-validate";
						
			fputs($fp, "POST /cgi-bin/webscr HTTP/1.0\r\n" ); 
			fputs($fp, "Content-type: application/x-www-form-urlencoded\r\n" ); 
			fputs($fp, "Content-length: " . strlen($response) . "\r\n\n" );
			fputs($fp, "$response\n\r" ); 
			fputs($fp, "\r\n" );

			$this->send_time = time();
			$this->paypal_response = ""; 

			// get response from paypal
			while (!feof($fp)) { 
				
			$this->paypal_response .= fgets( $fp, 1024 ); 

				if ($this->send_time < time() - $this->timeout) {
					$this->error_out("Timed out waiting for a response from PayPal. ($this->timeout seconds)" ,$em_headers );
				}
			}

			fclose( $fp );

		}
	}
	
	function is_verified() {
		if( ereg("VERIFIED", $this->paypal_response) )
			return true;
		else
			return false;
	} 

	function get_payment_status() {
		return $this->paypal_post_vars['payment_status'];
	}

	function error_out($message, $em_headers)
	{

		$date = date("m-d-Y G:i", time());
		$message .= "\n\nThe following data was received from PayPal:\n\n";
		$number = $this->paypal_post_vars['item_number'];
		$name = $this->paypal_post_vars['item_name'];
		@reset($this->paypal_post_vars);
		while( @list($key,$value) = @each($this->paypal_post_vars)) {
			$message .= $key . ':' . " \t$value\n";
		}
		mail($this->error_email,$this->sitename . " - $name - Order #$number - $date", $message, $em_headers);

	}
} 

?>