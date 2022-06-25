<?php

namespace ctodobom\APInterPHP\Cobranca;

class Mensagem implements \JsonSerializable
{
    private $linha1 = "";
    private $linha2 = "";
    private $linha3 = "";
    private $linha4 = "";
    private $linha5 = "";

    /**
     * @return string
     */
    public function getLinha1()
    {
        return $this->linha1;
    }

    /**
     * @return string
     */
    public function getLinha2()
    {
        return $this->linha2;
    }

    /**
     * @return string
     */
    public function getLinha3()
    {
        return $this->linha3;
    }

    /**
     * @return string
     */
    public function getLinha4()
    {
        return $this->linha4;
    }

    /**
     * @return string
     */
    public function getLinha5()
    {
        return $this->linha5;
    }

    /**
     * @param string $linha1
     */
    public function setLinha1($linha1)
    {
        $this->linha1 = $linha1;
    }

    /**
     * @param string $linha2
     */
    public function setLinha2($linha2)
    {
        $this->linha2 = $linha2;
    }

    /**
     * @param string $linha3
     */
    public function setLinha3($linha3)
    {
        $this->linha3 = $linha3;
    }

    /**
     * @param string $linha4
     */
    public function setLinha4($linha4)
    {
        $this->linha4 = $linha4;
    }

    /**
     * @param string $linha5
     */
    public function setLinha5($linha5)
    {
        $this->linha5 = $linha5;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
