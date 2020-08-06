<?php
namespace ctodobom\APInterPHP;

use ctodobom\APInterPHP\Cobranca\Boleto;

class BancoInter
{

    private $apiBaseURL = "https://apis.bancointer.com.br:8443";
    private $accountNumber = null;
    private $certificateFile = null;
    private $keyFile = null;
    private $keyPassword = null;

    private $curl = null;
    
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
    
    public function __construct(string $accountNumber, string $certificateFile, string $keyFile)
    {
        $this->accountNumber = $accountNumber;
        $this->certificateFile = $certificateFile;
        $this->keyFile = $keyFile;
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
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->certificateFile);
        curl_setopt($curl, CURLOPT_SSLKEY, $this->keyFile);
        if ($this->keyPassword) {
            curl_setopt($curl, CURLOPT_KEYPASSWD, $this->keyPassword);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');

        $http_params[] = 'x-inter-conta-corrente: '.$this->accountNumber;
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
    public function controllerPost(string $url, \JsonSerializable $data, array $http_params = null)
    {

        if ($http_params == null) {
            $http_params=array(
                'accept: application/json',
                'Content-type: application/json'
            );
        }
        
        $this->controllerInit($http_params);
        curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL.$url);

        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));
        
        $curlReply = curl_exec($this->curl);
        $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        curl_close($this->curl);
        $this->curl = null;

        $reply = new \stdClass();
        $reply->header = substr($curlReply, 0, $header_size);
        $reply->body = substr($curlReply, $header_size);

        if ($http_code > 299) {
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
        
        $this->controllerInit($http_params);
        curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL.$url);
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'GET');
        
        $curlReply = curl_exec($this->curl);
        $http_code = curl_getinfo($this->curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($this->curl, CURLINFO_HEADER_SIZE);
        curl_close($this->curl);
        $this->curl = null;
        
        $reply = new \stdClass();
        $reply->header = substr($curlReply, 0, $header_size);
        $reply->body = substr($curlReply, $header_size);
        
        if ($http_code > 299) {
            throw new BancoInterException("Erro HTTP ".$http_code, $http_code, $reply);
        }
        
        return $reply;
    }
    
    /**
     * Transmite um boleto para o Banco Inter
     * @param Boleto $boleto Boleto a ser transmitido
     * @return Boleto
     */
    public function createBoleto(Boleto $boleto) : Boleto
    {
        // garante que o boleto tem um controller
        $boleto->setController($this);
        
        $reply = $this->controllerPost("/openbanking/v1/certificado/boletos", $boleto);

        $replyData = json_decode($reply->body);
        
        $boleto->setNossoNumero($replyData->nossoNumero);
        $boleto->setCodigoBarras($replyData->codigoBarras);
        $boleto->setLinhaDigitavel($replyData->linhaDigitavel);
        
        return $boleto;
    }

    /**
     * 
     * @param string $nossoNumero
     * @return \stdClass
     */
    public function getBoleto(string $nossoNumero) : \stdClass
    {
        $reply = $this->controllerGet("/openbanking/v1/certificado/boletos/".$nossoNumero);

        $replyData = json_decode($reply->body);
        
        return $replyData;
    }
    
    public function baixaBoleto(string $nossoNumero, string $motivo = "ACERTOS")
    {
        $data = new stdSerializable();
        $data->codigoBaixa = $motivo;
        
        $reply = $this->controllerPost("/openbanking/v1/certificado/boletos/".$nossoNumero."/baixas", $data);
        
        $replyData = json_decode($reply->body);
        
        return $replyData;
        
    }
}
