<?php

class VendaDTO implements JsonSerializable
{
    private $idVenda;
    private $dataVenda;
    private $valorTotal;
    private $formaPagamento;
    private $idFuncionario;
    private $idCliente;

    public function getIdVenda()
    {
        return $this->idVenda;
    }

    public function setIdVenda($idVenda)
    {
        $this->idVenda = $idVenda;
    }

    public function getDataVenda()
    {
        return $this->dataVenda;
    }

    public function setDataVenda($dataVenda)
    {
        $this->dataVenda = $dataVenda;
    }

    public function getValorTotal()
    {
        return $this->valorTotal;
    }

    public function setValorTotal($valorTotal)
    {
        $this->valorTotal = $valorTotal;
    }

    public function getFormaPagamento()
    {
        return $this->formaPagamento;
    }

    public function setFormaPagamento($formaPagamento)
    {
        $this->formaPagamento = $formaPagamento;
    }

    public function getIdFuncionario()
    {
        return $this->idFuncionario;
    }

    public function setIdFuncionario($idFuncionario)
    {
        $this->idFuncionario = $idFuncionario;
    }

    public function getIdCliente()
    {
        return $this->idCliente;
    }

    public function setIdCliente($idCliente)
    {
        $this->idCliente = $idCliente;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
?>
