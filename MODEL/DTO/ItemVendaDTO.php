<?php

class ItemVendaDTO implements JsonSerializable
{
    private $idItem;
    private $idVenda;
    private $idMedicamento;
    private $quantidade;
    private $precoUnitario;
    private $subtotal;

    public function getIdItem()
    {
        return $this->idItem;
    }

    public function setIdItem($idItem)
    {
        $this->idItem = $idItem;
    }

    public function getIdVenda()
    {
        return $this->idVenda;
    }

    public function setIdVenda($idVenda)
    {
        $this->idVenda = $idVenda;
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

    public function getPrecoUnitario()
    {
        return $this->precoUnitario;
    }

    public function setPrecoUnitario($precoUnitario)
    {
        $this->precoUnitario = $precoUnitario;
    }

    public function getSubtotal()
    {
        return $this->subtotal;
    }

    public function setSubtotal($subtotal)
    {
        $this->subtotal = $subtotal;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
?>
