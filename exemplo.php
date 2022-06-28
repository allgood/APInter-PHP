<?php

require_once "vendor/autoload.php";

use ctodobom\APInterPHP\BancoInter;
use ctodobom\APInterPHP\TokenRequest;
use ctodobom\APInterPHP\BancoInterException;
use ctodobom\APInterPHP\Cobranca\Boleto;
use ctodobom\APInterPHP\Cobranca\Pagador;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// dados do correntista
$conta = $_ENV['INTER_CONTA'];
$cnpj = $_ENV['INTER_CNPJ'];
$certificado = $_ENV['INTER_CERTIFICATE_PATH'];
$chavePrivada = $_ENV['INTER_PRIVATE_KEY_PATH'];



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
// alterados o script de exemplo não funcionará.


// dados de teste
$cpfPagador = $_ENV['PAGADOR_CPF'];
$estadoPagador = $_ENV['PAGADOR_UF'];

$banco = new BancoInter($conta, $certificado, $chavePrivada, new TokenRequest($_ENV['INTER_CLIENT_ID'],$_ENV['INTER_CLIENT_SECRET'],'boleto-cobranca.read boleto-cobranca.write'), []);

// Se a chave privada estiver encriptada no disco
// $banco->setKeyPassword("senhadachave");

// define callback para salvar token emitido
$banco->setTokenNewCallback(function(string $tokenJson) {
    if ($tokenFile = fopen('inter-oauth-token.txt','w')) {
        fwrite($tokenFile, $tokenJson);
        fclose($tokenFile);
    }
});

// define callback para obter token do cache
$banco->setTokenLoadCallback(function() {
    $oAuthTokenData = null;
    // uso do @ para evitar o warning se o arquivo não existe
    if (($tokenFile = @fopen('inter-oauth-token.txt','r')) !== false) {
        // se tiver arquivo com token, carrega ele e retorna
        $tokenJson = fread($tokenFile, 8192);
        $oAuthTokenData = json_decode($tokenJson, true);
        fclose($tokenFile);
        return $oAuthTokenData;
    } else {
        // retorno "falso" força a emissão de novo token
        return false;
    }
});

$pagador = new Pagador();
$pagador->setTipoPessoa(Pagador::PESSOA_FISICA);
$pagador->setNome("Nome de Teste");
$pagador->setEndereco("Nome da rua");
$pagador->setNumero(42);
$pagador->setBairro("Centro");
$pagador->setCidade("Cidade");
$pagador->setCep($_ENV['PAGADOR_CEP']);

$pagador->setCpfCnpj($cpfPagador);
$pagador->setUf($estadoPagador);

$boleto = new Boleto();
$boleto->setPagador($pagador);
$boleto->setSeuNumero("123456");
$boleto->setValorNominal(100.10);
$boleto->setDataVencimento(date_add(new DateTime() , new DateInterval("P10D"))->format('Y-m-d'));

var_dump(json_decode(json_encode($boleto)));

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
    echo "\n\n".$e->getTraceAsString();
    die;
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
    echo "\n\n".$e->getTraceAsString();
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
    echo "\n\n".$e->getTraceAsString();
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
    echo "\n\n".$e->getTraceAsString();
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
    echo "\n\n".$e->getTraceAsString();
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
