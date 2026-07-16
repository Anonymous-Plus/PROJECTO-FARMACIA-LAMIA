<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/UtilizadorDTO.php';

class UtilizadorDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== AUTENTICAR ====================
    /**
     * Autentica um utilizador com username e senha
     * @param string $username
     * @param string $senha
     * @return UtilizadorDTO|null
     */
    public function autenticar($username, $senha)
    {
        try {
            $sql = "SELECT * FROM utilizador WHERE username = ? AND estado = 'Ativo'";
            $resultado = $this->bd->buscarUm($sql, [$username]);

            if ($resultado && password_verify($senha, $resultado['senha'])) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em autenticar(): " . $e->getMessage());
            return null;
        }
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra um novo utilizador
     * @param UtilizadorDTO $utilizador
     * @return bool|int - retorna o ID do novo utilizador ou false em caso de erro
     */
    public function cadastrar(UtilizadorDTO $utilizador)
    {
        try {
            // Verifica se username já existe
            if ($this->usernameExiste($utilizador->getUsername())) {
                error_log("Username já existe: " . $utilizador->getUsername());
                return false;
            }

            $senhaHash = password_hash($utilizador->getSenha(), PASSWORD_DEFAULT);

            $sql = "INSERT INTO utilizador (username, senha, nivel, estado, idFuncionario) 
                    VALUES (?, ?, ?, ?, ?)";

            $parametros = [
                $utilizador->getUsername(),
                $senhaHash,
                $utilizador->getNivel(),
                $utilizador->getEstado(),
                $utilizador->getIdFuncionario()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca um utilizador pelo ID
     * @param int $idUtilizador
     * @return UtilizadorDTO|null
     */
    public function buscarPorId($idUtilizador)
    {
        try {
            $sql = "SELECT * FROM utilizador WHERE idUtilizador = ?";
            $resultado = $this->bd->buscarUm($sql, [$idUtilizador]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um utilizador pelo username
     * @param string $username
     * @return UtilizadorDTO|null
     */
    public function buscarPorUsername($username)
    {
        try {
            $sql = "SELECT * FROM utilizador WHERE username = ?";
            $resultado = $this->bd->buscarUm($sql, [$username]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em buscarPorUsername(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um utilizador pelo ID do funcionário
     * @param int $idFuncionario
     * @return UtilizadorDTO|null
     */
    public function buscarPorIdFuncionario($idFuncionario)
    {
        try {
            $sql = "SELECT * FROM utilizador WHERE idFuncionario = ?";
            $resultado = $this->bd->buscarUm($sql, [$idFuncionario]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em buscarPorIdFuncionario(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se username já existe
     * @param string $username
     * @return bool
     */
    public function usernameExiste($username)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM utilizador WHERE username = ?";
            $resultado = $this->bd->buscarUm($sql, [$username]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em usernameExiste(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todos os utilizadores
     * @return array
     */
    public function listarTodos()
    {
        try {
            $sql = "SELECT u.*, f.nome as nomeFuncionario 
                    FROM utilizador u 
                    JOIN funcionario f ON u.idFuncionario = f.idFuncionario 
                    ORDER BY u.username ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $utilizadores = [];
            foreach ($resultados as $linha) {
                $utilizadores[] = $this->construirDTO($linha);
            }

            return $utilizadores;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em listarTodos(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista utilizadores por nível
     * @param string $nivel
     * @return array
     */
    public function listarPorNivel($nivel)
    {
        try {
            $sql = "SELECT u.*, f.nome as nomeFuncionario 
                    FROM utilizador u 
                    JOIN funcionario f ON u.idFuncionario = f.idFuncionario 
                    WHERE u.nivel = ? 
                    ORDER BY u.username ASC";

            $resultados = $this->bd->buscarTodos($sql, [$nivel]);

            $utilizadores = [];
            foreach ($resultados as $linha) {
                $utilizadores[] = $this->construirDTO($linha);
            }

            return $utilizadores;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em listarPorNivel(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista utilizadores por estado
     * @param string $estado
     * @return array
     */
    public function listarPorEstado($estado)
    {
        try {
            $sql = "SELECT u.*, f.nome as nomeFuncionario 
                    FROM utilizador u 
                    JOIN funcionario f ON u.idFuncionario = f.idFuncionario 
                    WHERE u.estado = ? 
                    ORDER BY u.username ASC";

            $resultados = $this->bd->buscarTodos($sql, [$estado]);

            $utilizadores = [];
            foreach ($resultados as $linha) {
                $utilizadores[] = $this->construirDTO($linha);
            }

            return $utilizadores;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em listarPorEstado(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista utilizadores com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT u.*, f.nome as nomeFuncionario 
                    FROM utilizador u 
                    JOIN funcionario f ON u.idFuncionario = f.idFuncionario 
                    ORDER BY u.username ASC 
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $utilizadores = [];
            foreach ($resultados as $linha) {
                $utilizadores[] = $this->construirDTO($linha);
            }

            return $utilizadores;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de utilizadores
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM utilizador";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de um utilizador
     * @param UtilizadorDTO $utilizador
     * @return bool
     */
    public function atualizar(UtilizadorDTO $utilizador)
    {
        try {
            $sql = "UPDATE utilizador 
                    SET username = ?, nivel = ?, estado = ?, idFuncionario = ? 
                    WHERE idUtilizador = ?";

            $parametros = [
                $utilizador->getUsername(),
                $utilizador->getNivel(),
                $utilizador->getEstado(),
                $utilizador->getIdFuncionario(),
                $utilizador->getIdUtilizador()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza a senha de um utilizador
     * @param int $idUtilizador
     * @param string $novaSenha
     * @return bool
     */
    public function atualizarSenha($idUtilizador, $novaSenha)
    {
        try {
            $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);

            $sql = "UPDATE utilizador SET senha = ? WHERE idUtilizador = ?";
            $parametros = [$senhaHash, $idUtilizador];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em atualizarSenha(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o estado de um utilizador
     * @param int $idUtilizador
     * @param string $estado
     * @return bool
     */
    public function atualizarEstado($idUtilizador, $estado)
    {
        try {
            $sql = "UPDATE utilizador SET estado = ? WHERE idUtilizador = ?";
            $parametros = [$estado, $idUtilizador];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em atualizarEstado(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza o nível de um utilizador
     * @param int $idUtilizador
     * @param string $nivel
     * @return bool
     */
    public function atualizarNivel($idUtilizador, $nivel)
    {
        try {
            $sql = "UPDATE utilizador SET nivel = ? WHERE idUtilizador = ?";
            $parametros = [$nivel, $idUtilizador];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em atualizarNivel(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga um utilizador
     * @param int $idUtilizador
     * @return bool
     */
    public function apagar($idUtilizador)
    {
        try {
            $sql = "DELETE FROM utilizador WHERE idUtilizador = ?";
            $parametros = [$idUtilizador];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Apaga todos os utilizadores de um funcionário (se necessário)
     * @param int $idFuncionario
     * @return bool
     */
    public function apagarPorFuncionario($idFuncionario)
    {
        try {
            $sql = "DELETE FROM utilizador WHERE idFuncionario = ?";
            $parametros = [$idFuncionario];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("UtilizadorDAO - Erro em apagarPorFuncionario(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto UtilizadorDTO a partir de um array
     * @param array $dados
     * @return UtilizadorDTO
     */
    private function construirDTO($dados)
    {
        $utilizador = new UtilizadorDTO();
        $utilizador->setIdUtilizador($dados['idUtilizador']);
        $utilizador->setUsername($dados['username']);
        $utilizador->setSenha($dados['senha']);
        $utilizador->setNivel($dados['nivel']);
        $utilizador->setEstado($dados['estado']);
        $utilizador->setIdFuncionario($dados['idFuncionario']);

        return $utilizador;
    }
}
