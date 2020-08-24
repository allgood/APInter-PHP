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



// A T E N Ç Ã O
//
// Todos os dados verificáveis precisam ser válidos
// Utilize sempre CPF/CNPJ, CEP, Cidade e Estado válidos
// Para evitar importunar estranhos utilize seus próprios
// dados ou de alguma pessoa que esteja ciente, pois as
// cobranças sempre são cadastradas no sistema quente
// do banco central e aparecerão no DDA dos sacados.
//
// Os dados de exemplo NÃO SÃO VÁLIDOS e se não forem
// alterados o script de teste não funcionará.



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
$boleto->setDataEmissao(date('Y-m-d'));
$boleto->setValorNominal(100.10);
$boleto->setDataVencimento(date_add(new DateTime() , new DateInterval("P10D"))->format('Y-m-d'));

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
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}

try {
    echo "\Download do PDF\n";
    $pdf = $banco->getPdfBoleto($boleto->getNossoNumero());
    echo "\n\nSalvo PDF em ".$pdf."\n";
} catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}


try {
    echo "\nConsultando boleto\n";
    $boleto2 = $banco->getBoleto($boleto->getNossoNumero());
    var_dump($boleto2);
} catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}

try {
    echo "\nBaixando boleto\n";
    $banco->baixaBoleto($boleto->getNossoNumero(), INTER_BAIXA_DEVOLUCAO);
    echo "Boleto Baixado";
} catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}

try {
    echo "\nConsultando boleto antigo\n";
    $boleto2 = $banco->getBoleto("00571817313");
    var_dump($boleto2);
} catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}

try {
    echo "\nListando boletos vencendo nos próximos 10 dias (apenas a primeira página)\n";
    $listaBoletos = $banco->listaBoletos(date('Y-m-d'), date_add(new DateTime() , new DateInterval("P10D"))->format('Y-m-d'));
    var_dump($listaBoletos);
} catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}

echo "\n\n";
