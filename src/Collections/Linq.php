<?php

namespace Collections;

use Exception;
use App\Object;
use Database\Model;
use Collections\Parsers\ArrayListParser;
use Collections\Parsers\MysqlParser;
use Database\ResultSet;

class Linq extends Collection {

    protected $parser = null;

    private function __construct(Collection $items){
        if($items instanceof ArrayList){
            $this->parser = new ArrayListParser($items);
        }elseif($items instanceof Model){
            $this->parser = new MysqlParser($items);
        }
    }

    public static function from($items){
        return new Linq($items);
    }

    public function where($val1, $val2, $val3 = ''){
        $this->parser->addWhere([
            'key1' => $val1,
            'comp' => func_num_args() == 2 ? '=' : $val2,
            'key2' => func_num_args() == 2 ? $val2 : $val3
        ]);
        return $this;
    }

    public function in($value, array $tests){
        $this->parser->addIn([
            'key'  => $value,
            'vals' => $tests
        ]);
        return $this;
    }

    public function union($arrayListSet){
        $this->parser->addUnion($arrayListSet);
        return $this;
    }

    public function order($attribute, $direction = 'asc'){
        $this->parser->addOrder(['orderBy' => $attribute, 'direction' => $direction]);
        return $this;
    }

    public function limit($limit, $offset = 0){
        $this->parser->setLimit((int)$limit);
        $this->parser->setOffset((int)$offset);
        return $this;
    }

    public function select(){
        $this->parser->setSelect(func_get_args());
        $this->parser->process();
        return $this->parser->getList();
    }

}