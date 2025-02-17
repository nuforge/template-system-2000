<?PHP
class site_links extends webcomictv_DB_Object {
    public $primarykey = 'site_link';


    public function initialize() {
        $this->joinTable('links', 'sl_link = link');
        $this->joinTable('link_types', 'link_link_type = link_type');
        $this->joinTable('members', 'member = link_member');
    }

}
?>