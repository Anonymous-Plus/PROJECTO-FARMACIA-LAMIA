<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/ItemVendaDTO.php';

class ItemVendaDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra um novo item de venda
     * @param ItemVendaDTO $item
     * @return bool|int - retorna o ID do novo item ou false em caso de erro
     */
    public function cadastrar(ItemVendaDTO $item)
    {
        try {
            // Calcula o subtotal se não estiver preenchido
            $subtotal = $item->getSubtotal() ?: ($item->getQuantidade() * $item->getPrecoUnitario());

            $sql = "INSERT INTO item_venda (idVenda, idMedicamento, quantidade, precoUnitario, subtotal) 
                    VALUES (?, ?, ?, ?, ?)";

            $parametros = [
                $item->getIdVenda(),
                $item->getIdMedicamento(),
                $item->getQuantidade(),
                $item->getPrecoUnitario(),
                $subtotal
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            // Decrementa o estoque do medicamento
            if ($novoId) {
                $this->decrementarEstoqueMedicamento($item->getIdMedicamento(), $item->getQuantidade());
            }

            return $novoId;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca um item de venda pelo ID
     * @param int $idItem
     * @return ItemVendaDTO|null
     */
    public function buscarPorId($idItem)
    {
        try {
            $sql = "SELECT iv.*, m.nome as nomeMedicamento, m.principioAtivo
                    FROM item_venda iv
                    LEFT JOIN medicamento m ON iv.idMedicamento = m.idMedicamento
                    WHERE iv.idItem = ?";

            $resultado = $this->bd->buscarUm($sql, [$idItem]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca todos os itens de uma venda
     * @param int $idVenda
     * @return array
     */
    public function buscarPorVenda($idVenda)
    {
        try {
            $sql = "SELECT iv.*, m.nome as nomeMedicamento, m.principioAtivo, m.dosagem
                    FROM item_venda iv
                    LEFT JOIN medicamento m ON iv.idMedicamento = m.idMedicamento
                    WHERE iv.idVenda = ?
                    ORDER BY iv.idItem ASC";

            $resultados = $this->bd->buscarTodos($sql, [$idVenda]);

            $itens = [];
            foreach ($resultados as $linha) {
                $itens[] = $this->construirDTO($linha);
            }

            return $itens;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em buscarPorVenda(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca todos os itens de um medicamento
     * @param int $idMedicamento
     * @return array
     */
    public function buscarPorMedicamento($idMedicamento)
    {
        try {
            $sql = "SELECT iv.*, m.nome as nomeMedicamento, m.principioAtivo
                    FROM item_venda iv
                    LEFT JOIN medicamento m ON iv.idMedicamento = m.idMedicamento
                    WHERE iv.idMedicamento = ?
                    ORDER BY iv.idVenda DESC";

            $resultados = $this->bd->buscarTodos($sql, [$idMedicamento]);

            $itens = [];
            foreach ($resultados as $linha) {
                $itens[] = $this->construirDTO($linha);
            }

            return $itens;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em buscarPorMedicamento(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um medicamento existe em uma venda
     * @param int $idVenda
     * @param int $idMedicamento
     * @return bool
     */
    public function medicamentoExisteEmVenda($idVenda, $idMedicamento)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM item_venda WHERE idVenda = ? AND idMedicamento = ?";
            $resultado = $this->bd->buscarUm($sql, [$idVenda, $idMedicamento]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em medicamentoExisteEmVenda(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todos os itens de venda
     * @return array
     */
    public function listarTodos()
    {
        try {
            $sql = "SELECT iv.*, m.nome as nomeMedicamento, m.principioAtivo
                    FROM item_venda iv
                    LEFT JOIN medicamento m ON iv.idMedicamento = m.idMedicamento
                    ORDER BY iv.idVenda DESC, iv.idItem ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $itens = [];
            foreach ($resultados as $linha) {
                $itens[] = $this->construirDTO($linha);
            }

            return $itens;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em listarTodos(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista itens com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT iv.*, m.nome as nomeMedicamento, m.principioAtivo
                    FROM item_venda iv
                    LEFT JOIN medicamento m ON iv.idMedicamento = m.idMedicamento
                    ORDER BY iv.idVenda DESC, iv.idItem ASC
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $itens = [];
            foreach ($resultados as $linha) {
                $itens[] = $this->construirDTO($linha);
            }

            return $itens;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de itens
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM item_venda";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna o total de itens de uma venda
     * @param int $idVenda
     * @return int
     */
    public function contarPorVenda($idVenda)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM item_venda WHERE idVenda = ?";
            $resultado = $this->bd->buscarUm($sql, [$idVenda]);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em contarPorVenda(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o total de uma venda
     * @param int $idVenda
     * @return float
     */
    public function calcularTotalVenda($idVenda)
    {
        try {
            $sql = "SELECT SUM(subtotal) as total FROM item_venda WHERE idVenda = ?";
            $resultado = $this->bd->buscarUm($sql, [$idVenda]);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em calcularTotalVenda(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o total de quantidade vendida de um medicamento
     * @param int $idMedicamento
     * @return int
     */
    public function calcularQuantidadeVendida($idMedicamento)
    {
        try {
            $sql = "SELECT SUM(quantidade) as total FROM item_venda WHERE idMedicamento = ?";
            $resultado = $this->bd->buscarUm($sql, [$idMedicamento]);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em calcularQuantidadeVendida(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o faturamento total de um medicamento
     * @param int $idMedicamento
     * @return float
     */
    public function calcularFaturamentoMedicamento($idMedicamento)
    {
        try {
            $sql = "SELECT SUM(subtotal) as total FROM item_venda WHERE idMedicamento = ?";
            $resultado = $this->bd->buscarUm($sql, [$idMedicamento]);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em calcularFaturamentoMedicamento(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de um item de venda
     * @param ItemVendaDTO $item
     * @return bool
     */
    public function atualizar(ItemVendaDTO $item)
    {
        try {
            // Calcula o novo subtotal
            $novoSubtotal = $item->getQuantidade() * $item->getPrecoUnitario();

            $sql = "UPDATE item_venda 
                    SET idVenda = ?, idMedicamento = ?, quantidade = ?, precoUnitario = ?, subtotal = ? 
                    WHERE idItem = ?";

            $parametros = [
                $item->getIdVenda(),
                $item->getIdMedicamento(),
                $item->getQuantidade(),
                $item->getPrecoUnitario(),
                $novoSubtotal,
                $item->getIdItem()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas a quantidade de um item
     * @param int $idItem
     * @param int $novaQuantidade
     * @return bool
     */
    public function atualizarQuantidade($idItem, $novaQuantidade)
    {
        try {
            // Busca o item para obter a quantidade atual
            $item = $this->buscarPorId($idItem);
            if (!$item) {
                return false;
            }

            $diferencaQuantidade = $novaQuantidade - $item->getQuantidade();
            $novoSubtotal = $novaQuantidade * $item->getPrecoUnitario();

            $sql = "UPDATE item_venda SET quantidade = ?, subtotal = ? WHERE idItem = ?";
            $parametros = [$novaQuantidade, $novoSubtotal, $idItem];

            $resultado = $this->bd->executar($sql, $parametros);

            // Atualiza o estoque do medicamento
            if ($resultado && $diferencaQuantidade != 0) {
                if ($diferencaQuantidade > 0) {
                    $this->decrementarEstoqueMedicamento($item->getIdMedicamento(), $diferencaQuantidade);
                } else {
                    $this->incrementarEstoqueMedicamento($item->getIdMedicamento(), abs($diferencaQuantidade));
                }
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em atualizarQuantidade(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o preço unitário de um item
     * @param int $idItem
     * @param float $novoPreco
     * @return bool
     */
    public function atualizarPreco($idItem, $novoPreco)
    {
        try {
            // Busca o item para calcular novo subtotal
            $item = $this->buscarPorId($idItem);
            if (!$item) {
                return false;
            }

            $novoSubtotal = $item->getQuantidade() * $novoPreco;

            $sql = "UPDATE item_venda SET precoUnitario = ?, subtotal = ? WHERE idItem = ?";
            $parametros = [$novoPreco, $novoSubtotal, $idItem];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em atualizarPreco(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga um item de venda
     * @param int $idItem
     * @return bool
     */
    public function apagar($idItem)
    {
        try {
            // Busca o item para recuperar os dados antes de apagar
            $item = $this->buscarPorId($idItem);
            if (!$item) {
                return false;
            }

            $sql = "DELETE FROM item_venda WHERE idItem = ?";
            $parametros = [$idItem];

            $resultado = $this->bd->executar($sql, $parametros);

            // Incrementa o estoque do medicamento
            if ($resultado) {
                $this->incrementarEstoqueMedicamento($item->getIdMedicamento(), $item->getQuantidade());
            }

            return $resultado;
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga todos os itens de uma venda
     * @param int $idVenda
     * @return bool
     */
    public function apagarPorVenda($idVenda)
    {
        try {
            // Busca todos os itens para incrementar estoque
            $itens = $this->buscarPorVenda($idVenda);

            foreach ($itens as $item) {
                $this->incrementarEstoqueMedicamento($item->getIdMedicamento(), $item->getQuantidade());
            }

            $sql = "DELETE FROM item_venda WHERE idVenda = ?";
            $parametros = [$idVenda];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em apagarPorVenda(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto ItemVendaDTO a partir de um array
     * @param array $dados
     * @return ItemVendaDTO
     */
    private function construirDTO($dados)
    {
        $item = new ItemVendaDTO();
        $item->setIdItem($dados['idItem']);
        $item->setIdVenda($dados['idVenda']);
        $item->setIdMedicamento($dados['idMedicamento']);
        $item->setQuantidade($dados['quantidade']);
        $item->setPrecoUnitario($dados['precoUnitario']);
        $item->setSubtotal($dados['subtotal']);

        return $item;
    }

    /**
     * Decrementa o estoque de um medicamento
     * @param int $idMedicamento
     * @param int $quantidade
     */
    private function decrementarEstoqueMedicamento($idMedicamento, $quantidade)
    {
        try {
            $sql = "UPDATE medicamento SET quantidadeEstoque = quantidadeEstoque - ? 
                    WHERE idMedicamento = ? AND quantidadeEstoque >= ?";
            $parametros = [$quantidade, $idMedicamento, $quantidade];

            $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro ao decrementar estoque: " . $e->getMessage());
        }
    }

    /**
     * Incrementa o estoque de um medicamento
     * @param int $idMedicamento
     * @param int $quantidade
     */
    private function incrementarEstoqueMedicamento($idMedicamento, $quantidade)
    {
        try {
            $sql = "UPDATE medicamento SET quantidadeEstoque = quantidadeEstoque + ? 
                    WHERE idMedicamento = ?";
            $parametros = [$quantidade, $idMedicamento];

            $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro ao incrementar estoque: " . $e->getMessage());
        }
    }

    /**
     * Apaga todos os itens de venda de um medicamento
     * @param int $idMedicamento
     * @return bool
     */
    public function apagarPorMedicamento($idMedicamento)
    {
        try {
            $sql = "DELETE FROM item_venda WHERE idMedicamento = ?";
            $parametros = [$idMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ItemVendaDAO - Erro em apagarPorMedicamento(): " . $e->getMessage());
            return false;
        }
    }
}
