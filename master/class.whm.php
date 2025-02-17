<?php

/**
 * Cpanel/WHM XML API class 
 * Auther: Arash Hemmat
 * License: GNU GPL
 * Date: September 25 2007
 */

class WHM
{
	var $controller = true;
	var $host = null;
	var $user=null;
	var $accessHash = null;
	var $errors=array();
	var $fp=null;

	/*
	 * initialization
	 */
	function startup(&$controller)
	{
		$this->controller =& $controller;
	}

	/*
	 * initialization
	 */
	function init($host,$user,$accessHash)
	{
		$this->host=$host;
		$this->user=$user;
		$accessHash = str_replace(array("\r", "\n"),"",$accessHash);
		$this->accessHash=$accessHash;
	}

	/*
	 * connect to the xml api
	 * Output: true on success, false on fail
	 */
	function connect($api_path)
	{
		/*
		 *  Open a socket for HTTPS
		 */
		 	//$this->fp = fsockopen("ssl://" . $this->host, 2087, $errno, $errstr, 30);

		/*
		 *  Uncomment to use unsecure HTTP instead
		 */
		$this->fp = fsockopen($this->host, 2086, $errno, $errstr, 300);

		/*
		 * Die on error initializing socket
		 */
		if ($errno == 0 && $this->fp == false)
		{
			$this->errors[]="Socket Error: Could not initialize socket.";
			return false;
		}
		elseif ($this->fp == false)
		{
			$this->errors[]="Socket Error #" . $errno . ": " . $errstr;
			return false;
		}

		/*
		 *  Assemble the header to send
		 */
		$header = "";
		$header .= "GET " . $api_path . " HTTP/1.0\r\n";
		$header .= "Host: " . $this->host . "\r\n";
		$header .= "Connection: Close\r\n";
		$header .= "Authorization: WHM " . $this->user . ":" . $this->accessHash . "\r\n";
		// Comment above line and uncomment below line to use password authentication in place of hash authentication
		//$header .= "Authorization: Basic " . base64_encode($user . ":" . $pass) . "\r\n";
		$header .= "\r\n";

		/*
		 * Send the Header
		 */
		if(!@fputs($this->fp, $header))
		{
			$this->errors[]='Unable to send header.';
			return false;
		}
	}

	/*
	 * Close the socket
	 */
	function disconnect()
	{
		fclose($this->fp);
	}

	/*
	 * Get the raw output from the server
	 * Output: string
	 */
	function getOutput()
	{
		$rawResult = "";
		while ($this->fp && !feof($this->fp))
		{
			$rawResult .= @fgets($this->fp, 128); // Suppress errors with @
		}


		/*
		 * Ignore headers
		 */
		$rawResultParts = explode("\r\n\r\n",$rawResult);
		$result = $rawResultParts[1];

		/*
		 * Output XML
		 */
		return $result;
	}


	/*
	 * This function lists the verison of cPanel and WHM installed on the server.
	 * Output: string
	 */
	function version()
	{
		//connect using prpoer xml api address
		$this->connect('/xml-api/version');
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		return $xml->version;
	}


	/*
	 * This function lists the server's hostname.
	 * Output: string
	 */
	function gethostname()
	{
		//connect using prpoer xml api address
		$this->connect('/xml-api/gethostname');
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		return $xml->hostname;
	}

	/*
	 * list currently active accounts
	 * Output: array on success, false on fail
	 */
	function listaccts()
	{
		//connect using prpoer xml api address
		$this->connect('/xml-api/listaccts');
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		$xml = new DOMDocument();
		$xml->loadXML($xmlstr);
		return $this->xml2array($xml);
	}


	/*
	 * list packages
	 * Output: array on success, false on fail
	 */
	function listpkgs()
	{
		//connect using prpoer xml api address
		$this->connect('/xml-api/listpkgs');
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		$xml = new DOMDocument();
		$xml->loadXML($xmlstr);
		return $this->xml2array($xml);
	}


	/*
	 * create a cpanel account
	 * Output: array on success, false on fail
	 */
	function createAccount($accountInfo)
	{
		//connect using prpoer xml api address
		//echo "/xml-api/createacct?username=$acctUser&password=$acctPass&plan=$acctPackg&domain=$acctDomain&ip=n&cgi=y&frontpage=y&cpmod=x3&useregns=0&reseller=0";
		$default=array('ip'=>'n','cgi'=>'y','frontpage'=>'y','cpmod'=>'x3','seregns'=>0,'reseller'=>'0');
		
		$arr= array_merge($default,$accountInfo);
		foreach ($arr as $k => $v) {
			$arr[] = $k . "=" . $v;
		}		
		$this->connect("/xml-api/createacct?" . implode('&',$arr));
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);


