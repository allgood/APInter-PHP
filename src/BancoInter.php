<?php
namespace ctodobom\APInterPHP;

use ctodobom\APInterPHP\Cobranca\Boleto;

define("INTER_BAIXA_ACERTOS", "ACERTOS");
define("INTER_BAIXA_PEDIDOCLIENTE", "APEDIDODOCLIENTE");
define("INTER_BAIXA_DEVOLUCAO", "DEVOLUCAO");
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
     * @param array $oAuthTokenData
     */
    public function __construct(
        string $accountNumber,
        string $certificateFile,
        string $keyFile,
        TokenRequest $tokenRequest,
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
        $this->checkOauthToken();
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
     * Check if have oAuthToken, or if it is expired, and request one if needed
     *
     */
    private function checkOAuthToken()
    {
        if (!$this->oAuthToken || $this->tokenTimestamp+$this->tokenExpiresIn > time()-10) {
            $reply = $this->controllerPost("/oauth/v2/token", $this->tokenRequest, null, false);
            
            $replyData = json_decode($reply->body);
            $this->oAuthToken = $replyData->access_token;
            $this->tokenExpiresIn = $replyData->expires_in;
            $this->tokenTimestamp = time();
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
        bool $postJson = true
    ) {

        if ($http_params == null) {
            $http_params=array(
                'accept: application/json',
                'Content-type: application/'.($postJson ? 'json' : 'x-www-form-urlencoded')
            );
        }
        
        if (!($data instanceof TokenRequest)) {
            $this->checkOAuthToken();
        }

        if ($this->oAuthToken) {
            $http_params[]='Authorization: Bearer '.$this->oAuthToken;
        }
        
        if ($postJson) {
            $prepared_data = json_encode($data);
        } else {
            $prepared_data = http_build_query($data->jsonSerialize());
        }
             
        $retry = 5;
        while ($retry>0) {
            $this->controllerInit($http_params);
            curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL.$url);
    
            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
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
                $retry=0;
            }
        }
        
        if ($http_code == 0) {
            throw new \Exception("Curl error: ".$curl_error);
        }
        
        if ($http_code < 200 || $http_code > 299) {
            throw new BancoInterException("Erro HTTP ".$http_code, $http_code, $reply);
        }
        return $reply;
    }
    
    public function controllerGet(string $url, array $http_params = null)
    {
        
        if ($http_params == null) {
            $http_params=array(
                'accept: application/json',
            );
        }

        $this->checkOAuthToken();
        
        $http_params[]='Authorization: Bearer '.$this->oAuthToken;
        
        $retry = 5;
        while ($retry>0) {
            $this->controllerInit($http_params);
            curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL.$url);
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
                $retry=0;
            }
        }
        
        if ($http_code == 0) {
            throw new \Exception("Curl error: ".$curl_error);
        }
        
        if ($http_code < 200 || $http_code > 299) {
            throw new BancoInterException("Erro HTTP ".$http_code, $http_code, $reply);
        }
        
        return $reply;
    }
    
    /**
     * Transmite um boleto para o Banco Inter
     *
     * @param  Boleto $boleto Boleto a ser transmitido
     * @return Boleto
     */
    public function createBoleto(Boleto $boleto) : Boleto
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
    public function getBoleto(string $nossoNumero) : \stdClass
    {
        $reply = $this->controllerGet("/cobranca/v2/boletos/".$nossoNumero);

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
    public function getPdfBoleto(string $nossoNumero, string $savePath = null) : string
    {
        if ($savePath == null) {
            $savePath = ini_get('upload_tmp_dir') ? ini_get('upload_tmp_dir') : sys_get_temp_dir();
        }
        
        $reply = $this->controllerGet("/cobranca/v2/boletos/".$nossoNumero."/pdf");

        $filename = tempnam($savePath, "boleto-inter-").".pdf";
        
        if (!file_put_contents($filename, base64_decode(json_decode($reply->body)->pdf))) {
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
    public function getPdfBoletoBase64(string $nossoNumero) : string
    {
        $http_params = [
            'accept: application/pdf'
        ];

        $reply = $this->controllerGet(
            "/cobranca/v2/boletos/$nossoNumero/pdf",
            $http_params
        );

        if (!$reply->body) {
            throw new BancoInterException('Erro ao receber o PDF', 0, $reply);
        }

        return $reply->body;
    }

    public function baixaBoleto(string $nossoNumero, string $motivo = "ACERTOS")
    {
        $data = new StdSerializable();
        $data->motivoCancelamento = $motivo;
        
        $reply = $this->controllerPost("/cobranca/v2/boletos/".$nossoNumero."/cancelar", $data);
        
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
    ) : \stdClass {

        $url = "/cobranca/v2/boletos";
        $url .= "?dataInicial=".$dataInicial;
        $url .= "&dataFinal=".$dataFinal;
        if ($filtro) {
            $url .= "&situacao=".$filtro;
        }
        if ($ordem) {
            if (endsWith($ordem, '_DSC')) {
                $ordem = str_replace('_DSC', '', $ordem);
                $inverterOrdem = true;
            }
            $url .= "&ordenarPor=".$ordem;
        }
        if ($inverterOrdem) {
            $url .= "&tipoOrdenacao=DESC";
        }
        $url .= "&paginaAtual=".$pagina;
        $url .= "&itensPorPagina=".$linhas;
        
        $reply = $this->controllerGet($url);
        
        $replyData = json_decode($reply->body);
        
        return $replyData;
    }
}
