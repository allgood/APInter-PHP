<?php

namespace ctodobom\APInterPHP;

class BancoInterException extends \Exception
{
    public $http_code;
    public $reply;
    public $url;

    public function __construct($message, $http_code, $reply, $url = null)
    {
        $this->http_code = $http_code;
        $this->reply = $reply;
        $this->url = $url;

        parent::__construct($message, 0, null);
    }
}
