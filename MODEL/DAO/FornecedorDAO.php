<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/FornecedorDTO.php';

class FornecedorDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra um novo fornecedor
     * @param FornecedorDTO $fornecedor
     * @return bool|int - retorna o ID do novo fornecedor ou false em caso de erro
     */
    public function cadastrar(FornecedorDTO $fornecedor)
    {
        try {
            // Verifica se o email já existe
            if ($fornecedor->getEmail() && $this->emailExiste($fornecedor->getEmail())) {
                error_log("Fornecedor com este email já existe: " . $fornecedor->getEmail());
                return false;
            }

            $sql = "INSERT INTO fornecedor (empresa, representante, telefone, email, endereco) 
                    VALUES (?, ?, ?, ?, ?)";

            $parametros = [
                $fornecedor->getEmpresa(),
                $fornecedor->getRepresentante(),
                $fornecedor->getTelefone(),
                $fornecedor->getEmail(),
                $fornecedor->getEndereco()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca um fornecedor pelo ID
     * @param int $idFornecedor
     * @return FornecedorDTO|null
     */
    public function buscarPorId($idFornecedor)
    {
        try {
            $sql = "SELECT * FROM fornecedor WHERE idFornecedor = ?";
            $resultado = $this->bd->buscarUm($sql, [$idFornecedor]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um fornecedor pelo email
     * @param string $email
     * @return FornecedorDTO|null
     */
    public function buscarPorEmail($email)
    {
        try {
            $sql = "SELECT * FROM fornecedor WHERE email = ?";
            $resultado = $this->bd->buscarUm($sql, [$email]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em buscarPorEmail(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um fornecedor pelo nome da empresa
     * @param string $empresa
     * @return FornecedorDTO|null
     */
    public function buscarPorEmpresa($empresa)
    {
        try {
            $sql = "SELECT * FROM fornecedor WHERE empresa = ?";
            $resultado = $this->bd->buscarUm($sql, [$empresa]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em buscarPorEmpresa(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um fornecedor pelo telefone
     * @param string $telefone
     * @return FornecedorDTO|null
     */
    public function buscarPorTelefone($telefone)
    {
        try {
            $sql = "SELECT * FROM fornecedor WHERE telefone = ?";
            $resultado = $this->bd->buscarUm($sql, [$telefone]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em buscarPorTelefone(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Verifica se o email já existe
     * @param string $email
     * @return bool
     */
    public function emailExiste($email)
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM fornecedor WHERE email = ?";
            $resultado = $this->bd->buscarUm($sql, [$email]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em emailExiste(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todos os fornecedores
     * @return array
     */
    public function listarTodos()
    {
        try {
            $sql = "SELECT * FROM fornecedor ORDER BY empresa ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $fornecedores = [];
            foreach ($resultados as $linha) {
                $fornecedores[] = $this->construirDTO($linha);
            }

            return $fornecedores;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em listarTodos(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca fornecedores por nome parcial da empresa
     * @param string $empresa
     * @return array
     */
    public function buscarPorNomeEmpresa($empresa)
    {
        try {
            $sql = "SELECT * FROM fornecedor 
                    WHERE empresa LIKE ? 
                    ORDER BY empresa ASC";

            $empresaPesquisa = '%' . $empresa . '%';
            $resultados = $this->bd->buscarTodos($sql, [$empresaPesquisa]);

            $fornecedores = [];
            foreach ($resultados as $linha) {
                $fornecedores[] = $this->construirDTO($linha);
            }

            return $fornecedores;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em buscarPorNomeEmpresa(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca fornecedores por representante
     * @param string $representante
     * @return array
     */
    public function buscarPorRepresentante($representante)
    {
        try {
            $sql = "SELECT * FROM fornecedor 
                    WHERE representante LIKE ? 
                    ORDER BY empresa ASC";

            $representantePesquisa = '%' . $representante . '%';
            $resultados = $this->bd->buscarTodos($sql, [$representantePesquisa]);

            $fornecedores = [];
            foreach ($resultados as $linha) {
                $fornecedores[] = $this->construirDTO($linha);
            }

            return $fornecedores;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em buscarPorRepresentante(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista fornecedores com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT * FROM fornecedor 
                    ORDER BY empresa ASC 
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $fornecedores = [];
            foreach ($resultados as $linha) {
                $fornecedores[] = $this->construirDTO($linha);
            }

            return $fornecedores;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de fornecedores
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM fornecedor";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de um fornecedor
     * @param FornecedorDTO $fornecedor
     * @return bool
     */
    public function atualizar(FornecedorDTO $fornecedor)
    {
        try {
            $sql = "UPDATE fornecedor 
                    SET empresa = ?, representante = ?, telefone = ?, email = ?, endereco = ? 
                    WHERE idFornecedor = ?";

            $parametros = [
                $fornecedor->getEmpresa(),
                $fornecedor->getRepresentante(),
                $fornecedor->getTelefone(),
                $fornecedor->getEmail(),
                $fornecedor->getEndereco(),
                $fornecedor->getIdFornecedor()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o contacto de um fornecedor (telefone e email)
     * @param int $idFornecedor
     * @param string $telefone
     * @param string $email
     * @return bool
     */
    public function atualizarContacto($idFornecedor, $telefone, $email)
    {
        try {
            $sql = "UPDATE fornecedor SET telefone = ?, email = ? WHERE idFornecedor = ?";
            $parametros = [$telefone, $email, $idFornecedor];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em atualizarContacto(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o endereço de um fornecedor
     * @param int $idFornecedor
     * @param string $novoEndereco
     * @return bool
     */
    public function atualizarEndereco($idFornecedor, $novoEndereco)
    {
        try {
            $sql = "UPDATE fornecedor SET endereco = ? WHERE idFornecedor = ?";
            $parametros = [$novoEndereco, $idFornecedor];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em atualizarEndereco(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o representante de um fornecedor
     * @param int $idFornecedor
     * @param string $novoRepresentante
     * @return bool
     */
    public function atualizarRepresentante($idFornecedor, $novoRepresentante)
    {
        try {
            $sql = "UPDATE fornecedor SET representante = ? WHERE idFornecedor = ?";
            $parametros = [$novoRepresentante, $idFornecedor];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em atualizarRepresentante(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o nome da empresa de um fornecedor
     * @param int $idFornecedor
     * @param string $novaEmpresa
     * @return bool
     */
    public function atualizarEmpresa($idFornecedor, $novaEmpresa)
    {
        try {
            $sql = "UPDATE fornecedor SET empresa = ? WHERE idFornecedor = ?";
            $parametros = [$novaEmpresa, $idFornecedor];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em atualizarEmpresa(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga um fornecedor
     * @param int $idFornecedor
     * @return bool
     */
    public function apagar($idFornecedor)
    {
        try {
            $sql = "DELETE FROM fornecedor WHERE idFornecedor = ?";
            $parametros = [$idFornecedor];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("FornecedorDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto FornecedorDTO a partir de um array
     * @param array $dados
     * @return FornecedorDTO
     */
    private function construirDTO($dados)
    {
        $fornecedor = new FornecedorDTO();
        $fornecedor->setIdFornecedor($dados['idFornecedor']);
        $fornecedor->setEmpresa($dados['empresa']);
        $fornecedor->setRepresentante($dados['representante']);
        $fornecedor->setTelefone($dados['telefone']);
        $fornecedor->setEmail($dados['email']);
        $fornecedor->setEndereco($dados['endereco']);

        return $fornecedor;
    }
}
