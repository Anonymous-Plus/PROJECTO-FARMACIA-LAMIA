<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/ReceitaMedicamentoDTO.php';

class ReceitaMedicamentoDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra um novo medicamento numa receita
     * @param ReceitaMedicamentoDTO $receitaMedicamento
     * @return bool|int - retorna o ID do novo registo ou false em caso de erro
     */
    public function cadastrar(ReceitaMedicamentoDTO $receitaMedicamento)
    {
        try {
            // Verifica se o medicamento já existe na receita
            if ($this->medicamentoExisteEmReceita($receitaMedicamento->getIdReceita(), $receitaMedicamento->getIdMedicamento())) {
                error_log("Medicamento já existe nesta receita");
                return false;
            }

            $sql = "INSERT INTO receita_medicamento (idReceita, idMedicamento, quantidade) 
                    VALUES (?, ?, ?)";

            $parametros = [
                $receitaMedicamento->getIdReceita(),
                $receitaMedicamento->getIdMedicamento(),
                $receitaMedicamento->getQuantidade()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca um registo pelo ID
     * @param int $idReceitaMedicamento
     * @return ReceitaMedicamentoDTO|null
     */
    public function buscarPorId($idReceitaMedicamento)
    {
        try {
            $sql = "SELECT * FROM receita_medicamento WHERE idReceitaMedicamento = ?";
            $resultado = $this->bd->buscarUm($sql, [$idReceitaMedicamento]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca medicamentos de uma receita
     * @param int $idReceita
     * @return array
     */
    public function buscarMedicamentosReceita($idReceita)
    {
        try {
            $sql = "SELECT rm.*, m.nome, m.principioAtivo, m.descricao
                    FROM receita_medicamento rm
                    JOIN medicamento m ON rm.idMedicamento = m.idMedicamento
                    WHERE rm.idReceita = ?
                    ORDER BY m.nome ASC";

            $resultados = $this->bd->buscarTodos($sql, [$idReceita]);

            $medicamentos = [];
            foreach ($resultados as $linha) {
                $medicamentos[] = $this->construirDTO($linha);
            }

            return $medicamentos;
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em buscarMedicamentosReceita(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um medicamento existe numa receita
     * @param int $idReceita
     * @param int $idMedicamento
     * @return bool
     */
    public function medicamentoExisteEmReceita($idReceita, $idMedicamento)
    {
        try {
            $sql = "SELECT COUNT(*) as total 
                    FROM receita_medicamento 
                    WHERE idReceita = ? AND idMedicamento = ?";

            $resultado = $this->bd->buscarUm($sql, [$idReceita, $idMedicamento]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em medicamentoExisteEmReceita(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todos os medicamentos nas receitas
     * @return array
     */
    public function listarTodos()
    {
        try {
            $sql = "SELECT rm.*, m.nome as nomeMedicamento, r.dataReceita
                    FROM receita_medicamento rm
                    JOIN medicamento m ON rm.idMedicamento = m.idMedicamento
                    JOIN receita r ON rm.idReceita = r.idReceita
                    ORDER BY r.dataReceita DESC, m.nome ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $receitaMedicamentos = [];
            foreach ($resultados as $linha) {
                $receitaMedicamentos[] = $this->construirDTO($linha);
            }

            return $receitaMedicamentos;
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em listarTodos(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista medicamentos de uma receita com paginação
     * @param int $idReceita
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarMedicamentosReceitaPaginado($idReceita, $pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT rm.*, m.nome, m.principioAtivo, m.descricao
                    FROM receita_medicamento rm
                    JOIN medicamento m ON rm.idMedicamento = m.idMedicamento
                    WHERE rm.idReceita = ?
                    ORDER BY m.nome ASC
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                $idReceita,
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
            error_log("ReceitaMedicamentoDAO - Erro em listarMedicamentosReceitaPaginado(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de medicamentos de uma receita
     * @param int $idReceita
     * @return int
     */
    public function contarMedicamentosReceita($idReceita)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM receita_medicamento WHERE idReceita = ?";
            $resultado = $this->bd->buscarUm($sql, [$idReceita]);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em contarMedicamentosReceita(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna o total geral de registos
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM receita_medicamento";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de um medicamento numa receita
     * @param ReceitaMedicamentoDTO $receitaMedicamento
     * @return bool
     */
    public function atualizar(ReceitaMedicamentoDTO $receitaMedicamento)
    {
        try {
            $sql = "UPDATE receita_medicamento 
                    SET quantidade = ? 
                    WHERE idReceitaMedicamento = ?";

            $parametros = [
                $receitaMedicamento->getQuantidade(),
                $receitaMedicamento->getIdReceitaMedicamento()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas a quantidade de um medicamento numa receita
     * @param int $idReceitaMedicamento
     * @param float $novaQuantidade
     * @return bool
     */
    public function atualizarQuantidade($idReceitaMedicamento, $novaQuantidade)
    {
        try {
            $sql = "UPDATE receita_medicamento SET quantidade = ? WHERE idReceitaMedicamento = ?";
            $parametros = [$novaQuantidade, $idReceitaMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em atualizarQuantidade(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga um medicamento de uma receita
     * @param int $idReceitaMedicamento
     * @return bool
     */
    public function apagar($idReceitaMedicamento)
    {
        try {
            $sql = "DELETE FROM receita_medicamento WHERE idReceitaMedicamento = ?";
            $parametros = [$idReceitaMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga todos os medicamentos de uma receita
     * @param int $idReceita
     * @return bool
     */
    public function apagarPorReceita($idReceita)
    {
        try {
            $sql = "DELETE FROM receita_medicamento WHERE idReceita = ?";
            $parametros = [$idReceita];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em apagarPorReceita(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga um medicamento de todas as receitas
     * @param int $idMedicamento
     * @return bool
     */
    public function apagarPorMedicamento($idMedicamento)
    {
        try {
            $sql = "DELETE FROM receita_medicamento WHERE idMedicamento = ?";
            $parametros = [$idMedicamento];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaMedicamentoDAO - Erro em apagarPorMedicamento(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto ReceitaMedicamentoDTO a partir de um array
     * @param array $dados
     * @return ReceitaMedicamentoDTO
     */
    private function construirDTO($dados)
    {
        $receitaMedicamento = new ReceitaMedicamentoDTO();
        $receitaMedicamento->setIdReceitaMedicamento($dados['idReceitaMedicamento']);
        $receitaMedicamento->setIdReceita($dados['idReceita']);
        $receitaMedicamento->setIdMedicamento($dados['idMedicamento']);
        $receitaMedicamento->setQuantidade($dados['quantidade']);

        return $receitaMedicamento;
    }
}
