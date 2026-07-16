<?php
require_once __DIR__ . '/../MODEL/DAO/UtilizadorDAO.php';
require_once __DIR__ . '/../MODEL/DTO/UtilizadorDTO.php';

class UtilizadorController
{
    private $utilizadorDAO;

    public function __construct()
    {
        $this->utilizadorDAO = new UtilizadorDAO();
    }

    // ==================== CADASTRAR ====================
    public function cadastrar($dados)
    {
        try {
            if (empty($dados['username']) || empty($dados['senha'])) {
                return ['success' => false, 'message' => 'Username e senha são obrigatórios.'];
            }

            if (strlen($dados['senha']) < 6) {
                return ['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres.'];
            }

            $utilizador = new UtilizadorDTO();
            $utilizador->setUsername($dados['username'] ?? '');
            $utilizador->setSenha($dados['senha'] ?? '');
            $utilizador->setNivel($dados['nivel'] ?? 'Funcionario');
            $utilizador->setEstado($dados['estado'] ?? 'Ativo');
            $utilizador->setIdFuncionario($dados['idFuncionario'] ?? 0);

            $resultado = $this->utilizadorDAO->cadastrar($utilizador);

            if ($resultado) {
                return ['success' => true, 'message' => 'Utilizador criado com sucesso!', 'id' => $resultado];
            }
            return ['success' => false, 'message' => 'Erro ao criar utilizador. Username já existe.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // ==================== LISTAR ====================
    public function listar()
    {
        try {
            $utilizadores = $this->utilizadorDAO->listarTodos();
            return ['success' => true, 'data' => $utilizadores];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao listar utilizadores.'];
        }
    }

    // ==================== BUSCAR ====================
    public function buscarPorId($id)
    {
        try {
            $utilizador = $this->utilizadorDAO->buscarPorId($id);
            if ($utilizador) {
                return ['success' => true, 'data' => $utilizador];
            }
            return ['success' => false, 'message' => 'Utilizador não encontrado.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao buscar utilizador.'];
        }
    }

    // ==================== ATUALIZAR ====================
    public function atualizar($dados)
    {
        try {
            $utilizador = new UtilizadorDTO();
            $utilizador->setIdUtilizador($dados['idUtilizador'] ?? 0);
            $utilizador->setUsername($dados['username'] ?? '');
            $utilizador->setNivel($dados['nivel'] ?? 'Funcionario');
            $utilizador->setEstado($dados['estado'] ?? 'Ativo');
            $utilizador->setIdFuncionario($dados['idFuncionario'] ?? 0);

            $resultado = $this->utilizadorDAO->atualizar($utilizador);

            if ($resultado) {
                return ['success' => true, 'message' => 'Utilizador atualizado com sucesso!'];
            }
            return ['success' => false, 'message' => 'Erro ao atualizar utilizador.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function atualizarSenha($dados)
    {
        try {
            $idUtilizador = $dados['idUtilizador'] ?? 0;
            $novaSenha = $dados['senha'] ?? '';

            if (!$idUtilizador || $novaSenha === '') {
                return ['success' => false, 'message' => 'ID e senha sao obrigatorios.'];
            }

            $resultado = $this->utilizadorDAO->atualizarSenha($idUtilizador, $novaSenha);

            if ($resultado) {
                return ['success' => true, 'message' => 'Senha atualizada com sucesso!'];
            }

            return ['success' => false, 'message' => 'Erro ao atualizar senha.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // ==================== APAGAR ====================
    public function apagar($id)
    {
        try {
            $resultado = $this->utilizadorDAO->apagar($id);
            if ($resultado) {
                return ['success' => true, 'message' => 'Utilizador apagado com sucesso!'];
            }
            return ['success' => false, 'message' => 'Erro ao apagar utilizador.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    // ==================== CONTAR ====================
    public function contar()
    {
        try {
            $total = $this->utilizadorDAO->contar();
            return ['success' => true, 'total' => $total];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contar utilizadores.'];
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'UtilizadorController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new UtilizadorController();
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
        case 'atualizarSenha':
            $resultado = $controller->atualizarSenha($dados);
            break;
        case 'apagar':
            $resultado = $controller->apagar($dados['idUtilizador'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idUtilizador'] ?? $dados['id'] ?? 0);
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
