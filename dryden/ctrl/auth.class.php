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

    public static function RequireUser(): bool
    {
        if (!isset($_SESSION['zpuid'])) {
            if (isset($_COOKIE['zUser'])) {
                $secure = isset($_COOKIE['zSec']) && (bool)$_COOKIE['zSec'];
                self::Authenticate($_COOKIE['zUser'], $_COOKIE['zPass'], false, $secure);
            }
            global $zdbh;
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
     * @param string $username The username to use to authenticate with.
     * @param string $password The password to use to authenticate with.
     * @param bool $rememberMe Remember the password for 30 days? (true/false)
     * @param bool $sessionSecurity
     * @return mixed Returns 'false' if the authentication fails otherwise will return the user ID.
     * @internal param bool $isCookie
     * @global db_driver $zdbh The ZPX database handle.
     * @internal param bool $checkingcookie The authentication request has come from a set cookie.
     */
    public static function Authenticate($username, $password, $rememberMe = false, $sessionSecurity = false)
    {
        global $zdbh;
        $sqlString = 'SELECT * FROM
                      x_accounts WHERE
                      ac_user_vc = :username AND
                      ac_pass_vc = :password AND
                      ac_enabled_in = 1 AND
                      ac_deleted_ts IS NULL';

        $bindArray = array(':username' => $username,
            ':password' => $password
        );

        $zdbh->bindQuery($sqlString, $bindArray);
        $row = $zdbh->returnRow();

        if ($row) {
            //Disabled till zpanel 10.0.3
            //runtime_sessionsecurity::sessionRegen();

            self::SetUserSession($row['ac_id_pk'], $sessionSecurity);
            $zdbh->exec('UPDATE x_accounts SET ac_lastlogon_ts=' . time() . ' WHERE ac_id_pk=' . $row['ac_id_pk']);
            if ($rememberMe) {
                setcookie("zUser", $username, time() + 60 * 60 * 24 * 30, "/");
                setcookie("zPass", $password, time() + 60 * 60 * 24 * 30, "/");
                //setcookie("zSec", $sessionSecuirty, time() + 60 * 60 * 24 * 30, "/");
            }

            runtime_hook::Execute('OnGoodUserLogin');
            return $row['ac_id_pk'];
        }

        runtime_hook::Execute('OnBadUserLogin');
        return false;
    }

    /**
     * Sets a user session ID.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @param int $user The Sentora user account ID to set the session as.
     * @param bool $sessionSecurity session security enabled
     * @return bool
     */
    public static function SetUserSession(int $user = 0, bool $sessionSecurity = true): bool
    {
        if ($user > 0) {
            $_SESSION['zpuid'] = $user;
            runtime_sessionsecurity::setCookie();
            runtime_sessionsecurity::setUserIP();
            runtime_sessionsecurity::setUserAgent();
            runtime_sessionsecurity::setSessionSecurityEnabled($sessionSecurity);
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
    public static function SetSession($name, $value = ""): bool
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
    public static function KillSession(): bool
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
    public static function KillCookies(): bool
    {
        setcookie("zUser", '', time() - 3600, "/");
        setcookie("zPass", '', time() - 3600, "/");
        unset($_COOKIE['zUser'], $_COOKIE['zPass'], $_COOKIE['zSec']);
        return true;
    }

    /**
     * Returns the UID (User ID) of the current logged in user.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @return int The current user's session ID.
     */
    public static function CurrentUserID(): int
    {
        global $controller;
        return $controller->GetControllerRequest('USER', 'zpuid');
    }

}
