<?php

class ReceitaDTO implements JsonSerializable
{
    private $idReceita;
    private $numeroReceita;
    private $medico;
    private $crm;
    private $dataReceita;
    private $observacao;
    private $idCliente;
    private $nomeCliente;

    public function getIdReceita()
    {
        return $this->idReceita;
    }

    public function setIdReceita($idReceita)
    {
        $this->idReceita = $idReceita;
    }

    public function getNumeroReceita()
    {
        return $this->numeroReceita;
    }

    public function setNumeroReceita($numeroReceita)
    {
        $this->numeroReceita = $numeroReceita;
    }

    public function getMedico()
    {
        return $this->medico;
    }

    public function setMedico($medico)
    {
        $this->medico = $medico;
    }

    public function getCrm()
    {
        return $this->crm;
    }

    public function setCrm($crm)
    {
        $this->crm = $crm;
    }

    public function getDataReceita()
    {
        return $this->dataReceita;
    }

    public function setDataReceita($dataReceita)
    {
        $this->dataReceita = $dataReceita;
    }

    public function getObservacao()
    {
        return $this->observacao;
    }

    public function setObservacao($observacao)
    {
        $this->observacao = $observacao;
    }

    public function getIdCliente()
    {
        return $this->idCliente;
    }

    public function setIdCliente($idCliente)
    {
        $this->idCliente = $idCliente;
    }

    public function getNomeCliente()
    {
        return $this->nomeCliente;
    }

    public function setNomeCliente($nomeCliente)
    {
        $this->nomeCliente = $nomeCliente;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
?>
