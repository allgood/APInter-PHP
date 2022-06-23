<?php
namespace ctodobom\APInterPHP;

class BancoInterValueSizeException extends \Exception
{

    public function __construct($value, $size, $exact)
    {
        $message = sprintf("'%s' must have %s %d characters", $value, ($exact?"":"up to"), $size);
        
        parent::__construct($message, 0, null);
    }
}
