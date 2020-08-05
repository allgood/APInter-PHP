<?php
namespace ctodobom\APInterPHP\Tests;

use PHPUnit\Framework\TestCase;
use ctodobom\APInterPHP\Cobranca\Mora;

final class MoraTest extends TestCase
{
    public function testMora() {
        $mora = new Mora();
        $this->assertInstanceOf(Mora::class, $mora);
    }
}
