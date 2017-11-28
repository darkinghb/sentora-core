<?php


class module_controller extends ctrl_module
{

    public static $ok;

    public static function getConfig()
    {
        global $zdbh;
        $currentuser = ctrl_auth::CurrentUserID();
        $sql = 'SELECT vh_php_handler FROM x_vhosts WHERE vh_acc_fk = :user';
        $numrows = $zdbh->prepare($sql);
        $numrows->bindParam(':user', $currentuser);
        $numrows->execute();
        $result = $numrows->fetchColumn();

        if ($result != null) {
            $fieldhtml = ctrl_options::OuputSettingMenuField("php_changer", '71|56', $result);

            $res[] = array('cleanname' => "PHP Version",
                'name' => "PHP Version",
                'description' => "Current php version",
                'value' => $result,
                'fieldhtml' => $fieldhtml);

            return $res;
        }

        return false;
    }

    public static function doUpdateConfig(): void
    {
        if (runtime_csfr::Protected()) {
            global $zdbh, $controller;
            if (!fs_director::CheckForEmptyValue($controller->GetControllerRequest('FORM', 'php_changer'))) {
                $currentUser = ctrl_auth::CurrentUserID();
                $value = $controller->GetControllerRequest('FORM', 'php_changer');
                if ($value != '71' && $value != '56') {
                    $value = '56';
                }

                $updatesql = $zdbh->prepare('UPDATE x_vhosts SET vh_php_handler = :value WHERE vh_acc_fk = :user');
                $updatesql->bindParam(':value', $value);
                $updatesql->bindParam(':user', $currentUser);
                $updatesql->execute();
                self::SetWriteApacheConfigTrue();
            }
            self::$ok = true;
        }
    }

    public static function SetWriteApacheConfigTrue()
    {
        global $zdbh;
        $zdbh->exec("UPDATE x_settings SET so_value_tx='true' WHERE so_name_vc='apache_changed'");
    }


    public static function getResult(): string
    {
        if (fs_director::CheckForEmptyValue(self::$ok)) {
            return '';
        }

        return ui_sysmessage::shout(ui_language::translate("Changes to your settings have been saved successfully!"));
    }

}