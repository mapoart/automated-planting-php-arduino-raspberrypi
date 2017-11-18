<?php

function sMakeRandomString($length, $type = '')
{
    if ($type == 'cap')
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    elseif ($type == 'low')
        $chars = "abcdefghijklmnopqrstuvwxyz";
    elseif ($type == 'num')
        $chars = "0123456789";
    else
        $chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";$rand = '';
    for ($i = 1; $i <= $length; $i++) {
        $num = rand(0, strlen($chars));
        $rand .= substr($chars, $num, 1);
    } return $rand;
}

function explodeX($delimiters, $string)
{
    $return_array = Array($string); // The array to return
    $d_count = 0;
    while (isset($delimiters[$d_count])) { // Loop to loop through all delimiters
        $new_return_array = Array();
        foreach ($return_array as $el_to_split) { // Explode all returned elements by the next delimiter
            try {
                $put_in_new_return_array = mb_split($delimiters[$d_count], $el_to_split);
                foreach ($put_in_new_return_array as $substr) { // Put all the exploded elements in array to return
                    $new_return_array[] = $substr;
                }
            } catch (Exception $exc) {
                dqLog('exception', 'Error in function: application/helpers/io_helper.php::explodeX(): ' . $exc->getTraceAsString());
            }
        }
        $return_array = $new_return_array; // Replace the previous return array by the next version
        $d_count++;
    }

    return $return_array; // Return the exploded elements
}
