<?php
$host = "https://172.20.3.18/owncloud/cors.php";
$c = curl_init();
$api = "/apps/tanet_auth/api/v2/filelist"; 
//$api = '';
$param_dir = "dir=/";
$url = $host . $api . "?" . $param_dir;
$header_token = "SSO-Token: e6c55c3d5afb45b2e82216f10f35b600";
$header_origin = "Origin: 172.20.3.18";
//$header_origin = "";
$headers = array($header_token, $header_origin);

curl_setopt($c, CURLOPT_URL, $url);
curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);


$result = curl_exec($c);
//$redirectURL = curl_getinfo($c,CURLINFO_EFFECTIVE_URL );
print_r(curl_error($c));
//print_r($redirectURL);
$http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
print_r($http_code);
print_r($result);
curl_close($c);
