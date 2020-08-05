<?php

require_once "vendor/autoload.php";

use ctodobom\APInterPHP\BancoInter;
use ctodobom\APInterPHP\BancoInterException;
use ctodobom\APInterPHP\Cobranca\Boleto;
use ctodobom\APInterPHP\Cobranca\Pagador;

// dados do correntista
$conta = "0000001";
$cnpj = "12123123000112";
$certificado = "/caminho/do/certificado.pem";
$chavePrivada = "/caminho/da/chaveprivada.key";

// dados de teste
$cpfPagador = "12312312312";
$estadoPagador = "XX";

$banco = new BancoInter($conta, $certificado, $chavePrivada);

// Se a chave privada estiver encriptada no disco
// $banco->setKeyPassword("senhadachave");

$pagador = new Pagador();
$pagador->setTipoPessoa(Pagador::PESSOA_FISICA);
$pagador->setNome("Nome de Teste");
$pagador->setEndereco("Nome da rua");
$pagador->setNumero(42);
$pagador->setBairro("Centro");
$pagador->setCidade("Cidade");
$pagador->setCep("12345000");

$pagador->setCnpjCpf($cpfPagador);
$pagador->setUf($estadoPagador);

$boleto = new Boleto();
$boleto->setCnpjCPFBeneficiario($cnpj);
$boleto->setPagador($pagador);
$boleto->setSeuNumero("123456");
$boleto->setDataEmissao("2020-08-05");
$boleto->setValorNominal(100.10);
$boleto->setDataVencimento("2020-08-10");

try {
    $banco->createBoleto($boleto);
    echo "\nBoleto Criado\n";
    echo "\n seuNumero: ".$boleto->getSeuNumero();
    echo "\n nossoNumero: ".$boleto->getNossoNumero();
    echo "\n codigoBarras: ".$boleto->getCodigoBarras();
    echo "\n linhaDigitavel: ".$boleto->getLinhaDigitavel();
} catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->http_headers;
    echo "\n\nConteúdo: \n";
    echo $e->http_body;
}
echo "\n\n";
