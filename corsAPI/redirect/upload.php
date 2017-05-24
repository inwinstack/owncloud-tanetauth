<?php
// 上傳檔案

$header_token = "SSO-Token: 95b88e71a7897dfc0b778158288078d3";
$header_origin = "Origin: http://myservice.com.tw";
$headers = array($header_token, $header_origin);
function callCorsApi($redirectDomain){
    global $headers;
    $host = $redirectDomain;
    $api = "/apps/tanet_auth/api/upload";
    $url = $host . "/cors.php" . $api;

    $c = curl_init();
    $path = realpath("/tmp/corsAPI/list.php");
    $cfile = new CURLFile($path);

    $post_data = array(
        "dir" => "/",
        "files[0]" => $cfile,
    );

    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);

    curl_exec($c);
    curl_close($c);
}


function getRedirectDomain(){
    global $headers;
    $host = "https://storage.edu.tw/cors.php";
    $url = $host;
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($c, CURLOPT_HEADER, 1);
    $result = curl_exec($c);

    curl_close($c);
    preg_match_all('/^Location:(.*)$/mi', $result, $matches);
    if (!empty($matches[1])){
        return explode('?',trim($matches[1][0]))[0];
    }
    return false;
}   

$redirectDomain = getRedirectDomain();
if ($redirectDomain){
    callCorsApi($redirectDomain);
}
