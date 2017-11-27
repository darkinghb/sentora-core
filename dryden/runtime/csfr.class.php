<?php

class runtime_csfr
{

    public static function Token(): string
    {
        self::Tokeniser();
        $token = $_SESSION['zpcsfr'];
        return "<input type=\"hidden\" name=\"csfr_token\" value=\"" . $token . "\">";
    }

    public static function Tokeniser(): void
    {
        if (!isset($_SESSION['zpcsfr'])) {
            $_SESSION['zpcsfr'] = runtime_randomstring::randomHash();
        }
    }

    public static function Protect(): ?bool
    {
        if (isset($_POST['csfr_token']) && ($_POST['csfr_token'] === $_SESSION['zpcsfr'])) {
            self::Tokeniser();
            return true;
        }
        $error_html = '<style type="text/css"><!--
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
            <div class="dbwarning"><strong>Application Error:</strong> [0204] - The form you attempted to submit had an invalid token!</p></div>';
        die($error_html);
    }

    public static function Protected(): bool
    {
        if (isset($_POST['csfr_token']) && ($_POST['csfr_token'] === $_SESSION['zpcsfr'])) {
            self::Tokeniser();
            return true;
        }
        return false;
    }

}
