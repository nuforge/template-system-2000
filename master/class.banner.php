<?php

class banner
{

	private $myBanner;
	private $myInfo;
	private $myKey = 'gd^53k@3';

	function __construct($info)
	{
		$this->rc4 = new rc4crypt;
		$this->myInfo = $this->decode($info);
		if ($this->myInfo['command'] == 1) {
			$this->myInfo['referrer'] = $_SERVER['HTTP_REFERER'];
			$this->set_banner($this->get_random_banner());
			$this->view_banner();
		} else {
			$this->click_banner();
		}
	}

	private function get_next_view()
	{
		$qry = 'SELECT max(view) as max FROM views;';
		$result = @mysql_fetch_assoc(@mysql_query($qry));
		$max = $result['max'] + 1;
		$this->myInfo['view'] = $max;
		return $max;
	}

	private function get_next_click()
	{
		$qry = 'SELECT max(click) as max FROM clicks;';
		$result = @mysql_fetch_assoc(@mysql_query($qry));
		$max = $result['max'] + 1;
		return $max;
	}

	private function get_random_banner()
	{
		$qry = "SELECT count(banner) as count FROM banners WHERE category = " . $this->myInfo['category'] . ";";
		$result = @mysql_fetch_assoc(@mysql_query($qry));
		return (rand(1, $result['count']) - 1);
	}

	private function set_banner($num)
	{
		$qry = "SELECT banner, filetype, link FROM banners WHERE category = " . $this->myInfo['category'] . " LIMIT 1 OFFSET $num;";
		//echo $qry;
		$result = @mysql_fetch_assoc(@mysql_query($qry));
		$this->myBanner = $result;
	}

	private function view_banner()
	{
		$next = $this->get_next_view();
		$view = 'views';
		if ($this->fraud()) {
			$view = 'views_bad';
		}
		$qry = "INSERT INTO $view VALUES ($next, '" . $this->myInfo['affid'] . "', '" . $this->myBanner['banner'] . "', NOW(),'" . $this->myInfo['referrer'] . "', '" . $_SERVER['REMOTE_ADDR'] . "');";
		@mysql_query($qry);
	}

	private function click_banner()
	{
		$next = $this->get_next_click();
		$bid = $this->get_bid_cost();
		$click = 'clicks';
		if ($this->fraud()) {
			$click = 'clicks_bad';
		}
		$qry = "INSERT INTO $click VALUES ($next, " . $this->myInfo['view'] . "," . $this->myInfo['affid'] . ", '" . $this->myInfo['banner'] . "' , NOW(), '" . urldecode($this->myInfo['referrer']) . "', '" . $_SERVER['REMOTE_ADDR'] . "', $bid);";
		echo $qry;
		@mysql_query($qry);
	}

	private function get_bid_cost()
	{
		$qry = "SELECT bid FROM banners WHERE banner = '" . $this->myInfo['banner'] . "';";
		$result = @mysql_fetch_assoc(@mysql_query($qry));
		return $result['bid'];
	}

	private function fraud()
	{


		return false;
	}

	public function banner()
	{
		return $this->myBanner['banner'];
	}
	public function file_type()
	{
		return $this->myBanner['filetype'];
	}
	public function url()
	{
		return $this->myBanner['link'];
	}
	public function referrer()
	{
		return $this->myInfo['referrer'];
	}
	public function is_view()
	{
		if ($this->myInfo['command'] == 1) {
			return true;
		} else {
			return false;
		}
	}

	public function click()
	{
		$arr['a'] = $this->myInfo['affid'];
		$arr['b'] = $this->myBanner['banner'];
		$arr['c'] = 2;
		$arr['r'] = urlencode($this->myInfo['referrer']);
		$arr['v'] = $this->myInfo['view'];
		return $this->encode($arr);
	}

	private function decode($code)
	{

		$in = explode('&', $this->rc4->endecrypt($this->myKey, str_replace('-', '%', $code), 'de'));
		foreach ($in as $k => $v) {
			$in2 = explode('=', $v);
			$a[$in2[0]] = $in2[1];
		}
		if (isset($a['a'])) {
			$arr['affid'] = $a['a'];
		}
		if (isset($a['b'])) {
			$arr['banner'] = $a['b'];
		}
		if (isset($a['c'])) {
			$arr['command'] = $a['c'];
		}
		if (isset($a['r'])) {
			$arr['referrer'] = $a['r'];
		}
		if (isset($a['s'])) {
			$arr['category'] = $a['s'];
		}
		if (isset($a['v'])) {
			$arr['view'] = $a['v'];
		}
		return $arr;
	}

	private function encode($array)
	{
		foreach ($array as $k => $v) {
			$arr[] = $k . '=' . $v;
		}
		$txt = str_replace('%', '-', $this->rc4->endecrypt($this->myKey, implode('&', $arr), 'en'));
		return $txt;
	}
}
