<?php
require_once __DIR__ . '/../MODEL/DAO/ItemVendaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/UtilizadorDAO.php';
require_once __DIR__ . '/../MODEL/DAO/VendaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/FuncionarioDAO.php';
require_once __DIR__ . '/../MODEL/DTO/FuncionarioDTO.php';

class FuncionarioController
{
    private $funcionarioDAO;

    public function __construct()
    {
        $this->funcionarioDAO = new FuncionarioDAO();
    }

    public function cadastrar($dados)
    {
        try {
            $funcionario = new FuncionarioDTO();
            $funcionario->setNome($dados['nome'] ?? '');
            $funcionario->setSexo($dados['sexo'] ?? '');
            $funcionario->setDataNascimento($dados['dataNascimento'] ?? '');
            $funcionario->setTelefone($dados['telefone'] ?? '');
            $funcionario->setEmail($dados['email'] ?? '');
            $funcionario->setCargo($dados['cargo'] ?? '');
            $funcionario->setSalario($dados['salario'] ?? 0);
            $funcionario->setDataAdmissao($dados['dataAdmissao'] ?? '');
            $funcionario->setEndereco($dados['endereco'] ?? '');

            $resultado = $this->funcionarioDAO->cadastrar($funcionario);

            if ($resultado) {
                return ['success' => true, 'message' => 'Funcionário cadastrado com sucesso!', 'id' => $resultado];
            }
            return ['success' => false, 'message' => 'Erro ao cadastrar. Email já existe.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function listar()
    {
        try {
            $funcionarios = $this->funcionarioDAO->listarTodos();
            return ['success' => true, 'data' => $funcionarios];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao listar funcionários.'];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $funcionario = $this->funcionarioDAO->buscarPorId($id);
            if ($funcionario) {
                return ['success' => true, 'data' => $funcionario];
            }
            return ['success' => false, 'message' => 'Funcionário não encontrado.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao buscar funcionário.'];
        }
    }

    public function buscarPorNome($nome)
    {
        try {
            $funcionarios = $this->funcionarioDAO->buscarPorNomeParcial($nome);
            return ['success' => true, 'data' => $funcionarios];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao pesquisar funcionários.'];
        }
    }

    public function atualizar($dados)
    {
        try {
            $funcionario = new FuncionarioDTO();
            $funcionario->setIdFuncionario($dados['idFuncionario'] ?? 0);
            $funcionario->setNome($dados['nome'] ?? '');
            $funcionario->setSexo($dados['sexo'] ?? '');
            $funcionario->setDataNascimento($dados['dataNascimento'] ?? '');
            $funcionario->setTelefone($dados['telefone'] ?? '');
            $funcionario->setEmail($dados['email'] ?? '');
            $funcionario->setCargo($dados['cargo'] ?? '');
            $funcionario->setSalario($dados['salario'] ?? 0);
            $funcionario->setDataAdmissao($dados['dataAdmissao'] ?? '');
            $funcionario->setEndereco($dados['endereco'] ?? '');

            $resultado = $this->funcionarioDAO->atualizar($funcionario);

            if ($resultado) {
                return ['success' => true, 'message' => 'Funcionário atualizado com sucesso!'];
            }
            return ['success' => false, 'message' => 'Erro ao atualizar funcionário.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function apagar($id)
    {
        try {
            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $utilizadorDAO = new UtilizadorDAO();
            $vendaDAO = new VendaDAO();
            $itemVendaDAO = new ItemVendaDAO();

            if (!$utilizadorDAO->apagarPorFuncionario($id)) {
                throw new Exception('Nao foi possivel apagar os utilizadores associados.');
            }

            $vendas = $pdo->prepare("SELECT idVenda FROM venda WHERE idFuncionario = ?");
            $vendas->execute([$id]);
            foreach ($vendas->fetchAll(PDO::FETCH_ASSOC) as $venda) {
                $idVenda = (int)($venda['idVenda'] ?? 0);
                if ($idVenda <= 0) {
                    continue;
                }

                if (!$itemVendaDAO->apagarPorVenda($idVenda)) {
                    throw new Exception('Nao foi possivel apagar os itens da venda.');
                }

                if (!$vendaDAO->apagar($idVenda)) {
                    throw new Exception('Nao foi possivel apagar a venda.');
                }
            }

            $resultado = $this->funcionarioDAO->apagar($id);
            if ($resultado) {
                $pdo->commit();
                return ['success' => true, 'message' => 'Funcionário apagado com sucesso!'];
            }

            $pdo->rollBack();
            return ['success' => false, 'message' => 'Erro ao apagar funcionário.'];
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
            $total = $this->funcionarioDAO->contar();
            return ['success' => true, 'total' => $total];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contar funcionários.'];
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'FuncionarioController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new FuncionarioController();
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
            $resultado = $controller->apagar($dados['idFuncionario'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idFuncionario'] ?? $dados['id'] ?? 0);
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
