<?php

namespace Pie\Crust\Util;

use Pie\Pie;
use Pie\Crust\Service;
use Pie\Crust\Net\Request;

class Validation extends Service{

    protected $validations;

    public function test(array $validations, &$errors = []){
        $this->validations = $validations;
        return $this->_test($errors);
    }

    protected function _test(&$errors){
        foreach($this->validations as $key => $val){
            $map = array_map('trim', explode('|', $val));
            $search = Pie::find($key);
            foreach($map as $req){
                $req = strtolower($req);
                // Value is required
                if($req == 'required' && $search == ''){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is missing'];
                }

                // Value min and max and len
                if(preg_match('/^(min|max|len):\d+/i', $req, $matches)){
                    $length = (int)(explode(':', $req)[1]);
                    $action = strtolower($matches[1]);
                    if($action == 'min' && strlen($search) < $length){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item length is too short'];
                    }elseif($action == 'max' && strlen($search) > $length){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item length is too long'];
                    }elseif($action == 'len' && strlen($search) != $length){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item does not equal the length'];
                    }
                }

                if(preg_match('/^between:(\d+),(\d+)/i', $req, $matches)){
                    $min = $matches[1];
                    $max = $matches[2];
                    $this->minMax($req, $key, $search, $min, $max, $errors);
                }

                // Search within a list of items
                if(preg_match('/^(notin|in):(.+)/i', $req, $matches)){
                    $action = strtolower($matches[1]);
                    $items = array_map('trim', explode(',', $matches[2]));
                    if($action == 'in' && !in_array($search, $items)){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is not in the list'];
                    }elseif($action == 'notin' && in_array($search, $items)){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is in the list'];
                    }
                }

                // Match an item against a regular expression
                if(preg_match('/match:(.+)/i', $req, $matches)){
                    if(!preg_match($matches[1], $search)){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item does not match'];
                    }
                }

                // Validate an ip address
                if($req == 'ip' && !filter_var($search, FILTER_VALIDATE_IP)){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is not an ip address'];
                }

                // Validate an email address
                if($req == 'email' && !filter_var($search, FILTER_VALIDATE_EMAIL)){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is not an email address'];
                }

                // Validate a url
                if($req == 'url' && !filter_var($search, FILTER_VALIDATE_URL)){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is not a url'];
                }

                // Validate a type of data
                if(preg_match('/typeof:(.+)/i', $req, $matches)){
                    $types = array_map('trim', explode(',', $matches[1]));
                    if(!in_array(gettype($search), $types)){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is not of type'];
                    }
                }

                if($req == 'json'){
                    json_decode($search);
                    if(json_last_error() != JSON_ERROR_NONE){
                        $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is not valid json'];
                    }
                }

                // Request key and value must be different
                if($req == 'different' && preg_replace('/^\$.+?\./', '', $key) == $search){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item key must be different from its value'];
                }

                // Request key and value must be the same
                if($req == 'same' && preg_replace('/^\$.+?\./', '', $key) != $search){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item key must be different from its value'];
                }

                // Require a numeric value
                if($req == 'numeric' && !is_numeric($search)){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item must be numeric'];
                }

                // Require a value of numbers only 0 or larger
                if($req == 'integer' && !ctype_digit($search)){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item must be an integer'];
                }

                // Require an alpha value
                if($req == 'alpha' && !ctype_alpha($search)){
                    $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item must be alpha'];
                }
            }
        }
        if(count($errors) > 0){
            return false;
        }
        return true;
    }

    private function minMax($req, $key, $search, $min, $max, &$errors){
        if(is_numeric($search)){
            if($search < $min || $search > $max){
                $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item is not between min and max'];
                return false;
            }
        }elseif(is_string($search)){
            if(strlen($search) < $min || strlen($search) > $max){
                $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item length is not between min and max'];
                return false;
            }
        }elseif(is_array($search)){
            $len = count($search);
            if($len < $min || $len > $max){
                $errors[] = ['key' => $req, 'value' => $search, 'item' => $key, 'msg' => 'Item count is not between min and max'];
                return false;
            }
        }
    }

}