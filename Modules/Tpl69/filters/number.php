<?php

function number($value, $decimals = 0){
    number_format($value, $decimals);
}

function currency($value, $symbol = '$', $decimals = 2){
    return $symbol . number_format($value, $decimals);
}
