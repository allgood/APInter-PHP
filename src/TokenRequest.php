<?php

namespace ctodobom\APInterPHP;

class TokenRequest implements \JsonSerializable
{
    private $client_id = "";
    private $client_secret = "";
    private $grant_type = "client_credentials";
    private $scope = "";


    public function __construct(string $client_id, string $client_secret, string $scope)
    {
        $this->client_id = $client_id;
        $this->client_secret = $client_secret;
        $this->scope = $scope;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
