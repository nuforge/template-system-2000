<?PHP

class exits extends webcomictv_DB_Object {

	public $primarykey = 'exit';

	public function checkHoneyPot($f_comic=false){
		if($f_comic) {$where = ' WHERE comic = ' . $f_comic . ' ' ;}
		$exits = @pg_fetch_all(@pg_query('SELECT DISTINCT (ip), title FROM exits LEFT JOIN comics USING (comic)' . $where . 'LIMIT 300;'));
		foreach ($exits as $k=>$v) {
			$res['result']= $this->getHoneyPot($v['ip']);
			if(!empty($res['result'])) {
				$res['comic_title'] = $v['title'];
				$res['ip'] = $v['ip'];
				$results[] = $res;
			}
		}
		return $results;
	}

	public function getHoneyPot ($ipaddr) {
		$ip=$ipaddr;
        // return 1 (Search engine) or 2 (Generic) if host is found, else return                                                                              0
        $ret=array();
        $access_key			='joequynisjbs';
        $type_txt   		='';
        $type_num   		=0;
        $engine_txt 		='';
        $engine_num 		=0;
        $days               =0;
        $score              =0;
        $domain="dnsbl.httpbl.org";
        $answer_codes=array(
            0       =>'Search Engine',
            1       =>'Suspicious',
            2       =>'Harvester',
            3       =>'Suspicious & Harvester',
            4       =>'Comment Spammer',
            5       =>'Suspicious & Comment Spammer',
            6       =>'Harvester & Comment Spammer',
            7       =>'Suspicious & Harvester & Comment Spammer'
     	);
        $search_engines=array(
			0 =>'Misc Engines',
            1 =>'AltaVista',
            2 =>'Ask',
            3 =>'Baidu',
            4 =>'Excite',
            5 =>'Google',
            6 =>'Looksmart',
            7 =>'Lycos',
            8 =>'MSN',
            9 =>'Yahoo',
           10 =>'Cuil',
           11 =>'InfoSeek'
           );

        list($a,$b,$c,$d)=explode('.',$ipaddr);
        $query=$access_key.".$d.$c.$b.$a.". $domain;
        $host=gethostbyname($query);

        list($a,$b,$c,$d)=explode(".",$host);
        $ret['rectype']=$a;
        $ret['days']=$b;
        $ret['score']=$c;
        $ret['typecode']=$d;

        if($ret['rectype']!=127) {
			$ret=array();
		} else {
             if($ret['days']==0) {
                $ret['type_desc']="Search Engine: ".$search_engines[$ret['score']];
             } else {
                $ret['type_desc']=$answer_codes[$ret['typecode']];
			 }
        }
        return($ret);

	}

	public function getExits ($comic, $start=false,$stop=false,$group='day') {
		if (!$start) {$start = date('Y-m-01');}
		if (!$stop) {$stop = date('Y-m-t');}
		$query = "SELECT count(exit) as exits FROM exits WHERE stamp >= '$start' AND stamp < '$stop' and comic = $comic;";
		return @pg_fetch_assoc(@pg_query($query));
	}

	public function getTopTen ($range=true,$group='day') {
		$query = "SELECT title, comic, count(exit) as count FROM exits LEFT JOIN comics USING (comic) ";
		if (!empty($range)) {
			if ($range === true) {
				$start = date('Y-m-01');
				$stop = date('Y-m-t');
			} else {
				$start = $range['start'];
				$stop = $range['stop'];
			}
			$query .= " WHERE stamp >= '$start' AND stamp < '$stop' ";
		}
		$query .= " group by title,comic ORDER BY count(exit) desc LIMIT 10;";
		return @pg_fetch_all(@pg_query($query));

	}


}
?>