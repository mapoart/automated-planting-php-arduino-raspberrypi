<?php

function doCurlRequest($url, $returnTransfer = true, $post = false, $postdata = null, $header = false, $verbose = false, $followLocation = false, $ssl_verifypeer = true)
{
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, $header);
    curl_setopt($ch, CURLOPT_VERBOSE, $verbose);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, $returnTransfer);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $followLocation);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, $ssl_verifypeer);
    curl_setopt($ch, CURLOPT_POST, $post);
    if (is_array($postdata))
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function curlRequest($url, $timout = 5)
{
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1:3128");
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}
