<?php
namespace ctodobom\APInterPHP\Cobranca;

class Desconto implements \JsonSerializable
{

    private $codigoDesconto = "NAOTEMDESCONTO";
    private $taxa = 0.0;
    private $valor = 0.0;
    private $data = "";
    
    const NAO_TEM_DESCONTO = 'NAOTEMDESCONTO';
    const VALOR_FIXO = 'VALORFIXODATAINFORMADA';
    const PERCENTUAL_FIXO = 'PERCENTUALDATAINFORMADA';
    const VALOR_DIA_CORRIDO = 'VALORANTECIPACAODIACORRIDO';
    const VALOR_DIA_UTIL = 'VALORANTECIPACAODIAUTIL';
    const PERCENTUAL_DIA_CORRIDO = 'PERCENTUALVALORNOMINALDIACORRIDO';
    const PERCENTUAL_DIA_UTIL = 'PERCENTUALVALORNOMINALDIAUTIL';

    /**
     * @return string
     */
    public function getCodigoDesconto()
    {
        return $this->codigoDesconto;
    }

    /**
     * @return number
     */
    public function getTaxa()
    {
        return $this->taxa;
    }

    /**
     * @return number
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * @param string $codigoDesconto
     */
    public function setCodigoDesconto($codigoDesconto)
    {
        $this->codigoDesconto = $codigoDesconto;
    }

    /**
     * @param number $taxa
     */
    public function setTaxa($taxa)
    {
        $this->taxa = $taxa;
    }

    /**
     * @param number $valor
     */
    public function setValor($valor)
    {
        $this->valor = $valor;
    }

    /**
     * @param string $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }
    
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
