<?php

SetWriteApacheConfigTrue();

function SetWriteApacheConfigTrue() {
    global $zdbh;
    $zdbh->exec("UPDATE x_settings
								SET so_value_tx='true'
								WHERE so_name_vc='apache_changed'");
}

?>