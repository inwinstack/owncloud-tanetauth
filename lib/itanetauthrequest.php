<?php

namespace OCA\Tanet_Auth;

interface ITanetAuthRequest {
    const INFO = "info";
    const USERPASSWORDGENERATOR = "userpasswordgenerator";

    public function name();
    public function send($data);
    public function getErrorMsg();
}

