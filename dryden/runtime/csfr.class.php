<?php

class runtime_csfr
{

    /**
     * Builds a 'hidden' form type which is populated with the generated token.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @return string The HTML form tag.
     */
    public static function Token()
    {
        self::Tokeniser();
        $token = $_SESSION['zpcsfr'];
        return "<input type=\"hidden\" name=\"csfr_token\" value=\"" . $token . "\">";
    }

    /**
     * Generates a new CSFR token.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @return bool
     */
    public static function Tokeniser()
    {
        if (!isset($_SESSION['zpcsfr'])) {
            $_SESSION['zpcsfr'] = runtime_randomstring::randomHash();
        }
        return true;
    }

    /**
     * Verfies that the submitted form has a valid CSFR token.
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @return bool
     */
    public static function Protect()
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

}
