<?php

use \Mapos\Service\Service;

function lg($type, $message)
{

    $logger = new \Analog\Logger;
    $log = MAPOS_BASE_PATH . 'logs/' . $type . '.log';

    if (strtolower($type == 'emergency')) {
        $logger->handler(Analog\Handler\Mail::init(
                EMERGENCY_EMAIL, // to
                'SECURITY ALERT! - MapOS IDS SECURITY BLOCK', // subject
                NOREPLY_EMAIL_FROM // from
        ));

        $logger->alert($message);
    }

    $logger->handler(\Analog::handler(function ($info) {
            static $conn = null;
            if (!$conn) {
                $conn = new \MongoClient();
            }
            $conn->{DB_NAME}->security_log->insert($info);
        }));

    $logger->alert($message);
}

//This will be depracated use @ or @$x?$x:'' type
function v(&$var = null, $default = null)
{
    return isset($var) ? $var : $default;
}

if (!function_exists('redirect')) {

    function redirect($uri = '', $method = 'location', $http_response_code = 302)
    {
        if (!preg_match('#^https?://#i', $uri)) {
            $uri = site_url($uri);
        }

        switch ($method) {
            case 'refresh': header("Refresh:0;url=" . $uri);
                break;
            default: header("Location: " . $uri, true, $http_response_code);
                break;
        }
        exit;
    }

}

function site_url($url)
{
    return BASE_URL . $url;
}

function su($url)
{
    redirect($url);
}

function gdb($field, $default = null)
{ // gi = get instance
    $s = Service::getInstance();
    return isset($s->db_elements[$field]) ? $s->db_elements[$field] : $default;
}

function sdb($field, $default = null)
{ // gi = get instance
    $s = Service::getInstance();
    return $s->db_elements[$field] = $default;
}

function gi()
{ // gi = get instance
    return Service::getInstance();
}

function gp($name, $default = null) //gp = get post
{
    if (!$name) {
        return null; // must be here overwrise
    }

    //Array to string will be shown!!!!
    $post = filter_input(INPUT_POST, $name);
    return isset($post) ? $post : $default;
}

function gu($name, $default = null)
{
    if (!$name) {
        return null; // must be here overwrise
    }


    $service = Service::getInstance();
    $segments = $service->segments;
    //First from segments
    //Secondly from querystring!
    for ($x = 1; $x < count($segments); $x = $x + 2) {
        if (v($segments[$x]) == $name) {
            return isset($segments[$x + 1]) ? $segments[$x + 1] : '';
        }
    }
    $get = filter_input(INPUT_GET, $name);
    return $get ? $get : $default;
}

function gseg($number, $default = false)
{ //gp = get post
    if (!$number) {
        return false; // must be here overwrise
    }

    $service = Service::getInstance();
    return v($service->segments[$number - 1], $default);
}

function u()
{

    if (!$a = filter_input(INPUT_SERVER, "REQUEST_URI")) {
        $a = $_SERVER["QUERY_STRING"];
        $a = explode('url=', $a)[1];
        return $a;
        //this is totally unsafe for now!!
    }
    return filter_input(INPUT_SERVER, "REQUEST_URI");
}

function gpu($name, $default = false)
{
    //gpu = get post or url param
    $post = gp($name);
    if (!$post) {
        return gu($name, $default);
    }

    return $post;
}

function gs($name, $default = null)
{ // gs = get session
    return isset($_SESSION[$name]) ? $_SESSION[$name] : $default;
}

function ss($name, $value)
{ // ss = set session
    $_SESSION[$name] = $value;
}

function ip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP')) {
        $ipaddress = getenv('HTTP_CLIENT_IP');
    } elseif (getenv('HTTP_X_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    } elseif (getenv('HTTP_X_FORWARDED')) {
        $ipaddress = getenv('HTTP_X_FORWARDED');
    } elseif (getenv('HTTP_FORWARDED_FOR')) {
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    } elseif (getenv('HTTP_FORWARDED')) {
        $ipaddress = getenv('HTTP_FORWARDED');
    } elseif (getenv('REMOTE_ADDR')) {
        $ipaddress = getenv('REMOTE_ADDR');
    } else {
        $ipaddress = 'UNKNOWN';
    }
    return $ipaddress;
}

function sm($message)
{
    $_SESSION['msg201404270420mapos'] = $message;
}

function gm()
{
    $message = isset($_SESSION['msg201404270420mapos']) ? $_SESSION['msg201404270420mapos'] : '';
    unset($_SESSION['msg201404270420mapos']);
    return $message;
}

function sid()
{
    return session_id();
}

//function gm() {
//    
//}

function o($name, $params = array(), $return = true)
{
    //loads object
    $t = gi();
    return $t->o($name, $params, $return);
}

function now()
{
    return new MongoDate(time());
}

function action($name, $id = null, $page = null)
{
    $service = Service::getInstance();
    if ($page === null) {
        $page = $service->page;
    }
    $page.='/';
    if ($name == 'edit') {
        //We make edit link
        return $page . 'id/' . $id;
    }
    return $page . 'action.' . $name . (($id . '--' != '--') ? ('/' . rand(1000000, 4000000) . '/id/' . $id) : null );
}
