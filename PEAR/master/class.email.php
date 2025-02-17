<?PHP

class email {

	public function __construct ($recipients=false) {
		if ($recipients) {$this->recipients = $recipients;}
		$this->contenttype['html'] = "text/html; charset=iso-8859-1";
		$this->contenttype['text'] = "text/plain; charset=us-ascii";
	
	}

	public function valid_email ($address) {
		if (eregi("[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,3})$", $address)) {
			return true;
		} else {
			return false;
		}
	}
	
	function mimeDecode ($mailtext) {
		$decoder = new Mail_mimeDecode($mailtext);
        $parts = $decoder->getSendArray();
        if (PEAR::isError($parts)) {return $parts;}
		if (!empty($this->recipients)) {$parts[1]['To'] = implode (', ',$this->recipients);}		
		return $parts;
	}
	
	function formatHeaders ($hdrs) {
		$headers['From'] = '"' . $hdrs['from']['name'] . '" <' . $hdrs['from']['email'] .'>';
		$headers['Sender'] = $headers['From'];
		$headers['To'] = implode (', ',$this->recipients);
		$headers['Subject'] = $hdrs['subject'];
		
		$headers['Content-Type'] = $this->contenttype[$hdrs['type']];
				
		return $headers;
	}
		
		
	function sendEmailTemplate($templateFile,$args=array(),$hdrs=false) {
		foreach((array)$args as $k=>$v) {$content->$k = $v;}
		$template = new HTML_Template_Flexy(array('compiler' => 'Regex','filters' => array('SimpleTags','Mail'),'templateDir'=>'../templates/emails/','compileDir'=>'/tmp/flexy_compiled_templates/'));
        $template->compile($templateFile . '.tpl');
        $body = $template->bufferedOutputObject($content);
		if ($hdrs) {$headers = $this->formatHeaders ($hdrs);
		} else{
			$parts = $this->mimeDecode($body);
			if (empty($this->recipients)) {$this->recipients = $parts[0];}
			$headers = $parts[1];
			$body = $parts[2];
		}
		
		$headers['Date'] = date('r');
		
        $mailOptions = PEAR::getStaticProperty('Mail','options');
        $mail = Mail::factory("SMTP",$mailOptions);
       	return PEAR::isError($mail) ? $mail : $mail->send($this->recipients,$headers,$body);
    }
}

?>