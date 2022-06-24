<?php

namespace ctodobom\APInterPHP;

use ctodobom\APInterPHP\Cobranca\Boleto;

define("INTER_BAIXA_ACERTOS", "ACERTOS");
define("INTER_BAIXA_PROTESTADO", "ACERTOS");
define("INTER_BAIXA_DEVOLUCAO", "ACERTOS");
define("INTER_BAIXA_SUBSTITUICAO", "SUBISTITUICAO");

define("INTER_FILTRO_TODOS", "TODOS");
define("INTER_FILTRO_VENCIDOSAVENCER", "VENCIDOSAVENCER");
define("INTER_FILTRO_EXPIRADOS", "EXPIRADOS");
define("INTER_FILTRO_PAGOS", "PAGOS");
define("INTER_FILTRO_TODOSBAIXADOS", "TODOSBAIXADOS");

define("INTER_ORDEM_NOSSONUMERO", "NOSSONUMERO");
define("INTER_ORDEM_SEUNUMERO", "SEUNUMERO");
define("INTER_ORDEM_VENCIMENTO", "DATAVENCIMENTO_ASC");
define("INTER_ORDEM_VENCIMENTO_DESC", "DATAVENCIMENTO_DSC");
define("INTER_ORDEM_NOMESACADO", "NOMESACADO");
define("INTER_ORDEM_VALOR", "VALOR_ASC");
define("INTER_ORDEM_VALOR_DESC", "VALOR_DSC");
define("INTER_ORDEM_STATUS", "STATUS_ASC");
define("INTER_ORDEM_STATUS_DESC", "STATUS_DSC");

class BancoInter
{
    private $apiBaseURL = "https://apis.bancointer.com.br";
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

        $http_params[] = 'x-inter-conta-corrente: ' . $this->accountNumber;
        curl_setopt($curl, CURLOPT_HTTPHEADER, $http_params);

        $this->curl = $curl;
    }

    /**
     *
     * @param  string            $url
     * @param  \JsonSerializable $data
     * @param  array             $http_params
     * @throws BancoInterException
     * @return \stdClass
     */
    public function controllerPost(string $url, \JsonSerializable $data, array $http_params = null)
    {

        if ($http_params == null) {
            $http_params = array(
                'accept: application/json',
                'Content-type: application/json'
            );
        }

        $retry = 5;
        while ($retry > 0) {
            $this->controllerInit($http_params);
            curl_setopt($this->curl, CURLOPT_URL, $this->apiBaseURL . $url);

            curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($this->curl, CURLOPT_POSTFIELDS, json_encode($data));

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

        $reply = $this->controllerPost("/openbanking/v1/certificado/boletos", $boleto);

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
        $reply = $this->controllerGet("/openbanking/v1/certificado/boletos/" . $nossoNumero);

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

        $http_params = array(
            'accept: application/pdf',
        );

        $reply = $this->controllerGet("/openbanking/v1/certificado/boletos/" . $nossoNumero . "/pdf", $http_params);

        $filename = tempnam($savePath, "boleto-inter-");
        if (!file_put_contents($filename, base64_decode($reply->body))) {
            throw new BancoInterException("Erro decodificando e salvando PDF", 0, $reply);
        }
        rename($filename, $filename .= ".pdf");

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
        $http_params = ['accept: application/pdf'];

        $reply = $this->controllerGet(
            "/openbanking/v1/certificado/boletos/$nossoNumero/pdf",
            $http_params
        );

        if (!$reply->body) {
            throw new BancoInterException("Erro ao receber o PDF", 0, $reply);
        }

        return $reply->body;
    }

    public function baixaBoleto(string $nossoNumero, string $motivo = "ACERTOS")
    {
        $data = new StdSerializable();
        $data->codigoBaixa = $motivo;

        $reply = $this->controllerPost("/openbanking/v1/certificado/boletos/" . $nossoNumero . "/baixas", $data);

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
        $filtro = "TODOS",
        $ordem = "NOSSONUMERO"
    ): \stdClass {

        $url = "/openbanking/v1/certificado/boletos";
        $url .= "?filtrarPor=" . $filtro;
        $url .= "&dataInicial=" . $dataInicial;
        $url .= "&dataFinal=" . $dataFinal;
        $url .= "&ordenarPor=" . $ordem;
        $url .= "&page=" . $pagina;
        $url .= "&size=" . $linhas;

        $reply = $this->controllerGet($url);

        $replyData = json_decode($reply->body);

        return $replyData;
    }
}
