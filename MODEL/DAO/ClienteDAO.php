<?php

require_once __DIR__ . '/conexao.php';
require_once __DIR__ . '/../DTO/ClienteDTO.php';

class ClienteDAO
{
    private $bd;

    public function __construct()
    {
        $this->bd = Conn::getInstance();
    }

    // ==================== CADASTRAR ====================
    /**
     * Cadastra um novo cliente
     * @param ClienteDTO $cliente
     * @return bool|int - retorna o ID do novo cliente ou false em caso de erro
     */
    public function cadastrar(ClienteDTO $cliente)
    {
        try {
            // Verifica se o email já existe
            if ($cliente->getEmail() && $this->emailExiste($cliente->getEmail())) {
                error_log("Cliente com este email já existe: " . $cliente->getEmail());
                return false;
            }

            $sql = "INSERT INTO cliente (nome, sexo, dataNascimento, telefone, email, endereco) 
                    VALUES (?, ?, ?, ?, ?, ?)";

            $parametros = [
                $cliente->getNome(),
                $cliente->getSexo(),
                $cliente->getDataNascimento(),
                $cliente->getTelefone(),
                $cliente->getEmail(),
                $cliente->getEndereco()
            ];

            $this->bd->executar($sql, $parametros);
            $novoId = $this->bd->ultimoId();

            return $novoId;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em cadastrar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== BUSCAR ====================
    /**
     * Busca um cliente pelo ID
     * @param int $idCliente
     * @return ClienteDTO|null
     */
    public function buscarPorId($idCliente)
    {
        try {
            $sql = "SELECT * FROM cliente WHERE idCliente = ?";
            $resultado = $this->bd->buscarUm($sql, [$idCliente]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em buscarPorId(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um cliente pelo email
     * @param string $email
     * @return ClienteDTO|null
     */
    public function buscarPorEmail($email)
    {
        try {
            $sql = "SELECT * FROM cliente WHERE email = ?";
            $resultado = $this->bd->buscarUm($sql, [$email]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em buscarPorEmail(): " . $e->getMessage());
            return null;
        }
    }

    /**
     * Busca um cliente pelo telefone
     * @param string $telefone
     * @return ClienteDTO|null
     */
    public function buscarPorTelefone($telefone)
    {
        try {
            $sql = "SELECT * FROM cliente WHERE telefone = ?";
            $resultado = $this->bd->buscarUm($sql, [$telefone]);

            if ($resultado) {
                return $this->construirDTO($resultado);
            }

            return null;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em buscarPorTelefone(): " . $e->getMessage());
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
            $sql = "SELECT COUNT(*) as total FROM cliente WHERE email = ?";
            $resultado = $this->bd->buscarUm($sql, [$email]);

            return isset($resultado['total']) && $resultado['total'] > 0;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em emailExiste(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== LISTAR ====================
    /**
     * Lista todos os clientes
     * @return array
     */
    public function listarTodos()
    {
        try {
            $sql = "SELECT * FROM cliente ORDER BY nome ASC";

            $resultados = $this->bd->buscarTodos($sql);

            $clientes = [];
            foreach ($resultados as $linha) {
                $clientes[] = $this->construirDTO($linha);
            }

            return $clientes;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em listarTodos(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista clientes por sexo
     * @param string $sexo
     * @return array
     */
    public function listarPorSexo($sexo)
    {
        try {
            $sql = "SELECT * FROM cliente WHERE sexo = ? ORDER BY nome ASC";

            $resultados = $this->bd->buscarTodos($sql, [$sexo]);

            $clientes = [];
            foreach ($resultados as $linha) {
                $clientes[] = $this->construirDTO($linha);
            }

            return $clientes;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em listarPorSexo(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Busca clientes por nome parcial
     * @param string $nome
     * @return array
     */
    public function buscarPorNomeParcial($nome)
    {
        try {
            $sql = "SELECT * FROM cliente 
                    WHERE nome LIKE ? 
                    ORDER BY nome ASC";

            $nomePesquisa = '%' . $nome . '%';
            $resultados = $this->bd->buscarTodos($sql, [$nomePesquisa]);

            $clientes = [];
            foreach ($resultados as $linha) {
                $clientes[] = $this->construirDTO($linha);
            }

            return $clientes;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em buscarPorNomeParcial(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Lista clientes com paginação
     * @param int $pagina
     * @param int $limite
     * @return array
     */
    public function listarComPaginacao($pagina = 1, $limite = 10)
    {
        try {
            $offset = ($pagina - 1) * $limite;

            $sql = "SELECT * FROM cliente 
                    ORDER BY nome ASC 
                    LIMIT :limite OFFSET :offset";

            $parametros = [
                ':limite' => (int)$limite,
                ':offset' => (int)$offset
            ];

            $resultados = $this->bd->buscarTodos($sql, $parametros);

            $clientes = [];
            foreach ($resultados as $linha) {
                $clientes[] = $this->construirDTO($linha);
            }

            return $clientes;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em listarComPaginacao(): " . $e->getMessage());
            return [];
        }
    }

    /**
     * Retorna o total de clientes
     * @return int
     */
    public function contar()
    {
        try {
            $sql = "SELECT COUNT(*) as total FROM cliente";
            $resultado = $this->bd->buscarUm($sql);

            return isset($resultado['total']) ? (int)$resultado['total'] : 0;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em contar(): " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Lista clientes por intervalo de data de nascimento
     * @param string $dataInicio
     * @param string $dataFim
     * @return array
     */
    public function listarPorDataNascimento($dataInicio, $dataFim)
    {
        try {
            $sql = "SELECT * FROM cliente 
                    WHERE dataNascimento BETWEEN ? AND ? 
                    ORDER BY dataNascimento ASC";

            $resultados = $this->bd->buscarTodos($sql, [$dataInicio, $dataFim]);

            $clientes = [];
            foreach ($resultados as $linha) {
                $clientes[] = $this->construirDTO($linha);
            }

            return $clientes;
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em listarPorDataNascimento(): " . $e->getMessage());
            return [];
        }
    }

    // ==================== ATUALIZAR ====================
    /**
     * Atualiza os dados de um cliente
     * @param ClienteDTO $cliente
     * @return bool
     */
    public function atualizar(ClienteDTO $cliente)
    {
        try {
            $sql = "UPDATE cliente 
                    SET nome = ?, sexo = ?, dataNascimento = ?, telefone = ?, email = ?, endereco = ? 
                    WHERE idCliente = ?";

            $parametros = [
                $cliente->getNome(),
                $cliente->getSexo(),
                $cliente->getDataNascimento(),
                $cliente->getTelefone(),
                $cliente->getEmail(),
                $cliente->getEndereco(),
                $cliente->getIdCliente()
            ];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em atualizar(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o contacto de um cliente (telefone e email)
     * @param int $idCliente
     * @param string $telefone
     * @param string $email
     * @return bool
     */
    public function atualizarContacto($idCliente, $telefone, $email)
    {
        try {
            $sql = "UPDATE cliente SET telefone = ?, email = ? WHERE idCliente = ?";
            $parametros = [$telefone, $email, $idCliente];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em atualizarContacto(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o endereço de um cliente
     * @param int $idCliente
     * @param string $novoEndereco
     * @return bool
     */
    public function atualizarEndereco($idCliente, $novoEndereco)
    {
        try {
            $sql = "UPDATE cliente SET endereco = ? WHERE idCliente = ?";
            $parametros = [$novoEndereco, $idCliente];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em atualizarEndereco(): " . $e->getMessage());
            return false;
        }
    }

    /**
     * Atualiza apenas o nome de um cliente
     * @param int $idCliente
     * @param string $novoNome
     * @return bool
     */
    public function atualizarNome($idCliente, $novoNome)
    {
        try {
            $sql = "UPDATE cliente SET nome = ? WHERE idCliente = ?";
            $parametros = [$novoNome, $idCliente];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em atualizarNome(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== APAGAR ====================
    /**
     * Apaga um cliente
     * @param int $idCliente
     * @return bool
     */
    public function apagar($idCliente)
    {
        try {
            $sql = "DELETE FROM cliente WHERE idCliente = ?";
            $parametros = [$idCliente];

            return $this->bd->executar($sql, $parametros);
        } catch (Exception $e) {
            error_log("ClienteDAO - Erro em apagar(): " . $e->getMessage());
            return false;
        }
    }

    // ==================== MÉTODOS AUXILIARES ====================
    /**
     * Constrói um objeto ClienteDTO a partir de um array
     * @param array $dados
     * @return ClienteDTO
     */
    private function construirDTO($dados)
    {
        $cliente = new ClienteDTO();
        $cliente->setIdCliente($dados['idCliente']);
        $cliente->setNome($dados['nome']);
        $cliente->setSexo($dados['sexo']);
        $cliente->setDataNascimento($dados['dataNascimento']);
        $cliente->setTelefone($dados['telefone']);
        $cliente->setEmail($dados['email']);
        $cliente->setEndereco($dados['endereco']);

        return $cliente;
    }
}
