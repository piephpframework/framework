<?php

namespace Database;

use PDO;
use App\Object;

class Db extends Object {

    protected $stmt, $pdo;

    public function connect(){
        $this->pdo = new PDO('mysql:dbname=piephp;host=127.0.0.1', 'root', 'afrid123');
    }

    public function query($query, array $params = []){
        if(!isset($this->pdo)){
            $this->connect();
        }
        $this->stmt = $this->pdo->prepare($query);
        $this->_bind($query, $params);
        $this->stmt->execute();
        return $this;
    }

    public function get(){
        return $this->stmt->fetchAll(PDO::FETCH_CLASS);
    }

    public static function tick($string){
        return '`' . implode('`.`', explode('.', $string)) . '`';
    }

    protected function _bind($query, $params){
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