		if($xml->result->status==1)
		{
			$result['status']=$xml->result->status;
			$result['statusmsg']=$xml->result->statusmsg;
			$result['ip']=$xml->result->options->ip;
			$result['nameserver']=$xml->result->options->nameserver;
			$result['nameserver2']=$xml->result->options->nameserver2;
			$result['nameserver3']=$xml->result->options->nameserver3;
			$result['nameserver4']=$xml->result->options->nameserver4;
			$result['package']=$xml->result->options->package;
			$result['rawout']=$xml->result->rawout;
			return $result;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}


	/*
	 * This function displays pertient account information for a specific account.
	 * Output: array on success , false on fail
	 */
	function accountsummary($accUser)
	{
		//connect using prpoer xml api address
		$this->connect("/xml-api/accountsummary?user=$accUser");
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->status==1)
		{
			$result['disklimit']=$xml->acct->disklimit;
			$result['diskused']=$xml->acct->diskused;
			$result['diskused']=$xml->acct->diskused;
			$result['domain']=$xml->acct->domain;
			$result['email']=$xml->acct->email;
			$result['ip']=$xml->acct->ip;
			$result['owner']=$xml->acct->owner;
			$result['partition']=$xml->acct->partition;
			$result['plan']=$xml->acct->plan;
			$result['startdate']=$xml->acct->startdate;
			$result['theme']=$xml->acct->theme;
			$result['unix_startdate']=$xml->acct->unix_startdate;
			$result['user']=$xml->acct->user;
			return $result;
		}
		else
		{
			$this->errors[]=$xml->statusmsg;
			return false;
		}
	}

	/*
	 *This function changes the passwd of a domain owner (cPanel) or reseller (WHM) account.
	 * Output: array on success , false on fail
	 */
	function passwd($accUser,$pass)
	{
		//connect using prpoer xml api address
		$this->connect("/xml-api/passwd?user=$accUser&pass=$pass");
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->passwd->status==1)
		{
			$result['statusmsg']=$xml->passwd->statusmsg;
			$result['frontpage']=$xml->passwd->frontpage;
			$result['ftp']=$xml->passwd->ftp;
			$result['mail']=$xml->passwd->mail;
			$result['mysql']=$xml->passwd->mysql;
			$result['system']=$xml->passwd->system;
			$result['rawout']=$xml->passwd->rawout;
			return $result;
		}
		else
		{
			$this->errors[]=$xml->passwd->statusmsg;
			return false;
		}
	}

	/*
	 * suspend a cpanel account
	 * Output: string (statusmsg) on success, false on fail
	 */
	function suspend($acctUser,$reason)
	{
		//connect using prpoer xml api address
		$this->connect("/xml-api/suspendacct?user=$acctUser&reason=$reason");
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1)
		{
			return $xml->result->statusmsg;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}

	/*
	 * unsuspend a suspended cpanel account
	 * Output: string (statusmsg) on success, false on fail
	 */
	function unsuspend($acctUser)
	{
		//connect using prpoer xml api address
		$this->connect("/xml-api/unsuspendacct?user=$acctUser");
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1)
		{
			return $xml->result->statusmsg;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}


	/*
	 * terminate a cpanel account
	 * Output: string (statusmsg) on success, false on fail
	 */
	function terminate($acctUser,$keepDns=0)
	{
		//connect using prpoer xml api address
		$this->connect("/xml-api/removeacct?user=$acctUser&keepdns=$keepDns");
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1)
		{
			return $xml->result->statusmsg;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}


