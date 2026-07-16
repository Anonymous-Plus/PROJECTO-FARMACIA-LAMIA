<?php
session_start();
if (!isset($_SESSION['usuario']) || !in_array($_SESSION['usuario']['nivel'], ['Administrador', 'Farmaceutico'])) {
    header('Location: ../index.php?erro=nao_autenticado');
    exit;
}

$usuario = $_SESSION['usuario'];
$nivel = $usuario['nivel'];
$basePath = $nivel === 'Farmaceutico' ? 'Farmaceutico' : 'Admin';

require_once __DIR__ . '/../CONTROLLER/ReceitaController.php';
require_once __DIR__ . '/../CONTROLLER/ClienteController.php';
require_once __DIR__ . '/../CONTROLLER/MedicamentoController.php';
require_once __DIR__ . '/../MODEL/DAO/ReceitaMedicamentoDAO.php';

$receitaCtrl = new ReceitaController();
$clienteCtrl = new ClienteController();
$medicamentoCtrl = new MedicamentoController();
$receitaMedicamentoDAO = new ReceitaMedicamentoDAO();

$receitas = $receitaCtrl->listar()['data'] ?? [];
$totalReceitas = $receitaCtrl->contar()['total'] ?? 0;
$clientes = $clienteCtrl->listar()['data'] ?? [];
$medicamentos = $medicamentoCtrl->listar()['data'] ?? [];

function receitaMedNecessita($med)
{
    $valor = $med->getNecessitaReceita();
    return $valor === 'Sim' || $valor === 1 || $valor === '1' || $valor === true;
}

