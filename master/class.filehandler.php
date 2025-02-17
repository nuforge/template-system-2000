<?PHP

class FileHandler {

	public function __construct ($formname=false,$sizelimit=30000,$filetypes=false,$uploaddir=false) {
		$this->sizelimit = $sizelimit;
		if (!empty($filetypes)) {$this->filetypes = $filetypes;}
		if (!empty($formname)) {$this->formname = $formname;}
		if (empty($uploaddir)) {$this->uploaddir  = $_SERVER['DOCUMENT_ROOT'] . '/uploads/';}
		else {$this->uploaddir  = $uploaddir;}
		
	}
	
	public function moveFile($o_file, $d_dir, $d_rename=false, $o_dir=false, $unlink=true ,$mkdir=true) {
		if(empty($o_dir)) { $o_dir = $this->uploaddir;}
		if (empty($d_rename)) {$d_rename = $o_file;}
		if (!file_exists($o_dir . $o_file)) { return false;}
		if(!copy($o_dir . $o_file, $d_dir . $d_rename)) { return false;}
		if ($unlink) { return unlink($o_dir . $o_file);}
		return true;
	}
	
}

?>