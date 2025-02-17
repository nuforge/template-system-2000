<?PHP

class members extends Base_Member
{


    public $connection_info = array('user' => 'webcomtv_wctvuser', 'password' => '*84tdRFH!nya', 'dbname' => 'webcomtv_webcomictv');

    public $primarykey = 'member';
    public $session_title = 'member_login';
    protected $member_info;
    protected $table_field_prefix = 'mem_';
    protected $db_username_field = 'mem_unique';
    protected $login_username_field = 'username';
    protected $db_password_field = 'mem_password';
    protected $login_password_field = 'password';


    public function globalQueries()
    {
        $this->addSelectStatement("extract('year' from age(mem_birthdate))", 'mem_age');
        $this->addSelectStatement("case when (age(mem_birthdate) < INTERVAL '1 week') then 1 else 0 end", 'mem_birthdate');
        $this->addSelectStatement("case when mem_gender='m' then 1 else 0 end", 'mem_male');
        $this->addSelectStatement("case when mem_gender='f' then 1 else 0 end", 'mem_female');

        $this->addSelectBoolean('mem_vip');
        $this->addSelectBoolean('mem_show_on_awc');
        return false;
    }

    public function globalJoins()
    {
        $this->joinTable('avatars', '(mem_avatar = avatar)');
        $this->joinTable('webcomics', '(avatar_webcomic = webcomic)');
        return true;
    }

    public function cleanValues($values, $table = false)
    {
        if (!$table) {
            $table = $this->table;
        }
        if (!is_array($values)) {
            return $values;
        }
        return pg_convert($this->db_connection, $table, $values, PGSQL_CONV_FORCE_NULL);
    }


    public function auto_login()
    {
        $rc4 = new rc4crypt;
        if (!empty($_SESSION[$this->session_title])) {
            $arr = explode(':', $rc4->endecrypt('x87jm43', $_SESSION[$this->session_title], 'de'));
            $loginifno = array($this->login_username_field => $arr[0], $this->login_password_field => $arr[1], 'remember' => 0);
            return $this->login($loginifno);
        } elseif (empty($_SESSION[$this->session_title]) && !empty($_COOKIE[$this->session_title])) {
            $log_info = $rc4->endecrypt('x87jm43', $_COOKIE[$this->session_title], 'de');
            $arr = explode(':', $log_info);
            $loginifno = array($this->login_username_field => $arr[0], $this->login_password_field => $arr[1], 'remember' => 1);
            return $this->login($loginifno);
        }
    }


    public function login($fa_loginInfo)
    {
        $rc4 = new rc4crypt;
        $this->member_info = $this->load(array($this->db_username_field => $fa_loginInfo[$this->login_username_field]));

        if (empty($fa_loginInfo[$this->login_username_field]) || empty($this->member_info) || $fa_loginInfo[$this->login_password_field] != $this->member_info[$this->db_password_field]) {
            return false;
        }

        $log_info = $this->member_info[$this->db_username_field] . ':' . $fa_loginInfo[$this->login_password_field] . ':' . mktime();

        $log_info = $rc4->endecrypt('x87jm43', $log_info, 'en');

        if (!empty($fa_loginInfo['remember'])) {
            $lifetime = time() + 365 * 24 * 60 * 60;
            setcookie($this->session_title, $log_info, $lifetime, "/");
        }

        $_SESSION[$this->session_title] = $log_info;
        $this->onlogin();
        return $this->member_info;
    }

    public function getMemberInfo()
    {
        return $this->member_info;
    }

    public function onlogin()
    {
        $set['mem_last_login'] = 'NOW()';
        $where['member'] = $this->member_info['member'];
        $this->update($set, $where);
    }


    public function checkIsUnique($f_value, $f_field, $f_caseSensitive = false)
    {

        $user = $this->validateValues(array('mem_' . $f_field => $f_value));
        $where = ($f_caseSensitive)  ? "mem_" . $f_field . " = " . $user['mem_' . $f_field] : "lower(mem_" . $f_field . ") = " . strtolower($user['mem_' . $f_field]);
        $q = "SELECT count(member) as count from members WHERE " . $where . ";";
        $res = @pg_fetch_assoc(@pg_query($q));
        return $res['count'] ? false : true;
    }
}
