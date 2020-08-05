<?php
namespace ctodobom\APInterPHP\Tests;

use PHPUnit\Framework\TestCase;
use ctodobom\APInterPHP\Cobranca\Desconto;

final class DescontoTest extends TestCase
{
    public function testDesconto() {
        $desconto = new Desconto();
        $this->assertInstanceOf(Desconto::class, $desconto);
    }
}
