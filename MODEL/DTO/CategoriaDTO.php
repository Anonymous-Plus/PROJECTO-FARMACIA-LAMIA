<?php

class CategoriaDTO implements JsonSerializable
{
private $idCategoria;
private $nomeCategoria;
private $descricao;

public function getIdCategoria()
{
    return $this->idCategoria;
}

public function setIdCategoria($idCategoria)
{
    $this->idCategoria = $idCategoria;
}

public function getNomeCategoria()
{
    return $this->nomeCategoria;
}

public function setNomeCategoria($nomeCategoria)
{
    $this->nomeCategoria = $nomeCategoria;
}

public function getDescricao()
{
    return $this->descricao;
}

public function setDescricao($descricao)
{
    $this->descricao = $descricao;
}

public function jsonSerialize(): array
{
    return get_object_vars($this);
}
}
?>
