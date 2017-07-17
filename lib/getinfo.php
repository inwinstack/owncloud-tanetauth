<?php
namespace OCA\Tanet_Auth;

class GetInfo implements IUserInfoRequest {

    private $setupParams = array();
    private $userId;
    private $email;
    private $displayName;
    private $errorMsg;
    private $sid;
    private $title = array();

    public function __construct(){

    }

    public function name() {
        return ITanetAuthRequest::INFO;
    }

    /**
     * setup userinfo
     *
     * @param array $param
     * @return void
     */
    public function setup($params)
    {
        foreach ($params as $key => $value) {
            $this->setupParams[$key] = $value;
        }
    }

    public function send($data = null) {
        
        $this->userId = $data["userid"];
        $this->token = $data["password"];
        $this->email = $data["userid"];
        $this->displayName = $data["userid"];
        $this->userSid = $data["userid"];
        
        if ($this->setupParams["action"] == "webDavLogin") {
            
            $res = radius_auth_open();
            $radserver = $data["radius_server"];
            $radport = $data["radius_port"];
            $sharedsecret = $data["radius_shared_secret"];
            
            radius_add_server($res, $radserver, $radport, $sharedsecret, 3, 3);
            radius_create_request($res, RADIUS_ACCESS_REQUEST);
            radius_put_string($res, RADIUS_USER_NAME, $this->userId);
            radius_put_string($res, RADIUS_USER_PASSWORD, $this->token);
            
            $req = radius_send_request($res);
            switch ($req) {
                case RADIUS_ACCESS_ACCEPT:
                    $this->region = $this->filterRegion($this->userId);
                    return true;
                default:
                    return false;
            }
        }

        $this->region = $this->filterRegion($this->userId);

        return true;

    }

    public function filterRegion($userId){
        $matchRegion = null;
        
        foreach (RoamingMap::$map as $roaming => $region) {
        
            if (preg_match($roaming, $userId)) {
                $matchRegion    =   $region;
            }
        }
        
        return $matchRegion;
    }
     
    public function getErrorMsg() {
        return $this->errorMsg;
    }

    public function getUserId() {
        return $this->userId;
    }

    public function getEmail() {
        return $this->email;
    }

    public function getDisplayName() {
        return $this->displayName;
    }
    
    
    /**
     * Get user auth token
     *
     * @return string $token
     */
    public function getToken()
    {
        return $this->token;
    }
    
    /**
     * Getter for user region
     *
     * @return string user region
     */
    public function getRegion() {
        return $this->region;
    }

    /**
     * Check user have permassion to use the service or not
     *
     * @return bool
     */
    public function hasPermission(){
        return true;
    }

    /**
     * Check has error massage or not
     *
     * @return true|false
     */
    public function hasErrorMsg()
    {
        return $this->errorMsg ? true : false;
    }

    /**
     * Get user role in this system
     *
     * @return string
     */
    public function getRole()
    {
        return 'TANet';
    }

}
