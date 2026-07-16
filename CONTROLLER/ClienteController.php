<?php
require_once __DIR__ . '/../MODEL/DAO/ClienteDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ReceitaDAO.php';
require_once __DIR__ . '/../MODEL/DTO/ClienteDTO.php';

class ClienteController
{
    private $clienteDAO;

    public function __construct()
    {
        $this->clienteDAO = new ClienteDAO();
    }

    public function cadastrar($dados)
    {
        try {
            $cliente = new ClienteDTO();
            $cliente->setNome($dados['nome'] ?? '');
            $cliente->setSexo($dados['sexo'] ?? '');
            $cliente->setDataNascimento($dados['dataNascimento'] ?? '');
            $cliente->setTelefone($dados['telefone'] ?? '');
            $cliente->setEmail($dados['email'] ?? '');
            $cliente->setEndereco($dados['endereco'] ?? '');

            $resultado = $this->clienteDAO->cadastrar($cliente);

            if ($resultado) {
                return ['success' => true, 'message' => 'Cliente cadastrado com sucesso!', 'id' => $resultado];
            }
            return ['success' => false, 'message' => 'Erro ao cadastrar. Email já existe.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function listar()
    {
        try {
            $clientes = $this->clienteDAO->listarTodos();
            return ['success' => true, 'data' => $clientes];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao listar clientes.'];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $cliente = $this->clienteDAO->buscarPorId($id);
            if ($cliente) {
                return ['success' => true, 'data' => $cliente];
            }
            return ['success' => false, 'message' => 'Cliente não encontrado.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao buscar cliente.'];
        }
    }

    public function buscarPorNome($nome)
    {
        try {
            $clientes = $this->clienteDAO->buscarPorNomeParcial($nome);
            return ['success' => true, 'data' => $clientes];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao pesquisar clientes.'];
        }
    }

    public function atualizar($dados)
    {
        try {
            $cliente = new ClienteDTO();
            $cliente->setIdCliente($dados['idCliente'] ?? 0);
            $cliente->setNome($dados['nome'] ?? '');
            $cliente->setSexo($dados['sexo'] ?? '');
            $cliente->setDataNascimento($dados['dataNascimento'] ?? '');
            $cliente->setTelefone($dados['telefone'] ?? '');
            $cliente->setEmail($dados['email'] ?? '');
            $cliente->setEndereco($dados['endereco'] ?? '');

            $resultado = $this->clienteDAO->atualizar($cliente);

            if ($resultado) {
                return ['success' => true, 'message' => 'Cliente atualizado com sucesso!'];
            }
            return ['success' => false, 'message' => 'Erro ao atualizar cliente.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function apagar($id)
    {
        try {
            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $receitaDAO = new ReceitaDAO();
            if (!$receitaDAO->apagarPorCliente($id)) {
                throw new Exception('Nao foi possivel apagar as receitas associadas.');
            }

            $resultado = $this->clienteDAO->apagar($id);
            if ($resultado) {
                $pdo->commit();
                return ['success' => true, 'message' => 'Cliente apagado com sucesso!'];
            }

            $pdo->rollBack();
            return ['success' => false, 'message' => 'Erro ao apagar cliente.'];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function contar()
    {
        try {
            $total = $this->clienteDAO->contar();
            return ['success' => true, 'total' => $total];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contar clientes.'];
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'ClienteController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new ClienteController();
    $dados = [];
    $metodo = $_SERVER['REQUEST_METHOD'] ?? 'GET';

    if ($metodo === 'POST') {
        $json = file_get_contents('php://input');
        $dados = json_decode($json, true);
        if (!is_array($dados)) {
            $dados = $_POST;
        }
    } else {
        $dados = $_GET;
    }

    $action = $dados['action'] ?? '';

    if ($action === '') {
        echo json_encode(['success' => false, 'message' => 'Acao nao especificada'], JSON_UNESCAPED_UNICODE);
        exit;
    }

    switch ($action) {
        case 'cadastrar':
            $resultado = $controller->cadastrar($dados);
            break;
        case 'atualizar':
            $resultado = $controller->atualizar($dados);
            break;
        case 'apagar':
            $resultado = $controller->apagar($dados['idCliente'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idCliente'] ?? $dados['id'] ?? 0);
            break;
        case 'listar':
            $resultado = $controller->listar();
            break;
        case 'contar':
            $resultado = $controller->contar();
            break;
        default:
            $resultado = ['success' => false, 'message' => 'Acao invalida'];
            break;
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    exit;
}
