<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/ReceitaDTO.php';

class ReceitaDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra uma nova receita
     * @param ReceitaDTO $receita
     * @return bool|int - retorna o ID da nova receita ou false em caso de erro
     */
    public function cadastrar(ReceitaDTO $receita)
    {
        try {
            // Verifica se o número da receita já existe
            if ($this->numeroReceitaExiste($receita->getNumeroReceita())) {
                error_log("Receita com este número já existe: " . $receita->getNumeroReceita());
                return false;
            }

            $sql = "INSERT INTO receita (numeroReceita, medico, crm, dataReceita, observacao, idCliente) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $parametros = [
                $receita->getNumeroReceita(),
                $receita->getMedico(),
                $receita->getCrm(),
                $receita->getDataReceita(),
                $receita->getObservacao(),
                $receita->getIdCliente()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca uma receita pelo ID
     * @param int $idReceita
     * @return ReceitaDTO|null
     */
    public function buscarPorId($idReceita)
    {
        try {
            $sql = "SELECT r.*, c.nome as nomeCliente
                    FROM receita r
                    LEFT JOIN cliente c ON r.idCliente = c.idCliente
                    WHERE r.idReceita = ?";
            $resultado = $this->bd->buscarUm($sql, [$idReceita]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca uma receita pelo número
     * @param string $numeroReceita
     * @return ReceitaDTO|null
     */
    public function buscarPorNumero($numeroReceita)
    {
        try {
            $sql = "SELECT * FROM receita WHERE numeroReceita = ?";
            $resultado = $this->bd->buscarUm($sql, [$numeroReceita]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em buscarPorNumero(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca receitas de um cliente
     * @param int $idCliente
     * @return array
     */
    public function buscarPorCliente($idCliente)
    {
        try {
            $sql = "SELECT r.*, c.nome as nomeCliente
                    FROM receita r
                    JOIN cliente c ON r.idCliente = c.idCliente
                    WHERE r.idCliente = ?
                    ORDER BY r.dataReceita DESC";

            $resultados = $this->bd->buscarTodos($sql, [$idCliente]);

            $receitas = [];
            foreach ($resultados as $linha) {
                $receitas[] = $this->construirDTO($linha);
            }

            return $receitas;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em buscarPorCliente(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca receitas de um médico
     * @param string $medico
     * @return array
     */
    public function buscarPorMedico($medico)
    {
        try {
            $sql = "SELECT r.*, c.nome as nomeCliente
                    FROM receita r
                    LEFT JOIN cliente c ON r.idCliente = c.idCliente
                    WHERE r.medico LIKE ?
                    ORDER BY r.dataReceita DESC";

            $medicoPesquisa = '%' . $medico . '%';
            $resultados = $this->bd->buscarTodos($sql, [$medicoPesquisa]);

            $receitas = [];
            foreach ($resultados as $linha) {
                $receitas[] = $this->construirDTO($linha);
            }

            return $receitas;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em buscarPorMedico(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca receitas por CRM
     * @param string $crm
     * @return array
     */
    public function buscarPorCrm($crm)
    {
        try {
            $sql = "SELECT r.*, c.nome as nomeCliente
                    FROM receita r
                    LEFT JOIN cliente c ON r.idCliente = c.idCliente
                    WHERE r.crm = ?
                    ORDER BY r.dataReceita DESC";

            $resultados = $this->bd->buscarTodos($sql, [$crm]);

            $receitas = [];
            foreach ($resultados as $linha) {
                $receitas[] = $this->construirDTO($linha);
            }

            return $receitas;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em buscarPorCrm(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se o número da receita existe
     * @param string $numeroReceita
     * @return bool
     */
    public function numeroReceitaExiste($numeroReceita)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM receita WHERE numeroReceita = ?";
            $resultado = $this->bd->buscarUm($sql, [$numeroReceita]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em numeroReceitaExiste(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todas as receitas
     * @return array
     */
    public function listarTodas()
    {
        try {
            $sql = "SELECT r.*, c.nome as nomeCliente
                    FROM receita r
                    LEFT JOIN cliente c ON r.idCliente = c.idCliente
                    ORDER BY r.dataReceita DESC";

            $resultados = $this->bd->buscarTodos($sql);

            $receitas = [];
            foreach ($resultados as $linha) {
                $receitas[] = $this->construirDTO($linha);
            }

            return $receitas;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em listarTodas(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista receitas por período
     * @param string $dataInicio (YYYY-MM-DD)
     * @param string $dataFim (YYYY-MM-DD)
     * @return array
     */
    public function listarPorPeriodo($dataInicio, $dataFim)
    {
        try {
            $sql = "SELECT r.*, c.nome as nomeCliente
                    FROM receita r
                    LEFT JOIN cliente c ON r.idCliente = c.idCliente
                    WHERE DATE(r.dataReceita) BETWEEN ? AND ?
                    ORDER BY r.dataReceita DESC";

            $resultados = $this->bd->buscarTodos($sql, [$dataInicio, $dataFim]);

            $receitas = [];
            foreach ($resultados as $linha) {
                $receitas[] = $this->construirDTO($linha);
            }

            return $receitas;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em listarPorPeriodo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista receitas com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT r.*, c.nome as nomeCliente
                    FROM receita r
                    LEFT JOIN cliente c ON r.idCliente = c.idCliente
                    ORDER BY r.dataReceita DESC
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $receitas = [];
            foreach ($resultados as $linha) {
                $receitas[] = $this->construirDTO($linha);
            }

            return $receitas;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de receitas
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM receita";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna o total de receitas de um cliente
     * @param int $idCliente
     * @return int
     */
    public function contarPorCliente($idCliente)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM receita WHERE idCliente = ?";
            $resultado = $this->bd->buscarUm($sql, [$idCliente]);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em contarPorCliente(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna o total de receitas de um médico
     * @param string $medico
     * @return int
     */
    public function contarPorMedico($medico)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM receita WHERE medico LIKE ?";
            $medicoPesquisa = '%' . $medico . '%';
            $resultado = $this->bd->buscarUm($sql, [$medicoPesquisa]);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em contarPorMedico(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de uma receita
     * @param ReceitaDTO $receita
     * @return bool
     */
    public function atualizar(ReceitaDTO $receita)
    {
        try {
            $sql = "UPDATE receita 
                    SET numeroReceita = ?, medico = ?, crm = ?, dataReceita = ?, observacao = ?, idCliente = ? 
                    WHERE idReceita = ?";

            $parametros = [
                $receita->getNumeroReceita(),
                $receita->getMedico(),
                $receita->getCrm(),
                $receita->getDataReceita(),
                $receita->getObservacao(),
                $receita->getIdCliente(),
                $receita->getIdReceita()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas a observação de uma receita
     * @param int $idReceita
     * @param string $novaObservacao
     * @return bool
     */
    public function atualizarObservacao($idReceita, $novaObservacao)
    {
        try {
            $sql = "UPDATE receita SET observacao = ? WHERE idReceita = ?";
            $parametros = [$novaObservacao, $idReceita];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em atualizarObservacao(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o cliente de uma receita
     * @param int $idReceita
     * @param int $novoIdCliente
     * @return bool
     */
    public function atualizarCliente($idReceita, $novoIdCliente)
    {
        try {
            $sql = "UPDATE receita SET idCliente = ? WHERE idReceita = ?";
            $parametros = [$novoIdCliente, $idReceita];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em atualizarCliente(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas a data de uma receita
     * @param int $idReceita
     * @param string $novaData
     * @return bool
     */
    public function atualizarData($idReceita, $novaData)
    {
        try {
            $sql = "UPDATE receita SET dataReceita = ? WHERE idReceita = ?";
            $parametros = [$novaData, $idReceita];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em atualizarData(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga uma receita
     * @param int $idReceita
     * @return bool
     */
    public function apagar($idReceita)
    {
        try {
            $sql = "DELETE FROM receita WHERE idReceita = ?";
            $parametros = [$idReceita];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga todas as receitas de um cliente
     * @param int $idCliente
     * @return bool
     */
    public function apagarPorCliente($idCliente)
    {
        try {
            $sql = "DELETE FROM receita WHERE idCliente = ?";
            $parametros = [$idCliente];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ReceitaDAO - Erro em apagarPorCliente(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto ReceitaDTO a partir de um array
     * @param array $dados
     * @return ReceitaDTO
     */
    private function construirDTO($dados)
    {
        $receita = new ReceitaDTO();
        $receita->setIdReceita($dados['idReceita']);
        $receita->setNumeroReceita($dados['numeroReceita']);
        $receita->setMedico($dados['medico']);
        $receita->setCrm($dados['crm']);
        $receita->setDataReceita($dados['dataReceita']);
        $receita->setObservacao($dados['observacao']);
        $receita->setIdCliente($dados['idCliente']);
        $receita->setNomeCliente($dados['nomeCliente'] ?? null);

        return $receita;
    }
}
