<?php
require_once __DIR__ . '/../MODEL/DAO/ReceitaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ReceitaMedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ClienteDAO.php';
require_once __DIR__ . '/../MODEL/DAO/MedicamentoDAO.php';
require_once __DIR__ . '/../MODEL/DTO/ReceitaDTO.php';
require_once __DIR__ . '/../MODEL/DTO/ReceitaMedicamentoDTO.php';

class ReceitaController
{
    private $receitaDAO;
    private $receitaMedicamentoDAO;
    private $clienteDAO;
    private $medicamentoDAO;

    public function __construct()
    {
        $this->receitaDAO = new ReceitaDAO();
        $this->receitaMedicamentoDAO = new ReceitaMedicamentoDAO();
        $this->clienteDAO = new ClienteDAO();
        $this->medicamentoDAO = new MedicamentoDAO();
    }

    private function normalizarItens($itens)
    {
        if (!is_array($itens)) {
            return [];
        }

        $saida = [];
        foreach ($itens as $item) {
            $idMedicamento = (int)($item['idMedicamento'] ?? 0);
            $quantidade = (int)($item['quantidade'] ?? 0);

            if ($idMedicamento > 0 && $quantidade > 0) {
                $saida[] = [
                    'idMedicamento' => $idMedicamento,
                    'quantidade' => $quantidade,
                ];
            }
        }

        return $saida;
    }

    private function salvarItens($idReceita, $itens)
    {
        foreach ($itens as $item) {
            $dto = new ReceitaMedicamentoDTO();
            $dto->setIdReceita($idReceita);
            $dto->setIdMedicamento($item['idMedicamento']);
            $dto->setQuantidade($item['quantidade']);

            if (!$this->receitaMedicamentoDAO->cadastrar($dto)) {
                return false;
            }
        }

        return true;
    }

    public function listar()
    {
        try {
            return ['success' => true, 'data' => $this->receitaDAO->listarTodas()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao listar receitas.'];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $receita = $this->receitaDAO->buscarPorId($id);
            if (!$receita) {
                return ['success' => false, 'message' => 'Receita não encontrada.'];
            }

            return [
                'success' => true,
                'data' => $receita,
                'itens' => $this->receitaMedicamentoDAO->buscarMedicamentosReceita($id)
            ];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao buscar receita.'];
        }
    }

    public function cadastrar($dados)
    {
        try {
            $numeroReceita = trim($dados['numeroReceita'] ?? '');
            $medico = trim($dados['medico'] ?? '');
            $crm = trim($dados['crm'] ?? '');
            $idCliente = (int)($dados['idCliente'] ?? 0);
            $dataReceita = $dados['dataReceita'] ?? date('Y-m-d');
            $observacao = trim($dados['observacao'] ?? '');
            $itens = $this->normalizarItens($dados['itens'] ?? []);

            if ($numeroReceita === '' || $medico === '' || $crm === '' || $idCliente <= 0 || empty($itens)) {
                return ['success' => false, 'message' => 'Preencha os campos obrigatórios e adicione pelo menos um medicamento.'];
            }

            $cliente = $this->clienteDAO->buscarPorId($idCliente);
            if (!$cliente) {
                return ['success' => false, 'message' => 'Cliente não encontrado.'];
            }

            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $receita = new ReceitaDTO();
            $receita->setNumeroReceita($numeroReceita);
            $receita->setMedico($medico);
            $receita->setCrm($crm);
            $receita->setDataReceita($dataReceita);
            $receita->setObservacao($observacao);
            $receita->setIdCliente($idCliente);

            $idReceita = $this->receitaDAO->cadastrar($receita);
            if (!$idReceita) {
                throw new Exception('Nao foi possivel gravar a receita.');
            }

            if (!$this->salvarItens($idReceita, $itens)) {
                throw new Exception('Nao foi possivel gravar os medicamentos da receita.');
            }

            $pdo->commit();
            return ['success' => true, 'message' => 'Receita criada com sucesso!', 'id' => $idReceita];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function atualizar($dados)
    {
        try {
            $idReceita = (int)($dados['idReceita'] ?? 0);
            $numeroReceita = trim($dados['numeroReceita'] ?? '');
            $medico = trim($dados['medico'] ?? '');
            $crm = trim($dados['crm'] ?? '');
            $idCliente = (int)($dados['idCliente'] ?? 0);
            $dataReceita = $dados['dataReceita'] ?? date('Y-m-d');
            $observacao = trim($dados['observacao'] ?? '');
            $itens = $this->normalizarItens($dados['itens'] ?? []);

            if ($idReceita <= 0 || $numeroReceita === '' || $medico === '' || $crm === '' || $idCliente <= 0 || empty($itens)) {
                return ['success' => false, 'message' => 'Dados inválidos para atualizar a receita.'];
            }

            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $receita = new ReceitaDTO();
            $receita->setIdReceita($idReceita);
            $receita->setNumeroReceita($numeroReceita);
            $receita->setMedico($medico);
            $receita->setCrm($crm);
            $receita->setDataReceita($dataReceita);
            $receita->setObservacao($observacao);
            $receita->setIdCliente($idCliente);

            if (!$this->receitaDAO->atualizar($receita)) {
                throw new Exception('Nao foi possivel atualizar a receita.');
            }

            if (!$this->receitaMedicamentoDAO->apagarPorReceita($idReceita)) {
                throw new Exception('Nao foi possivel limpar os medicamentos antigos.');
            }

            if (!$this->salvarItens($idReceita, $itens)) {
                throw new Exception('Nao foi possivel gravar os medicamentos da receita.');
            }

            $pdo->commit();
            return ['success' => true, 'message' => 'Receita atualizada com sucesso!'];
        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    public function apagar($id)
    {
        try {
            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            if (!$this->receitaMedicamentoDAO->apagarPorReceita($id)) {
                throw new Exception('Nao foi possivel apagar os medicamentos da receita.');
            }

            if (!$this->receitaDAO->apagar($id)) {
                throw new Exception('Nao foi possivel apagar a receita.');
            }

            $pdo->commit();
            return ['success' => true, 'message' => 'Receita apagada com sucesso!'];
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
            return ['success' => true, 'total' => $this->receitaDAO->contar()];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao contar receitas.'];
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'ReceitaController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new ReceitaController();
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
            $resultado = $controller->apagar($dados['idReceita'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idReceita'] ?? $dados['id'] ?? 0);
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
