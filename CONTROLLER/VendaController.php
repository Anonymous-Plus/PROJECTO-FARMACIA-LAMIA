<?php
require_once __DIR__ . '/../MODEL/DAO/conexao.php';
require_once __DIR__ . '/../MODEL/DAO/VendaDAO.php';
require_once __DIR__ . '/../MODEL/DAO/ItemVendaDAO.php';
require_once __DIR__ . '/../MODEL/DTO/VendaDTO.php';
require_once __DIR__ . '/../MODEL/DTO/ItemVendaDTO.php';

class VendaController
{
    private $vendaDAO;
    private $itemVendaDAO;

    public function __construct()
    {
        $this->vendaDAO = new VendaDAO();
        $this->itemVendaDAO = new ItemVendaDAO();
    }

    public function cadastrar($dados)
    {
        try {
            $itens = $dados['itens'] ?? [];
            if (!is_array($itens) || count($itens) === 0) {
                return [
                    'success' => false,
                    'message' => 'Adicione pelo menos um item à venda.'
                ];
            }

            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            $venda = new VendaDTO();
            $venda->setDataVenda($dados['dataVenda'] ?? date('Y-m-d H:i:s'));
            $venda->setValorTotal($dados['valorTotal'] ?? 0);
            $venda->setFormaPagamento($dados['formaPagamento'] ?? 'Dinheiro');
            $venda->setIdFuncionario($dados['idFuncionario'] ?? 0);
            $venda->setIdCliente($dados['idCliente'] ?? 0);

            $resultado = $this->vendaDAO->cadastrar($venda);

            if ($resultado) {
                foreach ($itens as $itemDados) {
                    $item = new ItemVendaDTO();
                    $quantidade = (int)($itemDados['qtd'] ?? $itemDados['quantidade'] ?? 0);
                    $precoUnitario = (float)($itemDados['preco'] ?? $itemDados['precoUnitario'] ?? 0);

                    $item->setIdVenda($resultado);
                    $item->setIdMedicamento($itemDados['id'] ?? $itemDados['idMedicamento'] ?? 0);
                    $item->setQuantidade($quantidade);
                    $item->setPrecoUnitario($precoUnitario);
                    $item->setSubtotal($quantidade * $precoUnitario);

                    if (!$this->itemVendaDAO->cadastrar($item)) {
                        throw new Exception('Nao foi possivel gravar um dos itens da venda.');
                    }
                }

                $pdo->commit();
                return [
                    'success' => true,
                    'message' => 'Venda registada com sucesso!',
                    'id' => $resultado
                ];
            }

            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao registar venda.'
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }

    public function listar()
    {
        try {
            $vendas = $this->vendaDAO->listarTodas();

            return [
                'success' => true,
                'data' => $vendas
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao listar vendas.'
            ];
        }
    }

    public function buscarPorId($id)
    {
        try {
            $venda = $this->vendaDAO->buscarPorId($id);

            if ($venda) {
                return [
                    'success' => true,
                    'data' => $venda
                ];
            }

            return [
                'success' => false,
                'message' => 'Venda não encontrada.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro ao buscar venda.'
            ];
        }
    }

    public function atualizar($dados)
    {
        try {
            $venda = new VendaDTO();

            $venda->setIdVenda($dados['idVenda'] ?? 0);
            $venda->setDataVenda($dados['dataVenda'] ?? '');
            $venda->setValorTotal($dados['valorTotal'] ?? 0);
            $venda->setFormaPagamento($dados['formaPagamento'] ?? '');
            $venda->setIdFuncionario($dados['idFuncionario'] ?? 0);
            $venda->setIdCliente($dados['idCliente'] ?? 0);

            if ($this->vendaDAO->atualizar($venda)) {
                return [
                    'success' => true,
                    'message' => 'Venda atualizada com sucesso!'
                ];
            }

            return [
                'success' => false,
                'message' => 'Erro ao atualizar venda.'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }

    public function apagar($id)
    {
        try {
            $pdo = Conn::getInstance()->getConnection();
            $pdo->beginTransaction();

            if (!$this->itemVendaDAO->apagarPorVenda($id)) {
                throw new Exception('Nao foi possivel remover os itens da venda.');
            }

            if ($this->vendaDAO->apagar($id)) {
                $pdo->commit();
                return [
                    'success' => true,
                    'message' => 'Venda eliminada com sucesso!'
                ];
            }

            $pdo->rollBack();
            return [
                'success' => false,
                'message' => 'Erro ao eliminar venda.'
            ];

        } catch (Exception $e) {
            if (isset($pdo) && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return [
                'success' => false,
                'message' => 'Erro: ' . $e->getMessage()
            ];
        }
    }
}

if (basename($_SERVER['SCRIPT_FILENAME']) === 'VendaController.php') {
    header('Content-Type: application/json; charset=utf-8');

    $controller = new VendaController();
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
            $resultado = $controller->apagar($dados['idVenda'] ?? $dados['id'] ?? 0);
            break;
        case 'buscar':
            $resultado = $controller->buscarPorId($dados['idVenda'] ?? $dados['id'] ?? 0);
            break;
        case 'listar':
            $resultado = $controller->listar();
            break;
        default:
            $resultado = ['success' => false, 'message' => 'Acao invalida'];
            break;
    }

    echo json_encode($resultado, JSON_UNESCAPED_UNICODE);
    exit;
}
