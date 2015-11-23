<?php

namespace Pie\Crust\Util;

class Math extends Service{

    const pi = 3.1415926535898;

    /**
     * Clamps a number between two values
     * @param number $number The number to test
     * @param number $min The minimum value
     * @param number $max The maximum value
     * @return number The clamped value
     */
    public static function clamp($number, $min, $max){
        return $number < $max ? ($number > $min ? $number : $min) : $max;
    }

    /**
     * Clamps a number between 0 and 1
     * @param number $number The number to test
     * @return number The clamped value
     */
    public static function clamp01($number){
        return $this->clamp($number, 0, 1);
    }

    /**
     * Get the percentage of a number
     * @param number $number The number to test
     * @param number $max The number that evaluates to 100%
     * @return number A number between 0 and 100
     */
    public static function percent($number, $max = 100, $decimals = 0){
        $percent = round(($number / $max) * 100, $decimals);
    }

    /**
     * Return the smaller of two values
     * @param number $number1 The first number
     * @param number $number2 The second number
     * @return number The smaller number
     */
    public static function min($number1, $number2){
        return min([$number1, $number2]);
    }

    /**
     * Return the larger of two values
     * @param number $number1 The first number
     * @param number $number2 The second number
     * @return number The larger number
     */
    public static function max($number1, $number2){
        return max([$number1, $number2]);
    }

}