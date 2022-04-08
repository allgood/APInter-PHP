<?php
namespace ctodobom\APInterPHP\Cobranca;

use ctodobom\APInterPHP\BancoInterValueSizeException;

class Pagador implements \JsonSerializable
{
    private $cpfCnpj = null;
    private $tipoPessoa = null;
    private $nome = null;
    private $endereco = null;
    private $numero = null;
    private $complemento = "";
    private $bairro = null;
    private $cidade = null;
    private $uf = null;
    private $cep = null;
    private $email = "";
    private $ddd = "";
    private $telefone = "";

    const PESSOA_FISICA = "FISICA";
    const PESSOA_JURIDICA = "JURIDICA";

    /**
     * Assert if value complies with size restriction
     *
     * @param  string $value
     * @param  int    $size
     * @param  bool   $exact
     * @throws BancoInterValueSizeException
     */
    private static function assertSize(string $value, int $size, bool $exact = false)
    {
        if ($exact && mb_strlen($value, "8bit") != $size) {
            throw new BancoInterValueSizeException($value, $size, true);
        } elseif (!$exact && mb_strlen($value, "8bit") > $size) {
            throw new BancoInterValueSizeException($value, $size, false);
        }
        return;
    }
    
    /**
     * @deprecated Favor usar getCpfCnpj()
     *
     * @return mixed
     */
    public function getCnpjCpf()
    {
        return $this->cpfCnpj;
    }

    /**
     *
     * @return mixed
     */
    public function getCpfCnpj()
    {
        return $this->cpfCnpj;
    }
    
    /**
     * @return mixed
     */
    public function getNome()
    {
        return $this->nome;
    }

    /**
     * @return mixed
     */
    public function getCep()
    {
        return $this->cep;
    }

    /**
     * @return mixed
     */
    public function getBairro()
    {
        return $this->bairro;
    }

    /**
     * @return mixed
     */
    public function getEndereco()
    {
        return $this->endereco;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @return string
     */
    public function getComplemento()
    {
        return $this->complemento;
    }

    /**
     * @return mixed
     */
    public function getCidade()
    {
        return $this->cidade;
    }

    /**
     * @return mixed
     */
    public function getUf()
    {
        return $this->uf;
    }

    /**
     * @return mixed
     */
    public function getTipoPessoa()
    {
        return $this->tipoPessoa;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getDdd()
    {
        return $this->ddd;
    }

    /**
     * @return string
     */
    public function getTelefone()
    {
        return $this->telefone;
    }

    /**
     * @deprecated Favor usar setCpfCnpj()
     *
     * @param mixed $cnpjCpf
     */
    public function setCnpjCpf($cnpjCpf)
    {
        static::assertSize($cnpjCpf, 15);
        $this->cpfCnpj = $cnpjCpf;
    }

    /**
     * @param mixed $cnpjCpf
     */
    public function setCpfCnpj($cnpjCpf)
    {
        static::assertSize($cnpjCpf, 15);
        $this->cpfCnpj = $cnpjCpf;
    }
    
    /**
     * @param mixed $nome
     */
    public function setNome($nome)
    {
        static::assertSize($nome, 100);
        $this->nome = $nome;
    }

    /**
     * @param mixed $cep
     */
    public function setCep($cep)
    {
        static::assertSize($cep, 8, true);
        $this->cep = $cep;
    }

    /**
     * @param mixed $bairro
     */
    public function setBairro($bairro)
    {
        static::assertSize($bairro, 60);
        $this->bairro = $bairro;
    }

    /**
     * @param mixed $endereco
     */
    public function setEndereco($endereco)
    {
        static::assertSize($endereco, 90);
        $this->endereco = $endereco;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero)
    {
        static::assertSize($numero, 10);
        $this->numero = $numero;
    }

    /**
     * @param string $complemento
     */
    public function setComplemento($complemento)
    {
        static::assertSize($complemento, 30);
        $this->complemento = $complemento;
    }

    /**
     * @param mixed $cidade
     */
    public function setCidade($cidade)
    {
        static::assertSize($cidade, 60);
        $this->cidade = $cidade;
    }

    /**
     * @param mixed $uf
     */
    public function setUf($uf)
    {
        static::assertSize($uf, 2, true);
        $this->uf = $uf;
    }

    /**
     * @param mixed $tipoPessoa
     */
    public function setTipoPessoa($tipoPessoa)
    {
        $this->tipoPessoa = $tipoPessoa;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        static::assertSize($email, 50);
        $this->email = $email;
    }

    /**
     * @param string $ddd
     */
    public function setDdd($ddd)
    {
        static::assertSize($ddd, 2, true);
        $this->ddd = $ddd;
    }

    /**
     * @param string $telefone
     */
    public function setTelefone($telefone)
    {
        static::assertSize($telefone, 9);
        $this->telefone = $telefone;
    }
    
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
