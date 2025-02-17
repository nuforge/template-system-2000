<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
*/

/**
 * Description of securitychecker
 *
 * @author nuForge
 */
class ManagerSiteSecurity extends Manager
{
    //put your code here
    protected $redirectURL = '/login.html';
    protected $privilegeChecks = array();
    protected $memberPrivileges = array();
    protected $passCheck;

    protected function initialize_ManagerSiteSecurity()
    {

        DEFINE('SECURITY_CHECKER_MEMBER_PRIVILEGES_MEMBER', 'mp_member');
        DEFINE('SECURITY_CHECKER_MEMBER_PRIVILEGES_PRIVILEGE', 'mp_privilege');
        DEFINE('SECURITY_CHECKER_PRIVILEGE_UNIQUE', 'privilege_unique');
    }

    public function __construct($obj_member_privilages = false)
    {
        $this->memberPrivilegesClass = ($obj_member_privilages) ? $obj_member_privilages : new member_privileges();
        if (empty($this->memberPrivilegesClass)) {
            return false;
        }
        $this->initialize();
    }

    public function setMemberPrivilegesClass($obj_member_privilages)
    {
        $this->memberPrivilegesClass =  $obj_member_privilages;
    }

    public function loadMemberPrivileges($f_member_id)
    {
        $privileges = $this->memberPrivilegesClass->getSelectList(SECURITY_CHECKER_PRIVILEGE_UNIQUE, SECURITY_CHECKER_PRIVILEGE_UNIQUE, false, array(SECURITY_CHECKER_MEMBER_PRIVILEGES_MEMBER => $f_member_id));

        $this->setMemberPrivileges($privileges);
        return $privileges;
    }

    public function getPrivilegeInterfaces($f_member_id)
    {
        $this->memberPrivilegesClass = $this->loadClass(SECURITY_CHECKER_MEMBER_PRIVILEGE_CLASS);
        return $this->memberPrivilegesClass->getList(array(SECURITY_CHECKER_MEMBER_PRIVILEGES_PRIVILEGE => 'asc'), array(SECURITY_CHECKER_MEMBER_PRIVILEGES_MEMBER => $f_member_id));
    }

    public function addPrivilegeCheck($f_privilegeId, $f_privilege_class = PRIVILEGE_CLASS_MUST)
    {
        $this->privilegeChecks[$f_privilege_class][] = $f_privilegeId;
        return true;
    }

    public function addMultiplePrivilegeChecks($fa_privilege_ids)
    {
        foreach ($fa_privilege_ids as $privilege => $class) {
            $this->privilegeChecks[$class][] = $privilege;
        }
        return true;
    }

    public function checkPrivileges($f_member, $f_privilege_class = PRIVILEGE_CLASS_ALL, $f_redirect = true)
    {

        if (!empty($f_member) && empty($this->privilegeChecks)) {
            return true;
        }
        switch ($f_privilege_class) {
            case PRIVILEGE_CLASS_ALL:
                $this->passCheck = $this->checkPrivilegesAll();
                break;
            case PRIVILEGE_CLASS_ANY:
                $this->passCheck = $this->checkPrivilegesAny($this->privilegeChecks[PRIVILEGE_CLASS_ANY]);
                break;
            case PRIVILEGE_CLASS_MUST:
                $this->passCheck = $this->checkPrivilegesMust($this->privilegeChecks[PRIVILEGE_CLASS_MUST]);
                break;
            case PRIVILEGE_CLASS_RESTRICT:
                $this->passCheck = $this->checkPrivilegesRestrict($this->privilegeChecks[PRIVILEGE_CLASS_RESTRICT]);
                break;
            default:
                $this->passCheck = $this->checkPrivilegesAll();
                break;
        }
        if (empty($f_member)) {
            $this->passCheck = false;
        }
        if ($this->passCheck) {
            return true;
        } else {
            if ($f_redirect) {
                header('location: ' . $this->redirectURL);
            } else {
                return false;
            }
        }
    }

    public function checkPrivilegesAll()
    {
        if (!$this->checkPrivilegesAny($this->privilegeChecks[PRIVILEGE_CLASS_ANY])) {
            return false;
        }
        if (!$this->checkPrivilegesMust($this->privilegeChecks[PRIVILEGE_CLASS_MUST])) {
            return false;
        }
        if (!$this->checkPrivilegesRestrict($this->privilegeChecks[PRIVILEGE_CLASS_RESTRICT])) {
            return false;
        }
        return true;
    }

    public function checkPrivilegesAny($fa_privileges_any)
    {
        if (empty($fa_privileges_any)) {
            return true;
        }
        if (empty($this->memberPrivileges)) {
            return false;
        }
        foreach ($f_privileges_any as $privilege) {
            if (in_array($privilege, $this->memberPrivileges, true)) {
                return true;
            }
        }
        return false;
    }

    public function checkPrivilegesMust($fa_privileges_must)
    {
        if (empty($fa_privileges_must)) {
            return true;
        }
        if (empty($this->memberPrivileges)) {
            return false;
        }
        foreach ($fa_privileges_must as $privilege) {
            if (!in_array($privilege, $this->memberPrivileges, true)) {
                return false;
            }
        }
        return true;
    }

    public function checkPrivilegesRestrict($fa_privileges_restrict)
    {
        if (empty($fa_privileges_restrict)) {
            return true;
        }
        if (empty($this->memberPrivileges)) {
            return false;
        }
        foreach ($fa_privileges_restrict as $privilege) {
            if (in_array($privilege, $this->memberPrivileges, true)) {
                return false;
            }
        }
        return true;
    }

    public function checkSinglePrivilege($f_member, $f_privilege, $f_restrict = false, $f_redirect = false)
    {
        //$this->memberPrivileges = $this->memberPrivilegesClass->getPrivilegeList(SECURITY_CHECKER_MEMBER_PRIVILEGES_PRIVILEGE,  SECURITY_CHECKER_PRIVILEGE_UNIQUE, false, array(SECURITY_CHECKER_MEMBER_PRIVILEGES_MEMBER => $f_member));

        if (empty($this->memberPrivileges)) {
            return false;
        }
        $result = ($f_restrict) ? !in_array($f_privilege, $this->memberPrivileges, true) : $result = (in_array($f_privilege, $this->memberPrivileges, true));

        if ($result) {
            return true;
        } else {
            if ($f_redirect) {
                header('location: ' . $this->redirectURL);
            } else {
                return false;
            }
        }
    }

    public function setMemberPrivileges($fa_member_privileges)
    {
        $this->memberPrivileges = $fa_member_privileges;
    }

    public function getMemberPrivileges($fa_member_privileges)
    {
        return $this->memberPrivileges;
    }

    public function setRedirectURL($f_redirect_url)
    {
        $this->redirectURL = $f_redirect_url;
    }

    public function getRedirectURL()
    {
        return $this->redirectURL;
    }
}
