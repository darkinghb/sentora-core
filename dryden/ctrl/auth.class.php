<?php

/**
 * @copyright 2014-2015 Sentora Project (http://www.sentora.org/)
 * Sentora is a GPL fork of the ZPanel Project whose original header follows:
 *
 * Authentication class handles ZPanel authentication and handles user sessions.
 * @package zpanelx
 * @subpackage dryden -> controller
 * @version 1.0.0
 * @author Bobby Allen (ballen@bobbyallen.me)
 * @copyright ZPanel Project (http://www.zpanelcp.com/)
 * @link http://www.zpanelcp.com/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
class ctrl_auth
{

    /**
     * Checks that the server has a valid session for the user if not it will redirect to the login screen.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @global db_driver $zdbh The ZPX database handle.
     * @return bool
     */
    public static function RequireUser(): bool
    {
        global $zdbh;
        if (!isset($_SESSION['zpuid'])) {
            if (isset($_COOKIE['zUser'])) {
                if (isset($_COOKIE['zSec'])) {
                    if ($_COOKIE['zSec'] == false) {
                        $secure = false;
                    } else {
                        $secure = true;
                    }
                } else {
                    $secure = true;
                }
                self::Authenticate($_COOKIE['zUser'], $_COOKIE['zPass'], true, $secure);
            }
            runtime_hook::Execute('OnRequireUserLogin');
            $sqlQuery = 'SELECT ac_usertheme_vc, ac_usercss_vc FROM
                         x_accounts WHERE
                         ac_user_vc = :zadmin';
            $bindArray = array(':zadmin' => 'zadmin');
            $zdbh->bindQuery($sqlQuery, $bindArray);
            $themeRow = $zdbh->returnRow();
            include 'etc/styles/' . $themeRow['ac_usertheme_vc'] . '/login.ztml';
            exit;
        }
        return true;
    }

    /**
     * The main authentication mechanism, checks username and password against the database and logs the user in on a successful authenitcation request.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @global db_driver $zdbh The ZPX database handle.
     * @param string $username The username to use to authenticate with.
     * @param string $password The password to use to authenticate with.
     * @param bool $rememberMe Remember the password for 30 days? (true/false)
     * @param bool $checkingcookie The authentication request has come from a set cookie.
     * @return mixed Returns 'false' if the authentication fails otherwise will return the user ID.
     */
    public static function Authenticate($username, $password, $rememberMe = false, $sessionSecurity = false)
    {
        global $zdbh;
        $sqlString = 'SELECT ac_id_pk FROM
                      x_accounts WHERE
                      ac_user_vc = :username AND
                      ac_pass_vc = :password AND
                      ac_enabled_in = 1 AND
                      ac_deleted_ts IS NULL';

        $bindArray = array(':username' => $username,
            ':password' => $password
        );

        $q = $zdbh->bindQuery($sqlString, $bindArray);
        $row = $q->fetchColumn();

        if ($row) {
            self::SetUserSession($row['ac_id_pk'], $sessionSecurity);
            $zdbh->exec("UPDATE x_accounts SET ac_lastlogon_ts=" . time() . " WHERE ac_id_pk=" . $row);
            if ($rememberMe) {
                setcookie("zUser", $username, time() + 60 * 60 * 24 * 30, "/");
                setcookie("zPass", $password, time() + 60 * 60 * 24 * 30, "/");
                //setcookie("zSec", $sessionSecuirty, time() + 60 * 60 * 24 * 30, "/");
            }

            runtime_hook::Execute('OnGoodUserLogin');
            return $row;
        }

        runtime_hook::Execute('OnBadUserLogin');
        return false;
    }

    /**
     * Sets a user session ID.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @param int $zpuid The Sentora user account ID to set the session as.
     * @return bool
     */
    public static function SetUserSession($zpuid = 0, $sessionSecuirty = true)
    {
        if (isset($zpuid)) {
            $_SESSION['zpuid'] = $zpuid;
            //Implamentation of session security
            runtime_sessionsecurity::setCookie();
            runtime_sessionsecurity::setUserIP();
            runtime_sessionsecurity::setUserAgent();
            runtime_sessionsecurity::setSessionSecurityEnabled($sessionSecuirty);


            return true;
        }

        return false;
    }

    /**
     * Sets the value of a given named session variable, if does not exist will create the session variable too.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @param string $name The name of the session variable to set.
     * @param string $value The value of the session variable to set.
     * @return boolean
     */
    public static function SetSession($name, $value = "")
    {
        if (isset($name)) {
            $_SESSION['' . $name . ''] = $value;
            return true;
        }

        return false;
    }

    /**
     * Destroys a session and ends a user's Sentora session.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @return bool
     */
    public static function KillSession()
    {
        runtime_hook::Execute('OnUserLogout');
        $_SESSION['zpuid'] = null;
        if (isset($_SESSION['ruid'])) {
            unset($_SESSION['ruid']);
        }
        unset($_COOKIE['zUserSaltCookie']);
        return true;
    }

    /**
     * Deletes the authentication 'rememberme' cookies.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @return bool
     */
    public static function KillCookies()
    {
        setcookie("zUser", '', time() - 3600, "/");
        setcookie("zPass", '', time() - 3600, "/");
        unset($_COOKIE['zUser']);
        unset($_COOKIE['zPass']);
        unset($_COOKIE['zSec']);
        return true;
    }

    /**
     * Returns the UID (User ID) of the current logged in user.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @global obj $controller The Sentora controller object.
     * @return int The current user's session ID.
     */
    public static function CurrentUserID()
    {
        global $controller;
        return $controller->GetControllerRequest('USER', 'zpuid');
    }

}