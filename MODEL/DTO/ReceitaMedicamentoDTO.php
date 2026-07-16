<?php

class ReceitaMedicamentoDTO implements JsonSerializable
{
    private $idReceitaMedicamento;
    private $idReceita;
    private $idMedicamento;
    private $quantidade;

    public function getIdReceitaMedicamento()
    {
        return $this->idReceitaMedicamento;
    }

    public function setIdReceitaMedicamento($idReceitaMedicamento)
    {
        $this->idReceitaMedicamento = $idReceitaMedicamento;
    }

    public function getIdReceita()
    {
        return $this->idReceita;
    }

    public function setIdReceita($idReceita)
    {
        $this->idReceita = $idReceita;
    }

    public function getIdMedicamento()
    {
        return $this->idMedicamento;
    }

    public function setIdMedicamento($idMedicamento)
    {
        $this->idMedicamento = $idMedicamento;
    }

    public function getQuantidade()
    {
        return $this->quantidade;
    }

    public function setQuantidade($quantidade)
    {
        $this->quantidade = $quantidade;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
?>
