<?php
namespace ctodobom\APInterPHP;

class BancoInterException extends \Exception
{

    public $http_code;
    public $http_headers;
    public $http_body;
    
    public function __construct($message, $http_code, $http_headers, $http_body)
    {
        $this->http_code = $http_code;
        $this->http_headers = $http_headers;
        $this->http_body = $http_body;
        
        parent::__construct($message, null, null);
    }
}
