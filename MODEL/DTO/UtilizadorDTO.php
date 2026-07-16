<?php

class UtilizadorDTO implements JsonSerializable
{
private $idUtilizador;
private $username;
private $senha;
private $nivel;
private $estado;
private $idFuncionario;
    public function getIdUtilizador()
    {
        return $this->idUtilizador;
    }

    public function setIdUtilizador($idUtilizador)
    {
        $this->idUtilizador = $idUtilizador;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function getSenha()
    {
        return $this->senha;
    }

    public function setSenha($senha)
    {
        $this->senha = $senha;
    }

    public function getNivel()
    {
        return $this->nivel;
    }

    public function setNivel($nivel)
    {
        $this->nivel = $nivel;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    public function getIdFuncionario()
    {
        return $this->idFuncionario;
    }

    public function setIdFuncionario($idFuncionario)
    {
        $this->idFuncionario = $idFuncionario;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
