<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/FuncionarioDTO.php';

class FuncionarioDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra um novo funcionário
     * @param FuncionarioDTO $funcionario
     * @return bool|int - retorna o ID do novo funcionário ou false em caso de erro
     */
    public function cadastrar(FuncionarioDTO $funcionario)
    {
        try {
            // Verifica se o email já existe
            if ($this->emailExiste($funcionario->getEmail())) {
                error_log("Email já cadastrado: " . $funcionario->getEmail());
                return false;
            }

            $sql = "INSERT INTO funcionario (nome, sexo, dataNascimento, telefone, email, 
                    cargo, salario, dataAdmissao, endereco) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";

            $parametros = [
                $funcionario->getNome(),
                $funcionario->getSexo(),
                $funcionario->getDataNascimento(),
                $funcionario->getTelefone(),
                $funcionario->getEmail(),
                $funcionario->getCargo(),
                $funcionario->getSalario(),
                $funcionario->getDataAdmissao(),
                $funcionario->getEndereco()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca um funcionário pelo ID
     * @param int $idFuncionario
     * @return FuncionarioDTO|null
     */
    public function buscarPorId($idFuncionario)
    {
        try {
            $sql = "SELECT * FROM funcionario WHERE idFuncionario = ?";
            $resultado = $this->bd->buscarUm($sql, [$idFuncionario]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um funcionário pelo email
     * @param string $email
     * @return FuncionarioDTO|null
     */
    public function buscarPorEmail($email)
    {
        try {
            $sql = "SELECT * FROM funcionario WHERE email = ?";
            $resultado = $this->bd->buscarUm($sql, [$email]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em buscarPorEmail(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um funcionário pelo nome
     * @param string $nome
     * @return FuncionarioDTO|null
     */
    public function buscarPorNome($nome)
    {
        try {
            $sql = "SELECT * FROM funcionario WHERE nome = ?";
            $resultado = $this->bd->buscarUm($sql, [$nome]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em buscarPorNome(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca funcionários por nome parcial
     * @param string $nome
     * @return array
     */
    public function buscarPorNomeParcial($nome)
    {
        try {
            $sql = "SELECT * FROM funcionario WHERE nome LIKE ? ORDER BY nome ASC";
            $nomePesquisa = '%' . $nome . '%';
            $resultados = $this->bd->buscarTodos($sql, [$nomePesquisa]);

            $funcionarios = [];
            foreach ($resultados as $linha) {
                $funcionarios[] = $this->construirDTO($linha);
            }

            return $funcionarios;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em buscarPorNomeParcial(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca funcionários por cargo
     * @param string $cargo
     * @return array
     */
    public function buscarPorCargo($cargo)
    {
        try {
            $sql = "SELECT * FROM funcionario WHERE cargo = ? ORDER BY nome ASC";
            $resultados = $this->bd->buscarTodos($sql, [$cargo]);

            $funcionarios = [];
            foreach ($resultados as $linha) {
                $funcionarios[] = $this->construirDTO($linha);
            }

            return $funcionarios;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em buscarPorCargo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca funcionários por sexo
     * @param string $sexo
     * @return array
     */
    public function buscarPorSexo($sexo)
    {
        try {
            $sql = "SELECT * FROM funcionario WHERE sexo = ? ORDER BY nome ASC";
            $resultados = $this->bd->buscarTodos($sql, [$sexo]);

            $funcionarios = [];
            foreach ($resultados as $linha) {
                $funcionarios[] = $this->construirDTO($linha);
            }

            return $funcionarios;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em buscarPorSexo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca funcionários por telefone
     * @param string $telefone
     * @return array
     */
    public function buscarPorTelefone($telefone)
    {
        try {
            $sql = "SELECT * FROM funcionario WHERE telefone LIKE ? ORDER BY nome ASC";
            $telefonePesquisa = '%' . $telefone . '%';
            $resultados = $this->bd->buscarTodos($sql, [$telefonePesquisa]);

            $funcionarios = [];
            foreach ($resultados as $linha) {
                $funcionarios[] = $this->construirDTO($linha);
            }

            return $funcionarios;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em buscarPorTelefone(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Verifica se um email já existe
     * @param string $email
     * @return bool
     */
    public function emailExiste($email)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM funcionario WHERE email = ?";
            $resultado = $this->bd->buscarUm($sql, [$email]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em emailExiste(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todos os funcionários
     * @return array
     */
    public function listarTodos()
    {
        try {
            $sql = "SELECT * FROM funcionario ORDER BY nome ASC";
            $resultados = $this->bd->buscarTodos($sql);

            $funcionarios = [];
            foreach ($resultados as $linha) {
                $funcionarios[] = $this->construirDTO($linha);
            }

            return $funcionarios;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em listarTodos(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista funcionários com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT * FROM funcionario 
                    ORDER BY nome ASC
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $funcionarios = [];
            foreach ($resultados as $linha) {
                $funcionarios[] = $this->construirDTO($linha);
            }

            return $funcionarios;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista funcionários por intervalo de data de admissão
     * @param string $dataInicio (YYYY-MM-DD)
     * @param string $dataFim (YYYY-MM-DD)
     * @return array
     */
    public function listarPorDataAdmissao($dataInicio, $dataFim)
    {
        try {
            $sql = "SELECT * FROM funcionario 
                    WHERE DATE(dataAdmissao) BETWEEN ? AND ?
                    ORDER BY dataAdmissao DESC";

            $resultados = $this->bd->buscarTodos($sql, [$dataInicio, $dataFim]);

            $funcionarios = [];
            foreach ($resultados as $linha) {
                $funcionarios[] = $this->construirDTO($linha);
            }

            return $funcionarios;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em listarPorDataAdmissao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de funcionários
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM funcionario";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Retorna o total de funcionários por cargo
     * @param string $cargo
     * @return int
     */
    public function contarPorCargo($cargo)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM funcionario WHERE cargo = ?";
            $resultado = $this->bd->buscarUm($sql, [$cargo]);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em contarPorCargo(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula a folha de pagamento total
     * @return float
     */
    public function calcularFolhaPagamento()
    {
        try {
            $sql = "SELECT SUM(salario) as total FROM funcionario";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em calcularFolhaPagamento(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula a folha de pagamento por cargo
     * @param string $cargo
     * @return float
     */
    public function calcularFolhaPagamentoPorCargo($cargo)
    {
        try {
            $sql = "SELECT SUM(salario) as total FROM funcionario WHERE cargo = ?";
            $resultado = $this->bd->buscarUm($sql, [$cargo]);

            return isset($resultado['total']) && $resultado['total'] ? (float)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em calcularFolhaPagamentoPorCargo(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Calcula o salário médio
     * @return float
     */
    public function calcularSalarioMedio()
    {
        try {
            $sql = "SELECT AVG(salario) as media FROM funcionario";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['media']) && $resultado['media'] ? (float)$resultado['media'] : 0;
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em calcularSalarioMedio(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de um funcionário
     * @param FuncionarioDTO $funcionario
     * @return bool
     */
    public function atualizar(FuncionarioDTO $funcionario)
    {
        try {
            $sql = "UPDATE funcionario 
                    SET nome = ?, sexo = ?, dataNascimento = ?, telefone = ?, email = ?, 
                    cargo = ?, salario = ?, dataAdmissao = ?, endereco = ? 
                    WHERE idFuncionario = ?";

            $parametros = [
                $funcionario->getNome(),
                $funcionario->getSexo(),
                $funcionario->getDataNascimento(),
                $funcionario->getTelefone(),
                $funcionario->getEmail(),
                $funcionario->getCargo(),
                $funcionario->getSalario(),
                $funcionario->getDataAdmissao(),
                $funcionario->getEndereco(),
                $funcionario->getIdFuncionario()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o cargo de um funcionário
     * @param int $idFuncionario
     * @param string $novoCargo
     * @return bool
     */
    public function atualizarCargo($idFuncionario, $novoCargo)
    {
        try {
            $sql = "UPDATE funcionario SET cargo = ? WHERE idFuncionario = ?";
            $parametros = [$novoCargo, $idFuncionario];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em atualizarCargo(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o salário de um funcionário
     * @param int $idFuncionario
     * @param float $novoSalario
     * @return bool
     */
    public function atualizarSalario($idFuncionario, $novoSalario)
    {
        try {
            $sql = "UPDATE funcionario SET salario = ? WHERE idFuncionario = ?";
            $parametros = [$novoSalario, $idFuncionario];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em atualizarSalario(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o email de um funcionário
     * @param int $idFuncionario
     * @param string $novoEmail
     * @return bool
     */
    public function atualizarEmail($idFuncionario, $novoEmail)
    {
        try {
            // Verifica se o novo email já existe em outro funcionário
            if ($this->emailExiste($novoEmail)) {
                $funcionarioExistente = $this->buscarPorEmail($novoEmail);
                if ($funcionarioExistente && $funcionarioExistente->getIdFuncionario() != $idFuncionario) {
                    error_log("Email já cadastrado para outro funcionário: " . $novoEmail);
                    return false;
                }
            }

            $sql = "UPDATE funcionario SET email = ? WHERE idFuncionario = ?";
            $parametros = [$novoEmail, $idFuncionario];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em atualizarEmail(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o telefone de um funcionário
     * @param int $idFuncionario
     * @param string $novoTelefone
     * @return bool
     */
    public function atualizarTelefone($idFuncionario, $novoTelefone)
    {
        try {
            $sql = "UPDATE funcionario SET telefone = ? WHERE idFuncionario = ?";
            $parametros = [$novoTelefone, $idFuncionario];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em atualizarTelefone(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o endereço de um funcionário
     * @param int $idFuncionario
     * @param string $novoEndereco
     * @return bool
     */
    public function atualizarEndereco($idFuncionario, $novoEndereco)
    {
        try {
            $sql = "UPDATE funcionario SET endereco = ? WHERE idFuncionario = ?";
            $parametros = [$novoEndereco, $idFuncionario];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em atualizarEndereco(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga um funcionário
     * @param int $idFuncionario
     * @return bool
     */
    public function apagar($idFuncionario)
    {
        try {
            $sql = "DELETE FROM funcionario WHERE idFuncionario = ?";
            $parametros = [$idFuncionario];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga todos os funcionários de um cargo
     * @param string $cargo
     * @return bool
     */
    public function apagarPorCargo($cargo)
    {
        try {
            $sql = "DELETE FROM funcionario WHERE cargo = ?";
            $parametros = [$cargo];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FuncionarioDAO - Erro em apagarPorCargo(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto FuncionarioDTO a partir de um array
     * @param array $dados
     * @return FuncionarioDTO
     */
    private function construirDTO($dados)
    {
        $funcionario = new FuncionarioDTO();
        $funcionario->setIdFuncionario($dados['idFuncionario']);
        $funcionario->setNome($dados['nome']);
        $funcionario->setSexo($dados['sexo']);
        $funcionario->setDataNascimento($dados['dataNascimento']);
        $funcionario->setTelefone($dados['telefone']);
        $funcionario->setEmail($dados['email']);
        $funcionario->setCargo($dados['cargo']);
        $funcionario->setSalario($dados['salario']);
        $funcionario->setDataAdmissao($dados['dataAdmissao']);
        $funcionario->setEndereco($dados['endereco']);

        return $funcionario;
    }
}
