<?php

namespace ctodobom\APInterPHP;

use ctodobom\APInterPHP\Cobranca\Boleto;
use Closure;

define("INTER_BAIXA_ACERTOS", "ACERTOS");
define("INTER_BAIXA_PEDIDOCLIENTE", "APEDIDODOCLIENTE");
define("INTER_BAIXA_DEVOLUCAO", "ACERTOS");
define("INTER_BAIXA_PAGO", "PAGODIRETOAOCLIENTE");
define("INTER_BAIXA_SUBSTITUICAO", "SUBSTITUICAO");

// não mais suportados, usando string válida para evitar erros
define("INTER_BAIXA_PROTESTADO", "ACERTOS");

define("INTER_FILTRO_EXPIRADOS", "EXPIRADO");
define("INTER_FILTRO_VENCIDO", "VENCIDO");
define("INTER_FILTRO_EMABERTO", "EMABERTO");
define("INTER_FILTRO_PAGOS", "PAGO");
define("INTER_FILTRO_CANCELADO", "CANCELADO");

// não mais suportados, usando string válida para evitar erros
define("INTER_FILTRO_TODOS", null);
define("INTER_FILTRO_TODOSBAIXADOS", "PAGO");
define("INTER_FILTRO_VENCIDOSAVENCER", "EMABERTO");

define("INTER_ORDEM_PAGADOR", null);
define("INTER_ORDEM_NOSSONUMERO", "NOSSONUMERO");
define("INTER_ORDEM_SEUNUMERO", "SEUNUMERO");
define("INTER_ORDEM_PAGAMENTO", "DATA_PAGAMENTO");
define("INTER_ORDEM_VENCIMENTO", "DATA_VENCIMENTO");
define("INTER_ORDEM_VALOR", "VALOR");
define("INTER_ORDEM_STATUS", "STATUS");

// não mais suportados, usando string válida para evitar erros
define("INTER_ORDEM_VENCIMENTO_DESC", "DATA_VENCIMENTO_DSC");
define("INTER_ORDEM_NOMESACADO", "PAGADOR");
define("INTER_ORDEM_VALOR_DESC", "VALOR_DSC");
define("INTER_ORDEM_STATUS_DESC", "STATUS_DSC");

class BancoInter
{
    private $apiBaseURL = "https://cdpj.partners.bancointer.com.br";
    private $accountNumber = null;
    private $certificateFile = null;
    private $keyFile = null;
    private $keyPassword = null;

    private $curl = null;

    private $tokenRequest = null;
    private $oAuthToken = null;
    private $tokenExpiresIn = null;
    private $tokenTimestamp = null;

    private ?Closure $tokenNewCallback = null;
    private ?Closure $tokenLoadCallback = null;

    /**
     * Get API Base URL
     *
     * @return string
     */
    public function getApiBaseURL()
    {
        return $this->apiBaseURL;
    }

    /**
     * Set API Base URL
     *
     * @param string $apiBaseURL
     */
    public function setApiBaseURL(string $apiBaseURL)
    {
        $this->apiBaseURL = $apiBaseURL;
    }

    public function setKeyPassword(string $keyPassword)
    {
        $this->keyPassword = $keyPassword;
    }

    /**
     *
     * @param string $accountNumber
     * @param string $certificateFile
     * @param string $keyFile
     * @param TokenRequest $tokenRequest
     * @param array $oAuthTokenData (deprecado) use callbacks para carregar e contabilizar uso do token
     */
    public function __construct(
        string $accountNumber,
        string $certificateFile,
        string $keyFile,
        TokenRequest $tokenRequest,
        #[Deprecated]
        array $oAuthTokenData = null
    ) {
        $this->accountNumber = $accountNumber;
        $this->certificateFile = $certificateFile;
        $this->keyFile = $keyFile;
        $this->tokenRequest = $tokenRequest;
        if ($oAuthTokenData) {
            $this->importOAuthToken($oAuthTokenData);
        }
    }

    /**
     * Obtém o token oAuth
     *
     * @return string oAuth token
     */
    public function getOAuthToken()
    {
        return $this->oAuthToken;
    }

