<?php
/**
 * @copyright 2014-2015 Sentora Project (http://www.sentora.org/)
 * Sentora is a GPL fork of the ZPanel Project whose original header follows:
 *
 * Class provides functionallity to generate secure random strings
 * @package zpanelx
 * @implimentation To be inpliment into zpanel's core fucntions by 10.0.3
 * @subpackage dryden -> runtime
 * @version 1.0.2
 * @author Sam Mottley (smottley@zpanelcp.com)
 * @copyright ZPanel Project (http://www.zpanelcp.com/)
 * @link http://www.zpanelcp.com/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */

class runtime_randomstring
{
    /**
     * Generate a random string
     * @author Sam Mottley (smottley@zpanelcp.com)
     * @param int $size The size of the hash default 50
     * @param bool|string $hash True or False or Type of algeritherm to hash the the mixed string with. Hash may break other settings depending on the argirithom selected.
     * @return string A random string
     * @internal param string $characters
     * @internal param string $charachters list of all allowed chars
     */
    public static function randomHash($size = 50, $hash = false)
    {
        $hashMixed = base64_encode(openssl_random_pseudo_bytes($size));

        //check if hashing is needed
        if ($hash == false) {
            //do not hash
            $hash = $hashMixed;
        } else {
            if ($hash == true) {
                $hash = 'sha256';
            }
            //Then hash the hash is sha256
            $hash = hash($hash, $hashMixed);
        }

        //Randomise string again
        $hash = str_shuffle($hash);

        //return hash
        return $hash;
    }


}

?>
