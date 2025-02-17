<?PHP

class dirReader {

	public function readDirectory($dir)	{
		$dh = dir($dir);
		$dir_array = array();
		while (false !== ($entry = $dh->read())) {
			if (($entry != '.') && ($entry != '..') && (substr($entry, (strlen($entry)-3)) != '.db')  ){
				if (is_file($dir . $entry)) {
					$dir_array[] = $entry;
				}
			}
		}
		$dh->close();
		return $dir_array;
	}

}
?>