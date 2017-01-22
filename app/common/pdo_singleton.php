<?php
/**
 * Created by PhpStorm.
 * User: mnobrega
 * Date: 21-01-2017
 * Time: 11:45
 */

class PDOSingleton
{
    /**@var $_db PDO */
    private $_db;
    static $_instance;

    protected function __construct()
    {
        $this->_db = new PDO('mysql:host='.DB_HOST.';charset=utf8;dbname='.DB_NAME,DB_USER,DB_PASS);
        $this->_db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    }

    private function __clone()
    {
        // TODO: Implement __clone() method.
    }

    public static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    protected function query($query)
    {
        return $this->_db->query($query);
    }

    protected function getObj(PDOStatement $SQLResult,$className,$allowNull)
    {
        $obj = null;
        if ($SQLResult->rowCount()==1)
        {
            $obj = new $className($SQLResult->fetch(PDO::FETCH_ASSOC));
        }
        elseif (!$allowNull)
        {
            throw new PDOException("Wrong number of results found:".$SQLResult->rowCount()." Should be exactly 1");
        }
        else
        {
            //do nothing
        }
        return $obj;
    }

    protected function getObjArray(PDOStatement $SQLResult, $className)
    {
        $result = array();
        while($row = $SQLResult->fetch(PDO::FETCH_ASSOC))
        {
            $result[] = new $className($row);
        }
        return $result;
    }

    protected function inArray(Array $arr)
    {
        return "('".(implode("','",$arr))."')";
    }
}