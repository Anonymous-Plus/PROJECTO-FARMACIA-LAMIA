<?php
require_once __DIR__ . '/../MODEL/DAO/FornecedorDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ItemVendaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/MedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ReceitaMedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DTO/FornecedorDTO.php';

class FornecedorController
{
    private $fornecedorDAO;

    public function __construct()
    {
        $this->fornecedorDAO = new FornecedorDAO();
    }

    public function cadastrar($dados)
    {
        try {
            $fornecedor = new FornecedorDTO();
            $fornecedor->setEmpresa($dados['empresa'] ?? '');
            $fornecedor->setRepresentante($dados['representante'] ?? '');
            $fornecedor->setTelefone($dados['telefone'] ?? '');
            $fornecedor->setEmail($dados['email'] ?? '');
            $fornecedor->setEndereco($dados['endereco'] ?? '');

            $resultado = $this->fornecedorDAO->cadastrar($fornecedor);

            if ($resultado) {
                return ['success' => true, 'message' => 'Fornecedor cadastrado com sucesso!', 'id' => $resultado];
            }
            return ['success' => false, 'message' => 'Erro ao cadastrar. Email já existe.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function listar()
    {
        try {
            $fornecedores = $this->fornecedorDAO->listarTodos();
            return ['success' => true, 'data' => $fornecedores];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao listar fornecedores.'];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $fornecedor = $this->fornecedorDAO->buscarPorId($id);
            if ($fornecedor) {
                return ['success' => true, 'data' => $fornecedor];
            }
            return ['success' => false, 'message' => 'Fornecedor não encontrado.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao buscar fornecedor.'];
        }
    }

    public function buscarPorNome($nome)
    {
        try {
            $fornecedores = $this->fornecedorDAO->buscarPorNomeEmpresa($nome);
            return ['success' => true, 'data' => $fornecedores];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao pesquisar fornecedores.'];
        }
    }

    public function atualizar($dados)
    {
        try {
            $fornecedor = new FornecedorDTO();
            $fornecedor->setIdFornecedor($dados['idFornecedor'] ?? 0);
            $fornecedor->setEmpresa($dados['empresa'] ?? '');
            $fornecedor->setRepresentante($dados['representante'] ?? '');
            $fornecedor->setTelefone($dados['telefone'] ?? '');
            $fornecedor->setEmail($dados['email'] ?? '');
            $fornecedor->setEndereco($dados['endereco'] ?? '');

            $resultado = $this->fornecedorDAO->atualizar($fornecedor);

            if ($resultado) {
                return ['success' => true, 'message' => 'Fornecedor atualizado com sucesso!'];
            }
            return ['success' => false, 'message' => 'Erro ao atualizar fornecedor.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function apagar($id)
    {
        try {
            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $medicamentoDAO = new MedicamentoDAO();
            $itemVendaDAO = new ItemVendaDAO();
            $receitaMedicamentoDAO = new ReceitaMedicamentoDAO();

            $stmtMedicamentos = $pdo->prepare("SELECT idMedicamento FROM medicamento WHERE idFornecedor = ?");
            $stmtMedicamentos->execute([$id]);
            foreach ($stmtMedicamentos->fetchAll(PDO::FETCH_ASSOC) as $medicamento) {
                $idMedicamento = (int)($medicamento['idMedicamento'] ?? 0);
                if ($idMedicamento <= 0) {
                    continue;
                }

                if (!$itemVendaDAO->apagarPorMedicamento($idMedicamento)) {
                    throw new Exception('Nao foi possivel apagar os itens de venda associados ao medicamento.');
                }

                if (!$receitaMedicamentoDAO->apagarPorMedicamento($idMedicamento)) {
                    throw new Exception('Nao foi possivel apagar os registos de receita associados ao medicamento.');
                }

                if (!$medicamentoDAO->apagar($idMedicamento)) {
                    throw new Exception('Nao foi possivel apagar o medicamento associado.');
                }
            }

            $resultado = $this->fornecedorDAO->apagar($id);
            if ($resultado) {
                $pdo->commit();
                return ['success' => true, 'message' => 'Fornecedor apagado com sucesso!'];
            }

            $pdo->rollBack();
            return ['success' => false, 'message' => 'Erro ao apagar fornecedor.'];
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
            $total = $this->fornecedorDAO->contar();
            return ['success' => true, 'total' => $total];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contar fornecedores.'];
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'FornecedorController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new FornecedorController();
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
            $resultado = $controller->apagar($dados['idFornecedor'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idFornecedor'] ?? $dados['id'] ?? 0);
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
