<?PHP

class members extends Base_Member {

    public $connection_info = array('user' => '', 'password' => '', 'dbname' => '');

    public $primarykey = 'member';
    public $session_title = 'member_login';
    protected $member_info;
    protected $table_field_prefix = 'mem_';
    protected $db_username_field = 'mem_unique';
    protected $login_username_field = 'username';
    protected $db_password_field = 'mem_password';
    protected $login_password_field = 'password';

    public function globalQueries() {
        //$this->addSelectStatement("extract('year' from age(mem_birthdate))",'mem_age');


        //$this->addSelectBoolean('mem_vip');
        return false;
    }

    public function globalJoins() {
        //$this->joinTable('avatars', '(mem_avatar = avatar)');
        return true;
    }

    public function getUsedClasses () {
        return $this->usedClasses;
    }

    public function initialize() {
        return NULL;
    }

    public function auto_login () {
        $rc4 = new rc4crypt;
        if (!empty($_SESSION[$this->session_title])) {
            $arr = explode(':', $rc4->endecrypt('x87jm43', $_SESSION [$this->session_title], 'de'));
            $loginifno = array ($this->login_username_field=>$arr[0], $this->login_password_field=>$arr[1], 'remember'=>0);
            return $this->login($loginifno);
        } elseif (empty($_SESSION[$this->session_title]) && !empty($_COOKIE[$this->session_title])) {
            $log_info = $rc4->endecrypt('x87jm43', $_COOKIE [$this->session_title], 'de');
            $arr = explode(':', $log_info);
            $loginifno = array ($this->login_username_field=>$arr[0], $this->login_password_field=>$arr[1], 'remember'=>1);
            return $this->login ($loginifno);
        }
    }

public function login ($fa_loginInfo) {
        $rc4 = new rc4crypt;
        $this->member_info = $this->load(array($this->db_username_field => $fa_loginInfo[$this->login_username_field]));

        if (empty($fa_loginInfo[$this->login_username_field]) || empty($this->member_info) || $fa_loginInfo[$this->login_password_field] != $this->member_info[$this->db_password_field]) {
            return false;
        }

        $log_info = $this->member_info[$this->db_username_field] . ':' . $fa_loginInfo[$this->login_password_field] . ':' . mktime();

        $log_info = $rc4->endecrypt('x87jm43', $log_info, 'en');

        if (!empty($fa_loginInfo['remember'])) {
            $lifetime = time() + 365*24*60*60;
            setcookie($this->session_title, $log_info, $lifetime, "/" );
        }

        $_SESSION [$this->session_title] = $log_info;
        $this->onlogin();
        return $this->member_info;
    }

    public function onlogin() {
        $this->update(array($this->table_field_prefix . 'last_login' => 'NOW()'), array('member' => $this->info['member']));

        return true;
    }
    
    public function checkIsUnique($f_value,$f_field,$f_caseSensitive=false) {

        $user = $this->validateValues(array($this->table_field_prefix.$f_field=>$f_value));
        $where = ($f_caseSensitive)  ? $this->table_field_prefix . $f_field . " = " . $user[$this->table_field_prefix  . $f_field] : "lower(mem_" . $f_field . ") = " . strtolower($user[$this->table_field_prefix  . $f_field]);
        $q= "SELECT count(member) as count from members WHERE " . $where . ";";
        $res = @pg_fetch_assoc(@pg_query($q));
        return $res['count'] ? false : true;
    }


    public function getList($order=false, $where=false, $limit=false, $offset=false, $group=false, $table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $query = "SELECT * FROM " . $table ;
        if ($where) {
            $query .= $this->formatWhere($where);
        }
        if ($group) {
            $query .= $this->formatGroup($group);
        }
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        if ($limit) {
            $query .= ' LIMIT ' . $limit . ' ';
        }
        if ($offset) {
            $query .= ' OFFSET ' . $offset . ' ';
        }
        
        return @pg_fetch_all(@pg_query($query . ';'));
    }

    public function load($where, $order=false, $table = false) {
        if (!$table) {
            $table = $this->table;
        }
        if (!is_array($where)) {
            $where = array($this->primarykey => $where);
        }
        $query = "SELECT * FROM " . $table ;
        $query .= $this->formatWhere($where);
        if ($order) {
            $query .= $this->formatOrder($order);
        }
        $query .= ' LIMIT 1 ';
        return @pg_fetch_assoc(@pg_query($query . ';'));
    }

    public function insert($values, $table = false) {
        if (!$table) {
            $table = $this->table;
        }
        $t_values = $this->pruneValues($values);
        if (!empty($t_values)) {
            $query = "INSERT INTO $table (" . implode(',', array_keys($t_values)) . ") VALUES (" . implode(',', $this->cleanValues($t_values)) . ")";
            if (!@pg_query($query . ';')) {
                return false;
            }
            $re = @pg_fetch_assoc(@pg_query("select currval('members_member_seq');"));
            return $re['currval'];
        }
        return false;
    }

    public function update($values, $where=false, $table=false) {
        if (!$table) {
            $table = $this->table;
        }
        $t_values = $this->cleanValues($this->pruneValues($values));
        $query = "UPDATE " . $table;
        $query .= $this->formatSet($t_values);
        if ($where) {
            $query .= $this->formatWhere($where);
        }
        //echo $query;
        return @pg_query($query . ';');
    }

    public function changePassword($member, $password) {
        return $this->update(array($this->table_field_prefix . 'password' => $password), array('member' => $member));
    }
}

?>