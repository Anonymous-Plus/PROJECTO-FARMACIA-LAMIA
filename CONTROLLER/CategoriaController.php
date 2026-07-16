<?php
require_once __DIR__ . '/../MODEL/DAO/CategoriaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ItemVendaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/MedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ReceitaMedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DTO/CategoriaDTO.php';

class CategoriaController
{
    private $categoriaDAO;

    public function __construct()
    {
        $this->categoriaDAO = new CategoriaDAO();
    }

    // ==================== CADASTRAR ====================
    public function cadastrar($dados)
    {
        try {
            $categoria = new CategoriaDTO();
            $categoria->setNomeCategoria($dados['nomeCategoria'] ?? '');
            $categoria->setDescricao($dados['descricao'] ?? '');

            $resultado = $this->categoriaDAO->cadastrar($categoria);

            if ($resultado) {
                return ['success' => true, 'message' => 'Categoria cadastrada com sucesso!', 'id' => $resultado];
            }
            return ['success' => false, 'message' => 'Erro ao cadastrar categoria. Nome já existe.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // ==================== LISTAR ====================
    public function listar()
    {
        try {
            $categorias = $this->categoriaDAO->listarTodas();
            return ['success' => true, 'data' => $categorias];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao listar categorias.'];
        }
    }

    // ==================== BUSCAR ====================
    public function buscarPorId($id)
    {
        try {
            $categoria = $this->categoriaDAO->buscarPorId($id);
            if ($categoria) {
                return ['success' => true, 'data' => $categoria];
            }
            return ['success' => false, 'message' => 'Categoria não encontrada.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao buscar categoria.'];
        }
    }

    public function buscarPorNome($nome)
    {
        try {
            $categoria = $this->categoriaDAO->buscarPorNomeParcial($nome);
            return ['success' => true, 'data' => $categoria];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao pesquisar categorias.'];
        }
    }

    // ==================== ATUALIZAR ====================
    public function atualizar($dados)
    {
        try {
            $categoria = new CategoriaDTO();
            $categoria->setIdCategoria($dados['idCategoria'] ?? 0);
            $categoria->setNomeCategoria($dados['nomeCategoria'] ?? '');
            $categoria->setDescricao($dados['descricao'] ?? '');

            $resultado = $this->categoriaDAO->atualizar($categoria);

            if ($resultado) {
                return ['success' => true, 'message' => 'Categoria atualizada com sucesso!'];
            }
            return ['success' => false, 'message' => 'Erro ao atualizar categoria.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // ==================== APAGAR ====================
    public function apagar($id)
    {
        try {
            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $medicamentoDAO = new MedicamentoDAO();
            $itemVendaDAO = new ItemVendaDAO();
            $receitaMedicamentoDAO = new ReceitaMedicamentoDAO();

            $stmtMedicamentos = $pdo->prepare("SELECT idMedicamento FROM medicamento WHERE idCategoria = ?");
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

            $resultado = $this->categoriaDAO->apagar($id);
            if ($resultado) {
                $pdo->commit();
                return ['success' => true, 'message' => 'Categoria apagada com sucesso!'];
            }

            $pdo->rollBack();
            return ['success' => false, 'message' => 'Erro ao apagar categoria.'];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // ==================== CONTAR ====================
    public function contar()
    {
        try {
            $total = $this->categoriaDAO->contar();
            return ['success' => true, 'total' => $total];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contar categorias.'];
        }
    }
}
if (basename($_SERVER['SCRIPT_FILENAME']) === 'CategoriaController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new CategoriaController();
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
            $resultado = $controller->apagar($dados['idCategoria'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idCategoria'] ?? $dados['id'] ?? 0);
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
