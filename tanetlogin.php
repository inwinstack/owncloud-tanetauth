<?php
if(!empty($_POST["account"]) || !empty($_POST["password"])) {
    require_once "config/config.php";
    $redirectHost = $_SERVER['SERVER_NAME'];
    $userid = $_POST["account"];
    $password = $_POST["password"];
    $ip = $_SERVER["REMOTE_ADDR"];

    $functionExists = function_exists("radius_auth_open");
    if (!$functionExists){
        $msg = '主機尚未安裝radius套件';
    }
    else{
        $res = radius_auth_open();

        if (!array_key_exists('radius_server', $CONFIG) ||
                !array_key_exists('radius_port', $CONFIG) ||
                !array_key_exists('radius_shared_secret', $CONFIG)||
                !array_key_exists('hash_key', $CONFIG)){
                    $msg = "儲存雲尚未設置TANet主機相關參數";
        }else{
            $radserver = $CONFIG['radius_server'];
            $radport = $CONFIG['radius_port'];
            $sharedsecret = $CONFIG['radius_shared_secret'];

            radius_add_server($res, $radserver, $radport, $sharedsecret, 5, 2);
            radius_create_request($res, RADIUS_ACCESS_REQUEST);
            radius_put_string($res, RADIUS_USER_NAME, $userid);
            radius_put_string($res, RADIUS_USER_PASSWORD, $password);

            $req = radius_send_request($res);
            switch ($req) {
                case RADIUS_ACCESS_ACCEPT:
                    
                    $ch = curl_init();
                    $url = "https://". $redirectHost ."/ocs/v1.php/apps/tanet_auth/checkNeedRedirect?format=json&userid=".$userid;
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                    $result = curl_exec($ch);
                    $result = json_decode($result,true);
                    curl_close($ch);
                    
                    if ($result['ocs']['meta']['statuscode'] == 100){
                        if ($result['ocs']['data']['result'] == 'ture'){
                            $redirectHost= $result['ocs']['data']['host'];
                        }
                    }
                    else{
                        $msg = "儲存雲驗證失敗";
                        break;
                    }
                    
                    $accountInfoArray = json_encode(['userid' => $userid,
                                          'time' => time(),
                                          'password' => $password,
                                          'ip' => $ip
                            
                    ]);

                    $hash = hash('SHA384', $CONFIG['hash_key'], true);
                    $app_cc_aes_key = substr($hash, 0, 32);
                    $app_cc_aes_iv = substr($hash, 32, 16);
                    
                    $accountInfoArray = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $app_cc_aes_key, $accountInfoArray, MCRYPT_MODE_CBC, $app_cc_aes_iv);
                    $encrypt_account = base64_encode($accountInfoArray);
                    $params["tanet"] = true;
                    $params["encrypt"] = $encrypt_account;
                    $params["check"] = md5($encrypt_account . $CONFIG['crc_key']);
                    $queryStr = "?" . http_build_query($params);

                    $redirectUrl = 'https://' . $redirectHost . '/index.php' . $queryStr;
                    header('location:' . $redirectUrl);
                    exit();
                case RADIUS_ACCESS_REJECT:
                    $msg = "帳號不存在或密碼錯誤";
                    break;
                default:
                    $msg = "TANet 主機回傳錯誤 : ". radius_strerror($res);
                    break;
            }

        }
    }
}
?>
<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>雲端儲存服務</title>
    <link rel="icon" href="apps/tanet_auth/img/edu_logo.png">
    <link rel="stylesheet" href="apps/tanet_auth/css/login.css">
    
  </head>

  <body>
    <div><img class="resize"></img></div>
    <div class="login-card">
      <div class="logo"></img></div>
      <form method="post" name="tanetlogin">
        <input type="text" name="account" placeholder="TANet Roaming 帳號" required>
        <input type="password" name="password" placeholder="TANet Roaming 密碼" required>
        <input type="submit" name="login" class="login login-submit" value="登入">
      </form>
      <div class="push">
        <?php if(isset($msg)) echo "<p style='color:red;'>$msg</p>"; ?>
      </div>
    </div>
    <div class="footer">
        <div class="footer-img"></div>
        <div class="footer-text">
            請使用教育體系 TANet Roaming帳號進行登入<br>
            Copyright © Ministry of Education. All rigths reserved.
        </div>
    </div>
  </body>
</html>
