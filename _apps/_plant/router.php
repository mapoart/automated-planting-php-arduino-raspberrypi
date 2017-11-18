<?php

$service->routing = array('' => ''

);

$url = filter_input(INPUT_GET, "url");
$segments = explode('/', $url);

//We remove querystring from the page name, from last array element.
$pagename = end($segments);
$pagename = explode('?', $pagename);
$pagename = $pagename[0];
$segments[sizeof($segments) - 1] = $pagename;

use Mapos\Mapos;

$mapos = new Mapos();
$mapos->setBaseUrl('http://' . filter_input(INPUT_SERVER, "HTTP_HOST") . '/');
//
//var_dump($segments);

$service->segments = $segments;

//var_dump($service->segments);
//$mapos->setSegments($segments);

$mapos->setPageSuffix('.html');
$mapos->setDefaultController('frontend');
$mapos->setRequestMethod($service->getRequestMethod());
$mapos->run();