/*
	 * Creates Package
	 * Output: array on success, false on fail
	 */
	function addPackage($packageInfo)
	{
		$vars = array('name','featurelist','quota','ip','cgi','frontpage','cpmod','maxftp','maxsql','maxpop','maxlst','maxsub','maxpark','maxaddon','hasshell','bwlimit');
		//connect using prpoer xml api address
		
		foreach ($packageInfo as $k=>$v) {
			if(!(array_search($k,$vars) === false)) {
				$arr[] = $k . "=" . $v;
			}
		}
		$this->connect("/xml-api/addpkg?" . implode('&',$arr));
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1)
		{
			$result['statusmsg']=$xml->result->statusmsg;
			$result['rawout']=$xml->result->rawout;
			return $result;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}


	function editPackage($packageInfo)
	{
		$vars = array('name','featurelist','quota','ip','cgi','frontpage','cpmod','maxftp','maxsql','maxpop','maxlst','maxsub','maxpark','maxaddon','hasshell','bwlimit');
		//connect using prpoer xml api address
		
		foreach ($packageInfo as $k=>$v) {
			if(!(array_search($k,$vars) === false)) {
				$arr[] = $k . "=" . $v;
			}
		}
		$this->connect("/xml-api/editpkg?" . implode('&',$arr));
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1)
		{
			$result['statusmsg']=$xml->result->statusmsg;
			$result['rawout']=$xml->result->rawout;
			return $result;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}


	function deletePackage($packageName)
	{
		$this->connect("/xml-api/killpkg?" . $packageName);
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1)
		{
			$result['statusmsg']=$xml->result->statusmsg;
			$result['rawout']=$xml->result->rawout;
			return $result;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}
	/*
	 * Upgrade/Downgrade and Account (Change Package)
	 * Output: array on success, false on fail
	 */
	function changepackage($accUser,$pkg)
	{
		//connect using prpoer xml api address
		$this->connect("/xml-api/changepackage?user=$accUser&pkg=$pkg");
		//get the output
		$xmlstr=$this->getOutput();
		if($xmlstr=='')
		{
			$this->errors[]='No output.';
			return false;
		}
		//disconnect
		$this->disconnect();

		//get the output xml as an array using simple xml
		$xml = new SimpleXMLElement($xmlstr);

		if($xml->result->status==1)
		{
			$result['statusmsg']=$xml->result->statusmsg;
			$result['rawout']=$xml->result->rawout;
			return $result;
		}
		else
		{
			$this->errors[]=$xml->result->statusmsg;
			return false;
		}
	}
	
	
	function xml2array($domnode)
	{
		$nodearray = array();
		$domnode = $domnode->firstChild;
		while (!is_null($domnode))
		{
			$currentnode = $domnode->nodeName;
			switch ($domnode->nodeType)
			{
				case XML_TEXT_NODE:
					if(!(trim($domnode->nodeValue) == "")) $nodearray = $domnode->nodeValue;
				break;
				case XML_ELEMENT_NODE:
					if ($domnode->hasAttributes() )
					{
						$elementarray = array();
						$attributes = $domnode->attributes;
						foreach ($attributes as $index => $domobj)
						{
							$elementarray[$domobj->name] = $domobj->value;
						}
					}
				break;
			}
			if ( $domnode->hasChildNodes() )
			{
				$nodearray[$currentnode][] = $this->xml2array($domnode);
				if (isset($elementarray))
				{
					$currnodeindex = count($nodearray[$currentnode]) - 1;
					$nodearray[$currentnode][$currnodeindex]['@'] = $elementarray;
				}
			} else {
				if (isset($elementarray) && $domnode->nodeType != XML_TEXT_NODE)
				{
					$nodearray[$currentnode]['@'] = $elementarray;
				}
			}
			$domnode = $domnode->nextSibling;
		}
		return $nodearray;
	}
	
}


/*username (string)
    User name of the account. Ex: user 

domain (string)
    Domain name. Ex: domain.tld 

plan (string)
    Package to use for account creation. Ex: reseller_gold 

quota (integer)
    Disk space quota in MB. (0-999999, 0 is unlimited) 

password (string)
    Password to access cPanel. Ex: p@ss!w0rd$123 

ip (string)
    Whether or not the domain has a dedicated IP address. (y = Yes, n = No) 

cgi (string)
    Whether or not the domain has cgi access. (y = Yes, n = No) 

frontpage (string)
    Whether or not the domain has FrontPage extensions installed. (y = Yes, n = No) 

hasshell (string)
    Whether or not the domain has shell / ssh access. (y = Yes, n = No) 

contactemail (string)
    Contact email address for the account. Ex: user@otherdomain.tld 

cpmod (string)
    cPanel theme name. Ex: x3 

maxftp (string)
    Maximum number of FTP accounts the user can create. (0-999999 | unlimited, null | 0 is unlimited) 

maxsql (string)
    Maximum number of SQL databases the user can create. (0-999999 | unlimited, null | 0 is unlimited) 

maxpop (string)
    Maximum number of email accounts the user can create. (0-999999 | unlimited, null | 0 is unlimited) 

maxlst (string)
    Maximum number of mailing lists the user can create. (0-999999 | unlimited, null | 0 is unlimited) 

maxsub (string)
    Maximum number of subdomains the user can create. (0-999999 | unlimited, null | 0 is unlimited) 

maxpark (string)
    Maximum number of parked domains the user can create. (0-999999 | unlimited, null | 0 is unlimited) 

maxaddon (string)
    Maximum number of addon domains the user can create. (0-999999 | unlimited, null | 0 is unlimited) 

bwlimit (string)
    Bandiwdth limit in MB. (0-999999, 0 is unlimited) 

customip (string)
    Specific IP address for the site. 

useregns (boolean)
    Use the registered nameservers for the domain instead of the ones configured on the server. (1 = Yes, 0 = No) 

hasuseregns (boolean)
    Set to 1 if you are using the above option. 

reseller (boolean)
    Give reseller privileges to the account. (1 = Yes, 0 = No) */


?>