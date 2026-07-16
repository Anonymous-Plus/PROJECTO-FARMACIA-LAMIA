<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['nivel'] !== 'Atendente') {
    header('Location: ../../index.php?erro=nao_autenticado');
    exit;
}
$usuario = $_SESSION['usuario'];
$nivel = $usuario['nivel'];
$paginaAtual = basename($_SERVER['PHP_SELF']);

require_once '../../CONTROLLER/VendaController.php';
require_once '../../CONTROLLER/ClienteController.php';
require_once '../../CONTROLLER/MedicamentoController.php';

$vendCtrl = new VendaController();
$cliCtrl = new ClienteController();
$medCtrl = new MedicamentoController();

$vendas = $vendCtrl->listar()['data'] ?? [];
$clientes = $cliCtrl->listar()['data'] ?? [];
$medicamentos = $medCtrl->listar()['data'] ?? [];

$vendasHoje = 0; $hoje = date('Y-m-d'); $faturamentoHoje = 0;
foreach ($vendas as $v) {
    if (date('Y-m-d', strtotime($v->getDataVenda())) == $hoje) {
        $vendasHoje++;
        $faturamentoHoje += $v->getValorTotal();
    }
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Farmácia Lâmia | PDV</title>
    <link rel="shortcut icon" href="../imgs/logo.jpeg" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root{--bg:#f0f2f5;--sidebar-bg:#1a2b3c;--sidebar-hover:#233446;--primary:#2c5f4b;--primary-dark:#1f4d3f;--text-dark:#1a1a2e;--text-muted:#6c757d;--text-light:#fff;--border:#e9ecef;--shadow:0 2px 10px rgba(0,0,0,0.08);--radius:12px;--danger:#dc3545;--warning:#ffc107;--success:#28a745;--info:#17a2b8;--transition:all 0.3s ease}
        *{margin:0;padding:0;box-sizing:border-box}body{font-family:'Inter',sans-serif;background:var(--bg);display:flex;min-height:100vh}
        .sidebar{width:260px;background:var(--sidebar-bg);color:var(--text-light);position:fixed;top:0;left:0;height:100vh;overflow-y:auto;z-index:100;display:flex;flex-direction:column}
        .sidebar-logo{padding:1.5rem 1.5rem 1rem;border-bottom:1px solid rgba(255,255,255,0.1);text-align:center}
        .sidebar-logo h2{font-size:1.4rem;font-weight:700;color:#8dceb4;margin:0}
        .sidebar-logo span{font-size:0.7rem;text-transform:uppercase;letter-spacing:2px;color:rgba(255,255,255,0.5);display:block;margin-top:2px}
        .sidebar-user{padding:1rem 1.5rem;border-bottom:1px solid rgba(255,255,255,0.1);display:flex;align-items:center;gap:0.75rem}
        .sidebar-user img{width:42px;height:42px;border-radius:50%;border:2px solid var(--primary)}
        .sidebar-user-info .nome{font-weight:600;font-size:0.9rem;color:#fff}
        .sidebar-user-info .nivel{font-size:0.7rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:1px}
        .sidebar-nav{flex:1;padding:0.75rem 0}.sidebar-nav ul{list-style:none}.sidebar-nav li a{display:flex;align-items:center;gap:0.85rem;padding:0.75rem 1.5rem;color:rgba(255,255,255,0.7);text-decoration:none;font-size:0.9rem;font-weight:500;transition:var(--transition);border-left:3px solid transparent}
        .sidebar-nav li a:hover,.sidebar-nav li a.active{background:rgba(44,95,75,0.2);color:#8dceb4;border-left-color:#8dceb4}
        .sidebar-nav li a i{font-size:1.2rem;width:24px;text-align:center}
        .sidebar-footer{padding:1rem 1.5rem;border-top:1px solid rgba(255,255,255,0.1)}
        .sidebar-footer a{display:flex;align-items:center;gap:0.85rem;color:rgba(255,255,255,0.5);text-decoration:none;font-size:0.85rem;cursor:pointer}.sidebar-footer a:hover{color:#ff6b6b}
        .main-content{margin-left:260px;flex:1;min-height:100vh}
        .topbar{background:#fff;padding:1rem 2rem;display:flex;align-items:center;justify-content:space-between;box-shadow:var(--shadow);position:sticky;top:0;z-index:50}
        .menu-toggle{display:none;background:none;border:none;font-size:1.5rem;cursor:pointer;color:var(--text-dark)}
        .topbar-user{display:flex;align-items:center;gap:0.5rem}.topbar-user img{width:38px;height:38px;border-radius:50%}.topbar-user span{font-weight:600;font-size:0.85rem}
        .page-content{padding:2rem}
        .page-header{margin-bottom:2rem;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:1rem}
        .page-header h1{font-size:1.6rem;font-weight:700;color:var(--text-dark)}
        .btn{padding:0.6rem 1.2rem;border-radius:8px;font-size:0.85rem;font-weight:600;cursor:pointer;border:none;display:inline-flex;align-items:center;gap:0.4rem;transition:var(--transition);font-family:'Inter',sans-serif}
        .btn-primary{background:var(--primary);color:#fff}.btn-primary:hover{background:var(--primary-dark);transform:translateY(-2px);box-shadow:0 4px 15px rgba(44,95,75,0.3)}
        .btn-success{background:var(--success);color:#fff}.btn-danger{background:var(--danger);color:#fff}.btn-xs{padding:0.3rem 0.6rem;font-size:0.75rem}
        .cards-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:1rem;margin-bottom:1.5rem}
        .card{background:#fff;border-radius:var(--radius);padding:1.2rem;box-shadow:var(--shadow);text-align:center}
        .card .card-number{font-size:1.8rem;font-weight:700;color:var(--text-dark)}.card .card-label{font-size:0.8rem;color:var(--text-muted);margin-top:0.25rem}
        .pdv-container{display:grid;grid-template-columns:1fr 1fr;gap:1.5rem}
        .pdv-produtos{background:#fff;border-radius:var(--radius);padding:1.5rem;box-shadow:var(--shadow)}
        .pdv-carrinho{background:#fff;border-radius:var(--radius);padding:1.5rem;box-shadow:var(--shadow);display:flex;flex-direction:column}
        .pdv-carrinho h3{margin-bottom:1rem;font-size:1.1rem;font-weight:600}
        .form-group{margin-bottom:1rem}.form-group label{display:block;font-weight:600;font-size:0.85rem;margin-bottom:0.35rem;color:var(--text-dark)}
        .form-group select,.form-group input{width:100%;padding:0.7rem 1rem;border:2px solid var(--border);border-radius:10px;font-size:0.9rem;font-family:'Inter',sans-serif}
        .form-group select:focus,.form-group input:focus{outline:none;border-color:var(--primary);box-shadow:0 0 0 3px rgba(44,95,75,0.1)}
        .lista-medicamentos{max-height:350px;overflow-y:auto;margin-top:0.5rem}
        .med-item{display:flex;justify-content:space-between;align-items:center;padding:0.75rem;border:1px solid var(--border);border-radius:8px;margin-bottom:0.5rem;cursor:pointer;transition:var(--transition)}
        .med-item:hover{background:rgba(44,95,75,0.05);border-color:var(--primary)}
        .med-item .med-nome{font-weight:600;font-size:0.9rem}.med-item .med-preco{color:var(--primary);font-weight:600;font-size:0.9rem}
        .carrinho-itens{flex:1;overflow-y:auto;max-height:300px;margin-bottom:1rem}
        .carrinho-item{display:flex;justify-content:space-between;align-items:center;padding:0.6rem 0;border-bottom:1px solid var(--border)}
        .carrinho-total{text-align:right;font-size:1.4rem;font-weight:700;color:var(--primary);padding-top:1rem;border-top:2px solid var(--border);margin-top:auto}
        .modal-overlay{position:fixed;inset:0;background:rgba(0,0,0,0.6);backdrop-filter:blur(5px);z-index:1000;display:flex;align-items:center;justify-content:center;padding:1.5rem;opacity:0;visibility:hidden;transition:all 0.3s}
        .modal-overlay.active{opacity:1;visibility:visible}
        .modal{background:#fff;border-radius:20px;width:100%;max-width:420px;box-shadow:0 25px 60px rgba(0,0,0,0.3);transform:translateY(30px) scale(0.95);transition:all 0.4s cubic-bezier(0.175,0.885,0.32,1.275)}
        .modal-overlay.active .modal{transform:translateY(0) scale(1)}
        .modal-body{padding:2.5rem;text-align:center}
        .toast{position:fixed;top:20px;right:20px;background:var(--success);color:#fff;padding:1rem 1.5rem;border-radius:10px;box-shadow:0 10px 30px rgba(0,0,0,0.2);z-index:2000;transform:translateX(120%);transition:transform 0.4s}
        .toast.show{transform:translateX(0)}.toast.error{background:var(--danger)}
        @media(max-width:992px){.sidebar{left:-260px}.sidebar.open{left:0}.main-content{margin-left:0}.menu-toggle{display:block}.pdv-container{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="toast" id="toast"></div>

    <!-- SIDEBAR -->
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-logo"><h2>FARMÁCIA LÂMIA</h2><span>Painel Atendente</span></div>
        <div class="sidebar-user"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['username']); ?>&background=2c5f4b&color=fff" alt=""><div class="sidebar-user-info"><div class="nome"><?php echo htmlspecialchars($usuario['username']); ?></div><div class="nivel"><?php echo htmlspecialchars($nivel); ?></div></div></div>
        <nav class="sidebar-nav"><ul>
            <li><a href="index.php"><i class="bi bi-grid-fill"></i> Dashboard</a></li>
            <li><a href="vendas.php" class="active"><i class="bi bi-cart4"></i> Nova Venda</a></li>
            <li><a href="medicamentos.php"><i class="bi bi-capsule"></i> Medicamentos</a></li>
            <li><a href="clientes.php"><i class="bi bi-people-fill"></i> Clientes</a></li>
        </ul></nav>
        <div class="sidebar-footer"><a href="#" onclick="abrirModalLogout()"><i class="bi bi-box-arrow-left"></i> Terminar Sessão</a></div>
    </aside>

    <!-- MAIN -->
    <div class="main-content">
        <header class="topbar">
            <button class="menu-toggle" id="menuToggle"><i class="bi bi-list"></i></button>
            <div class="topbar-user"><img src="https://ui-avatars.com/api/?name=<?php echo urlencode($usuario['username']); ?>&background=2c5f4b&color=fff" alt=""><span><?php echo htmlspecialchars($usuario['username']); ?></span></div>
        </header>
        <div class="page-content">
            <div class="page-header"><h1><i class="bi bi-cart4"></i> Ponto de Venda</h1></div>
            
            <div class="cards-grid">
                <div class="card"><div class="card-number"><?php echo $vendasHoje; ?></div><div class="card-label">Vendas Hoje</div></div>
                <div class="card"><div class="card-number"><?php echo number_format($faturamentoHoje,0); ?> KZ</div><div class="card-label">Faturamento Hoje</div></div>
                <div class="card"><div class="card-number"><?php echo count($medicamentos); ?></div><div class="card-label">Medicamentos</div></div>
            </div>

            <div class="pdv-container">
                <!-- LISTA DE MEDICAMENTOS -->
                <div class="pdv-produtos">
                    <h3 style="margin-bottom:1rem;"><i class="bi bi-capsule"></i> Medicamentos Disponíveis</h3>
                    <div class="form-group"><input type="text" id="buscaMed" placeholder="Buscar medicamento..." oninput="filtrarMedicamentos()"></div>
                    <div class="lista-medicamentos" id="listaMedicamentos">
                        <?php foreach($medicamentos as $m): if($m->getQuantidadeEstoque() > 0): ?>
                        <div class="med-item" onclick="adicionarAoCarrinho(<?php echo $m->getIdMedicamento(); ?>, '<?php echo htmlspecialchars(addslashes($m->getNome())); ?>', <?php echo $m->getPrecoVenda(); ?>)">
                            <div><div class="med-nome"><?php echo htmlspecialchars($m->getNome()); ?></div><small style="color:var(--text-muted)">Stock: <?php echo $m->getQuantidadeEstoque(); ?></small></div>
                            <div class="med-preco"><?php echo number_format($m->getPrecoVenda(),2); ?> KZ</div>
                        </div>
                        <?php endif; endforeach; ?>
                    </div>
                </div>

                <!-- CARRINHO -->
                <div class="pdv-carrinho">
                    <h3><i class="bi bi-cart3"></i> Carrinho</h3>
                    <div class="form-row" style="display:grid;grid-template-columns:1fr 1fr;gap:0.5rem;margin-bottom:0.5rem;">
                        <div class="form-group"><label>Cliente</label><select id="vendaCliente"><option value="">Não registado</option><?php foreach($clientes as $c): ?><option value="<?php echo $c->getIdCliente(); ?>"><?php echo htmlspecialchars($c->getNome()); ?></option><?php endforeach; ?></select></div>
                        <div class="form-group"><label>Pagamento</label><select id="vendaPagamento"><option value="Dinheiro">Dinheiro</option><option value="Cartao">Cartão</option><option value="Transferencia">Transferência</option><option value="Multicaixa Express">Multicaixa Express</option></select></div>
                    </div>
                    <div class="carrinho-itens" id="carrinhoItens"><p style="text-align:center;color:var(--text-muted);padding:2rem;">Carrinho vazio</p></div>
                    <div class="carrinho-total" id="carrinhoTotal">Total: 0.00 KZ</div>
                    <button class="btn btn-success" style="width:100%;margin-top:1rem;justify-content:center;" onclick="finalizarVenda()"><i class="bi bi-check-lg"></i> Finalizar Venda</button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL LOGOUT -->
    <div class="modal-overlay" id="modalLogout"><div class="modal" style="text-align:center;"><div class="modal-body"><div style="font-size:3rem;color:var(--danger);margin-bottom:1rem;"><i class="bi bi-box-arrow-right"></i></div><h4 style="font-size:1.2rem;color:var(--text-dark);">Terminar Sessão</h4><p style="color:var(--text-muted);">Tem certeza que deseja sair?</p><div style="display:flex;gap:1rem;justify-content:center;margin-top:2rem;"><button class="btn" onclick="document.getElementById('modalLogout').classList.remove('active')" style="background:#e9ecef;">Cancelar</button><a href="../../logout.php" class="btn btn-danger" style="text-decoration:none;"><i class="bi bi-box-arrow-right"></i> Sair</a></div></div></div></div>

    <script>
        function abrirModalLogout(){document.getElementById('modalLogout').classList.add('active')}
        const menuToggle=document.getElementById('menuToggle'),sidebar=document.getElementById('sidebar');menuToggle.addEventListener('click',()=>sidebar.classList.toggle('open'));document.addEventListener('click',(e)=>{if(window.innerWidth<=992&&!sidebar.contains(e.target)&&e.target!==menuToggle)sidebar.classList.remove('open')});
        
        let carrinho=[];
        function adicionarAoCarrinho(id,nome,preco){const exist=carrinho.find(i=>i.id==id);exist?exist.qtd++:carrinho.push({id,nome,preco,qtd:1});atualizarCarrinho()}
        function atualizarCarrinho(){const c=document.getElementById('carrinhoItens');let t=0;if(carrinho.length===0){c.innerHTML='<p style="text-align:center;color:var(--text-muted);padding:2rem;">Carrinho vazio</p>'}else{c.innerHTML=carrinho.map((item,i)=>{const s=item.preco*item.qtd;t+=s;return`<div class="carrinho-item"><span><strong>${item.nome}</strong> x${item.qtd}</span><span style="font-weight:600;">${s.toFixed(2)} KZ</span><button class="btn btn-danger btn-xs" onclick="removerItem(${i})"><i class="bi bi-trash"></i></button></div>`}).join('')}document.getElementById('carrinhoTotal').textContent=`Total: ${t.toFixed(2)} KZ`}
        function removerItem(i){carrinho.splice(i,1);atualizarCarrinho()}
        function finalizarVenda(){
            if(carrinho.length===0){mostrarToast('Adicione medicamentos ao carrinho!',true);return}

            const payload = {
                action: 'cadastrar',
                idCliente: parseInt(document.getElementById('vendaCliente').value) || 0,
                idFuncionario: <?php echo (int)$usuario['idFuncionario']; ?>,
                formaPagamento: document.getElementById('vendaPagamento').value,
                valorTotal: carrinho.reduce((total, item) => total + (item.preco * item.qtd), 0),
                itens: carrinho
            };

            const btn = document.querySelector('.btn-success');
            btn.disabled = true;
            btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';

            fetch('../../CONTROLLER/VendaController.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(payload)
            })
            .then(r=>r.json())
            .then(d=>{
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Finalizar Venda';
                if(d.success){
                    mostrarToast('Venda finalizada com sucesso!');
                    carrinho=[];
                    atualizarCarrinho();
                    setTimeout(()=>location.reload(),1000);
                } else {
                    mostrarToast('Erro: '+(d.message||'Erro desconhecido'),true);
                }
            })
            .catch(()=>{
                btn.disabled = false;
                btn.innerHTML = '<i class="bi bi-check-lg"></i> Finalizar Venda';
                mostrarToast('Erro de conexao com o servidor!',true);
            });
        }
        function filtrarMedicamentos(){const t=document.getElementById('buscaMed').value.toLowerCase();document.querySelectorAll('#listaMedicamentos .med-item').forEach(item=>{item.style.display=item.textContent.toLowerCase().includes(t)?'':'none'})}
        function mostrarToast(msg,erro=false){const t=document.getElementById('toast');t.textContent=msg;t.className='toast '+(erro?'error':'');t.classList.add('show');setTimeout(()=>t.classList.remove('show'),3000)}
        document.querySelectorAll('.modal-overlay').forEach(o=>{o.addEventListener('click',function(e){if(e.target===this)this.classList.remove('active')})});
        document.addEventListener('keydown',function(e){if(e.key==='Escape')document.querySelectorAll('.modal-overlay.active').forEach(m=>m.classList.remove('active'))});
    </script>
</body>
</html>
