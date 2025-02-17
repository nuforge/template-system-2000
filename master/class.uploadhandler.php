<?PHP

class UploadHandler extends FileHandler{
	
	public function upload_file_rename ($formname=false,$newname=false,$sizelimit=false,$filetypes=false,$uploaddir=false) {
		if (!$sizelimit) {$sizelimit = $this->sizelimit;}
		if (!$filetypes) {$filetypes = $this->filetypes;}
		if (!$formname) {$formname = $this->formname;}
		if (!$newname) {$newname = 't-' . date('ymdHisu') . substr(md5(rand(0,1000)),0,4);}
		$this->returnName = $newname;
		if (!$uploaddir) {$uploaddir = $this->uploaddir;}
		
		$uploadfile = $uploaddir . basename($_FILES[$formname]['name']);
		$type = explode('/',$_FILES[$formname]['type']);
		if ($type[1] == 'pjpeg') { $type[1] == 'jpeg';}
		if ($_FILES[$formname]['size'] > $this->sizelimit) {return false;}
		if (!empty ($filetypes) && (!in_array($type[1],array_keys($filetypes)) && !in_array($type[0],$filetypes) )) {return false;}
		if (!is_uploaded_file($_FILES[$formname]['tmp_name'])) {return false;}
		if (!move_uploaded_file($_FILES[$formname]['tmp_name'], $uploadfile)) {return false;}
		return rename($uploadfile, $uploaddir . $newname . '.' . $type[1]);
		
	}
	
	public function upload_file ($formname=false,$sizelimit=false,$filetypes=false,$uploaddir=false) {
		if (!$sizelimit) {$sizelimit = $this->sizelimit;}
		if (!$filetypes) {$filetypes = $this->filetypes;}
		if (is_string($filetypes)) {$filetypes = array($filetypes);}
		if (!$formname) {$formname = $this->formname;}
		if (!$uploaddir ) {$uploaddir = $this->uploaddir;}
		
		$uploadfile = $uploaddir . basename($_FILES[$formname]['name']);
		$type = explode('/',$_FILES[$formname]['type']);
		if ($type[1] == 'pjpeg') { $type[1] == 'jpeg';}		
		if ($_FILES[$formname]['size'] > $this->sizelimit) {return false;}
		if (!empty ($filetypes) && (!in_array($type[1],array_keys($filetypes)) && !in_array($type[0],$filetypes) )) {return false;}
		if (!is_uploaded_file($_FILES[$formname]['tmp_name'])) {return false;}
		
		$this->returnName = $_FILES[$formname]['tmp_name'];
		return !move_uploaded_file($_FILES[$formname]['tmp_name'], $uploadfile);
	}
	
	public function getName ( ) {
		return $this->returnName;
	}
}

?>