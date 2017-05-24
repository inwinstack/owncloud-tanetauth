<?php
namespace OCA\Tanet_Auth;

interface IUserInfoRequest extends ITanetAuthRequest {
    /**
     * setup userinfo
     * @param array params
     * @return void
     * @author Dauba
     **/
    public function setup($params);

    /**
     * Getter for UserId
     *
     * @return string
     * @author Dauba
     */
    public function getUserId();

    /**
     * Getter for Email
     *
     * @return string
     * @author Dauba
     */
    public function getEmail();

    

    /**
     * Getter for display name
     *
     * @return string
     * @author Dauba
     */
    public function getDisplayName();

    /**
     * Getter for region
     *
     * @return string
     * @author Dauba
     */
    public function getRegion();

    /**
     * Check user permission
     *
     * @return bool
     * @author Dauba
     */
    public function hasPermission();

    
    /**
     * Check has error message or not
     *
     * @return true|false
     * @author Dauba
     **/
    public function hasErrorMsg();
}
