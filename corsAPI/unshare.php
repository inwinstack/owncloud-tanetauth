<?php
$host = "https://172.20.3.18/owncloud/cors.php";
$c = curl_init();
$api = "/apps/tanet_auth/api/v2/unshare"; 
$url = $host . $api;

$header_token = "SSO-Token: 87648e536513d2c506ce9e8815c43b5e";
$header_origin = "Origin: 172.20.3.18";

$headers = array($header_token, $header_origin);
$post_data = array();
$post_data["id"] = "13";
$post_data["type"] = "file";
curl_setopt($c, CURLOPT_URL, $url);
curl_setopt($c, CURLOPT_HTTPHEADER, $headers);
curl_setopt($c, CURLOPT_RETURNTRANSFER, true);
curl_setopt($c, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($c, CURLOPT_SSL_VERIFYPEER, 0);
//curl_setopt($c, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($c, CURLOPT_POST, 1);
curl_setopt($c, CURLOPT_POSTFIELDS, http_build_query($post_data));

$result = curl_exec($c);
//$redirectURL = curl_getinfo($c,CURLINFO_EFFECTIVE_URL );
//print_r(curl_error($c));
//print_r($redirectURL);
//$http_code = curl_getinfo($c, CURLINFO_HTTP_CODE);
//print_r($http_code);
print_r($result);
curl_close($c);