    /**
     * return current oAuthToken data
     *
     * @return []
     */
    public function exportOAuthToken()
    {
        $this->checkOauthToken(false);
        return([
            "access_token" => $this->oAuthToken,
            "expires_in" => $this->tokenExpiresIn,
            "timestamp" => $this->tokenTimestamp
        ]);
    }

    /**
     * import oAuthToken data
     *
     * @param [] $tokenData
     */
    private function importOAuthToken($tokenData)
    {
        $this->oAuthToken = $tokenData["access_token"];
        $this->tokenExpiresIn = $tokenData["expires_in"];
        $this->tokenTimestamp = $tokenData["timestamp"];
    }

    /**
     * Tenta carregar token através de callback, verifica se tem o token oAuth
     * disponível, se ele está expirado, requisitando novo token e executando
     * o callback se necessário.
     *
     * @param boolean $emitCallbacks Callbacks serão executados (default true)
     */
    private function checkOAuthToken($emitCallbacks = true)
    {
        if ($emitCallbacks && $this->tokenLoadCallback) {
            if (($loadedTokenData = ($this->tokenLoadCallback)()) !== false) {
                $this->importOAuthToken($loadedTokenData);
            } else {
                $this->oAuthToken = false;
            }
        }

        $curtime = time();

        if (!$this->oAuthToken || $this->tokenTimestamp + $this->tokenExpiresIn < $curtime - 10) {
            $reply = $this->controllerPost("/oauth/v2/token", $this->tokenRequest, null, false);

            $replyData = json_decode($reply->body);
            $this->oAuthToken = $replyData->access_token;
            $this->tokenExpiresIn = $replyData->expires_in;
            $this->tokenTimestamp = time();

            if ($emitCallbacks && $this->tokenNewCallback) {
                ($this->tokenNewCallback)(json_encode($this->exportOAuthToken()));
            }
        }
    }

