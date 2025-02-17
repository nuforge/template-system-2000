<?PHP
class links extends webcomictv_DB_Object {
    public $primarykey = 'link';

    public function initialize() {
        $this->joinTable('link_types', 'link_link_type = link_type');
        $this->joinTable('members', 'member = link_member');
    }
}
?>