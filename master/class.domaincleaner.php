<?php

class DomainCleaner {

	public function __construct(/**/) {
		$this->tlds = Array('com','net','org','biz','coop','info','museum','name','mobi','pro','edu','gov','int','mil','ac','ad','ae','af','ag','ai','al','am','an','ao','aq','ar','as','at','au','aw','az','ba','bb','bd','be','bf','bg','bh','bi','bj','bm','bn','bo','br','bs','bt','bv','bw','by','bz','ca','cc','cd','cf','cg','ch','ci','ck','cl','cm','cn','co','cr','cu','cv','cx','cy','cz','de','dj','dk','dm','do','dz','ec','ee','eg','eh','er','es','et','fi','fj','fk','fm','fo','fr','ga','gd','ge','gf','gg','gh','gi','gl','gm','gn','gp','gq','gr','gs','gt','gu','gv','gy','hk','hm','hn','hr','ht','hu','id','ie','il','im','in','io','iq','ir','is','it','je','jm','jo','jp','ke','kg','kh','ki','km','kn','kp','kr','kw','ky','kz','la','lb','lc','li','lk','lr','ls','lt','lu','lv','ly','ma','mc','md','mg','mh','mk','ml','mm','mn','mo','mp','mq','mr','ms','mt','mu','mv','mw','mx','my','mz','na','nc','ne','nf','ng','ni','nl','no','np','nr','nu','nz','om','pa','pe','pf','pg','ph','pk','pl','pm','pn','pr','ps','pt','pw','py','qa','re','ro','rw','ru','sa','sb','sc','sd','se','sg','sh','si','sj','sk','sl','sm','sn','so','sr','st','sv','sy','sz','tc','td','tf','tg','th','tj','tk','tm','tn','to','tp','tr','tt','tv','tw','tz','ua','ug','uk','um','us','uy','uz','va','vc','ve','vg','vi','vn','vu','ws','wf','ye','yt','yu','za','zm','zw');
		$this->regex = '/([a-zA-Z0-9]([a-zA-Z0-9\-]{0,61}[a-zA-Z0-9])?\.)+[a-zA-Z]{2,6}/';
	}
	
	public function clean_domains ($string) {
		preg_match_all($this->regex, strtolower($string), $matches);
		$matches = $matches[0];
		
		$matches = $this->clean_www ($matches);
		$matches = $this->clean_www_2 ($matches);
		$matches = array_unique($matches);
		asort($matches);
		return $matches;
	}
	
	public function clean ($domain) {
		preg_match_all($this->regex, strtolower($domain), $matches);
		$matches = $matches[0];
		
		$matches = $this->clean_www ($matches);
		$matches = $this->clean_www_2 ($matches);
		$matches = array_unique($matches);
		asort($matches);
		return $matches[0];
	
	}
	
	private function clean_www_2 ($matched) {
		foreach ($matched as $key=>$dom){
			$temp = explode('.',$dom);
			$size = count($temp) - 1;
			$t = array();
			$bad = false;
			$done = false;
			for ($a = $size; !$done && $a >= 0 ; $a--) {
				array_unshift ($t, $temp[$a]);
				if (array_search($temp[$a], $this->tlds)===false) {
					if ($a == $size || empty($temp[$a])) {
						unset ($matched[$key]);
						$bad = true;
					}
					$done = true;
				}			
			}
			if (!$bad) {
				$dom = implode('.', $t);
				$matched[$key] = $dom;	
			}
		}
		return $matched;
	}
	
	private function clean_www ($matched) {
		foreach ($matched as $key=>$dom){
			$temp = explode('.',$dom);
			if ($temp[0] == 'www' && count($temp) > 2) {array_shift ($temp);}
			$dom = implode('.', $temp);
			if (array_search($temp[count($temp)-1], $this->tlds)===false) {
				unset ($matched[$key]);
			} else {
				$matched[$key]=$dom;
			}
		}
		return $matched;
	}
}
?>