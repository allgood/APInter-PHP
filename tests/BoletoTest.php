<?php
namespace ctodobom\APInterPHP\Tests;

use PHPUnit\Framework\TestCase;
use ctodobom\APInterPHP\Cobranca\Boleto;
use ctodobom\APInterPHP\Cobranca\Pagador;

final class BoletoTest extends TestCase
{
    public function testBoleto() {
        $boleto = new Boleto();
        $this->assertInstanceOf(Boleto::class, $boleto);
        
        $boleto->setPagador(new Pagador());        
    }
}
