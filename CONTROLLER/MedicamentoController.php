<?php
require_once __DIR__ . '/../MODEL/DAO/ItemVendaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/MedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ReceitaMedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DTO/MedicamentoDTO.php';

class MedicamentoController
{
    private $medicamentoDAO;

    public function __construct()
    {
        $this->medicamentoDAO = new MedicamentoDAO();
    }

    public function cadastrar($dados)
    {
        try {
            $medicamento = new MedicamentoDTO();
            $medicamento->setNome($dados['nome'] ?? '');
            $medicamento->setDescricao($dados['descricao'] ?? '');
            $medicamento->setPrincipioAtivo($dados['principioAtivo'] ?? '');
            $medicamento->setDosagem($dados['dosagem'] ?? '');
            $medicamento->setPrecoCompra($dados['precoCompra'] ?? 0);
            $medicamento->setPrecoVenda($dados['precoVenda'] ?? 0);
            $medicamento->setQuantidadeEstoque($dados['quantidadeEstoque'] ?? 0);
            $medicamento->setEstoqueMinimo($dados['estoqueMinimo'] ?? 0);
            $medicamento->setDataFabricacao($dados['dataFabricacao'] ?? '');
            $medicamento->setDataValidade($dados['dataValidade'] ?? '');
            $medicamento->setNecessitaReceita($dados['necessitaReceita'] ?? 'Não');
            $medicamento->setIdCategoria($dados['idCategoria'] ?? 0);
            $medicamento->setIdFornecedor($dados['idFornecedor'] ?? 0);

            $resultado = $this->medicamentoDAO->cadastrar($medicamento);

            if ($resultado) {
                return ['success' => true, 'message' => 'Medicamento cadastrado com sucesso!', 'id' => $resultado];
            }
            return ['success' => false, 'message' => 'Erro ao cadastrar medicamento.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function listar()
    {
        try {
            $medicamentos = $this->medicamentoDAO->listarTodos();
            return ['success' => true, 'data' => $medicamentos];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao listar medicamentos.'];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $medicamento = $this->medicamentoDAO->buscarPorId($id);
            if ($medicamento) {
                return ['success' => true, 'data' => $medicamento];
            }
            return ['success' => false, 'message' => 'Medicamento não encontrado.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao buscar medicamento.'];
        }
    }

    public function buscarPorNome($nome)
    {
        try {
            $medicamentos = $this->medicamentoDAO->buscarPorNomeParcial($nome);
            return ['success' => true, 'data' => $medicamentos];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao pesquisar medicamentos.'];
        }
    }

    public function atualizar($dados)
    {
        try {
            $medicamento = new MedicamentoDTO();
            $medicamento->setIdMedicamento($dados['idMedicamento'] ?? 0);
            $medicamento->setNome($dados['nome'] ?? '');
            $medicamento->setDescricao($dados['descricao'] ?? '');
            $medicamento->setPrincipioAtivo($dados['principioAtivo'] ?? '');
            $medicamento->setDosagem($dados['dosagem'] ?? '');
            $medicamento->setPrecoCompra($dados['precoCompra'] ?? 0);
            $medicamento->setPrecoVenda($dados['precoVenda'] ?? 0);
            $medicamento->setQuantidadeEstoque($dados['quantidadeEstoque'] ?? 0);
            $medicamento->setEstoqueMinimo($dados['estoqueMinimo'] ?? 0);
            $medicamento->setDataFabricacao($dados['dataFabricacao'] ?? '');
            $medicamento->setDataValidade($dados['dataValidade'] ?? '');
            $medicamento->setNecessitaReceita($dados['necessitaReceita'] ?? 'Não');
            $medicamento->setIdCategoria($dados['idCategoria'] ?? 0);
            $medicamento->setIdFornecedor($dados['idFornecedor'] ?? 0);

            $resultado = $this->medicamentoDAO->atualizar($medicamento);

            if ($resultado) {
                return ['success' => true, 'message' => 'Medicamento atualizado com sucesso!'];
            }
            return ['success' => false, 'message' => 'Erro ao atualizar medicamento.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function apagar($id)
    {
        try {
            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $itemVendaDAO = new ItemVendaDAO();
            $receitaMedicamentoDAO = new ReceitaMedicamentoDAO();

            if (!$itemVendaDAO->apagarPorMedicamento($id)) {
                throw new Exception('Nao foi possivel apagar os itens de venda associados.');
            }

            if (!$receitaMedicamentoDAO->apagarPorMedicamento($id)) {
                throw new Exception('Nao foi possivel apagar os registos de receita associados.');
            }

            $resultado = $this->medicamentoDAO->apagar($id);
            if ($resultado) {
                $pdo->commit();
                return ['success' => true, 'message' => 'Medicamento apagado com sucesso!'];
            }

            $pdo->rollBack();
            return ['success' => false, 'message' => 'Erro ao apagar medicamento.'];
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
            $total = $this->medicamentoDAO->contar();
            return ['success' => true, 'total' => $total];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contar medicamentos.'];
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'MedicamentoController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new MedicamentoController();
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
            $resultado = $controller->apagar($dados['idMedicamento'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idMedicamento'] ?? $dados['id'] ?? 0);
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
