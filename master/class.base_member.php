<?PHP

class Base_Member extends DB_Object {

    public $primarykey = 'member';
    public $session_title = 'member_login';
    protected $member_info;
    protected $table_field_prefix = 'mem_';
    protected $db_username_field = 'mem_unique';
    protected $login_username_field = 'username';
    protected $db_password_field = 'mem_password';
    protected $login_password_field = 'password';

    public function auto_login() {
        $rc4 = new rc4crypt;
        if (!empty($_SESSION[$this->session_title])) {
            $arr = explode(':', $rc4->endecrypt('x87jm43', $_SESSION [$this->session_title], 'de'));
            $loginifno = array($this->login_username_field => $arr[0], $this->login_password_field => $arr[1], 'remember' => 0);
            return $this->login($loginifno);
        } elseif (empty($_SESSION[$this->session_title]) && !empty($_COOKIE[$this->session_title])) {
            $log_info = $rc4->endecrypt('x87jm43', $_COOKIE [$this->session_title], 'de');
            $arr = explode(':', $log_info);
            $loginifno = array($this->login_username_field => $arr[0], $this->login_password_field => $arr[1], 'remember' => 1);
            return $this->login($loginifno);
        }
    }

    public function initialize() {
        $rc4 = new rc4crypt;
        if (!empty($_SESSION[$this->session_title])) {
            $arr = explode(':', $rc4->endecrypt('x87jm43', $_SESSION [$this->session_title], 'de'));
            $loginifno = array('username' => $arr[0], 'password' => $arr[1], 'remember' => 0);
            return $this->login($loginifno);
        } elseif (empty($_SESSION[$this->session_title]) && !empty($_COOKIE[$this->session_title])) {
            $log_info = $rc4->endecrypt('x87jm43', $_COOKIE [$this->session_title], 'de');
            $arr = explode(':', $log_info);
            $loginifno = array('username' => $arr[0], 'password' => $arr[1], 'remember' => 1);
            return $this->login($loginifno);
        }
    }

    public function getTitles($member=false) {
        if (!$member) {
            $member = $this->memberInfo['member'];
        }
        $query = 'SELECT * FROM membertitles LEFT JOIN titles USING (title) WHERE member = ' . $member;
        return @pg_fetch_all(@pg_query($query . ';'));
    }

    public function securityRedirect($clearance=0, $redirect='login.html') {
        if ($this->security($clearance)) {
            return true;
        } else {
            header("location: " . $redirect);
        }
    }

    public function security($clearance=0) {
        if (!isset($_SESSION[$this->session_title])) {
            $_SESSION['redir_url'] = $_SERVER['REQUEST_URI'];
            return false;
        } else {
            $rc4 = new rc4crypt;
            $arr = explode(':', $rc4->endecrypt('x87jm43', $_SESSION [$this->session_title], 'de'));
            $arr = array_combine(array('username', 'password', 'time'), $arr);
            if (!$this->login($arr)) {
                return false;
            }
            if (!$clearance) {
                return true;
            }
            $titles = $this->getTitles();
            if (empty($titles)) {
                return false;
            }
            foreach ($titles as $v) {
                if (is_array($clearance)) {
                    if (array_search($v, $clearance) === true) {
                        return true;
                    }
                } else {
                    if ($v['title'] >= $clearance) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    public function checkUsername($username) {
        $user = $this->validateValues(array('username' => $username));
        $q = "SELECT count(member) as count from members WHERE lower(username) = " . strtolower($user['username']) . ";";
        $res = @pg_fetch_assoc(@pg_query($q));
        return $res['count'];
    }

    public function onlogin() {
        return true;
    }

    public function login($logininfo) {
        $rc4 = new rc4crypt;
        $this->memberInfo = $this->load(array('username' => $logininfo['username']));
        if (empty($logininfo['username']) || empty($this->memberInfo) || $logininfo['password'] != $this->memberInfo['password']) {
            return false;
        }

        $log_info = $this->memberInfo['username'] . ':' . $logininfo['password'] . ':' . mktime();
        $log_info = $rc4->endecrypt('x87jm43', $log_info, 'en');

        if (!empty($logininfo['remember'])) {
            $lifetime = time() + 365 * 24 * 60 * 60;
            setcookie($this->session_title, $log_info, $lifetime, "/");
        }

        $_SESSION [$this->session_title] = $log_info;
        $this->onlogin();
        return $this->memberInfo;
    }

    public function logout() {
        setcookie($this->session_title, $_COOKIE[$this->session_title], time() - 3000, "/");
        return session_destroy();
    }

}

?>