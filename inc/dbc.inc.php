<?php

$zlo = new debug_logger();

try {
    $zdbh = new db_driver("mysql:host=$host;dbname=$dbname", $user, $pass, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $zdbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $zlo->method = "text";
    $zlo->logcode = "0100";
    $zlo->detail = "Unable to connect or authenticate against the database supplied!";
    $zlo->mextra = $e;
    $error_html = "<style type=\"text/css\"><!--
            .dbwarning {
                    font-family: Verdana, Geneva, sans-serif;
                    font-size: 14px;
                    color: #C00;
                    background-color: #FCC;
                    padding: 30px;
                    border: 1px solid #C00;
            }
            p {
                    font-size: 12px;
                    color: #666;
            }
            </style>
            <div class=\"dbwarning\"><strong>Critical Error:</strong> [0100] - Unable to connect or authenticate to the Sentora database (<em>$dbname</em>).<p>We advice that you contact the server administrator to ensure that the database server is online and that the correct connection parameters are being used.</p></div>";

    die($error_html);
}