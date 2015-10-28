<?php

namespace Object69\Modules\Database69;

use Exception;
use Object69\Modules\Database69\Db;
use Object69\Modules\Database69\ResultSet;

/**
 * @property Db $db Database Connection
 */
class Model{

    protected $table  = null;
    protected $db;
    protected $fields = [];

    public function __construct(Db $db, $table = null){
        $this->table = $table;
        $this->db    = $db;
    }

    public function __set($name, $value){
        $this->fields[$name] = $value;
    }

    public function __get($name){
        return $this->fields[$name];
    }

    /**
     * Gets items from the database
     * @param array $settings Extra settings for the query
     * @return ResultSet
     */
    public function get(array $settings = []){
        $values = $this->getValues();
        $table  = $this->getTable();
        $where  = $this->getWhere($settings);
        $order  = $this->getOrder($settings);
        $limit  = $this->getLimit($settings);

        $query = "select * from $table $where $order $limit";

        $this->reset();
        if(isset($settings['limit']) && $settings['limit'] == 1){
            return $this->db->getRow($query, $values);
        }
        return $this->db->getAll($query, $values);
    }

    /**
     *
     * @param string $string
     * @param array $properties
     * @return \Object69\Modules\Database69\Raw
     */
    public function sub($string, array $properties = []){
        return new Sub($string, $properties);
    }

    /**
     * Gets a value using a key/value pair
     * @param mixed $keys
     * @param string $keyCol
     * @param string $valCol
     * @return type
     */
    public function value($keys, $keyCol = 'key', $valCol = 'value'){
        $table = $this->getTable();
        if($this->db->validName($keyCol) && $this->db->validName($valCol)){
            $keys         = !is_array($keys) ? [$keys] : $keys;
            $placeholders = $this->getPlaceholders($keys);

            $query = "select `$keyCol`, `$valCol` from $table where `$keyCol` in($placeholders)";

            $items = $this->db->getAll($query, $keys);
            $final = [];
            foreach($items as $value){
                $final[$value[$keyCol]] = $value[$valCol];
            }
            return $final;
        }
    }

    /**
     * Inserts items into the database
     * @return int
     */
    public function save(array $settings = null){
        if(empty($this->fields)){
            return false;
        }
        $columns     = $this->getColumns();
        $values      = $this->getValues();
        $placeholers = $this->getPlaceholders();
        $table       = $this->getTable();
        $duplicate   = $this->duplicateKey($settings);

        $query = "insert into $table ($columns) values ($placeholers) $duplicate";

        $this->db->query($query, $values);
        $this->reset();
        return $this->db->rowCount();
    }

    public function find($search, $settings = []){
        $settings = array_merge_recursive($settings, ['where' => ['where' => false]]);
        $values   = $this->getValues($settings);
        $table    = $this->getTable();
        $columns  = $this->getColumns(null, $settings);
        $where    = $this->getWhere($settings);
        $where    = !empty($where) ? $where . ' and ' : $where;

        $query = "select *,
        (match ($columns) against (? in natural language mode with query expansion)) as score
        from $table
        where $where match ($columns) against (? in natural language mode with query expansion)
        order by score desc";

        $p = [];
        $p[] = $search;
        foreach($values as $val){
            $p[] = $val;
        }
        $p[] = $search;
        
        return $this->db->getAll($query, $p);
    }

    /**
     * Edits items in the database
     */
    public function edit(){

    }

    /**
     * Removes items from the database
     */
    public function remove(){

    }

    public function reset(){
        $this->fields = [];
    }

    protected function getPlaceholders($vals = null){
        $items  = [];
        $fileds = $vals !== null ? $vals : $this->fields;
        foreach(array_values($fileds) as $field){
            if($field instanceof Sub){
                $items[] = $field->getString();
            }else{
                $items[] = '?';
            }
        }

        return implode(',', $items);

//        $vals = $vals === null ? array_values($this->fields) : array_values($vals);
//        return implode(",", array_pad([], count($vals), "?"));
    }

    protected function getColumns($keys = null, $settings = []){
        $keys  = $keys === null ? array_keys($this->fields) : $keys;
        $fkeys = [];
        foreach($keys as $key){
            if(isset($settings['columns']['ignoredColumns']) && in_array($key, $settings['columns']['ignoredColumns'])){
                continue;
            }
            $fkeys[] = $key;
        }
        return implode(",", $fkeys);
    }

    protected function getValues(array $settings = []){
        $vals = array_values($this->fields);
        $keys = array_keys($this->fields);
        $arr  = [];
        foreach($vals as $index => $val){
            if(isset($settings['where']['ignoredColumns']) && in_array($keys[$index], $settings['where']['ignoredColumns'])){
                continue;
            }
            if($val instanceof Sub){
                $arr = array_merge($arr, $val->getProperties());
            }elseif(is_array($val)){
                $arr = array_merge($arr, $val);
            }else{
                $arr[] = $val;
            }
        }
        return array_map(function($item){
            return ltrim($item, '!><');
        }, $arr);
    }

    protected function getTable(){
        if($this->db->validName($this->table)){
            return $this->table;
        }
        throw new Exception("Invalid table name '$this->table'");
    }

    protected function getWhere(array $settings = []){
        $keys = array_keys($this->fields);
        $vals = array_values($this->fields);

        $items = [];
        foreach($keys as $index => $value){
            if($value instanceof Sub){
                echo 'here';
            }
            if(isset($settings['where']['ignoredColumns']) && in_array($value, $settings['where']['ignoredColumns'])){
                continue;
            }
            if($vals[$index][0] == '!'){
                $items[] = "$value != ?";
            }elseif($vals[$index][0] == '>'){
                $items[] = "$value > ?";
            }elseif($vals[$index][0] == '<'){
                $items[] = "$value < ?";
            }elseif(is_array($vals[$index])){
                $items[] = "$value in(" . implode(',', array_pad([], count($vals[$index]), '?')) . ")";
            }elseif($vals[$index] === null){
                $items[] = "$value is null";
            }else{
                $items[] = "$value = ?";
            }

            if(isset($settings['comp']) && count($settings['comp']) > 0){
                if(isset($settings['comp'][$index])){
                    $items[] = $settings['comp'][$index];
                }
            }
        }

        $str = '';
        if(!isset($settings['where']['where']) || $settings['where']['where'] === true){
            $str = count($items) > 0 ? ' where ' : '';
        }
        if(isset($settings['comp']) && count($settings['comp']) > 0){
            return $str . implode(' ', $items);
        }
        return $str . implode(' and ', $items);
    }

    protected function getLimit(array $settings){
        if(isset($settings['limit'])){
            return 'limit ' . (int)$settings['limit'];
        }
        return '';
    }

    protected function getOrder(array $settings){
        if(isset($settings['order'])){
            $dirs = [];
            foreach($settings['order'] as $column => $dirc){
                $col    = is_int($column) ? $dirc : $column;
                $dir    = is_int($column) ? 'asc' : $dirc;
                $dirs[] = '`' . $col . '` ' . $dir;
            }
            return 'order by ' . implode(', ', $dirs);
        }
        return '';
    }

    protected function duplicateKey(array $settings){
        if($settings === null || !isset($settings['onDuplicate']) || empty($settings['onDuplicate'])){
            return '';
        }

        $str   = 'on duplicate key update';
        $items = [];

        foreach($settings['onDuplicate'] as $dup){
            if(!$this->db->validName($dup)){
                throw new Exception('Invalid column name "' . $dup . '"');
            }
            $items[] = $dup . ' = values(' . $dup . ')';
        }
        return $str . ' ' . implode(',', $items);
    }

}
