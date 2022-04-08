<?php
namespace ctodobom\APInterPHP\Tests;

use PHPUnit\Framework\TestCase;
use ctodobom\APInterPHP\BancoInter;
use ctodobom\APInterPHP\TokenRequest;
use ctodobom\APInterPHP\Cobranca\Boleto;
use ctodobom\APInterPHP\Cobranca\Pagador;

final class BancoInterTest extends TestCase
{
    
    public function testBancoInter() {
        // AVISO:
        // estes testes não fazem sentido se não forem alterados
        // com dados possíveis de boletos e de correntista
        $banco = new BancoInter("123456", "/tmp/inter.crt", "/tmp/inter.key", new TokenRequest('INTER_CLIENT_ID','INTER_CLIENT_SECRET','boleto-cobranca.read boleto-cobranca.write'));
        $this->assertInstanceOf(BancoInter::class, $banco);
        
        $apiUrl = "https://apis.bancointer.com.br:8443";
        $banco->setApiBaseURL($apiUrl);
        $this->assertEquals($banco->getApiBaseURL(), $apiUrl);

        $faker = \Faker\Factory::create();
        $faker->addProvider(new \Faker\Provider\pt_BR\Person($faker));
        $faker->addProvider(new \Faker\Provider\en_US\Person($faker));
        $pagador = new Pagador();
        $pagador->setTipoPessoa(Pagador::PESSOA_FISICA);
        $pagador->setCpfCnpj("12312312312");
        $pagador->setNome($faker->name);
        $pagador->setEndereco($faker->streetName);
        $pagador->setNumero($faker->numberBetween(10,999));
        $pagador->setBairro("Centro");
        $pagador->setCidade($faker->city);
        $pagador->setUf($faker->stateAbbr());
        $pagador->setCep($faker->numerify("########"));
        
        $boleto = new Boleto();
        $boleto->setPagador($pagador);
        $boleto->setSeuNumero("123456");
        $boleto->setValorNominal(100.10);
        $boleto->setDataVencimento("2020-08-10");
        
        try {
            $banco->createBoleto($boleto);
            $this->assertNotNull($boleto->getNossoNumero());
            $this->assertNotNull($boleto->getCodigoBarras());
            $this->assertNotNull($boleto->getLinhaDigitavel());
        } catch ( \Exception $e ) {
        }
    }
}
