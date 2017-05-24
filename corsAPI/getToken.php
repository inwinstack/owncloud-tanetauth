<?php
$userid = 'Your email';
$password = 'Your password';

$url = "http://sso.cloud.edu.tw/ORG/service/EduCloud/auth/tokens";
$data = ["UserId" => $userid, "Password" => $password, "UserIP" => 'Your Host IP'];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$head = curl_exec($ch);
$head = json_decode($head);
if ($head->actXML->statusCode == 200) {
    $token = $head->actXML->rsInfo->tokenId; 
    print_r($token);
}
else{
    print_r($head);
}    
