<?php
// 取得資料夾分享連結

$header_token = "SSO-Token: 95b88e71a7897dfc0b778158288078d3";
$header_origin = "Origin: http://myservice.com.tw";
$headers = array($header_token, $header_origin);
function callCorsApi($redirectDomain){
    global $headers;
    $host = $redirectDomain;
    $api = "/apps/tanet_auth/api/share";
    $url = $host . "/cors.php" . $api;
    $post_data = array();
    $post_data["files"] = array(["id" => "472441", "name" => "Test.txt", "type" => "file"]);
    $post_data["password"] = "1234";
    $post_data["expiration"] = "2017-04-31";
    $c = curl_init();
    curl_setopt($c, CURLOPT_URL, $url);
    curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($c, CURLOPT_POST, 1);
    curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($post_data));
    curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
    curl_exec($c);
    $http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
    print_r($http_code);
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

