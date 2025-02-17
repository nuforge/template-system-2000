<?PHP
class messages extends webcomicz_DB_Object {

	public $primarykey = 'message';
	
	public function readMessage ($message,$member){
		$arr['member'] =$member;
		$arr['message'] =$message;
		$ret = $arr;
		$q = "UPDATE messages set read = NOW() WHERE message = " . $ret['message'] . " AND receiver = " . $ret['member'] . " AND read IS NULL;";
		$q .= "SELECT * from messages WHERE (sender = " . $ret['member'] . " OR receiver = " . $ret['member'] . ") and message = " . $ret['message'] . ";";
		return @pg_fetch_assoc(@pg_query ($q . ';'));
	}
	
	
	public function replyToMessage($message) {
		$this->update(array('replied'=>'NOW()'),array('message'=>$message['parent']));
		return $this->add($message);
	}
	
	
	public function sendMessage($message) {
		return $this->add($message);
	}
	
	public function getUnread ($member,$count=true) {
		if($count) {
			$re = @pg_fetch_assoc(@pg_query("SELECT count(message) as unread FROM messages WHERE read IS NULL AND receiver = $member;"));
			return $re['unread'];
		}
	}
	
	public function getReadMessages ($member) {
		$member = $this->cleanValues(array('receiver'=>$member));
		return $this->getReceiverList(array('sent'=>'desc'),'receiver = ' . $member['receiver'] . ' AND read IS NOT NULL');
	
	}
	
	public function getUnreadMessages ($member) {
		return $this->getReceiverList(array('sent'=>'desc'),array('receiver'=>$member,'read'=>'NULL'));
	}
	
	public function getSentMessages ($member) {
		return $this->getSenderList(array('sent'=>'desc'),array('sender'=>$member));
		
	}
	
	public function getCheckSendBuffer ($member){
			$member = $this->cleanValues(array('sender'=>$member));		
			$q = "SELECT ((NOW() - sent) < INTERVAL '1 minute') as check FROM messages WHERE message = (SELECT max(message) FROM messages WHERE sender =" . $member['sender'] .");";
			$re = @pg_fetch_assoc(@pg_query($q));
			return $this->bool2bool($re['check']);
	}
	
	public function getSenderList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN members ON (member=receiver) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
	public function getReceiverList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table . ' LEFT JOIN members ON (member=sender) ';
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
	public function getList ($order=false,$where=false,$limit=false,$offset=false,$table=false) {
		if (!$table) {$table = $this->table;}
		$query = 'SELECT * FROM ' . $table;
		if ($where) {$query .= $this->formatWhere($where);}
		if ($order) {$query .= $this->formatOrder($order);}
		if ($limit) {$query .= ' LIMIT ' . $limit . ' ';}
		if ($offset) {$query .= ' OFFSET ' . $offset . ' ';}
		return @pg_fetch_all(@pg_query ($query . ';'));
	}
	
	public function text2html ($text) {
		$text = strip_tags($text, '<a><b><i><u><br><pre><blockquote><xmp><ol><ul><li>');
		$text = str_replace ('\r\n\r\n', '</p><p>',$text);
		$text = nl2br($text);
		return '<p>' . $text . '</p>';
	}
	
	public function html2text ($text) {
		$text = str_replace ('</p><p>',"\r\n\r\n", $text);
		$text = str_replace ('<br>',"\r\n", $text);
		$text = strip_tags($text, '<a><b><i><u><br><pre><blockquote><xmp><ol><ul><li>');
		return $text;
	}
	
}
?>