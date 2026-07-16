<?php
require_once __DIR__ . '/../MODEL/DAO/UtilizadorDAO.php';
require_once __DIR__ . '/../MODEL/DAO/FuncionarioDAO.php';
require_once __DIR__ . '/../MODEL/DTO/UtilizadorDTO.php';

class AuthController
{
    private $utilizadorDAO;

    public function __construct()
    {
        $this->utilizadorDAO = new UtilizadorDAO();
    }

    /**
     * Login
     */
    public function login($username, $senha)
    {
        try {
            if (empty($username) || empty($senha)) {
                return ['success' => false, 'message' => 'Preencha todos os campos.'];
            }

            $utilizador = $this->utilizadorDAO->autenticar($username, $senha);

            if ($utilizador) {
                if (session_status() === PHP_SESSION_NONE) {
                    session_start();
                }
                session_regenerate_id(true);

                $_SESSION['usuario'] = [
                    'id' => $utilizador->getIdUtilizador(),
                    'username' => $utilizador->getUsername(),
                    'nivel' => $utilizador->getNivel(),
                    'idFuncionario' => $utilizador->getIdFuncionario(),
                    'logado_em' => date('Y-m-d H:i:s')
                ];

                return [
                    'success' => true,
                    'message' => 'Login efetuado com sucesso!',
                    'data' => [
                        'id' => $utilizador->getIdUtilizador(),
                        'username' => $utilizador->getUsername(),
                        'nivel' => $utilizador->getNivel()
                    ]
                ];
            }

            return ['success' => false, 'message' => 'Username ou senha inválidos.'];
        } catch (Exception $e) {
            error_log("AuthController - Erro: " . $e->getMessage());
            return ['success' => false, 'message' => 'Erro no sistema.'];
        }
    }

    public function criarAdministradorInicial($dados)
    {
        try {
            $codigoSecreto = trim($dados['setup_code'] ?? '');
            $username = trim($dados['username'] ?? '');
            $senha = $dados['senha'] ?? '';
            $confirmarSenha = $dados['confirmar_senha'] ?? '';
            $idFuncionario = (int)($dados['idFuncionario'] ?? 0);
            $codigoValido = 'LAMIA-ADMIN-2026';

            if ($codigoSecreto !== $codigoValido) {
                return ['success' => false, 'message' => 'Código secreto inválido.'];
            }

            if ($username === '' || $senha === '' || $confirmarSenha === '' || $idFuncionario <= 0) {
                return ['success' => false, 'message' => 'Preencha todos os campos do administrador.'];
            }

            if ($senha !== $confirmarSenha) {
                return ['success' => false, 'message' => 'As senhas não coincidem.'];
            }

            if (strlen($senha) < 6) {
                return ['success' => false, 'message' => 'A senha deve ter no mínimo 6 caracteres.'];
            }

            $bd = Conn::getInstance();
            $adminExistente = $bd->buscarUm("SELECT COUNT(*) as total FROM utilizador WHERE nivel = 'Administrador'");
            if ((int)($adminExistente['total'] ?? 0) > 0) {
                return ['success' => false, 'message' => 'Já existe um administrador cadastrado.'];
            }

            $funcionarioDAO = new FuncionarioDAO();
            if (!$funcionarioDAO->buscarPorId($idFuncionario)) {
                return ['success' => false, 'message' => 'Funcionário inválido.'];
            }

            if ($this->utilizadorDAO->usernameExiste($username)) {
                return ['success' => false, 'message' => 'Esse username já está em uso.'];
            }

            $utilizador = new UtilizadorDTO();
            $utilizador->setUsername($username);
            $utilizador->setSenha($senha);
            $utilizador->setNivel('Administrador');
            $utilizador->setEstado('Ativo');
            $utilizador->setIdFuncionario($idFuncionario);

            $resultado = $this->utilizadorDAO->cadastrar($utilizador);
            if ($resultado) {
                return ['success' => true, 'message' => 'Administrador criado com sucesso!'];
            }

            return ['success' => false, 'message' => 'Não foi possível criar o administrador.'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro: ' . $e->getMessage()];
        }
    }

    /**
     * Logout
     */
    public function logout()
    {
        try {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION = [];
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            session_destroy();
            return ['success' => true, 'message' => 'Sessão terminada!'];
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Erro ao terminar sessão.'];
        }
    }

    /**
     * Verificar sessão
     */
    public function verificarSessao()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
            return ['success' => true, 'logado' => true, 'data' => $_SESSION['usuario']];
        }
        return ['success' => true, 'logado' => false];
    }

    /**
     * Verificar nível de acesso
     */
    public function verificarNivel($niveisPermitidos)
    {
        $sessao = $this->verificarSessao();
        if (!$sessao['logado']) {
            return ['success' => false, 'message' => 'Faça login para continuar.'];
        }
        if (is_string($niveisPermitidos)) {
            $niveisPermitidos = [$niveisPermitidos];
        }
        if (in_array($_SESSION['usuario']['nivel'], $niveisPermitidos)) {
            return ['success' => true, 'message' => 'Acesso permitido.'];
        }
        return ['success' => false, 'message' => 'Acesso negado.'];
    }
}
