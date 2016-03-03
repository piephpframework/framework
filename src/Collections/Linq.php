<?php

namespace Collections;

use Exception;
use Application\Object;
use Database\Model;
use Collections\Processors\ArrayListProcessor;
use Collections\Processors\MysqlProcessor;
use Database\ResultSet;

class Linq extends Collection {

    protected $processor = null;

    private function __construct(Collection $items){
        if($items instanceof ArrayList){
            $this->processor = new ArrayListProcessor($items);
        }elseif($items instanceof Model){
            $this->processor = new MysqlProcessor($items);
        }
    }

    public static function from($items){
        return new Linq($items);
    }

    public function where($val1, $val2, $val3 = ''){
        $this->processor->addWhere([
            'key1' => $val1,
            'comp' => func_num_args() == 2 ? '=' : $val2,
            'key2' => func_num_args() == 2 ? $val2 : $val3
        ]);
        return $this;
    }

    public function in($value, array $tests){
        $this->processor->addIn([
            'key'  => $value,
            'vals' => $tests
        ]);
        return $this;
    }

    public function union($arrayListSet){
        $this->processor->addUnion($arrayListSet);
        return $this;
    }

    public function order($attribute, $direction = 'asc'){
        $this->processor->addOrder(['orderBy' => $attribute, 'direction' => $direction]);
        return $this;
    }

    public function limit($limit, $offset = 0){
        $this->processor->setLimit((int)$limit);
        $this->processor->setOffset((int)$offset);
        return $this;
    }

    public function select(){
        $this->processor->setSelect(func_get_args());
        $this->processor->process();
        return $this->processor->getList();
    }

}