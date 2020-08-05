<?php
namespace ctodobom\APInterPHP\Tests;

use PHPUnit\Framework\TestCase;
use ctodobom\APInterPHP\Cobranca\Pagador;

final class PagadorTest extends TestCase
{
    public function testPagador() {
        $pagador = new Pagador();
        $this->assertInstanceOf(Pagador::class, $pagador);
    }
}
