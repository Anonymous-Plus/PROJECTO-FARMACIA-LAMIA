<?php

class MedicamentoDTO implements JsonSerializable
{
private $idMedicamento;
private $nome;
private $descricao;
private $principioAtivo;
private $dosagem;
private $precoCompra;
private $precoVenda;
private $quantidadeEstoque;
private $estoqueMinimo;
private $dataFabricacao;
private $dataValidade;
private $necessitaReceita;
private $idCategoria;
private $idFornecedor;

    public function getIdMedicamento()
    {
        return $this->idMedicamento;
    }

    public function setIdMedicamento($idMedicamento)
    {
        $this->idMedicamento = $idMedicamento;
    }

    public function getNome()
    {
        return $this->nome;
    }

    public function setNome($nome)
    {
        $this->nome = $nome;
    }

    public function getDescricao()
    {
        return $this->descricao;
    }

    public function setDescricao($descricao)
    {
        $this->descricao = $descricao;
    }
    public function getPrincipioAtivo()
    {
        return $this->principioAtivo;
    }

    public function setPrincipioAtivo($principioAtivo)
    {
        $this->principioAtivo = $principioAtivo;
    }
    public function getDosagem()
    {
        return $this->dosagem;
    }

    public function setDosagem($dosagem)
    {
        $this->dosagem = $dosagem;
    }
    public function getPrecoCompra()
    {
        return $this->precoCompra;
    }

    public function setPrecoCompra($precoCompra)
    {
        $this->precoCompra = $precoCompra;
    }
    public function getPrecoVenda()
    {
        return $this->precoVenda;
    }

    public function setPrecoVenda($precoVenda)
    {
        $this->precoVenda = $precoVenda;
    }
    public function getQuantidadeEstoque()
    {
        return $this->quantidadeEstoque;
    }

    public function setQuantidadeEstoque($quantidadeEstoque)
    {
        $this->quantidadeEstoque = $quantidadeEstoque;
    }
    public function getEstoqueMinimo()
    {
        return $this->estoqueMinimo;
    }

    public function setEstoqueMinimo($estoqueMinimo)
    {
        $this->estoqueMinimo = $estoqueMinimo;
    }
    public function getDataFabricacao()
    {
        return $this->dataFabricacao;
    }
    public function setDataFabricacao($dataFabricacao)
    {
        $this->dataFabricacao = $dataFabricacao;
    }
    public function getDataValidade()
    {
        return $this->dataValidade;
    }
    public function setDataValidade($dataValidade)
    {
        $this->dataValidade = $dataValidade;
    }
    public function getNecessitaReceita()
    {
        return $this->necessitaReceita;
    }
    public function setNecessitaReceita($necessitaReceita)
    {
        $this->necessitaReceita = $necessitaReceita;
    }
    public function getIdCategoria()
    {
        return $this->idCategoria;
    }
    public function setIdCategoria($idCategoria)
    {
        $this->idCategoria = $idCategoria;
    }
    public function getIdFornecedor()
    {
        return $this->idFornecedor;
    }
    public function setIdFornecedor($idFornecedor)
    {
        $this->idFornecedor = $idFornecedor;
    }

    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
?>
