<?php
// Verificação de sessão
session_start();
if (!isset($_SESSION['usuario']) || empty($_SESSION['usuario'])) {
    header('Location: ../index.php?erro=nao_autenticado');
    exit;
}

// Dados do utilizador logado
$usuario = $_SESSION['usuario'];
$nivel = $usuario['nivel'];

// Carregar controllers para dados reais
require_once '../../CONTROLLER/MedicamentoController.php';
require_once '../../CONTROLLER/ClienteController.php';
require_once '../../CONTROLLER/VendaController.php';
require_once '../../CONTROLLER/FuncionarioController.php';

$medicamentoController = new MedicamentoController();
$clienteController = new ClienteController();
$vendaController = new VendaController();
$funcionarioController = new FuncionarioController();

// Buscar totais reais
$totalMedicamentos = $medicamentoController->contar()['total'] ?? 0;
$totalClientes = $clienteController->contar()['total'] ?? 0;

// Buscar medicamentos com stock baixo (menos de 10 unidades)
$medicamentosBaixoStock = 0;
$listaMedicamentos = $medicamentoController->listar()['data'] ?? [];
foreach ($listaMedicamentos as $med) {
    if ($med->getQuantidadeEstoque() < 10) {
        $medicamentosBaixoStock++;
    }
}

// Total de vendas do dia
$vendasHoje = 0;
// Aqui poderia buscar vendas do dia, mas para já mantemos o número