$medicamentosReceita = array_values(array_filter($medicamentos, 'receitaMedNecessita'));
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmácia Lâmia | Receitas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root {
            --primary:#2c5f4b;
            --primary-dark:#214738;
            --secondary:#f5f7f2;
            --accent:#d9a441;
            --danger:#b54b4b;
            --success:#2f855a;
            --warning:#c77700;
            --text:#1f2937;
            --muted:#6b7280;
            --card:#ffffff;
            --border:#e5e7eb;
            --shadow:0 14px 40px rgba(15,23,42,.10);
        }
        *{box-sizing:border-box}
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:linear-gradient(135deg,#f7faf7 0%,#eef4ef 100%);color:var(--text)}
        a{text-decoration:none;color:inherit}
        .sidebar{position:fixed;inset:0 auto 0 0;width:260px;background:linear-gradient(180deg,var(--primary-dark),var(--primary));color:#fff;padding:1.5rem 1.2rem;display:flex;flex-direction:column;box-shadow:var(--shadow);z-index:20}
        .sidebar-logo h2{margin:0;font-size:1.35rem;letter-spacing:.5px}
        .sidebar-logo span{display:block;margin-top:.3rem;color:rgba(255,255,255,.7);font-size:.85rem}
        .sidebar-user{display:flex;align-items:center;gap:.9rem;margin:1.5rem 0;padding:1rem;background:rgba(255,255,255,.09);border-radius:18px}
        .sidebar-user img,.topbar-user img{width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid rgba(255,255,255,.2)}
        .sidebar-user .nome,.topbar-user span{font-weight:700}
        .sidebar-user .nivel{font-size:.75rem;color:rgba(255,255,255,.7);text-transform:uppercase;letter-spacing:1px}
        .sidebar-nav ul{list-style:none;padding:0;margin:0;display:flex;flex-direction:column;gap:.35rem;flex:1}
        .sidebar-nav a{display:flex;align-items:center;gap:.8rem;padding:.9rem 1rem;border-radius:14px;color:#eaf3ee;transition:.2s}
        .sidebar-nav a:hover,.sidebar-nav a.active{background:rgba(255,255,255,.12);transform:translateX(3px)}
        .sidebar-footer{margin-top:auto;padding-top:1rem}
        .sidebar-footer a{display:flex;align-items:center;gap:.8rem;padding:.9rem 1rem;border-radius:14px;background:rgba(255,255,255,.08);color:#fff}
        .main-content{margin-left:260px;min-height:100vh;padding:1.25rem 1.25rem 2rem}
        .topbar{display:flex;align-items:center;justify-content:space-between;background:rgba(255,255,255,.78);backdrop-filter:blur(12px);border:1px solid rgba(255,255,255,.55);box-shadow:var(--shadow);border-radius:22px;padding:.9rem 1rem;margin-bottom:1.2rem}
        .topbar-left,.topbar-right{display:flex;align-items:center;gap:1rem}
        .menu-toggle{display:none;border:none;background:var(--primary);color:#fff;width:42px;height:42px;border-radius:12px;font-size:1.1rem}
        .topbar-search{position:relative}
        .topbar-search input{padding:.8rem 1rem .8rem 2.5rem;border:1px solid var(--border);border-radius:14px;min-width:270px;background:#fff}
        .topbar-search i{position:absolute;left:.9rem;top:50%;transform:translateY(-50%);color:var(--muted)}
        .page-content{max-width:1400px}
        .page-title{font-size:2rem;margin:0 0 .2rem}
        .page-subtitle{margin:0 0 1.4rem;color:var(--muted)}
        .cards{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:1rem;margin-bottom:1rem}
        .card{background:var(--card);border:1px solid rgba(229,231,235,.9);box-shadow:var(--shadow);border-radius:20px;padding:1rem;display:flex;justify-content:space-between;align-items:center}
        .card-number{font-size:2rem;font-weight:800;color:var(--primary)}
        .card-label{color:var(--muted);font-size:.92rem}
        .card-icon{width:54px;height:54px;border-radius:16px;display:grid;place-items:center;color:#fff;font-size:1.35rem}
        .card-icon.receitas{background:linear-gradient(135deg,#2c5f4b,#4c8d71)}
        .card-icon.medicamentos{background:linear-gradient(135deg,#0f766e,#14b8a6)}
        .card-icon.clientes{background:linear-gradient(135deg,#9a6b2f,#d9a441)}
        .card-icon.print{background:linear-gradient(135deg,#7c3aed,#a855f7)}
        .toolbar{display:flex;gap:.75rem;flex-wrap:wrap;justify-content:space-between;align-items:center;background:rgba(255,255,255,.85);border:1px solid var(--border);box-shadow:var(--shadow);border-radius:20px;padding:1rem;margin-bottom:1rem}
        .btn{border:none;border-radius:14px;padding:.9rem 1.1rem;font-weight:700;cursor:pointer;display:inline-flex;align-items:center;gap:.5rem;transition:.2s}
        .btn-primary{background:var(--primary);color:#fff}
        .btn-primary:hover{background:var(--primary-dark)}
        .btn-secondary{background:#e8efe9;color:var(--primary)}
        .btn-danger{background:var(--danger);color:#fff}
        .btn-light{background:#f3f4f6;color:var(--text)}
        .table-wrap{background:var(--card);border:1px solid var(--border);box-shadow:var(--shadow);border-radius:24px;overflow:hidden}
        .table-head{padding:1rem 1.1rem;display:flex;justify-content:space-between;align-items:center;border-bottom:1px solid var(--border)}
        table{width:100%;border-collapse:collapse}
        th,td{padding:1rem;text-align:left;border-bottom:1px solid #eef2f7;vertical-align:top}
        th{font-size:.78rem;text-transform:uppercase;letter-spacing:.7px;color:var(--muted);background:#fbfcfb}
        tr:hover td{background:#fbfdfb}
        .badge{display:inline-flex;align-items:center;gap:.3rem;padding:.4rem .7rem;border-radius:999px;font-size:.78rem;font-weight:700}
        .badge-ok{background:#e6f6ee;color:#136f43}
        .badge-soft{background:#eef2ff;color:#4338ca}
        .actions{display:flex;gap:.45rem;flex-wrap:wrap}
        .actions .btn{padding:.6rem .75rem;border-radius:12px;font-size:.85rem}
        .modal-overlay{position:fixed;inset:0;background:rgba(15,23,42,.55);display:none;align-items:center;justify-content:center;padding:1rem;z-index:50}
        .modal-overlay.active{display:flex}
        .modal{background:#fff;width:min(980px,100%);border-radius:24px;box-shadow:var(--shadow);max-height:92vh;overflow:auto}
        .modal-header{display:flex;justify-content:space-between;align-items:center;padding:1rem 1.2rem;border-bottom:1px solid var(--border)}
        .modal-body{padding:1.2rem}
        .modal-close{border:none;background:#f3f4f6;width:40px;height:40px;border-radius:12px;font-size:1rem;cursor:pointer}
        .grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:1rem}
        .grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:1rem}
        .form-group{display:flex;flex-direction:column;gap:.45rem}
        .form-group label{font-size:.9rem;font-weight:700}
        .form-group input,.form-group select,.form-group textarea{padding:.9rem 1rem;border:1px solid var(--border);border-radius:14px;font:inherit}
        .form-group textarea{min-height:96px;resize:vertical}
        .section-title{margin:1rem 0 .75rem;font-size:1rem}
        .items-table{width:100%;border-collapse:collapse}
        .items-table th,.items-table td{padding:.75rem;border-bottom:1px solid #eef2f7}
        .items-tools{display:flex;gap:.75rem;flex-wrap:wrap;align-items:center;margin:.5rem 0 1rem}
        .muted{color:var(--muted)}
        .toast{position:fixed;right:1rem;bottom:1rem;background:#111827;color:#fff;padding:1rem 1.1rem;border-radius:16px;box-shadow:var(--shadow);display:none;z-index:60;max-width:360px}
        .toast.show{display:block}
        .toast.error{background:var(--danger)}
        .receipt-preview{padding:1rem;border:1px dashed #d1d5db;border-radius:18px;background:#fafdfb}
        .receipt-header{display:flex;justify-content:space-between;gap:1rem;align-items:flex-start;border-bottom:2px solid #dfe9e4;padding-bottom:1rem;margin-bottom:1rem}
        .receipt-header h3{margin:0;color:var(--primary)}
        .receipt-meta{font-size:.92rem;color:var(--muted)}
        .receipt-items{width:100%;border-collapse:collapse;margin-top:1rem}
        .receipt-items th,.receipt-items td{padding:.6rem .5rem;border-bottom:1px solid #edf2ee;font-size:.95rem}
        @media (max-width:1100px){
            .sidebar{transform:translateX(-100%);transition:.25s}
            .sidebar.open{transform:translateX(0)}
            .main-content{margin-left:0}
            .menu-toggle{display:inline-flex}
            .cards,.grid,.grid-3{grid-template-columns:1fr}
            .topbar{flex-direction:column;align-items:stretch}
            .topbar-search input{min-width:0;width:100%}
        }
        @media print{
            body{background:#fff}
            .sidebar,.topbar,.toolbar,.cards,.table-wrap,.toast,.no-print{display:none !important}
            .main-content{margin:0;padding:0}
            .receipt-print{display:block !important}
        }
        .receipt-print{display:none}
    </style>
</head>
<body>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h2>FARMÁCIA LÂMIA</h2>
            <span>Painel <?php echo $basePath === 'Admin' ? 'Admin' : 'Farmacêutico'; ?></span>
        </div>
        <div class="sidebar-user">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['username']); ?>&background=2c5f4b&color=fff" alt="Avatar">
            <div>
                <div class="nome"><?php echo htmlspecialchars($usuario['username']); ?></div>
                <div class="nivel"><?php echo htmlspecialchars($nivel); ?></div>
            </div>
        </div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="<?php echo $basePath; ?>/index.php"><i class="bi bi-grid-fill"></i> Dashboard</a></li>
                <li><a href="<?php echo $basePath; ?>/medicamentos.php"><i class="bi bi-capsule"></i> Medicamentos</a></li>
                <li><a href="<?php echo $basePath; ?>/clientes.php"><i class="bi bi-people-fill"></i> Clientes</a></li>
                <li><a href="<?php echo $basePath; ?>/vendas.php"><i class="bi bi-cart4"></i> Vendas</a></li>
                <li><a href="receitas.php" class="active"><i class="bi bi-receipt-cutoff"></i> Receitas</a></li>
                <?php if ($nivel === 'Administrador'): ?>
                    <li><a href="<?php echo $basePath; ?>/utilizadores.php"><i class="bi bi-shield-lock"></i> Utilizadores</a></li>
                <?php endif; ?>
                <?php if ($nivel === 'Administrador' || $nivel === 'Farmaceutico'): ?>
                    <li><a href="<?php echo $basePath; ?>/fornecedores.php"><i class="bi bi-truck"></i> Fornecedores</a></li>
                    <li><a href="<?php echo $basePath; ?>/categorias.php"><i class="bi bi-tags"></i> Categorias</a></li>
                    <li><a href="<?php echo $basePath; ?>/funcionarios.php"><i class="bi bi-person-badge"></i> Funcionários</a></li>
                <?php endif; ?>
                <?php if ($basePath === 'Admin'): ?>
                    <li><a href="<?php echo $basePath; ?>/relatorios.php"><i class="bi bi-graph-up"></i> Relatórios</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="sidebar-footer">
            <a href="#" onclick="abrirModalLogout()"><i class="bi bi-box-arrow-left"></i> Terminar Sessão</a>
        </div>
    </aside>

    <div class="main-content">
        <header class="topbar">
            <div class="topbar-left">
                <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
                <div class="topbar-search">
                    <i class="bi bi-search"></i>
                    <input type="text" id="buscaTopo" placeholder="Pesquisar receitas, clientes ou médicos...">
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-user">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['username']); ?>&background=2c5f4b&color=fff" alt="">
                    <span><?php echo htmlspecialchars($usuario['username']); ?></span>
                </div>
            </div>
        </header>

        <div class="page-content">
            <h1 class="page-title"><i class="bi bi-receipt-cutoff"></i> Receitas</h1>
            <p class="page-subtitle">Criar, editar, imprimir e exportar receitas médicas.</p>

            <div class="cards">
                <div class="card">
                    <div>
                        <div class="card-number"><?php echo (int)$totalReceitas; ?></div>
                        <div class="card-label">Total de Receitas</div>
                    </div>
                    <div class="card-icon receitas"><i class="bi bi-receipt-cutoff"></i></div>
                </div>
                <div class="card">
                    <div>
                        <div class="card-number"><?php echo count($clientes); ?></div>
                        <div class="card-label">Clientes Disponíveis</div>
                    </div>
                    <div class="card-icon clientes"><i class="bi bi-people-fill"></i></div>
                </div>
                <div class="card">
                    <div>
                        <div class="card-number"><?php echo count($medicamentosReceita); ?></div>
                        <div class="card-label">Medicamentos com Receita</div>
                    </div>
                    <div class="card-icon medicamentos"><i class="bi bi-capsule-pill"></i></div>
                </div>
                <div class="card">
                    <div>
                        <div class="card-number">PDF</div>
                        <div class="card-label">Impressão / Exportação</div>
                    </div>
                    <div class="card-icon print"><i class="bi bi-printer"></i></div>
                </div>
            </div>

            <div class="toolbar no-print">
                <div class="muted">Receitas só podem ser emitidas por Administrador ou Farmacêutico.</div>
                <div class="actions">
                    <button class="btn btn-secondary" onclick="abrirModalReceita()"><i class="bi bi-plus-lg"></i> Nova Receita</button>
                    <button class="btn btn-light" onclick="location.reload()"><i class="bi bi-arrow-clockwise"></i> Atualizar</button>
                </div>
            </div>

            <div class="table-wrap">
                <div class="table-head">
                    <strong>Listagem de Receitas</strong>
                    <span class="muted"><?php echo count($receitas); ?> registos</span>
                </div>
                <div style="overflow:auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Número</th>
                                <th>Cliente</th>
                                <th>Médico</th>
                                <th>Data</th>
                                <th>Medicamentos</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody id="tabelaBody">
                            <?php foreach ($receitas as $r): ?>
                                <tr data-id="<?php echo (int)$r->getIdReceita(); ?>" data-text="<?php echo strtolower(htmlspecialchars($r->getNumeroReceita() . ' ' . $r->getMedico() . ' ' . ($r->getNomeCliente() ?? ''))); ?>">
                                    <td><strong><?php echo htmlspecialchars($r->getNumeroReceita()); ?></strong></td>
                                    <td><?php echo htmlspecialchars($r->getNomeCliente() ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($r->getMedico()); ?></td>
                                    <td><?php echo date('d/m/Y', strtotime($r->getDataReceita())); ?></td>
                                    <td><span class="badge badge-soft"><i class="bi bi-capsule"></i> <?php echo (int)$receitaMedicamentoDAO->contarMedicamentosReceita((int)$r->getIdReceita()); ?></span></td>
                                    <td>
                                        <div class="actions">
                                            <button class="btn btn-light" onclick="verReceita(<?php echo (int)$r->getIdReceita(); ?>)"><i class="bi bi-eye"></i></button>
                                            <button class="btn btn-secondary" onclick="editarReceita(<?php echo (int)$r->getIdReceita(); ?>)"><i class="bi bi-pencil"></i></button>
                                            <button class="btn btn-primary" onclick="imprimirReceita(<?php echo (int)$r->getIdReceita(); ?>)"><i class="bi bi-printer"></i></button>
                                            <button class="btn btn-danger" onclick="abrirModalExclusao(<?php echo (int)$r->getIdReceita(); ?>, '<?php echo htmlspecialchars(addslashes($r->getNumeroReceita())); ?>')"><i class="bi bi-trash"></i></button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (empty($receitas)): ?>
                                <tr><td colspan="6" class="muted" style="text-align:center;padding:2rem;">Nenhuma receita cadastrada.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalReceita">
        <div class="modal">
            <div class="modal-header">
                <strong id="modalTitulo">Nova Receita</strong>
                <button class="modal-close" onclick="fecharModal('modalReceita')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div class="grid">
                    <div class="form-group">
                        <label>Número da Receita</label>
                        <input type="text" id="numeroReceita">
                    </div>
                    <div class="form-group">
                        <label>Cliente</label>
                        <select id="idCliente">
                            <option value="">Selecionar cliente</option>
                            <?php foreach ($clientes as $c): ?>
                                <option value="<?php echo (int)$c->getIdCliente(); ?>"><?php echo htmlspecialchars($c->getNome()); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="grid">
                    <div class="form-group">
                        <label>Médico</label>
                        <input type="text" id="medico">
                    </div>
                    <div class="form-group">
                        <label>CRM</label>
                        <input type="text" id="crm">
                    </div>
                </div>
                <div class="grid">
                    <div class="form-group">
                        <label>Data da Receita</label>
                        <input type="date" id="dataReceita">
                    </div>
                    <div class="form-group">
                        <label>Observação</label>
                        <input type="text" id="observacao">
                    </div>
                </div>

                <h4 class="section-title">Medicamentos da Receita</h4>
                <div class="items-tools">
                    <select id="medicamentoSelect" style="min-width:260px;padding:.9rem 1rem;border:1px solid var(--border);border-radius:14px;">
                        <option value="">Selecionar medicamento</option>
                        <?php foreach ($medicamentosReceita as $m): ?>
                            <option value="<?php echo (int)$m->getIdMedicamento(); ?>" data-nome="<?php echo htmlspecialchars($m->getNome()); ?>" data-dosagem="<?php echo htmlspecialchars($m->getDosagem()); ?>">
                                <?php echo htmlspecialchars($m->getNome() . ' - ' . ($m->getDosagem() ?: 'Sem dosagem')); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" id="quantidadeMedicamento" min="1" value="1" style="width:110px;padding:.9rem 1rem;border:1px solid var(--border);border-radius:14px;">
                    <button class="btn btn-secondary" type="button" onclick="adicionarItem()"><i class="bi bi-plus-lg"></i> Adicionar</button>
                </div>

                <table class="items-table">
                    <thead>
                        <tr>
                            <th>Medicamento</th>
                            <th>Qtd</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody id="itensBody">
                        <tr><td colspan="3" class="muted" style="text-align:center;padding:1rem;">Nenhum medicamento adicionado.</td></tr>
                    </tbody>
                </table>

                <div style="display:flex;justify-content:flex-end;gap:.75rem;margin-top:1rem;">
                    <button class="btn btn-light" type="button" onclick="fecharModal('modalReceita')">Cancelar</button>
                    <button class="btn btn-primary" type="button" onclick="salvarReceita()"><i class="bi bi-check-lg"></i> Salvar</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalVisualizar">
        <div class="modal" style="width:min(860px,100%);">
            <div class="modal-header">
                <strong>Detalhes da Receita</strong>
                <button class="modal-close" onclick="fecharModal('modalVisualizar')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <div id="conteudoVisualizar" class="receipt-preview"></div>
                <div class="actions no-print" style="justify-content:flex-end;margin-top:1rem;">
                    <button class="btn btn-primary" onclick="imprimirAtual()"><i class="bi bi-printer"></i> Imprimir / Exportar PDF</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalExclusao">
        <div class="modal" style="width:min(460px,100%);text-align:center;">
            <div class="modal-header">
                <strong>Confirmar Exclusão</strong>
                <button class="modal-close" onclick="fecharModal('modalExclusao')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <p>Tem certeza que deseja excluir a receita <strong id="nomeExcluir"></strong>?</p>
                <p class="muted">Os medicamentos associados também serão removidos.</p>
                <div class="actions" style="justify-content:center;margin-top:1rem;">
                    <button class="btn btn-light" onclick="fecharModal('modalExclusao')">Cancelar</button>
                    <button class="btn btn-danger" id="btnConfirmarExclusao">Excluir</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modalLogout">
        <div class="modal" style="width:min(420px,100%);text-align:center;">
            <div class="modal-header">
                <strong>Terminar Sessão</strong>
                <button class="modal-close" onclick="fecharModal('modalLogout')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <p>Deseja sair da sua sessão agora?</p>
                <div class="actions" style="justify-content:center;margin-top:1rem;">
                    <button class="btn btn-light" onclick="fecharModal('modalLogout')">Cancelar</button>
                    <a class="btn btn-danger" href="../logout.php">Sair</a>
                </div>
            </div>
        </div>
    </div>

    <div id="toast" class="toast"></div>

    <div class="receipt-print" id="receiptPrint"></div>

    <script>
        const API_RECEITA = '../CONTROLLER/ReceitaController.php';
        const API_CLIENTE = '../CONTROLLER/ClienteController.php';
        const API_MEDICAMENTO = '../CONTROLLER/MedicamentoController.php';
        let receitaEmEdicao = null;
        let receitaAtual = null;
        let itens = [];

        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        menuToggle.addEventListener('click', () => sidebar.classList.toggle('open'));

        function abrirModalReceita() {
            receitaEmEdicao = null;
            itens = [];
            receitaAtual = null;
            document.getElementById('modalTitulo').textContent = 'Nova Receita';
            document.getElementById('numeroReceita').value = gerarNumero();
            document.getElementById('idCliente').value = '';
            document.getElementById('medico').value = '';
            document.getElementById('crm').value = '';
            document.getElementById('dataReceita').value = new Date().toISOString().slice(0,10);
            document.getElementById('observacao').value = '';
            renderItens();
            document.getElementById('modalReceita').classList.add('active');
        }

        function fecharModal(id) {
            document.getElementById(id).classList.remove('active');
        }

        function gerarNumero() {
            const d = new Date();
            const pad = n => String(n).padStart(2, '0');
            return `RX-${d.getFullYear()}${pad(d.getMonth()+1)}${pad(d.getDate())}-${pad(d.getHours())}${pad(d.getMinutes())}${pad(d.getSeconds())}`;
        }

        function adicionarItem() {
            const sel = document.getElementById('medicamentoSelect');
            const id = parseInt(sel.value || '0', 10);
            const qtd = parseInt(document.getElementById('quantidadeMedicamento').value || '0', 10);
            if (!id || qtd < 1) {
                mostrarToast('Selecione um medicamento e informe a quantidade.', true);
                return;
            }

            const existente = itens.find(i => i.idMedicamento === id);
            const option = sel.options[sel.selectedIndex];
            const nome = option.getAttribute('data-nome') || option.textContent;

            if (existente) {
                existente.quantidade += qtd;
            } else {
                itens.push({ idMedicamento: id, nome, quantidade: qtd });
            }

            renderItens();
        }

        function removerItem(index) {
            itens.splice(index, 1);
            renderItens();
        }

        function renderItens() {
            const tbody = document.getElementById('itensBody');
            if (!itens.length) {
                tbody.innerHTML = '<tr><td colspan="3" class="muted" style="text-align:center;padding:1rem;">Nenhum medicamento adicionado.</td></tr>';
                return;
            }

            tbody.innerHTML = itens.map((item, index) => `
                <tr>
                    <td><strong>${escapeHtml(item.nome)}</strong></td>
                    <td>${item.quantidade}</td>
                    <td><button class="btn btn-danger" type="button" onclick="removerItem(${index})"><i class="bi bi-trash"></i></button></td>
                </tr>
            `).join('');
        }

        async function salvarReceita() {
            const payload = {
                action: receitaEmEdicao ? 'atualizar' : 'cadastrar',
                idReceita: receitaEmEdicao || '',
                numeroReceita: document.getElementById('numeroReceita').value.trim(),
                idCliente: document.getElementById('idCliente').value,
                medico: document.getElementById('medico').value.trim(),
                crm: document.getElementById('crm').value.trim(),
                dataReceita: document.getElementById('dataReceita').value,
                observacao: document.getElementById('observacao').value.trim(),
                itens: itens.map(i => ({ idMedicamento: i.idMedicamento, quantidade: i.quantidade }))
            };

            try {
                const resposta = await fetch(API_RECEITA, {
                    method: 'POST',
                    headers: {'Content-Type':'application/json'},
                    body: JSON.stringify(payload)
                });
                const dados = await resposta.json();
                if (!dados.success) throw new Error(dados.message || 'Nao foi possivel salvar a receita.');
                fecharModal('modalReceita');
                mostrarToast(dados.message || 'Receita salva com sucesso!');
                setTimeout(() => location.reload(), 700);
            } catch (erro) {
                mostrarToast(erro.message || 'Erro de conexão com o servidor.', true);
            }
        }

        async function carregarReceita(id) {
            const resposta = await fetch(API_RECEITA, {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({ action: 'buscar', idReceita: id })
            });
            const dados = await resposta.json();
            if (!dados.success) throw new Error(dados.message || 'Nao foi possivel carregar a receita.');
            return dados;
        }

        async function editarReceita(id) {
            try {
                const dados = await carregarReceita(id);
                const r = dados.data;
                receitaEmEdicao = id;
                receitaAtual = dados;
                itens = (dados.itens || []).map(item => ({
                    idMedicamento: parseInt(item.idMedicamento || 0, 10),
                    nome: item.nomeMedicamento || item.nome || 'Medicamento',
                    quantidade: parseInt(item.quantidade || 0, 10)
                }));

                document.getElementById('modalTitulo').textContent = 'Editar Receita';
                document.getElementById('numeroReceita').value = r.numeroReceita || '';
                document.getElementById('idCliente').value = r.idCliente || '';
                document.getElementById('medico').value = r.medico || '';
                document.getElementById('crm').value = r.crm || '';
                document.getElementById('dataReceita').value = (r.dataReceita || '').slice(0,10);
                document.getElementById('observacao').value = r.observacao || '';
                renderItens();
                document.getElementById('modalReceita').classList.add('active');
            } catch (erro) {
                mostrarToast(erro.message || 'Erro ao carregar receita.', true);
            }
        }

        async function verReceita(id) {
            try {
                const dados = await carregarReceita(id);
                receitaAtual = dados;
                const r = dados.data;
                const itensHtml = (dados.itens || []).map(item => `
                    <tr>
                        <td>${escapeHtml(item.nomeMedicamento || item.nome || '')}</td>
                        <td>${escapeHtml(item.quantidade || '')}</td>
                    </tr>
                `).join('');

                document.getElementById('conteudoVisualizar').innerHTML = `
                    <div class="receipt-header">
                        <div>
                            <h3>${escapeHtml(r.numeroReceita || 'Receita')}</h3>
                            <div class="receipt-meta">Cliente: ${escapeHtml(r.nomeCliente || '')}</div>
                            <div class="receipt-meta">Médico: ${escapeHtml(r.medico || '')} | CRM: ${escapeHtml(r.crm || '')}</div>
                        </div>
                        <div class="receipt-meta" style="text-align:right;">
                            <div><strong>Data:</strong> ${formatarData(r.dataReceita)}</div>
                            <div><strong>Observação:</strong> ${escapeHtml(r.observacao || 'Sem observação')}</div>
                        </div>
                    </div>
                    <table class="receipt-items">
                        <thead><tr><th>Medicamento</th><th>Qtd</th></tr></thead>
                        <tbody>${itensHtml || '<tr><td colspan="2" class="muted">Sem itens.</td></tr>'}</tbody>
                    </table>
                `;
                document.getElementById('modalVisualizar').classList.add('active');
            } catch (erro) {
                mostrarToast(erro.message || 'Erro ao carregar receita.', true);
            }
        }

        function imprimirAtual() {
            if (!receitaAtual) {
                mostrarToast('Carregue primeiro uma receita.', true);
                return;
            }
            abrirJanelaImpressao(receitaAtual);
        }

        async function imprimirReceita(id) {
            try {
                const dados = await carregarReceita(id);
                abrirJanelaImpressao(dados);
            } catch (erro) {
                mostrarToast(erro.message || 'Erro ao preparar impressão.', true);
            }
        }

        function abrirJanelaImpressao(dados) {
            const r = dados.data;
            const itensHtml = (dados.itens || []).map(item => `
                <tr>
                    <td>${escapeHtml(item.nomeMedicamento || item.nome || '')}</td>
                    <td>${escapeHtml(item.quantidade || '')}</td>
                </tr>
            `).join('');

            const win = window.open('', '_blank', 'width=900,height=700');
            win.document.write(`
                <html><head><title>${escapeHtml(r.numeroReceita || 'Receita')}</title>
                <style>
                    body{font-family:Arial,sans-serif;padding:24px;color:#1f2937}
                    .box{border:1px solid #dbe5dd;border-radius:16px;padding:20px}
                    h1{margin:0 0 8px;color:#2c5f4b}
                    .meta{color:#6b7280;font-size:14px;margin-bottom:6px}
                    table{width:100%;border-collapse:collapse;margin-top:16px}
                    th,td{padding:10px;border-bottom:1px solid #e5e7eb;text-align:left}
                    .actions{margin-top:16px;display:flex;gap:10px}
                    .btn{padding:10px 14px;border:none;border-radius:10px;cursor:pointer}
                    .btn-primary{background:#2c5f4b;color:#fff}
                    .btn-light{background:#e5e7eb;color:#111827}
                    @media print{.actions{display:none}}
                </style></head><body>
                <div class="box">
                    <h1>Receita ${escapeHtml(r.numeroReceita || '')}</h1>
                    <div class="meta"><strong>Cliente:</strong> ${escapeHtml(r.nomeCliente || '')}</div>
                    <div class="meta"><strong>Médico:</strong> ${escapeHtml(r.medico || '')}</div>
                    <div class="meta"><strong>CRM:</strong> ${escapeHtml(r.crm || '')}</div>
                    <div class="meta"><strong>Data:</strong> ${formatarData(r.dataReceita)}</div>
                    <div class="meta"><strong>Observação:</strong> ${escapeHtml(r.observacao || 'Sem observação')}</div>
                    <table>
                        <thead><tr><th>Medicamento</th><th>Quantidade</th></tr></thead>
                        <tbody>${itensHtml || '<tr><td colspan="2">Sem itens.</td></tr>'}</tbody>
                    </table>
                </div>
                <div class="actions">
                    <button class="btn btn-primary" onclick="window.print()">Imprimir / Exportar PDF</button>
                    <button class="btn btn-light" onclick="window.close()">Fechar</button>
                </div>
                </body></html>
            `);
            win.document.close();
        }

        function abrirModalExclusao(id, nome) {
            document.getElementById('nomeExcluir').textContent = nome;
            document.getElementById('btnConfirmarExclusao').onclick = () => excluirReceita(id);
            document.getElementById('modalExclusao').classList.add('active');
        }

        async function excluirReceita(id) {
            try {
                const resposta = await fetch(API_RECEITA, {
                    method:'POST',
                    headers:{'Content-Type':'application/json'},
                    body: JSON.stringify({action:'apagar', idReceita:id})
                });
                const dados = await resposta.json();
                if (!dados.success) throw new Error(dados.message || 'Nao foi possivel excluir a receita.');
                fecharModal('modalExclusao');
                mostrarToast(dados.message || 'Receita excluída com sucesso!');
                setTimeout(() => location.reload(), 500);
            } catch (erro) {
                mostrarToast(erro.message || 'Erro de conexão com o servidor.', true);
            }
        }

        function mostrarToast(msg, erro=false) {
            const t = document.getElementById('toast');
            t.textContent = msg;
            t.className = 'toast' + (erro ? ' error' : '');
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3000);
        }

        function formatarData(data) {
            if (!data) return '';
            const d = new Date(data);
            if (isNaN(d.getTime())) return data;
            return d.toLocaleDateString('pt-PT');
        }

        function escapeHtml(text) {
            return String(text ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#39;');
        }

        document.getElementById('buscaTopo').addEventListener('input', function () {
            const termo = this.value.toLowerCase();
            document.querySelectorAll('#tabelaBody tr').forEach(tr => {
                if (!tr.dataset.text) return;
                tr.style.display = tr.dataset.text.includes(termo) ? '' : 'none';
            });
        });

        document.querySelectorAll('.modal-overlay').forEach(o => {
            o.addEventListener('click', function (e) {
                if (e.target === this) this.classList.remove('active');
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.modal-overlay.active').forEach(m => m.classList.remove('active'));
            }
        });

        function abrirModalLogout() {
            document.getElementById('modalLogout').classList.add('active');
        }
    </script>
</body>
</html>
