<?php

namespace Modules\Database69;

use Exception;
use PDO;
use PDOStatement;

/**
 * @property PDOStatement $stmt PDO Database Statement
 */
class Db{

    protected
            $database = 'object69',
            $hostname = 'localhost',
            $username = 'root',
            $password = 'afrid123',
            $port     = 3306,
            $dsn      = 'mysql',
            $db       = null;

    public function get($table){
        return new DBO($table, $this);
    }

    public function connect(){
        if($this->db !== null){
            return;
        }
        try{
            $this->db = new PDO("$this->dsn:dbname=$this->database;host=$this->hostname;port=" . (int)$this->port, $this->username, $this->password);
            return $this;
        }catch(Exception $e){
            throw $e;
        }
    }

    public function query($query, array $params = []){
        try{
            $this->connect();
            $this->stmt = $this->db->prepare($query);
            $this->bind($query, $params);
            $this->stmt->execute();
            return true;
        }catch(Exception$e){
            throw $e;
        }
        return $this;
    }

    public function getAll($query, array $params = []){
        $this->query($query, $params);
        return $this->stmt->fetchAll(PDO::FETCH_CLASS);
    }

    public function rowCount(){
        return $this->stmt->rowCount();
    }

    protected function bind($query, array $params){
        if(strpos($query, "?")){
            array_unshift($params, null);
            unset($params[0]);
        }
        foreach($params as $key => $val){
            switch(gettype($val)){
                case "boolean":
                    $type = PDO::PARAM_BOOL;
                    break;
                case "integer":
                    $type = PDO::PARAM_INT;
                    break;
                case "string":
                    $type = PDO::PARAM_STR;
                    break;
                case "null":
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
                    break;
            }
            $this->stmt->bindValue($key, $val, $type);
        }
    }

    /**
     * Tests to see if a string is a valid table/column name
     * @param string $string
     * @return boolean
     */
    public function validName($string){
        return !preg_match("/[^a-zA-Z0-9\$_\.]/i", $string);
    }

    /**
     * Tests an array of tables to see if they are vaild
     * @param array $tables An array of tables to test
     * @throws Exception
     */
    public function testTables(array $tables){
        foreach($tables as $table){
            if(!$this->_validName($table)){
                throw new Exception("Invalid Table Name '$table'.");
            }
        }
        return true;
    }

}
