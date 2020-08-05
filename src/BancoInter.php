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
     * Transmite um boleto para o Banco Inter
     * @param Boleto $boleto Boleto a ser transmitido
     * @throws BancoInterException
     * @return Boleto
     */
    public function createBoleto(Boleto $boleto) : Boleto
    {
        $http_params=array(
            'accept: application/json',
            'Content-type: application/json',
            'x-inter-conta-corrente: '.$this->accountNumber
        );
        
        $data = json_encode($boleto);

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_URL, $this->apiBaseURL."/openbanking/v1/certificado/boletos");
        curl_setopt($curl, CURLOPT_HEADER, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSLCERT, $this->certificateFile);
        curl_setopt($curl, CURLOPT_SSLKEY, $this->keyFile);
        if ($this->keyPassword) {
            curl_setopt($curl, CURLOPT_KEYPASSWD, $this->keyPassword);
        }
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($curl, CURLOPT_HTTPHEADER, $http_params);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        $reply = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $header_size = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
        curl_close($curl);

        $header = substr($reply, 0, $header_size);
        $body = substr($reply, $header_size);

        if ($http_code != 200) {
            throw new BancoInterException("Erro HTTP ".$http_code, $http_code, $header, $body);
        }
        
        $replyData = json_decode($body);
        
        $boleto->setNossoNumero($replyData->nossoNumero);
        $boleto->setCodigoBarras($replyData->codigoBarras);
        $boleto->setLinhaDigitavel($replyData->linhaDigitavel);
        
        return $boleto;
    }
}
