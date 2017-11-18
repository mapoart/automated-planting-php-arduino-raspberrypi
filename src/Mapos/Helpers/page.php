<?php

/**
 * function borrowed from CodeIgniter
 * Add's _1 to a string or increment the ending number to allow _2, _3, etc
 *
 * @param   string  $str  required
 * @param   string  $separator  What should the duplicate number be appended with
 * @param   string  $first  Which number should be used for the first dupe increment
 * @return  string
 */
function increment_string($str, $separator = '_', $first = 1)
{
    preg_match('/(.+)' . $separator . '([0-9]+)$/', $str, $match);

    return isset($match[2]) ? $match[1] . $separator . ($match[2] + 1) : $str . $separator . $first;
}

function next_page($page_name, $extension = '.html')
{
    //Making next page from page name eg. form2_2, for now is used CI function,
    //but next time can be used for other pages, which are not simply incrementation
    $page_name = str_replace('.html', '', $page_name);
    return increment_string($page_name) . $extension;
}

function prev_page($page_name, $extension = '.html')
{
    //Making previous page from page name eg. form2_2, for now is used CI function,
    //but next time can be used for other pages, which are not simply incrementation
    $page_base = page_base($page_name);

    $page_step = (page_step($page_name) * 1) - 1;
    return $page_base . '_' . $page_step . $extension;
}

function page_base($page_name)
{
    //Returns the base of the page. eg. if page name = Form1_1.html will return Form1,
    //if the form is BestPage_10 will return BestPage
    $page_name = explode('_', $page_name);
    return $page_name[0];
}

function page_step($page_name)
{
    //gets actual step, eg. form1_24.html, will return 24, form10_2.html will return 2
    $p = str_replace('.html', '', $page_name);
    $p = explode('_', $p);
    $r = end($p);
    return is_numeric($r) ? $r : 0;
}
