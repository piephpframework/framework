<?php

namespace Object69\Modules\Database69;

use Exception;
use PDO;
use PDOStatement;

/**
 * @property PDOStatement $stmt PDO Database Statement
 */
class Db{

    protected
            $database = '',
            $hostname = '',
            $username = '',
            $password = '',
            $port     = 3306,
            $dsn      = 'mysql',
            $db       = null;

    /**
     * Loads a database connection
     * @param type $connection_name
     * @return Db
     */
    public function load($connection_name){
        $this->config([
            'hostname' => $_ENV[$connection_name]['hostname'],
            'database' => $_ENV[$connection_name]['database'],
            'username' => $_ENV[$connection_name]['username'],
            'password' => $_ENV[$connection_name]['password']
        ]);
        return $this;
    }

    public function config(array $config){
        $this->database = isset($config['database']) ? $config['database'] : '';
        $this->hostname = isset($config['hostname']) ? $config['hostname'] : '';
        $this->username = isset($config['username']) ? $config['username'] : '';
        $this->password = isset($config['password']) ? $config['password'] : '';
        $this->port     = isset($config['port']) ? $config['port'] : 3306;
        $this->dsn      = isset($config['dsn']) ? $config['dsn'] : 'mysql';
        return $this;
    }

    /**
     * Connects to the database
     * @return Db
     * @throws Exception
     */
    public function connect(){
        if($this->db !== null){
            return $this;
        }
        try{
            $this->db = new PDO("$this->dsn:dbname=$this->database;host=$this->hostname;port=" . (int)$this->port, $this->username, $this->password);
            return $this;
        }catch(Exception $e){
            throw $e;
        }
    }

    /**
     * Gets a database object
     * @param string $table
     * @return Model
     */
    public function get($table){
        if(strpos($table, '\\') !== false){
            return new $table($this);
        }
        return new Model($this, $table);
    }

    /**
     * Queries the database
     * @param string $query
     * @param array $params
     * @return Db
     * @throws Exception
     */
    public function query($query, array $params = []){
        try{
            $this->connect();
            $this->stmt = $this->db->prepare($query);
            $this->bind($query, $params);
            $this->stmt->execute();
        }catch(Exception$e){
            throw $e;
        }
        return $this;
    }

    /**
     * Gets all found items from a query
     * @param type $query
     * @param array $params
     * @return type
     */
    public function getAll($query, array $params = []){
        $this->query($query, $params);
        return $this->stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Gets a row from a query
     * @param string $query
     * @param array $params
     * @return type
     */
    public function getRow($query, array $params = []){
        $this->query($query, $params);
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Gets the first column from a query
     * @param string $query
     * @param array $params
     * @return type
     */
    public function getOne($query, array $params = []){
        $this->query($query, $params);
        return $this->stmt->fetchColumn();
    }

    /**
     * Returns the number of rows affected by the last SQL statement
     * @return int
     */
    public function rowCount(){
        return $this->stmt->rowCount();
    }

    /**
     * Binds items to their placeholder
     * @param string $query
     * @param array $params
     */
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