// Nome do funcionário logado
$nomeFuncionario = '';
if ($usuario['idFuncionario']) {
    $funcionario = $funcionarioController->buscarPorId($usuario['idFuncionario']);
    if ($funcionario['success']) {
        $nomeFuncionario = $funcionario['data']->getNome();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmácia Lâmia | Painel Administrativo</title>
    <link rel="shortcut icon" href="../imgs/logo.jpeg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #f0f2f5;
            --sidebar-bg: #1a2b3c;
            --sidebar-hover: #233446;
            --sidebar-active: #2c5f4b;
            --topbar-bg: #ffffff;
            --card-bg: #ffffff;
            --text-dark: #1a1a2e;
            --text-muted: #6c757d;
            --text-light: #ffffff;
            --primary: #2c5f4b;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --border: #e9ecef;
            --shadow: 0 2px 10px rgba(0,0,0,0.08);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg);
            display: flex;
            min-height: 100vh;
        }

        /* ========== SIDEBAR ========== */
        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: var(--text-light);
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            overflow-y: auto;
            z-index: 100;
            transition: var(--transition);
            padding: 0;
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-logo h2 {
            font-size: 1.4rem;
            font-weight: 700;
            color: #8dceb4;
            margin: 0;
        }

        .sidebar-logo span {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 2px;
            color: rgba(255,255,255,0.5);
            display: block;
            margin-top: 2px;
        }

        .sidebar-user {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-user img {
            width: 42px;
            height: 42px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary);
        }

        .sidebar-user-info {
            flex: 1;
        }

        .sidebar-user-info .nome {
            font-weight: 600;
            font-size: 0.9rem;
            color: #fff;
        }

        .sidebar-user-info .nivel {
            font-size: 0.7rem;
            color: rgba(255,255,255,0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .sidebar-nav {
            flex: 1;
            padding: 0.75rem 0;
        }

        .sidebar-nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-nav li {
            margin: 2px 0;
        }

        .sidebar-nav li a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            padding: 0.75rem 1.5rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: var(--transition);
            border-left: 3px solid transparent;
        }

        .sidebar-nav li a:hover {
            background: var(--sidebar-hover);
            color: #fff;
            border-left-color: var(--primary);
        }

        .sidebar-nav li a.active {
            background: rgba(44, 95, 75, 0.2);
            color: #8dceb4;
            border-left-color: #8dceb4;
        }

        .sidebar-nav li a i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
        }

        .sidebar-footer {
            padding: 1rem 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-footer a {
            display: flex;
            align-items: center;
            gap: 0.85rem;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            font-size: 0.85rem;
            transition: var(--transition);
        }

        .sidebar-footer a:hover {
            color: #ff6b6b;
        }

        /* ========== MAIN CONTENT ========== */
        .main-content {
            margin-left: 260px;
            flex: 1;
            min-height: 100vh;
            transition: var(--transition);
        }

        /* ========== TOPBAR ========== */
        .topbar {
            background: var(--topbar-bg);
            padding: 1rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .topbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            cursor: pointer;
            color: var(--text-dark);
        }

        .topbar-search {
            position: relative;
            width: 300px;
        }

        .topbar-search input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.5rem;
            border: 1px solid var(--border);
            border-radius: 25px;
            font-size: 0.85rem;
            background: var(--bg);
            transition: var(--transition);
        }

        .topbar-search input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(44, 95, 75, 0.1);
        }

        .topbar-search i {
            position: absolute;
            left: 0.9rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
        }

        .topbar-right {
            display: flex;
            align-items: center;
            gap: 1.5rem;
        }

        .topbar-notification {
            position: relative;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--text-muted);
        }

        .topbar-notification .badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: var(--danger);
            color: #fff;
            font-size: 0.6rem;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .topbar-user {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }

        .topbar-user img {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            object-fit: cover;
        }

        .topbar-user span {
            font-weight: 600;
            font-size: 0.85rem;
        }

        /* ========== PAGE CONTENT ========== */
        .page-content {
            padding: 2rem;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 0.25rem;
        }

        .page-subtitle {
            color: var(--text-muted);
            font-size: 0.9rem;
            margin-bottom: 2rem;
        }

        /* ========== CARDS ========== */
        .cards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .card {
            background: var(--card-bg);
            border-radius: var(--radius);
            padding: 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            transition: var(--transition);
            border: 1px solid var(--border);
        }

        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .card-info .card-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            line-height: 1;
        }

        .card-info .card-label {
            font-size: 0.85rem;
            color: var(--text-muted);
            margin-top: 0.4rem;
        }

        .card-icon {
            width: 55px;
            height: 55px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: #fff;
        }

        .card-icon.medicamentos { background: linear-gradient(135deg, #2c5f4b, #3d7a62); }
        .card-icon.vendas { background: linear-gradient(135deg, #17a2b8, #1fc8db); }
        .card-icon.clientes { background: linear-gradient(135deg, #ffc107, #ffdb4d); }
        .card-icon.stock { background: linear-gradient(135deg, #dc3545, #ff6b6b); }

        /* ========== TABELA ========== */
        .table-container {
            background: var(--card-bg);
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            border: 1px solid var(--border);
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-header h3 {
            font-size: 1.1rem;
            font-weight: 600;
            margin: 0;
        }

        .table-header .btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            border: none;
            transition: var(--transition);
        }

        .btn-primary {
            background: var(--primary);
            color: #fff;
        }

        .btn-primary:hover {
            background: #1f4d3f;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th, table td {
            padding: 0.9rem 1.5rem;
            text-align: left;
            font-size: 0.85rem;
        }

        table th {
            background: var(--bg);
            font-weight: 600;
            color: var(--text-muted);
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 0.5px;
        }

        table tr {
            border-bottom: 1px solid var(--border);
        }

        table tr:last-child {
            border-bottom: none;
        }

        table tr:hover {
            background: rgba(44, 95, 75, 0.03);
        }

        .badge-status {
            padding: 0.25rem 0.6rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
        }

        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }

        /* ========== RESPONSIVO ========== */
        @media (max-width: 992px) {
            .sidebar {
                left: -260px;
            }
            .sidebar.open {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .menu-toggle {
                display: block;
            }
            .topbar-search {
                width: 200px;
            }
        }

        @media (max-width: 576px) {
            .topbar {
                padding: 1rem;
            }
            .topbar-search {
                display: none;
            }
            .page-content {
                padding: 1rem;
            }
            .cards-grid {
                grid-template-columns: 1fr;
            }
        }
        /* ========== MODAIS ========== */
.modal-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,0.6);
    backdrop-filter: blur(5px);
    z-index: 1000;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
}

.modal-overlay.active {
    opacity: 1;
    visibility: visible;
}

.modal {
    background: #fff;
    border-radius: 20px;
    width: 100%;
    max-width: 500px;
    box-shadow: 0 25px 60px rgba(0,0,0,0.3);
    transform: translateY(30px) scale(0.95);
    transition: all 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
}

.modal-overlay.active .modal {
    transform: translateY(0) scale(1);
}

.modal-body {
    padding: 2rem;
}

.btn {
    padding: 0.6rem 1.2rem;
    border-radius: 8px;
    font-size: 0.85rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    transition: all 0.3s;
    font-family: 'Inter', sans-serif;
}

.btn-danger {
    background: #dc3545;
    color: #fff;
}

.btn-danger:hover {
    background: #c82333;
}
    </style>
</head>
<body>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h2>FARMÁCIA LÂMIA</h2>
            <span>Painel Administrativo</span>
        </div>
        
        <div class="sidebar-user">
            <img src="../../assets/img/user-avatar.jpg" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($nomeFuncionario ?: $usuario['username']); ?>&background=2c5f4b&color=fff'">
            <div class="sidebar-user-info">
                <div class="nome"><?php echo htmlspecialchars($nomeFuncionario ?: $usuario['username']); ?></div>
                <div class="nivel"><?php echo htmlspecialchars($nivel); ?></div>
            </div>
        </div>

        <nav class="sidebar-nav">
            <ul>
                <li><a href="index.php" class="active"><i class="bi bi-grid-fill"></i> Dashboard</a></li>
                <li><a href="medicamentos.php"><i class="bi bi-capsule"></i> Medicamentos</a></li>
                
                <?php if (in_array($nivel, ['Administrador', 'Gerente'])): ?>
                <li><a href="fornecedores.php"><i class="bi bi-truck"></i> Fornecedores</a></li>
                <?php endif; ?>
                
                <li><a href="vendas.php"><i class="bi bi-cart4"></i> Vendas</a></li>
                <?php if (in_array($nivel, ['Administrador', 'Gerente'])): ?>
                <li><a href="../receitas.php"><i class="bi bi-receipt-cutoff"></i> Receitas</a></li>
                <?php endif; ?>
                <li><a href="clientes.php"><i class="bi bi-people-fill"></i> Clientes</a></li>
                
                <?php if (in_array($nivel, ['Administrador', 'Gerente'])): ?>
                <li><a href="funcionarios.php"><i class="bi bi-person-badge"></i> Funcionários</a></li>
                <li><a href="categorias.php"><i class="bi bi-tags"></i> Categorias</a></li>
                <?php endif; ?>

                <?php if ($nivel === 'Administrador'): ?>
                <li><a href="utilizadores.php"><i class="bi bi-shield-lock"></i> Utilizadores</a></li>
                <?php endif; ?>
                
                <?php if (in_array($nivel, ['Administrador', 'Gerente'])): ?>
                <li><a href="relatorios.php"><i class="bi bi-graph-up"></i> Relatórios</a></li>
                <?php endif; ?>
            </ul>
        </nav>

            <div class="sidebar-footer">
        <a href="#" onclick="abrirModalLogout()"><i class="bi bi-box-arrow-left"></i> Terminar Sessão</a>
    </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="main-content">
        <!-- TOPBAR -->
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
                <div class="topbar-search">
                    <i class="bi bi-search"></i>
                    <input type="text" placeholder="Pesquisar...">
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-notification">
                    <i class="bi bi-bell"></i>
                    <span class="badge"><?php echo $medicamentosBaixoStock; ?></span>
                </div>
                <div class="topbar-user">
                    <img src="../../assets/img/user-avatar.jpg" alt="Avatar" onerror="this.src='https://ui-avatars.com/api/?name=<?php echo urlencode($nomeFuncionario ?: $usuario['username']); ?>&background=2c5f4b&color=fff'">
                    <span><?php echo htmlspecialchars($nomeFuncionario ?: $usuario['username']); ?></span>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <div class="page-content">
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Bem-vindo de volta, <?php echo htmlspecialchars($nomeFuncionario ?: $usuario['username']); ?>!</p>

            <!-- CARDS -->
            <div class="cards-grid">
                <div class="card">
                    <div class="card-info">
                        <div class="card-number"><?php echo $totalMedicamentos; ?></div>
                        <div class="card-label">Total Medicamentos</div>
                    </div>
                    <div class="card-icon medicamentos"><i class="bi bi-capsule"></i></div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <div class="card-number"><?php echo $vendasHoje ?: '--'; ?></div>
                        <div class="card-label">Vendas Hoje</div>
                    </div>
                    <div class="card-icon vendas"><i class="bi bi-cart4"></i></div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <div class="card-number"><?php echo $totalClientes; ?></div>
                        <div class="card-label">Total Clientes</div>
                    </div>
                    <div class="card-icon clientes"><i class="bi bi-people-fill"></i></div>
                </div>

                <div class="card">
                    <div class="card-info">
                        <div class="card-number"><?php echo $medicamentosBaixoStock; ?></div>
                        <div class="card-label">Stock Baixo</div>
                    </div>
                    <div class="card-icon stock"><i class="bi bi-exclamation-triangle"></i></div>
                </div>
            </div>

            <!-- TABELA DE MEDICAMENTOS RECENTES -->
            <div class="table-container">
                <div class="table-header">
                    <h3>📋 Medicamentos com Stock Baixo</h3>
                    <a href="medicamentos.php"><button class="btn btn-primary">Ver Todos</button></a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Nome</th>
                            <th>Categoria</th>
                            <th>Stock Atual</th>
                            <th>Stock Mínimo</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $count = 0;
                        foreach ($listaMedicamentos as $med): 
                            if ($med->getQuantidadeEstoque() < 10 && $count < 5):
                                $count++;
                                $statusClass = $med->getQuantidadeEstoque() == 0 ? 'badge-danger' : 'badge-warning';
                                $statusText = $med->getQuantidadeEstoque() == 0 ? 'Esgotado' : 'Baixo';
                        ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($med->getNome()); ?></strong></td>
                            <td><?php echo htmlspecialchars($med->getIdCategoria()); ?></td>
                            <td><?php echo $med->getQuantidadeEstoque(); ?></td>
                            <td><?php echo $med->getEstoqueMinimo(); ?></td>
                            <td><span class="badge-status <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                        </tr>
                        <?php 
                            endif;
                        endforeach; 
                        if ($count == 0):
                        ?>
                        <tr>
                            <td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);">
                                ✅ Todos os medicamentos estão com stock adequado!
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<!-- MODAL DE LOGOUT -->
<div class="modal-overlay" id="modalLogout">
    <div class="modal" style="max-width:420px; text-align:center;">
        <div class="modal-body" style="padding:2.5rem;">
            <div style="font-size:4rem; margin-bottom:1rem;">👋</div>
            <h4 style="font-size:1.3rem; margin-bottom:0.5rem; color:#1a1a2e;">Terminar Sessão</h4>
            <p style="color:#6c757d; font-size:0.9rem;">Tem certeza que deseja sair do sistema?</p>
            <div style="display:flex; gap:1rem; justify-content:center; margin-top:2rem;">
                <button class="btn" onclick="document.getElementById('modalLogout').classList.remove('active')" style="background:#e9ecef; color:#1a1a2e;">Cancelar</button>
                <a href="../../logout.php" class="btn btn-danger" style="text-decoration:none;"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </div>
        </div>
    </div>
</div>

<script>
// Função para abrir modal de logout
function abrirModalLogout() {
    document.getElementById('modalLogout').classList.add('active');
}

// Função para fechar qualquer modal
function fecharModal(id) {
    document.getElementById(id).classList.remove('active');
}

// Fechar modal ao clicar fora
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', function(e) {
        if (e.target === this) {
            this.classList.remove('active');
        }
    });
});

// Fechar modal com tecla ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay.active').forEach(modal => {
            modal.classList.remove('active');
        });
    }
});

// Toggle sidebar no mobile
const menuToggle = document.getElementById('menuToggle');
const sidebar = document.getElementById('sidebar');

menuToggle.addEventListener('click', () => {
    sidebar.classList.toggle('open');
});

// Fechar sidebar ao clicar fora (mobile)
document.addEventListener('click', (e) => {
    if (window.innerWidth <= 992) {
        if (!sidebar.contains(e.target) && e.target !== menuToggle) {
            sidebar.classList.remove('open');
        }
    }
});
</script>
</body>
</html>
