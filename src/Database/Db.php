<?php

namespace Database;

use PDO;
use Application\Object;

class Db extends Object {

    protected $stmt, $pdo;

    public function connect(){
        $this->pdo = new PDO('mysql:dbname=piephp;host=127.0.0.1', 'root', 'afrid123');
    }

    /**
     * Queries the database
     * @param string $query The query string to execute on the database
     * @return Db
     */
    public function query($query, array $params = []){
        if(!isset($this->pdo)){
            $this->connect();
        }
        $this->stmt = $this->pdo->prepare($query);
        $this->_bind($query, $params);
        $this->stmt->execute();
        return $this;
    }

    /**
     * Fetches all the items from the database result
     * @return array An array of PDO fetch results
     */
    public function get(){
        return $this->stmt->fetchAll(PDO::FETCH_CLASS);
    }

    /**
     * Places database tick marks '`' arround the proper items
     * @param string $string The item to be tested
     * @return string The resulting string
     */
    public static function tick($string){
        return '`' . implode('`.`', explode('.', $string)) . '`';
    }

    /**
     * Binds parameters to their proper placeholders
     * @param string $query The query string
     * @param array $params The parameters to bind
     * @return void
     */
    protected function _bind($query, array $params){
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

}