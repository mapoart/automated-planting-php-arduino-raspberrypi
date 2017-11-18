<?php

function fu($url)
{ //friendly url
    return url_title(trim(strtr($url, '()[]`!@#$%^&*_+={}:;",.<>/?', '***************************')));
}

function url($url = false)
{ //returns url
}

function url_cleanup($url)
{
    // remove double slashes which sometimes appearing on the string concatenation
    return reduce_double_slashes($url);
}

function params_cleanup($params_string, $space_after = false)
{
    //Cleanup the parameteres eg. ,A,B,,C will return A,B,C
    //etc... Second parameter is for ,space things.
    //So if is set, after comma there will be space
    $params_string = reduce_multiples($params_string);
    if (!$space_after):
        return str_replace(', ', ',', $params_string);
    endif;
    return $params_string;
}

/**
 * Create URL Title
 *
 * Takes a "title" string as input and creates a
 * human-friendly URL string with a "separator" string 
 * as the word separator.
 *
 * @access	public
 * @param	string	the string
 * @param	string	the separator
 * @return	string
 */
if (!function_exists('url_title')) {

    function url_title($str, $separator = '-', $lowercase = FALSE)
    {
        if ($separator == 'dash') {
            $separator = '-';
        } else if ($separator == 'underscore') {
            $separator = '_';
        }

        $q_separator = preg_quote($separator);

        $trans = array(
            '&.+?;' => '',
            '[^a-z0-9 _-]' => '',
            '\s+' => $separator,
            '(' . $q_separator . ')+' => $separator
        );

        $str = strip_tags($str);

        foreach ($trans as $key => $val) {
            $str = preg_replace("#" . $key . "#i", $val, $str);
        }

        if ($lowercase === TRUE) {
            $str = strtolower($str);
        }

        return trim($str, $separator);
    }

}

