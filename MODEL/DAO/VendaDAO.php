<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/VendaDTO.php';

class VendaDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra uma nova venda
     * @param VendaDTO $venda
     * @return bool|int - retorna o ID da nova venda ou false em caso de erro
     */
    public function cadastrar(VendaDTO $venda)
    {
        try {
            $sql = "INSERT INTO venda (dataVenda, valorTotal, formaPagamento, idFuncionario, idCliente) 
                    VALUES (?, ?, ?, ?, ?)";

            $parametros = [
                $venda->getDataVenda(),
                $venda->getValorTotal(),
                $venda->getFormaPagamento(),
                $venda->getIdFuncionario(),
                $venda->getIdCliente()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca uma venda pelo ID
     * @param int $idVenda
     * @return VendaDTO|null
     */
    public function buscarPorId($idVenda)
    {
        try {
            $sql = "SELECT * FROM venda WHERE idVenda = ?";
            $resultado = $this->bd->buscarUm($sql, [$idVenda]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca vendas de um cliente
     * @param int $idCliente
     * @return array
     */
    public function buscarPorCliente($idCliente)
    {
        try {
            $sql = "SELECT * FROM venda WHERE idCliente = ? ORDER BY dataVenda DESC";
            $resultados = $this->bd->buscarTodos($sql, [$idCliente]);

            $vendas = [];
            foreach ($resultados as $linha) {
                $vendas[] = $this->construirDTO($linha);
            }

            return $vendas;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em buscarPorCliente(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca vendas realizadas por um funcionário
     * @param int $idFuncionario
     * @return array
     */
    public function buscarPorFuncionario($idFuncionario)
    {
        try {
            $sql = "SELECT * FROM venda WHERE idFuncionario = ? ORDER BY dataVenda DESC";
            $resultados = $this->bd->buscarTodos($sql, [$idFuncionario]);

            $vendas = [];
            foreach ($resultados as $linha) {
                $vendas[] = $this->construirDTO($linha);
            }

            return $vendas;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em buscarPorFuncionario(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca vendas por forma de pagamento
     * @param string $formaPagamento
     * @return array
     */
    public function buscarPorFormaPagamento($formaPagamento)
    {
        try {
            $sql = "SELECT * FROM venda WHERE formaPagamento = ? ORDER BY dataVenda DESC";
            $resultados = $this->bd->buscarTodos($sql, [$formaPagamento]);

            $vendas = [];
            foreach ($resultados as $linha) {
                $vendas[] = $this->construirDTO($linha);
            }

            return $vendas;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em buscarPorFormaPagamento(): " . $e->getMessage());
            return [];
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todas as vendas
     * @return array
     */
    public function listarTodas()
    {
        try {
            $sql = "SELECT v.*, 
                           c.nome as nomeCliente, 
                           f.nome as nomeFuncionario
                    FROM venda v
                    LEFT JOIN cliente c ON v.idCliente = c.idCliente
                    LEFT JOIN funcionario f ON v.idFuncionario = f.idFuncionario
                    ORDER BY v.dataVenda DESC";

            $resultados = $this->bd->buscarTodos($sql);

            $vendas = [];
            foreach ($resultados as $linha) {
                $vendas[] = $this->construirDTO($linha);
            }

            return $vendas;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em listarTodas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista vendas por período
     * @param string $dataInicio (YYYY-MM-DD)
     * @param string $dataFim (YYYY-MM-DD)
     * @return array
     */
    public function listarPorPeriodo($dataInicio, $dataFim)
    {
        try {
            $sql = "SELECT v.*, 
                           c.nome as nomeCliente, 
                           f.nome as nomeFuncionario
                    FROM venda v
                    LEFT JOIN cliente c ON v.idCliente = c.idCliente
                    LEFT JOIN funcionario f ON v.idFuncionario = f.idFuncionario
                    WHERE DATE(v.dataVenda) BETWEEN ? AND ?
                    ORDER BY v.dataVenda DESC";

            $resultados = $this->bd->buscarTodos($sql, [$dataInicio, $dataFim]);

            $vendas = [];
            foreach ($resultados as $linha) {
                $vendas[] = $this->construirDTO($linha);
            }

            return $vendas;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em listarPorPeriodo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista vendas com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT v.*, 
                           c.nome as nomeCliente, 
                           f.nome as nomeFuncionario
                    FROM venda v
                    LEFT JOIN cliente c ON v.idCliente = c.idCliente
                    LEFT JOIN funcionario f ON v.idFuncionario = f.idFuncionario
                    ORDER BY v.dataVenda DESC
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $vendas = [];
            foreach ($resultados as $linha) {
                $vendas[] = $this->construirDTO($linha);
            }

            return $vendas;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de vendas
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM venda";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o valor total de vendas em um período
     * @param string $dataInicio (YYYY-MM-DD)
     * @param string $dataFim (YYYY-MM-DD)
     * @return float
     */
    public function calcularTotalPeriodo($dataInicio, $dataFim)
    {
        try {
            $sql = "SELECT SUM(valorTotal) as total 
                    FROM venda 
                    WHERE DATE(dataVenda) BETWEEN ? AND ?";

            $resultado = $this->bd->buscarUm($sql, [$dataInicio, $dataFim]);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em calcularTotalPeriodo(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o valor total de vendas de um cliente
     * @param int $idCliente
     * @return float
     */
    public function calcularTotalCliente($idCliente)
    {
        try {
            $sql = "SELECT SUM(valorTotal) as total FROM venda WHERE idCliente = ?";
            $resultado = $this->bd->buscarUm($sql, [$idCliente]);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em calcularTotalCliente(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o valor total de vendas de um funcionário
     * @param int $idFuncionario
     * @return float
     */
    public function calcularTotalFuncionario($idFuncionario)
    {
        try {
            $sql = "SELECT SUM(valorTotal) as total FROM venda WHERE idFuncionario = ?";
            $resultado = $this->bd->buscarUm($sql, [$idFuncionario]);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em calcularTotalFuncionario(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna o total geral de vendas
     * @return float
     */
    public function calcularTotalGeral()
    {
        try {
            $sql = "SELECT SUM(valorTotal) as total FROM venda";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em calcularTotalGeral(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de uma venda
     * @param VendaDTO $venda
     * @return bool
     */
    public function atualizar(VendaDTO $venda)
    {
        try {
            $sql = "UPDATE venda 
                    SET dataVenda = ?, valorTotal = ?, formaPagamento = ?, idFuncionario = ?, idCliente = ? 
                    WHERE idVenda = ?";

            $parametros = [
                $venda->getDataVenda(),
                $venda->getValorTotal(),
                $venda->getFormaPagamento(),
                $venda->getIdFuncionario(),
                $venda->getIdCliente(),
                $venda->getIdVenda()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas a forma de pagamento de uma venda
     * @param int $idVenda
     * @param string $novaFormaPagamento
     * @return bool
     */
    public function atualizarFormaPagamento($idVenda, $novaFormaPagamento)
    {
        try {
            $sql = "UPDATE venda SET formaPagamento = ? WHERE idVenda = ?";
            $parametros = [$novaFormaPagamento, $idVenda];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em atualizarFormaPagamento(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o valor total de uma venda
     * @param int $idVenda
     * @param float $novoValor
     * @return bool
     */
    public function atualizarValorTotal($idVenda, $novoValor)
    {
        try {
            $sql = "UPDATE venda SET valorTotal = ? WHERE idVenda = ?";
            $parametros = [$novoValor, $idVenda];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em atualizarValorTotal(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga uma venda
     * @param int $idVenda
     * @return bool
     */
    public function apagar($idVenda)
    {
        try {
            $sql = "DELETE FROM venda WHERE idVenda = ?";
            $parametros = [$idVenda];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("VendaDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto VendaDTO a partir de um array
     * @param array $dados
     * @return VendaDTO
     */
    private function construirDTO($dados)
    {
        $venda = new VendaDTO();
        $venda->setIdVenda($dados['idVenda']);
        $venda->setDataVenda($dados['dataVenda']);
        $venda->setValorTotal($dados['valorTotal']);
        $venda->setFormaPagamento($dados['formaPagamento']);
        $venda->setIdFuncionario($dados['idFuncionario']);
        $venda->setIdCliente($dados['idCliente']);

        return $venda;
    }
}
