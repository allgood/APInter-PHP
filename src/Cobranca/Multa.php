<?php
namespace ctodobom\APInterPHP\Cobranca;

class Multa implements \JsonSerializable
{

    private $codigoMulta = "NAOTEMMULTA";
    private $valor = 0.0;
    private $taxa = 0.0;
    private $data = "";
    
    const NAO_TEM_MULTA = 'NAOTEMMULTA';
    const VALOR_FIXO = 'VALORFIXO';
    const PERCENTUAL = 'PERCENTUAL';
    /**
     * @return string
     */
    public function getCodigoMulta()
    {
        return $this->codigoMulta;
    }

    /**
     * @return number
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @return number
     */
    public function getTaxa()
    {
        return $this->taxa;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }
    
    /**
     * @param string $codigos
     */
    public function setCodigoMulta($codigoMulta)
    {
        $this->codigoMulta = $codigoMulta;
    }

    /**
     * @param number $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    /**
     * @param number $taxa
     */
    public function setTaxa($taxa)
    {
        $this->taxa = $taxa;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function jsonSerialize(): mixed
    {
        return get_object_vars($this);
    }
}
