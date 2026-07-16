<?php
session_start();
if (!isset($_SESSION['usuario'])) { header('Location: ../../index.php?erro=nao_autenticado'); exit; }
$usuario = $_SESSION['usuario']; $nivel = $usuario['nivel'];
if (!in_array($nivel, ['Administrador', 'Farmaceutico'])) { header('Location: index.php'); exit; }
$paginaAtual = basename($_SERVER['PHP_SELF']); $titulo = 'Fornecedores';
require_once '../../CONTROLLER/FornecedorController.php';
$controller = new FornecedorController();
$lista = $controller->listar()['data'] ?? [];
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmácia Lâmia | <?php echo $titulo; ?></title>
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
            --primary-dark: #1f4d3f;
            --success: #28a745;
            --warning: #ffc107;
            --danger: #dc3545;
            --info: #17a2b8;
            --border: #e9ecef;
            --shadow: 0 2px 10px rgba(0,0,0,0.08);
            --radius: 12px;
            --transition: all 0.3s ease;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

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
            display: flex;
            flex-direction: column;
        }

        .sidebar-logo {
            padding: 1.5rem 1.5rem 1rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            text-align: center;
        }

        .sidebar-logo h2 { font-size: 1.4rem; font-weight: 700; color: #8dceb4; margin: 0; }
        .sidebar-logo span { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 2px; color: rgba(255,255,255,0.5); display: block; margin-top: 2px; }

        .sidebar-user {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-user img { width: 42px; height: 42px; border-radius: 50%; object-fit: cover; border: 2px solid var(--primary); }
        .sidebar-user-info { flex: 1; }
        .sidebar-user-info .nome { font-weight: 600; font-size: 0.9rem; color: #fff; }
        .sidebar-user-info .nivel { font-size: 0.7rem; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px; }

        .sidebar-nav { flex: 1; padding: 0.75rem 0; }
        .sidebar-nav ul { list-style: none; padding: 0; margin: 0; }
        .sidebar-nav li { margin: 2px 0; }

        .sidebar-nav li a {
            display: flex; align-items: center; gap: 0.85rem;
            padding: 0.75rem 1.5rem; color: rgba(255,255,255,0.7);
            text-decoration: none; font-size: 0.9rem; font-weight: 500;
            transition: var(--transition); border-left: 3px solid transparent;
        }

        .sidebar-nav li a:hover { background: var(--sidebar-hover); color: #fff; border-left-color: var(--primary); }
        .sidebar-nav li a.active { background: rgba(44, 95, 75, 0.2); color: #8dceb4; border-left-color: #8dceb4; }
        .sidebar-nav li a i { font-size: 1.2rem; width: 24px; text-align: center; }

        .sidebar-footer { padding: 1rem 1.5rem; border-top: 1px solid rgba(255,255,255,0.1); }
        .sidebar-footer a { display: flex; align-items: center; gap: 0.85rem; color: rgba(255,255,255,0.5); text-decoration: none; font-size: 0.85rem; cursor: pointer; }
        .sidebar-footer a:hover { color: #ff6b6b; }

        /* ========== MAIN CONTENT ========== */
        .main-content { margin-left: 260px; flex: 1; min-height: 100vh; transition: var(--transition); }

        /* ========== TOPBAR ========== */
        .topbar {
            background: var(--topbar-bg); padding: 1rem 2rem;
            display: flex; align-items: center; justify-content: space-between;
            box-shadow: var(--shadow); position: sticky; top: 0; z-index: 50;
        }

        .topbar-left { display: flex; align-items: center; gap: 1rem; }
        .menu-toggle { display: none; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: var(--text-dark); }

        .topbar-search { position: relative; width: 300px; }
        .topbar-search input { width: 100%; padding: 0.6rem 1rem 0.6rem 2.5rem; border: 1px solid var(--border); border-radius: 25px; font-size: 0.85rem; background: var(--bg); transition: var(--transition); }
        .topbar-search input:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 3px rgba(44,95,75,0.1); }
        .topbar-search i { position: absolute; left: 0.9rem; top: 50%; transform: translateY(-50%); color: var(--text-muted); }

        .topbar-right { display: flex; align-items: center; gap: 1.5rem; }
        .topbar-notification { position: relative; cursor: pointer; font-size: 1.2rem; color: var(--text-muted); }
        .topbar-notification .badge { position: absolute; top: -6px; right: -8px; background: var(--danger); color: #fff; font-size: 0.6rem; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .topbar-user { display: flex; align-items: center; gap: 0.5rem; cursor: pointer; }
        .topbar-user img { width: 38px; height: 38px; border-radius: 50%; object-fit: cover; }
        .topbar-user span { font-weight: 600; font-size: 0.85rem; }

        /* ========== PAGE CONTENT ========== */
        .page-content { padding: 2rem; }
        .page-header { margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .page-header h1 { font-size: 1.6rem; font-weight: 700; color: var(--text-dark); }
        .page-header p { color: var(--text-muted); font-size: 0.9rem; margin-top: 0.25rem; }

        /* ========== BOTÕES ========== */
        .btn { padding: 0.6rem 1.2rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; cursor: pointer; border: none; text-decoration: none; display: inline-flex; align-items: center; gap: 0.4rem; transition: var(--transition); font-family: 'Inter', sans-serif; }
        .btn-primary { background: var(--primary); color: #fff; } .btn-primary:hover { background: var(--primary-dark); transform: translateY(-2px); box-shadow: 0 4px 15px rgba(44,95,75,0.3); }
        .btn-danger { background: var(--danger); color: #fff; } .btn-danger:hover { background: #c82333; }
        .btn-warning { background: var(--warning); color: #000; }
        .btn-xs { padding: 0.3rem 0.6rem; font-size: 0.75rem; }

        /* ========== TABELA ========== */
        .table-container { background: var(--card-bg); border-radius: var(--radius); box-shadow: var(--shadow); overflow: hidden; border: 1px solid var(--border); }
        .table-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; }
        .table-header h3 { font-size: 1.1rem; font-weight: 600; margin: 0; }
        table { width: 100%; border-collapse: collapse; }
        table th, table td { padding: 0.9rem 1.5rem; text-align: left; font-size: 0.85rem; }
        table th { background: var(--bg); font-weight: 600; color: var(--text-muted); text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; }
        table tr { border-bottom: 1px solid var(--border); } table tr:last-child { border-bottom: none; } table tr:hover { background: rgba(44,95,75,0.03); }
        .acoes { display: flex; gap: 0.3rem; }

        /* ========== MODAIS ========== */
        .modal-overlay { position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(5px); z-index: 1000; display: flex; align-items: center; justify-content: center; padding: 1.5rem; opacity: 0; visibility: hidden; transition: all 0.3s ease; }
        .modal-overlay.active { opacity: 1; visibility: visible; }
        .modal { background: #fff; border-radius: 20px; width: 100%; max-width: 600px; box-shadow: 0 25px 60px rgba(0,0,0,0.3); transform: translateY(30px) scale(0.95); transition: all 0.4s cubic-bezier(0.175,0.885,0.32,1.275); max-height: 90vh; overflow-y: auto; }
        .modal-overlay.active .modal { transform: translateY(0) scale(1); }
        .modal-header { padding: 1.5rem 2rem; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .modal-header h3 { font-size: 1.3rem; font-weight: 700; color: var(--text-dark); margin: 0; }
        .modal-close { background: #f5f5f5; border: none; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 1.2rem; color: #666; transition: all 0.3s; display: flex; align-items: center; justify-content: center; }
        .modal-close:hover { background: var(--danger); color: #fff; transform: rotate(90deg); }
        .modal-body { padding: 2rem; }
        .modal-footer { padding: 1rem 2rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 0.75rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label { display: block; font-weight: 600; font-size: 0.85rem; color: var(--text-dark); margin-bottom: 0.4rem; }
        .form-group input, .form-group select { width: 100%; padding: 0.75rem 1rem; border: 2px solid var(--border); border-radius: 10px; font-size: 0.9rem; transition: all 0.3s; font-family: 'Inter', sans-serif; }
        .form-group input:focus, .form-group select:focus { outline: none; border-color: var(--primary); box-shadow: 0 0 0 4px rgba(44,95,75,0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        .delete-modal .modal { max-width: 420px; text-align: center; }
        .delete-icon { font-size: 3rem; color: var(--danger); margin-bottom: 1rem; }
        .delete-modal .modal-body h4 { font-size: 1.2rem; margin-bottom: 0.5rem; color: var(--text-dark); }
        .delete-modal .modal-body p { color: var(--text-muted); font-size: 0.9rem; }

        /* ========== TOAST ========== */
        .toast { position: fixed; top: 20px; right: 20px; background: var(--success); color: #fff; padding: 1rem 1.5rem; border-radius: 10px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); z-index: 2000; transform: translateX(120%); transition: transform 0.4s cubic-bezier(0.175,0.885,0.32,1.275); display: flex; align-items: center; gap: 0.75rem; font-weight: 500; }
        .toast.show { transform: translateX(0); } .toast.error { background: var(--danger); }

        /* ========== RESPONSIVO ========== */
        @media (max-width: 992px) { .sidebar { left: -260px; } .sidebar.open { left: 0; } .main-content { margin-left: 0; } .menu-toggle { display: block; } .topbar-search { width: 200px; } }
        @media (max-width: 576px) { .topbar { padding: 1rem; } .topbar-search { display: none; } .page-content { padding: 1rem; } .form-row { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

    <!-- TOAST -->
    <div class="toast" id="toast"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo">
            <h2>FARMÁCIA LÂMIA</h2>
            <span>Painel Administrativo</span>
        </div>
        <div class="sidebar-user">
            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['username']); ?>&background=2c5f4b&color=fff" alt="Avatar">
            <div class="sidebar-user-info">
                <div class="nome"><?php echo htmlspecialchars($usuario['username']); ?></div>
                <div class="nivel"><?php echo htmlspecialchars($nivel); ?></div>
            </div>
        </div>
        <nav class="sidebar-nav"><ul>
            <li><a href="index.php" class="active"><i class="bi bi-grid-fill"></i> Dashboard</a></li>
            <li><a href="medicamentos.php"><i class="bi bi-capsule"></i> Medicamentos</a></li>
            <li><a href="fornecedores.php"><i class="bi bi-truck"></i> Fornecedores</a></li>
            <li><a href="vendas.php"><i class="bi bi-cart4"></i> Vendas</a></li>
            <li><a href="clientes.php"><i class="bi bi-people-fill"></i> Clientes</a></li>
            <li><a href="relatorios.php"><i class="bi bi-graph-up"></i> Relatórios</a></li>
        </ul></nav>
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
                    <input type="text" placeholder="Pesquisar fornecedores..." id="buscaTopo">
                </div>
            </div>
            <div class="topbar-right">
                <div class="topbar-notification">
                    <i class="bi bi-bell"></i>
                    <span class="badge"><?php echo count($lista); ?></span>
                </div>
                <div class="topbar-user">
                    <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['username']); ?>&background=2c5f4b&color=fff" alt="Avatar">
                    <span><?php echo htmlspecialchars($usuario['username']); ?></span>
                </div>
            </div>
        </header>

        <!-- PAGE CONTENT -->
        <div class="page-content">
            <div class="page-header">
                <div>
                    <h1><i class="bi bi-truck"></i> <?php echo $titulo; ?></h1>
                    <p>Gerir fornecedores de medicamentos</p>
                </div>
                <button class="btn btn-primary" onclick="abrirModalCadastro()"><i class="bi bi-plus-lg"></i> Novo Fornecedor</button>
            </div>

            <div class="table-container">
                <div class="table-header">
                    <h3>Todos os Fornecedores (<?php echo count($lista); ?>)</h3>
                    <div class="search-box">
                        <input type="text" placeholder="Buscar fornecedor..." id="busca" style="padding:0.5rem 1rem;border:1px solid var(--border);border-radius:20px;font-size:0.85rem;width:250px;outline:none;">
                    </div>
                </div>
                <table>
                    <thead>
                        <tr><th>Empresa</th><th>Representante</th><th>Telefone</th><th>Email</th><th>Ações</th></tr>
                    </thead>
                    <tbody id="tabelaBody">
                        <?php foreach ($lista as $f): ?>
                        <tr data-id="<?php echo $f->getIdFornecedor(); ?>">
                            <td><strong><?php echo htmlspecialchars($f->getEmpresa()); ?></strong></td>
                            <td><?php echo htmlspecialchars($f->getRepresentante() ?: '—'); ?></td>
                            <td><?php echo $f->getTelefone() ?: '—'; ?></td>
                            <td><?php echo $f->getEmail() ?: '—'; ?></td>
                            <td class="acoes">
                                <button class="btn btn-warning btn-xs" onclick="abrirModalEdicao(<?php echo $f->getIdFornecedor(); ?>, '<?php echo htmlspecialchars(addslashes($f->getEmpresa())); ?>', '<?php echo htmlspecialchars(addslashes($f->getRepresentante())); ?>', '<?php echo htmlspecialchars(addslashes($f->getTelefone())); ?>', '<?php echo htmlspecialchars(addslashes($f->getEmail())); ?>', '<?php echo htmlspecialchars(addslashes($f->getEndereco())); ?>')"><i class="bi bi-pencil"></i></button>
                                <button class="btn btn-danger btn-xs" onclick="abrirModalExclusao(<?php echo $f->getIdFornecedor(); ?>, '<?php echo htmlspecialchars(addslashes($f->getEmpresa())); ?>')"><i class="bi bi-trash"></i></button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (count($lista) == 0): ?>
                        <tr><td colspan="5" style="text-align:center; padding:2rem; color:var(--text-muted);"><i class="bi bi-inbox" style="font-size:2rem; display:block; margin-bottom:0.5rem;"></i>Nenhum fornecedor cadastrado.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- MODAL DE CADASTRO / EDIÇÃO -->
    <div class="modal-overlay" id="modalCadastro">
        <div class="modal">
            <div class="modal-header">
                <h3 id="modalTitulo">Novo Fornecedor</h3>
                <button class="modal-close" onclick="fecharModal('modalCadastro')"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="modal-body">
                <form id="formFornecedor">
                    <input type="hidden" id="fornecedorId">
                    <div class="form-group">
                        <label>Empresa <span style="color:var(--danger);">*</span></label>
                        <input type="text" id="empresa" placeholder="Nome da empresa" required>
                    </div>
                    <div class="form-group">
                        <label>Representante</label>
                        <input type="text" id="representante" placeholder="Nome do representante">
                    </div>
                    <div class="form-row">
                        <div class="form-group"><label>Telefone</label><input type="text" id="telefone" placeholder="Ex: +244 923 456 789"></div>
                        <div class="form-group"><label>Email</label><input type="email" id="email" placeholder="fornecedor@empresa.com"></div>
                    </div>
                    <div class="form-group">
                        <label>Endereço</label>
                        <input type="text" id="endereco" placeholder="Morada completa">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn" onclick="fecharModal('modalCadastro')" style="background:#e9ecef; color:var(--text-dark);">Cancelar</button>
                <button class="btn btn-primary" onclick="salvarFornecedor()"><i class="bi bi-check-lg"></i> Salvar</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE EXCLUSÃO -->
    <div class="modal-overlay delete-modal" id="modalExclusao">
        <div class="modal">
            <div class="modal-body" style="padding:2.5rem;">
                <div class="delete-icon"><i class="bi bi-exclamation-triangle-fill"></i></div>
                <h4>Confirmar Exclusão</h4>
                <p>Tem certeza que deseja excluir o fornecedor <strong id="nomeExcluir"></strong>?</p>
                <p style="color:var(--danger); font-size:0.8rem; margin-top:0.5rem;">Esta ação não pode ser desfeita!</p>
            </div>
            <div class="modal-footer" style="justify-content:center; border-top:none; padding-top:0;">
                <button class="btn" onclick="fecharModal('modalExclusao')" style="background:#e9ecef; color:var(--text-dark);">Cancelar</button>
                <button class="btn btn-danger" id="btnConfirmarExclusao"><i class="bi bi-trash"></i> Excluir</button>
            </div>
        </div>
    </div>

    <!-- MODAL DE LOGOUT -->
    <div class="modal-overlay" id="modalLogout">
        <div class="modal" style="max-width:420px; text-align:center;">
            <div class="modal-body" style="padding:2.5rem;">
                <div style="font-size:3rem; color:var(--danger); margin-bottom:1rem;"><i class="bi bi-box-arrow-right"></i></div>
                <h4 style="font-size:1.2rem; margin-bottom:0.5rem; color:var(--text-dark);">Terminar Sessão</h4>
                <p style="color:var(--text-muted); font-size:0.9rem;">Tem certeza que deseja sair do sistema?</p>
                <div style="display:flex; gap:1rem; justify-content:center; margin-top:2rem;">
                    <button class="btn" onclick="fecharModal('modalLogout')" style="background:#e9ecef; color:var(--text-dark);">Cancelar</button>
                    <a href="../../logout.php" class="btn btn-danger" style="text-decoration:none;"><i class="bi bi-box-arrow-right"></i> Sair</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        const API_FORNECEDOR='../../CONTROLLER/FornecedorController.php';
        let fornecedorEmEdicao=null;
        const menuToggle=document.getElementById('menuToggle'),sidebar=document.getElementById('sidebar');
        menuToggle.addEventListener('click',()=>sidebar.classList.toggle('open'));
        document.addEventListener('click',(e)=>{if(window.innerWidth<=992&&!sidebar.contains(e.target)&&e.target!==menuToggle)sidebar.classList.remove('open')});

        function abrirModalCadastro(){fornecedorEmEdicao=null;document.getElementById('modalTitulo').textContent='Novo Fornecedor';document.getElementById('formFornecedor').reset();document.getElementById('fornecedorId').value='';document.getElementById('modalCadastro').classList.add('active')}
        async function carregarFornecedor(id){const resposta=await fetch(API_FORNECEDOR,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'buscar',idFornecedor:id})});const dados=await resposta.json();if(!dados.success)throw new Error(dados.message||'Não foi possível carregar o fornecedor.');return dados.data}
        async function abrirModalEdicao(id){try{const forn=await carregarFornecedor(id);fornecedorEmEdicao=id;document.getElementById('modalTitulo').textContent='Editar Fornecedor';document.getElementById('fornecedorId').value=id;document.getElementById('empresa').value=forn.empresa||'';document.getElementById('representante').value=forn.representante||'';document.getElementById('telefone').value=forn.telefone||'';document.getElementById('email').value=forn.email||'';document.getElementById('endereco').value=forn.endereco||'';document.getElementById('modalCadastro').classList.add('active')}catch(erro){mostrarToast(erro.message||'Erro ao carregar fornecedor.',true)}}
        function abrirModalExclusao(id,nome){document.getElementById('nomeExcluir').textContent=nome;document.getElementById('btnConfirmarExclusao').onclick=()=>excluirFornecedor(id);document.getElementById('modalExclusao').classList.add('active')}
        function abrirModalLogout(){document.getElementById('modalLogout').classList.add('active')}
        function fecharModal(id){document.getElementById(id).classList.remove('active')}
        async function salvarFornecedor(){const empresa=document.getElementById('empresa').value.trim();if(!empresa){mostrarToast('Preencha o nome da empresa!',true);return}const payload={action:fornecedorEmEdicao?'atualizar':'cadastrar',idFornecedor:fornecedorEmEdicao||'',empresa,representante:document.getElementById('representante').value.trim(),telefone:document.getElementById('telefone').value.trim(),email:document.getElementById('email').value.trim(),endereco:document.getElementById('endereco').value.trim()};try{const resposta=await fetch(API_FORNECEDOR,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify(payload)});const dados=await resposta.json();if(!dados.success)throw new Error(dados.message||'Não foi possível salvar o fornecedor.');fecharModal('modalCadastro');mostrarToast(dados.message||'Fornecedor salvo com sucesso!');setTimeout(()=>location.reload(),900)}catch(erro){mostrarToast(erro.message||'Erro de conexão com o servidor.',true)}}
        async function excluirFornecedor(id){try{const resposta=await fetch(API_FORNECEDOR,{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({action:'apagar',idFornecedor:id})});const dados=await resposta.json();if(!dados.success)throw new Error(dados.message||'Não foi possível excluir o fornecedor.');fecharModal('modalExclusao');mostrarToast(dados.message||'Fornecedor excluído com sucesso!');const row=document.querySelector(`tr[data-id="${id}"]`);if(row)row.remove()}catch(erro){mostrarToast(erro.message||'Erro de conexão com o servidor.',true)}}
        function mostrarToast(msg,erro=false){const t=document.getElementById('toast');t.textContent=msg;t.className='toast '+(erro?'error':'');t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3000)}

        document.getElementById('busca').addEventListener('input',function(){const t=this.value.toLowerCase();document.querySelectorAll('#tabelaBody tr').forEach(tr=>{tr.style.display=tr.textContent.toLowerCase().includes(t)?'':'none'})});
        document.getElementById('buscaTopo').addEventListener('input',function(){document.getElementById('busca').value=this.value;document.getElementById('busca').dispatchEvent(new Event('input'))});
        document.querySelectorAll('.modal-overlay').forEach(o=>{o.addEventListener('click',function(e){if(e.target===this)this.classList.remove('active')})});
        document.addEventListener('keydown',function(e){if(e.key==='Escape')document.querySelectorAll('.modal-overlay.active').forEach(m=>m.classList.remove('active'))});
    </script>
</body>
</html>
