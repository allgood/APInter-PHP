<?php
namespace ctodobom\APInterPHP\Cobranca;

use ctodobom\APInterPHP\BancoInter;

class Boleto implements \JsonSerializable
{

    private $dataEmissao = null;
    private $seuNumero = null;
    private $dataLimite = "SESSENTA";
    private $dataVencimento = null;
    private $valorNominal = 0.0;
    private $valorAbatimento = 0.0;
    private $cnpjCPFBeneficiario = null;
    private $numDiasAgenda = "SESSENTA";
    
    private $pagador = null;
    private $mensagem = null;
    private $desconto1 = null;
    private $desconto2 = null;
    private $desconto3 = null;
    private $multa = null;
    private $mora = null;
    
    private $nossoNumero = null;
    private $codigoBarras = null;
    private $linhaDigitavel = null;

    private $controller = null;
    
    const SESSENTA_DIAS = "SESSENTA";
    const TRINTA_DIAS = "TRINTA";
    
    /**
     * @return mixed
     */
    public function getDataEmissao()
    {
        return $this->dataEmissao;
    }

    /**
     * @return mixed
     */
    public function getSeuNumero()
    {
        return $this->seuNumero;
    }

    /**
     * @return string
     */
    public function getDataLimite()
    {
        return $this->dataLimite;
    }

    /**
     * @return mixed
     */
    public function getDataVencimento()
    {
        return $this->dataVencimento;
    }

    /**
     * @return number
     */
    public function getValorNominal()
    {
        return $this->valorNominal;
    }

    /**
     * @return number
     */
    public function getValorAbatimento()
    {
        return $this->valorAbatimento;
    }

    /**
     * @return mixed
     */
    public function getCnpjCPFBeneficiario()
    {
        return $this->cnpjCPFBeneficiario;
    }

    /**
     * @return string
     */
    public function getNumDiasAgenda()
    {
        return $this->numDiasAgenda;
    }

    /**
     *
     * @return Pagador
     */
    public function getPagador() : Pagador
    {
        return $this->pagador;
    }

    /**
     *
     * @return Mensagem
     */
    public function getMensagem() : Mensagem
    {
        return $this->mensagem;
    }

    /**
     * @return Desconto
     */
    public function getDesconto1() : Desconto
    {
        return $this->desconto1;
    }

    /**
     * @return Desconto
     */
    public function getDesconto2() : Desconto
    {
        return $this->desconto2;
    }

    /**
     * @return Desconto
     */
    public function getDesconto3() : Desconto
    {
        return $this->desconto3;
    }

    /**
     * @return \ctodobom\APInterPHP\Cobranca\Multa
     */
    public function getMulta() : Multa
    {
        return $this->multa;
    }

    /**
     * @return \ctodobom\APInterPHP\Cobranca\Mora
     */
    public function getMora() : Mora
    {
        return $this->mora;
    }

    /**
     *
     * @return BancoInter
     */
    public function getController() : BancoInter
    {
        return $this->controller;
    }
    
    /**
     * @param mixed $dataEmissao
     */
    public function setDataEmissao($dataEmissao)
    {
        $this->dataEmissao = $dataEmissao;
    }

    /**
     * @param mixed $seuNumero
     */
    public function setSeuNumero($seuNumero)
    {
        $this->seuNumero = $seuNumero;
    }

    /**
     * @param string $dataLimite
     */
    public function setDataLimite($dataLimite)
    {
        $this->dataLimite = $dataLimite;
    }

    /**
     * @param mixed $dataVencimento
     */
    public function setDataVencimento($dataVencimento)
    {
        $this->dataVencimento = $dataVencimento;
    }

    /**
     * @param number $valorNominal
     */
    public function setValorNominal($valorNominal)
    {
        $this->valorNominal = $valorNominal;
    }

    /**
     * @param number $valorAbatimento
     */
    public function setValorAbatimento($valorAbatimento)
    {
        $this->valorAbatimento = $valorAbatimento;
    }

    /**
     * @param mixed $cnpjCPFBeneficiario
     */
    public function setCnpjCPFBeneficiario($cnpjCPFBeneficiario)
    {
        $this->cnpjCPFBeneficiario = $cnpjCPFBeneficiario;
    }

    /**
     * @param string $numDiasAgenda
     */
    public function setNumDiasAgenda($numDiasAgenda)
    {
        $this->numDiasAgenda = $numDiasAgenda;
    }

    /**
     *
     * @param Pagador $pagador
     */
    public function setPagador(Pagador $pagador)
    {
        $this->pagador = $pagador;
    }

    /**
     *
     * @param Mensagem $mensagem
     */
    public function setMensagem(Mensagem $mensagem)
    {
        $this->mensagem = $mensagem;
    }

    /**
     * @param \ctodobom\APInterPHP\Cobranca\Desconto $desconto1
     */
    public function setDesconto1($desconto1) : Desconto
    {
        $this->desconto1 = $desconto1;
    }

    /**
     * @param \ctodobom\APInterPHP\Cobranca\Desconto $desconto2
     */
    public function setDesconto2($desconto2) : Desconto
    {
        $this->desconto2 = $desconto2;
    }

    /**
     * @param \ctodobom\APInterPHP\Cobranca\Desconto $desconto3
     */
    public function setDesconto3($desconto3) : Desconto
    {
        $this->desconto3 = $desconto3;
    }

    /**
     * @param \ctodobom\APInterPHP\Cobranca\Multa $multa
     */
    public function setMulta(Multa $multa)
    {
        $this->multa = $multa;
    }

    /**
     * @param \ctodobom\APInterPHP\Cobranca\Mora $mora
     */
    public function setMora(Mora $mora)
    {
        $this->mora = $mora;
    }

    /**
     * @return mixed
     */
    public function getNossoNumero()
    {
        return $this->nossoNumero;
    }

    /**
     * @return mixed
     */
    public function getCodigoBarras()
    {
        return $this->codigoBarras;
    }

    /**
     * @return mixed
     */
    public function getLinhaDigitavel()
    {
        return $this->linhaDigitavel;
    }

    /**
     * @param mixed $nossoNumero
     */
    public function setNossoNumero($nossoNumero)
    {
        $this->nossoNumero = $nossoNumero;
    }

    /**
     * @param mixed $codigoBarras
     */
    public function setCodigoBarras($codigoBarras)
    {
        $this->codigoBarras = $codigoBarras;
    }

    /**
     * @param mixed $linhaDigitavel
     */
    public function setLinhaDigitavel($linhaDigitavel)
    {
        $this->linhaDigitavel = $linhaDigitavel;
    }

    /**
     *
     * @param BancoInter $controller
     */
    public function setController(BancoInter $controller)
    {
        $this->controller = $controller;
    }
    
    public function __construct()
    {
        $this->mensagem = new Mensagem();
        $this->desconto1 = new Desconto();
        $this->desconto2 = new Desconto();
        $this->desconto3 = new Desconto();
        $this->multa = new Multa();
        $this->mora = new Mora();
    }

    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
