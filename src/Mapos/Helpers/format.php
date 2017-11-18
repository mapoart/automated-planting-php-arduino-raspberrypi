<?php

function sortStrLen($a, $b)
{
    return strlen($b) - strlen($a);
}

function highlight_this($text, $words, $start = "<span class=\"highlight\">", $end = "</span>")
{
    $words = trim($words);
    $the_count = 0;
    $wordsArray = explode(' ', $words);

    usort($wordsArray, 'sortStrLen');

    foreach ($wordsArray as $word) {
        if (strlen(trim($word)) != 0) {
            $exclude_list = array("do", "na", "word3");
        }
        // Check if it's excluded
        if (strlen($word) == 1 || in_array(strtolower($word), $exclude_list)) {
            
        } else {
            $text = str_ireplace($word, $start . strtoupper($word) . $end, $text, $count);
            $the_count = $count + $the_count;
        }
    }

    return $text;
}

function formatVAT($vat)
{
    return $vat . '%';
}

function formatEmail($email)
{
    return '<a href="mailto:' . $email . '">' . $email . '</a>';
}

function formatDate($seconds)
{
    return date('d-m-Y H:i:s', $seconds);
}

function formatCurrency($val, $currency = "PLN")
{
    $service = gi();
    if (isset($service->currencyCache[$val])) {
        return $service->currencyCache[$val];
    }

    $fmt = $service->currencyFormatter;
    $r = $fmt->formatCurrency($val, $currency);

    return $service->currencyCache[$val] = $r;
}

function formatBytes($b, $p = null)
{
    if ($b < 1024) {
        return $b . ' B';
    }
    $units = array("B", "kB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB");
    $c = 0;
    if (!$p && $p !== 0) {
        foreach ($units as $k => $u) {
            if (($b / pow(1024, $k)) >= 1) {
                $r["bytes"] = $b / pow(1024, $k);
                $r["units"] = $u;
                $c++;
            }
        }
        return number_format($r["bytes"], 2) . " " . $r["units"];
    } else {
        return number_format($b / pow(1024, $p)) . " " . $units[$p];
    }
}

//Later to make it more logic, for ad-loc
function displayUserName(&$user)
{
    echo trim(v($user['name']));
}

function solrClear($keyword)
{
    return str_replace(':', '', $keyword);
}

function obj2xml($v, $indent = '')
{
    while (list($key, $val) = each($v)) {
        if ($key == '__attr')
            continue;
        // Check for __attr 
        if (is_object($val->__attr)) {
            while (list($key2, $val2) = each($val->__attr)) {
                $attr .= " $key2=\"$val2\"";
            }
        } else {
            $attr = '';
        }
        if (is_array($val) || is_object($val)) {
            print("$indent<$key$attr>\n");
            obj2xml($val, $indent . '  ');
            print("$indent</$key>\n");
        } else {
            print("$indent<$key$attr>$val</$key>\n");
        }
    }
}
