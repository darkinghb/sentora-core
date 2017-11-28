<?php

/**
 * @copyright 2014-2015 Sentora Project (http://www.sentora.org/)
 * Sentora is a GPL fork of the ZPanel Project whose original header follows:
 *
 * Database access class, enables PDO database access.
 * @package zpanelx
 * @subpackage dryden -> db
 * @version 1.0.0
 * @author Bobby Allen (ballen@bobbyallen.me)
 * @copyright ZPanel Project (http://www.zpanelcp.com/)
 * @link http://www.zpanelcp.com/
 * @license GPL (http://www.gnu.org/licenses/gpl.html)
 */
class db_driver extends PDO
{

    var $css = "<style type=\"text/css\"><!--
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
                    color: #000;
                    white-space: pre-wrap;
            }
            pre {
                color: #666;
            }
            </style>";
    /**
     *
     * @var \PDOStatement
     */
    private $_prepared = null;
    /**
     *
     * @var \PDOStatement
     */
    private $_executed = null;
    /**
     *
     * @var array
     */
    private $_result = null;
    /**
     *
     */
    private $queriesExecuted = array();

    /**
     *
     * @param String $dsn
     * @param String $username
     * @param String $password
     * @param $driver_options [optional]
     */
    public function __construct($dsn, $username = null, $password = null, $driver_options = null)
    {
        parent::__construct($dsn, $username, $password, $driver_options);
    }

    public function query($query): ?\PDOStatement
    {
        try {
            $result = parent::query($query);
            return ($result);
        } catch (PDOException $e) {
            $errormessage = $this->errorInfo();
            $clean = $this->cleanexpmessage($e);
            if (!runtime_controller::IsCLI()) {
                $error_html = $this->css . "<div class=\"dbwarning\"><strong>Critical Error:</strong> [0144] - Sentora database 'query' error (" . $this->errorCode() . ").<p>A database query has caused an error, the details of which can be found below.</p><p><strong>Error message:</strong></p><pre> " . $errormessage[2] . "</pre><p><strong>SQL Executed:</strong></p><pre>" . $query . "</pre><p><strong>Stack trace: </strong></p><pre>" . $clean . "</pre></div>";
            } else {
                $error_html = "SQL Error: " . $errormessage[2] . "\n";
            }
            die($error_html);
        }
    }

    /**
     *
     * @param String $exception
     * @return String
     */
    private function cleanexpmessage($exception)
    {
        $res = strstr($exception, "]: ", false);
        $res1 = str_replace(']: ', '', $res);
        $res2 = strstr($res1, 'Stack', true);
        $stack = strstr($exception, 'Stack trace:', false);
        $stack1 = strstr($stack, '}', true);
        $stack2 = str_replace("Stack trace:", "", $stack1);
        return $res2 . $stack2 . "}";
    }

    public function exec($query)
    {
        try {
            return parent::exec($query);
        } catch (PDOException $e) {
            $errormessage = $this->errorInfo();
            $clean = $this->cleanexpmessage($e);
            if (!runtime_controller::IsCLI()) {
                $error_html = $this->css . "<div class=\"dbwarning\"><strong>Critical Error:</strong> [0144] - Sentora database 'exec' error (" . $this->errorCode() . ").<p>A database query has caused an error, the details of which can be found below.</p><p><strong>Error message:</strong></p><pre> " . $errormessage[2] . "</pre><p><strong>SQL Executed:</strong></p><pre>" . $query . "</pre><p><strong>Stack trace: </strong></p><pre>" . $clean . "</pre></div>";
            } else {
                $error_html = "SQL Error: " . $errormessage[2] . "\n";
            }
            die($error_html);
        }
    }

    /**
     * The main query function using bind variables for SQL injection protection.
     * Returns an array of results.
     * @author Kevin Andrews (kandrews@zpanelcp.com)
     * @param String $sqlString
     * @param Array $bindArray
     * @return PDOStatement
     * @internal param array $driver_options [optional]
     */
    public function bindQuery($sqlString, array $bindArray): \PDOStatement
    {
        $prep = parent::prepare($sqlString);
        $prep->execute($bindArray);
        $this->setExecuted($prep);

        return $prep;
    }

    public function prepare($query, $driver_options = array()): ?\PDOStatement
    {
        try {
            $result = parent::prepare($query, $driver_options);
            $this->queriesExecuted[] = $query;
            return ($result);
        } catch (PDOException $e) {
            $errormessage = $this->errorInfo();
            $clean = $this->cleanexpmessage($e);
            if (!runtime_controller::IsCLI()) {
                $error_html = $this->css . "<div class=\"dbwarning\"><strong>Critical Error:</strong> [0144] - Sentora database 'prepare' error (" . $this->errorCode() . ").<p>A database query has caused an error, the details of which can be found below.</p><p><strong>Error message:</strong></p><pre> " . $errormessage[2] . "</pre><p><strong>SQL Executed:</strong></p><pre>" . $query . "</pre><p><strong>Stack trace: </strong></p><pre>" . $clean . "</pre></div>";
            } else {
                $error_html = "SQL Error: " . $errormessage[2] . "\n";
            }
            die($error_html);
        }
    }



    /**
     *
     * @param PDOStatement $executed
     */
    private function setExecuted(PDOStatement $executed): void
    {
        $this->_executed = $executed;
    }

    public function queryWithParams($query, $params): PDOStatement
    {

    }

    public function returnRow()
    {
        return $this->getExecuted()->fetch(PDO::FETCH_ASSOC);
    }

    /**
     *
     * @return \PDOStatement
     */
    private function getExecuted()
    {
        return $this->_executed;
    }

    /**
     * Returns a multidimensional array of results.
     * @return array
     */
    public function returnRows()
    {
        return $this->getExecuted()->fetchAll();
    }

    /**
     * Returns the rows affected by any query.
     * @return int
     */
    public function returnResult()
    {
        return $this->getExecuted()->rowCount();
    }

    /**
     * The function is the equilivent to mysql_real_escape_string needed due to PDO issues with `
     * @author Sam Mottley (smottley@zpanelcp.com)
     * @param String $string string to be cleaned
     * @return String Clean version of the string
     */
    public function mysqlRealEscapeString($string)
    {
        $search = array("\\", "\0", "\n", "\r", "\x1a", "'", '"', "`"); //`
        $replace = array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"', ""); //`
        $cleanString = str_replace($search, $replace, $string);
        return $cleanString;
    }

    /**
     * Returns a list of all the current queries executed. (Implemented for the Debug/Execution class!)
     * @author Bobby Allen (ballen@bobbyallen.me)
     * @return array List of executed SQL queries.
     */
    public function getExecutedQueries()
    {
        return $this->queriesExecuted;
    }



}

?>
