<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/MedicamentoDTO.php';

class MedicamentoDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra um novo medicamento
     * @param MedicamentoDTO $medicamento
     * @return bool|int - retorna o ID do novo medicamento ou false em caso de erro
     */
    public function cadastrar(MedicamentoDTO $medicamento)
    {
        try {
            $sql = "INSERT INTO medicamento (nome, descricao, principioAtivo, dosagem, precoCompra, 
                    precoVenda, quantidadeEstoque, estoqueMinimo, dataFabricacao, dataValidade, 
                    necessitaReceita, idCategoria, idFornecedor) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $parametros = [
                $medicamento->getNome(),
                $medicamento->getDescricao(),
                $medicamento->getPrincipioAtivo(),
                $medicamento->getDosagem(),
                $medicamento->getPrecoCompra(),
                $medicamento->getPrecoVenda(),
                $medicamento->getQuantidadeEstoque(),
                $medicamento->getEstoqueMinimo(),
                $medicamento->getDataFabricacao(),
                $medicamento->getDataValidade(),
                $medicamento->getNecessitaReceita(),
                $medicamento->getIdCategoria(),
                $medicamento->getIdFornecedor()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca um medicamento pelo ID
     * @param int $idMedicamento
     * @return MedicamentoDTO|null
     */
    public function buscarPorId($idMedicamento)
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.idMedicamento = ?";

            $resultado = $this->bd->buscarUm($sql, [$idMedicamento]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um medicamento pelo nome
     * @param string $nome
     * @return MedicamentoDTO|null
     */
    public function buscarPorNome($nome)
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.nome = ?";

            $resultado = $this->bd->buscarUm($sql, [$nome]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarPorNome(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca medicamentos por princípio ativo
     * @param string $principioAtivo
     * @return array
     */
    public function buscarPorPrincipioAtivo($principioAtivo)
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.principioAtivo LIKE ?
                    ORDER BY m.nome ASC";

            $principioPesquisa = '%' . $principioAtivo . '%';
            $resultados = $this->bd->buscarTodos($sql, [$principioPesquisa]);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarPorPrincipioAtivo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca medicamentos por categoria
     * @param int $idCategoria
     * @return array
     */
    public function buscarPorCategoria($idCategoria)
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.idCategoria = ?
                    ORDER BY m.nome ASC";

            $resultados = $this->bd->buscarTodos($sql, [$idCategoria]);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarPorCategoria(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca medicamentos por fornecedor
     * @param int $idFornecedor
     * @return array
     */
    public function buscarPorFornecedor($idFornecedor)
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.idFornecedor = ?
                    ORDER BY m.nome ASC";

            $resultados = $this->bd->buscarTodos($sql, [$idFornecedor]);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarPorFornecedor(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca medicamentos que necessitam receita
     * @return array
     */
    public function buscarMedicamentosComReceita()
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.necessitaReceita = 'Sim'
                    ORDER BY m.nome ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarMedicamentosComReceita(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca medicamentos com estoque abaixo do mínimo
     * @return array
     */
    public function buscarEstoqueAbaixoMinimo()
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.quantidadeEstoque < m.estoqueMinimo
                    ORDER BY m.nome ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarEstoqueAbaixoMinimo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca medicamentos vencidos
     * @return array
     */
    public function buscarMedicamentosVencidos()
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.dataValidade < NOW()
                    ORDER BY m.dataValidade ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarMedicamentosVencidos(): " . $e->getMessage());
            return [];
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todos os medicamentos
     * @return array
     */
    public function listarTodos()
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    ORDER BY m.nome ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em listarTodos(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca medicamentos por nome parcial
     * @param string $nome
     * @return array
     */
    public function buscarPorNomeParcial($nome)
    {
        try {
            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    WHERE m.nome LIKE ?
                    ORDER BY m.nome ASC";

            $nomePesquisa = '%' . $nome . '%';
            $resultados = $this->bd->buscarTodos($sql, [$nomePesquisa]);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em buscarPorNomeParcial(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista medicamentos com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT m.*, c.nomeCategoria, f.empresa as nomeEmpresaFornecedor
                    FROM medicamento m
                    LEFT JOIN categoria c ON m.idCategoria = c.idCategoria
                    LEFT JOIN fornecedor f ON m.idFornecedor = f.idFornecedor
                    ORDER BY m.nome ASC
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de medicamentos
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM medicamento";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o valor total do estoque
     * @return float
     */
    public function calcularValorEstoque()
    {
        try {
            $sql = "SELECT SUM(quantidadeEstoque * precoCompra) as valor FROM medicamento";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['valor']) && $resultado['valor'] ? (float)$resultado['valor'] : 0;
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em calcularValorEstoque(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de um medicamento
     * @param MedicamentoDTO $medicamento
     * @return bool
     */
    public function atualizar(MedicamentoDTO $medicamento)
    {
        try {
            $sql = "UPDATE medicamento 
                    SET nome = ?, descricao = ?, principioAtivo = ?, dosagem = ?, precoCompra = ?, 
                    precoVenda = ?, quantidadeEstoque = ?, estoqueMinimo = ?, dataFabricacao = ?, 
                    dataValidade = ?, necessitaReceita = ?, idCategoria = ?, idFornecedor = ? 
                    WHERE idMedicamento = ?";

            $parametros = [
                $medicamento->getNome(),
                $medicamento->getDescricao(),
                $medicamento->getPrincipioAtivo(),
                $medicamento->getDosagem(),
                $medicamento->getPrecoCompra(),
                $medicamento->getPrecoVenda(),
                $medicamento->getQuantidadeEstoque(),
                $medicamento->getEstoqueMinimo(),
                $medicamento->getDataFabricacao(),
                $medicamento->getDataValidade(),
                $medicamento->getNecessitaReceita(),
                $medicamento->getIdCategoria(),
                $medicamento->getIdFornecedor(),
                $medicamento->getIdMedicamento()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas os preços de um medicamento
     * @param int $idMedicamento
     * @param float $precoCompra
     * @param float $precoVenda
     * @return bool
     */
    public function atualizarPrecos($idMedicamento, $precoCompra, $precoVenda)
    {
        try {
            $sql = "UPDATE medicamento SET precoCompra = ?, precoVenda = ? WHERE idMedicamento = ?";
            $parametros = [$precoCompra, $precoVenda, $idMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em atualizarPrecos(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o estoque de um medicamento
     * @param int $idMedicamento
     * @param int $novaQuantidade
     * @return bool
     */
    public function atualizarEstoque($idMedicamento, $novaQuantidade)
    {
        try {
            $sql = "UPDATE medicamento SET quantidadeEstoque = ? WHERE idMedicamento = ?";
            $parametros = [$novaQuantidade, $idMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em atualizarEstoque(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Incrementa o estoque de um medicamento
     * @param int $idMedicamento
     * @param int $quantidade
     * @return bool
     */
    public function incrementarEstoque($idMedicamento, $quantidade)
    {
        try {
            $sql = "UPDATE medicamento SET quantidadeEstoque = quantidadeEstoque + ? WHERE idMedicamento = ?";
            $parametros = [$quantidade, $idMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em incrementarEstoque(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Decrementa o estoque de um medicamento
     * @param int $idMedicamento
     * @param int $quantidade
     * @return bool
     */
    public function decrementarEstoque($idMedicamento, $quantidade)
    {
        try {
            $sql = "UPDATE medicamento SET quantidadeEstoque = quantidadeEstoque - ? WHERE idMedicamento = ? AND quantidadeEstoque >= ?";
            $parametros = [$quantidade, $idMedicamento, $quantidade];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em decrementarEstoque(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o fornecedor de um medicamento
     * @param int $idMedicamento
     * @param int $novoIdFornecedor
     * @return bool
     */
    public function atualizarFornecedor($idMedicamento, $novoIdFornecedor)
    {
        try {
            $sql = "UPDATE medicamento SET idFornecedor = ? WHERE idMedicamento = ?";
            $parametros = [$novoIdFornecedor, $idMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em atualizarFornecedor(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga um medicamento
     * @param int $idMedicamento
     * @return bool
     */
    public function apagar($idMedicamento)
    {
        try {
            $sql = "DELETE FROM medicamento WHERE idMedicamento = ?";
            $parametros = [$idMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga todos os medicamentos de uma categoria
     * @param int $idCategoria
     * @return bool
     */
    public function apagarPorCategoria($idCategoria)
    {
        try {
            $sql = "DELETE FROM medicamento WHERE idCategoria = ?";
            $parametros = [$idCategoria];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em apagarPorCategoria(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga todos os medicamentos de um fornecedor
     * @param int $idFornecedor
     * @return bool
     */
    public function apagarPorFornecedor($idFornecedor)
    {
        try {
            $sql = "DELETE FROM medicamento WHERE idFornecedor = ?";
            $parametros = [$idFornecedor];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("MedicamentoDAO - Erro em apagarPorFornecedor(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto MedicamentoDTO a partir de um array
     * @param array $dados
     * @return MedicamentoDTO
     */
    private function construirDTO($dados)
    {
        $medicamento = new MedicamentoDTO();
        $medicamento->setIdMedicamento($dados['idMedicamento']);
        $medicamento->setNome($dados['nome']);
        $medicamento->setDescricao($dados['descricao']);
        $medicamento->setPrincipioAtivo($dados['principioAtivo']);
        $medicamento->setDosagem($dados['dosagem']);
        $medicamento->setPrecoCompra($dados['precoCompra']);
        $medicamento->setPrecoVenda($dados['precoVenda']);
        $medicamento->setQuantidadeEstoque($dados['quantidadeEstoque']);
        $medicamento->setEstoqueMinimo($dados['estoqueMinimo']);
        $medicamento->setDataFabricacao($dados['dataFabricacao']);
        $medicamento->setDataValidade($dados['dataValidade']);
        $medicamento->setNecessitaReceita($dados['necessitaReceita']);
        $medicamento->setIdCategoria($dados['idCategoria']);
        $medicamento->setIdFornecedor($dados['idFornecedor']);

        return $medicamento;
    }
}
