<?php

class FornecedorDTO implements JsonSerializable
{
private $idFornecedor;
private $empresa;
private $representante;
private $telefone;
private $email;
private $endereco;
    public function getIdFornecedor()
    {
        return $this->idFornecedor;
    }

    public function setIdFornecedor($idFornecedor)
    {
        $this->idFornecedor = $idFornecedor;
    }

    public function getEmpresa()
    {
        return $this->empresa;
    }

    public function setEmpresa($empresa)
    {
        $this->empresa = $empresa;
    }

    public function getRepresentante()
    {
        return $this->representante;
    }

    public function setRepresentante($representante)
    {
        $this->representante = $representante;
    }

    public function getTelefone()
    {
        return $this->telefone;
    }

    public function setTelefone($telefone)
    {
        $this->telefone = $telefone;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function getEndereco()
    {
        return $this->endereco;
    }

    public function setEndereco($endereco)
    {
        $this->endereco = $endereco;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
?>
