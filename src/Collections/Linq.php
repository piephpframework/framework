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

    /**
     * Initiate a new Linq object. Can not be created publicallly
     * @param Collection $items A collection to search
     * @return Linq
     */
    private function __construct(Collection $items){
        if($items instanceof ArrayList){
            $this->processor = new ArrayListProcessor($items);
        }elseif($items instanceof Model){
            $this->processor = new MysqlProcessor($items);
        }
    }

    /**
     * Create a new Linq search from a collection
     * @param Collection $items A collection of items to search
     * @return Linq
     */
    public static function from(Collection $items){
        return new Linq($items);
    }

    /**
     * Filters the items based on operations
     * @param string $val1
     *      The first value to compare
     * @param mixed $val2
     *      The comparison type if $val3 is set.
     *      If $val3 is not set then equality is used.
     * @param mixed $val3
     *      The second value to compare.
     * @return Linq
     */
    public function where($val1, $val2, $val3 = ''){
        $this->processor->addWhere([
            'key1' => $val1,
            'comp' => func_num_args() == 2 ? '=' : $val2,
            'key2' => func_num_args() == 2 ? $val2 : $val3
        ]);
        return $this;
    }

    /**
     * Tests for values in an array
     * @param string $tets An array of items to test
     * @return Linq
     */
    public function in($value, array $tests){
        $this->processor->addIn([
            'key'  => $value,
            'vals' => $tests
        ]);
        return $this;
    }

    /**
     * Unites another collection to the current collection
     * @param Collection $collection The collection to unite
     * @return Linq
     */
    public function union(Collection $collection){
        $this->processor->addUnion($collection);
        return $this;
    }

    /**
     * Orders a collection in asending or desending order
     * @param string $attribute The key to sort
     * @param string $direction The direction to sort (asc or desc)
     * @return Linq
     */
    public function order($attribute, $direction = 'asc'){
        $this->processor->addOrder(['orderBy' => $attribute, 'direction' => $direction]);
        return $this;
    }

    /**
     * Limits the returned results in the collection search
     * @param int $limit The number of results to return
     * @param int $offset The starting offset to start
     * @return Linq
     */
    public function limit($limit, $offset = 0){
        $this->processor->setLimit((int)$limit);
        $this->processor->setOffset((int)$offset);
        return $this;
    }

    /**
     * Executes the search
     * @param string $... The attributes to select. If nothing is passed then all attributes are selected
     * @return ArrayList An ArrayList containing the results of the seach
     */
    public function select(){
        $this->processor->setSelect(func_get_args());
        $this->processor->process();
        return $this->processor->getList();
    }

}