    /**
     * Inicializa a conexão com a API
     *
     * @param array $http_params
     */
    private function controllerInit(array $http_params)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->certificateFile);
        curl_setopt($curl, CURLOPT_SSLKEY, $this->keyFile);
        curl_setopt($curl, CURLOPT_CAPATH, "/etc/ssl/certs/");
        if ($this->keyPassword) {
            curl_setopt($curl, CURLOPT_KEYPASSWD, $this->keyPassword);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        curl_setopt($curl, CURLOPT_HTTPHEADER, $http_params);

        $this->curl = $curl;
    }

    /**
     *
     * @param string $url
     * @param \JsonSerializable $data
     * @param array $http_params
     * @throws BancoInterException
     * @return \stdClass
     */
    public function controllerPost(
        string $url,
        \JsonSerializable $data,
        array $http_params = null,
        bool $postJson = true,
        bool $methodPut = false
    ) {

        if ($http_params == null) {
            $http_params = array(
                'accept: application/json',
                'Content-type: application/' . ($postJson ? 'json' : 'x-www-form-urlencoded')
            );
        }

        if (!($data instanceof TokenRequest)) {
            $this->checkOAuthToken();
        }

        if ($this->oAuthToken) {
            $http_params[] = 'Authorization: Bearer ' . $this->oAuthToken;
        }

        if ($postJson) {
            $prepared_data = json_encode($data);
        } else {
            $prepared_data = http_build_query($data->jsonSerialize());
        }

        $retry = 5;
        while ($retry > 0) {
            $this->controllerInit($http_params);
            curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL . $url);

            if ($methodPut) {
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'PUT');
            } else {
                curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            }


            curl_setopt($this->curl, CURLOPT_POSTFIELDS, $prepared_data);

            $curlReply = curl_exec($this->curl);
            if (!$curlReply) {
                $curl_error = curl_error($this->curl);
            }
            $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
            curl_close($this->curl);
            $this->curl = null;

            $reply = new \stdClass();
            $reply->header = substr($curlReply, 0, $header_size);
            $reply->body = substr($curlReply, $header_size);

            if ($http_code == 503) {
                $retry--;
            } else {
                $retry = 0;
            }
        }

        if ($http_code == 0) {
            throw new \Exception("Curl error: " . $curl_error);
        }

        if ($http_code < 200 || $http_code > 299) {
            throw new BancoInterException("Erro HTTP " . $http_code, $http_code, $reply);
        }
        return $reply;
    }

    public function controllerGet(string $url, array $http_params = null)
    {

        if ($http_params == null) {
            $http_params = array(
                'accept: application/json',
            );
        }

        $this->checkOAuthToken();

        $http_params[] = 'Authorization: Bearer ' . $this->oAuthToken;

        $retry = 5;
        while ($retry > 0) {
            $this->controllerInit($http_params);
            curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL . $url);
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');

            $curlReply = curl_exec($this->curl);
            if (!$curlReply) {
                $curl_error = curl_error($this->curl);
            }
            $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
            curl_close($this->curl);
            $this->curl = null;

            $reply = new \stdClass();
            $reply->header = substr($curlReply, 0, $header_size);
            $reply->body = substr($curlReply, $header_size);

            if ($http_code == 503) {
                $retry--;
                sleep(5);
            } else {
                $retry = 0;
            }
        }

        if ($http_code == 0) {
            throw new \Exception("Curl error: " . $curl_error);
        }

        if ($http_code < 200 || $http_code > 299) {
            throw new BancoInterException("Erro HTTP " . $http_code, $http_code, $reply);
        }

        return $reply;
    }

    /**
     * Transmite um boleto para o Banco Inter
     *
     * @param  Boleto $boleto Boleto a ser transmitido
     * @return Boleto
     */
    public function createBoleto(Boleto $boleto): Boleto
    {
        // garante que o boleto tem um controller
        $boleto->setController($this);

        $reply = $this->controllerPost("/cobranca/v2/boletos", $boleto);

        $replyData = json_decode($reply->body);

        $boleto->setNossoNumero($replyData->nossoNumero);
        $boleto->setCodigoBarras($replyData->codigoBarras);
        $boleto->setLinhaDigitavel($replyData->linhaDigitavel);

        return $boleto;
    }

    /**
     *
     * @param  string $nossoNumero
     * @return \stdClass
     */
    public function getBoleto(string $nossoNumero): \stdClass
    {
        $reply = $this->controllerGet("/cobranca/v2/boletos/" . $nossoNumero);

        $replyData = json_decode($reply->body);

        return $replyData;
    }

    /**
     * Faz download do PDF do boleto
     *
     * @param  string $nossoNumero
     * @param  string $savePath    Pasta a salvar o arquivo (default para a pasta de upload ou tmp)
     * @throws BancoInterException
     * @return string Caminho completo do arquivo baixado
     */
    public function getPdfBoleto(string $nossoNumero, string $savePath = null): string
    {
        if ($savePath == null) {
            $savePath = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        }

        $reply = $this->getPdfBoletoBase64($nossoNumero);

        $filename = tempnam($savePath, "boleto-inter-") . ".pdf";

        if (!file_put_contents($filename, base64_decode($reply))) {
            throw new BancoInterException("Erro decodificando e salvando PDF", 0, $reply);
        }

        return $filename;
    }

    /**
     * Faz download do PDF do boleto e retorna apenas o conteúdo binário
     * codificado em string base64
     *
     * @param  string $nossoNumero
     * @throws BancoInterException
     * @return string Conteúdo do PDF codificado em string base64
     */
    public function getPdfBoletoBase64(string $nossoNumero): string
    {
        $reply = $this->controllerGet("/cobranca/v2/boletos/$nossoNumero/pdf");

        if (!$reply->body) {
            throw new BancoInterException('Erro ao receber o PDF', 0, $reply);
        }

        return json_decode($reply->body)->pdf;
    }

    public function baixaBoleto(string $nossoNumero, string $motivo = "ACERTOS")
    {
        $data = new StdSerializable();
        $data->motivoCancelamento = $motivo;

        $reply = $this->controllerPost("/cobranca/v2/boletos/" . $nossoNumero . "/cancelar", $data);

        $replyData = json_decode($reply->body);

        return $replyData;
    }

    /**
     * Retorna lista de boletos registrados no banco
     *
     * @param  string $dataInicial Data de vencimento inicial
     * @param  string $dataFinal   Data de vencimento final
     * @param  number $pagina      Página de resultado (default =
     *                             0)
     * @param  number $linhas      Linhas de resultado (default = 20)
     * @param  string $filtro      Filtro de resultado (default = "TODOS")
     * @param  string $ordem       Ordem do resultado (default = "NOSSONUMERO")
     * @return \stdClass
     */
    public function listaBoletos(
        string $dataInicial,
        string $dataFinal,
        $pagina = 0,
        $linhas = 20,
        $filtro = null,
        $ordem = null,
        $inverterOrdem = false
    ): \stdClass {

        $url = "/cobranca/v2/boletos";
        $url .= "?dataInicial=" . $dataInicial;
        $url .= "&dataFinal=" . $dataFinal;
        if ($filtro) {
            $url .= "&situacao=" . $filtro;
        }
        if ($ordem) {
            if (endsWith($ordem, '_DSC')) {
                $ordem = str_replace('_DSC', '', $ordem);
                $inverterOrdem = true;
            }
            $url .= "&ordenarPor=" . $ordem;
        }
        if ($inverterOrdem) {
            $url .= "&tipoOrdenacao=DESC";
        }
        $url .= "&paginaAtual=" . $pagina;
        $url .= "&itensPorPagina=" . $linhas;

        $reply = $this->controllerGet($url);

        $replyData = json_decode($reply->body);

        return $replyData;
    }

    /**
     * Define uma função a ser chamada quando um novo token for emitido,
     * permitindo que a aplicação armazene o novo token em um cache
     *
     * A função irá receber o token formatado como uma string JSON sobre
     * a saída do método exportOAuthToken(), que poderá ser usada para
     * importar o token posteriormente no parâmetro $oAuthTokenData do
     * construtor da classe BancoInter.
     *
     * É aconselhável que a função faça uso de alguma implementação de
     * cache e semáforos para garantir que a o token seja utilizado
     * corretamente entre processos concorrentes.
     *
     * @param Closure $callback
     */
    public function setTokenNewCallback(Closure $callback)
    {
        $this->tokenNewCallback = $callback;
    }

    /**
     * Define uma função a ser chamada sempre que for necessária a
     * utilização do token oAuth permitindo a carga do token a partir
     * do cache e a contabilização da utilização tanto com base na
     * quantidade quanto com base no tempo.
     *
     * A função deve retornar um array com os elementos access_token,
     * expires_in e timestamp, ou false caso um novo token deva
     * ser emitido.
     *
     * É aconselhável que a função faça uso de alguma implementação de
     * cache e semáforos para garantir que a contabilização ocorra de forma
     * coerente entre processos.
     *
     * @param Closure $callback
     */
    public function setTokenLoadCallback(Closure $callback)
    {
        $this->tokenLoadCallback = $callback;
    }


    /**
     * Retorna o saldo da conta na data informada. Caso não seja informada
     * uma data, retorna o saldo atual.
     *
     * @param \DateTime $dataSaldo
     * @return float
     */
    public function getSaldo(\DateTime $dataSaldo = null): ?float
    {
        if (!$dataSaldo) {
            $dataSaldo = new \DateTime();
        }

        $reply = $this->controllerGet("/banking/v2/saldo?dataSaldo=" . $dataSaldo->format('Y-m-d'));
        $replyData = json_decode($reply->body);

        return $replyData->disponivel ?? null;
    }

    /**
     * Consulta o extrato em um período entre datas específico. Para utilizar esta chamada,
     * suas credenciais junto ao Banco Inter precisam ter acesso à permissão "Consulta de extrato
     * e saldo", e você precisa declarar o escopo extrato.read ao criar o TokenRequest.
     *
     * @param \DateTime dataInicio
     * @param \DateTime dataFim
     * @return \stdClass
     */
    public function getExtrato(\DateTime $dataInicio, \DateTime $dataFim): \stdClass
    {
        $params['dataInicio'] = $dataInicio->format('Y-m-d');
        $params['dataFim'] = $dataFim->format('Y-m-d');

        $url = "/banking/v2/extrato?" . http_build_query($params);

        $reply = $this->controllerGet($url);

        return json_decode($reply->body);
    }

    /**
     * Consulta o extrato COMPLETO em um período entre datas específico. Para utilizar esta chamada,
     * suas credenciais junto ao Banco Inter precisam ter acesso à permissão "Consulta de extrato
     * e saldo", e você precisa declarar o escopo extrato.read ao criar o TokenRequest.
     *
     * Referência do extrato completo: https://developers.bancointer.com.br/reference/extratocomplete
     *
     * @param \DateTime $dataInicio
     * @param \DateTime $dataFim
     * @param int $pagina número da página
     * @param string $tipoOperacao 'C' para crédito e 'D' para débito
     * @param string $tipoTransacao PIX, CAMBIO, ESTORNO, INVESTIMENTO, TRANSFERENCIA, PAGAMENTO, BOLETO_COBRANCA, OUTROS
     * @return \stdClass
     */
    public function getExtratoCompleto(\DateTime $dataInicio, \DateTime $dataFim, int $pagina = 0, int $tamanhoPagina = 50, $tipoOperacao = '', $tipoTransacao = '',): \stdClass
    {
        $params['dataInicio'] = $dataInicio->format('Y-m-d');
        $params['dataFim'] = $dataFim->format('Y-m-d');
        $params['pagina'] = $pagina;
        $params['tamanhoPagina'] = $tamanhoPagina;
        $params['tipoOperacao'] = $tipoOperacao;
        $params['tipoTransacao'] = $tipoTransacao;

        $url = "/banking/v2/extrato/completo?" . http_build_query($params);


        $reply = $this->controllerGet($url);

        return json_decode($reply->body);
    }


    /**
     * Cria o webhook que receberá atualizações automáticos dos boletos (cobranças)
     * Referência: https://developers.bancointer.com.br/reference/criarwebhookboleto
     *
     * @param $url
     * @return boolean
     */

    public function createWebhook($webhookUrl)
    {

        $url = "/cobranca/v2/boletos/webhook";

        $params = new \stdClass();

        $params->webhookUrl = $webhookUrl;

        //Verifica se a URL do webhook é válida
        if (!filter_var($webhookUrl, FILTER_VALIDATE_URL)) {
            return false;
        }

        try {
            $reply = $this->controllerPost($url, $params, null, true, true);
        } catch (BancoInterException $e) {
            return false;
        }

        if ($reply) {
            return true;
        }
    }

    /**
     * Retorna o webhook cadastrado, se houver
     *
     * @return string
     */

    public function getWebhook(): string
    {

        $url = "/cobranca/v2/boletos/webhook";

        $reply = $this->controllerGet($url);

        return $reply->body;
    }

    /**
     * Deleta o webhook, se houver. Caso não haja nenhum webhook, retornará o código HTTP 404
     */
    public function deleteWebhook(): string
    {

        $url = "/cobranca/v2/boletos/webhook";

        $reply = $this->controllerDelete($url);

        return $reply->body;
    }

    public function controllerDelete(
        string $url,
        array $http_params = null,
    ) {

        if ($http_params == null) {
            $http_params = array(
                'accept: application/json',
            );
        }

        if ($this->oAuthToken) {
            $http_params[] = 'Authorization: Bearer ' . $this->oAuthToken;
        }

        $retry = 5;
        while ($retry > 0) {
            $this->controllerInit($http_params);
            curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL . $url);

            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'DELETE');

            $curlReply = curl_exec($this->curl);
            if (!$curlReply) {
                $curl_error = curl_error($this->curl);
            }
            $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
            $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
            curl_close($this->curl);
            $this->curl = null;

            $reply = new \stdClass();
            $reply->header = substr($curlReply, 0, $header_size);
            $reply->body = substr($curlReply, $header_size);

            if ($http_code == 503) {
                $retry--;
            } else {
                $retry = 0;
            }
        }
        if ($http_code == 0) {
            throw new \Exception("Curl error: " . $curl_error);
        }
        if ($http_code < 200 || $http_code > 299) {
            throw new BancoInterException("Erro HTTP " . $http_code, $http_code, $reply);
        }
        return $reply;
    }
}
