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
    $boleto2 = $banco->getBoleto("00571817313"); // alterar para um número de boleto seu
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

// Exemplos de utilização dos endpoints de saldo e extrato do Inter

// Primeiro, cria uma instância da classe BancoInter, observe que o scope necessário é o extrato.read
// Verifique se seu certificado possui esta permissão no Internet Banking, em "Suas aplicações"
$banco = new BancoInter($conta, $certificado, $chavePrivada, new TokenRequest($_ENV['INTER_CLIENT_ID'],$_ENV['INTER_CLIENT_SECRET'],'extrato.read'));

// Retorna o saldo atual
try {
    //Se não passar nenhuma data como parâmetreo, retorna o saldo atual, número float
    $saldo = $banco->getSaldo();
    if ($saldo)
    {
        echo "\n\nSeu saldo hoje: R$ ".number_format($saldo, 2, ",", ".")."\n\n";
    }
    else {
        echo "\n\nO saldo retornou nulo\n\n";
    }
}
catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}

// Retorna o saldo em uma data específica
try {
    //Retorna o saldo no último dia do ano, para a contabilidade, por exemplo
    $dataDoSaldo = new DateTime('2021-12-31');
    $saldo = $banco->getSaldo($dataDoSaldo);
    if ($saldo)
    {
        echo "\n\nSeu saldo em ".$dataDoSaldo->format('d/m/Y').": R$ ".number_format($saldo, 2, ",", ".")."\n\n";
    }
    else {
        echo "\n\nO saldo retornou nulo\n\n";
    }
}
catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}

// Retorna o extrato dos últimos 7 dias, incluindo o dia atual
// Segue parte de exemplo real de como o extrato é retornado:
//
//object(stdClass)#93 (1) {
//["transacoes"]=>
//  array(74) {
//    [0]=>
//    object(stdClass)#36 (6) {
//    ["dataEntrada"]=>
//      string(10) "2022-07-11"
//    ["tipoTransacao"]=>
//      string(3) "PIX"
//    ["tipoOperacao"]=>
//      string(1) "C"
//    ["valor"]=>
//      string(4) "24.9"
//    ["titulo"]=>
//      string(12) "Pix recebido"
//    ["descricao"]=>
//      string(47) "PIX RECEBIDO - Cp :00000000-HUGO BRAGA"

try {
    $dataFim = new DateTime();
    $dataInicio = new DateTime();
    $dataInicio->sub(new DateInterval("P7D"));

    $extrato = $banco->getExtrato($dataInicio, $dataFim);
    echo "\n\nSeu extrato:\n\n";
    var_dump($extrato);

} catch ( BancoInterException $e ) {
    echo "\n\n".$e->getMessage();
    echo "\n\nCabeçalhos: \n";
    echo $e->reply->header;
    echo "\n\nConteúdo: \n";
    echo $e->reply->body;
}