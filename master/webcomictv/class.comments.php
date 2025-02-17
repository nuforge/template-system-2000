<?PHP
class comments extends webcomictv_DB_Object {
    public $primarykey = 'comment';



    public function globalQueries() {
        $this->addSelectBoolean('mem_vip');
        $this->addSelectBoolean('mem_show_on_awc');
        return false;
    }

    public function globalJoins() {
        $this->joinTable('members', 'comment_member = member');
        return true;
    }

    public function getCheckPostBuffer ($f_member, $f_wait='1 minute') {
        $q = "SELECT ((NOW() - comment_stamp) < INTERVAL '" . $f_wait . "') as check FROM comments WHERE comment = (SELECT max(comment) FROM comments WHERE comment_member =" . $f_member .");";
        $re = @pg_fetch_assoc(@pg_query($q));
        return $this->bool2bool($re['check']);
    }


}
?>