<?php
require_once __DIR__ . '/../MODEL/DAO/UtilizadorDAO.php';

// ========== TRATAMENTO DE ERROS ==========
$erro = '';
$sucesso = '';
$temAdmin = false;

try {
    $utilizadorDAO = new UtilizadorDAO();
    $temAdmin = count($utilizadorDAO->listarPorNivel('Administrador')) > 0;
} catch (Exception $e) {
    $temAdmin = false;
}
if (isset($_GET['erro'])) {
    switch ($_GET['erro']) {
        case 'login_invalido':
            $erro = 'Username ou senha inválidos!';
            break;
        case 'campos_vazios':
            $erro = 'Preencha todos os campos!';
            break;
        case 'nao_autenticado':
            $erro = 'Faça login para aceder ao sistema.';
            break;
        default:
            $erro = urldecode($_GET['erro']);
            break;
    }
}
if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'admin_criado') {
    $sucesso = 'Administrador criado com sucesso. Já pode iniciar sessão.';
}
?>
<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Farmácia | Excelência em Cuidados</title>
    <link rel="shortcut icon" href="imgs/logo.jpeg" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-pb3K4eQc+2Pd4l9MZalw+Y4f7Gg/jP4mA4F7pfjY03iIuL6WmGLraJZ+ue0HGXKat5TQ3ODCrdkqyGD8j+4w/A==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="css/index.css">
</head>
<body>

    <header>
        <div class="container">
            <div class="navbar">
                <div class="logo">
                    <h1>FARMÁCIA LÂMIA</h1>
                    <p>FARMÁCIA & BOTICA</p>
                </div>
                <button class="menu-toggle" aria-label="Abrir menu">
                    <i class="fas fa-bars"></i>
                </button>
                <ul class="nav-links">
                    <li><a href="#home">Início</a></li>
                    <li><a href="#sobre">A Farmácia</a></li>
                    <li><a href="#medicamentos">Medicamentos</a></li>
                    <li><a href="#infoextra">Serviços</a></li>
                    <li><a href="#infoextra">Clientes</a></li>
                    <li><a href="#contato">Contato</a></li>
                    <li><a href="#" id="botaoLoginModal">Login</a></li>
                </ul>
            </div>
        </div>
    </header>

    <main>
        <section id="sessao">
            <h2>Bem-Vindo a Farmácia</h2>
        </section>

        <section id="home" class="hero container">
            <?php if ($sucesso): ?>
            <div class="alert alert-success" style="margin:1rem auto 0;max-width:900px;"><?php echo htmlspecialchars($sucesso); ?></div>
            <?php endif; ?>
            <div class="farmacia-galeria">
                <div class="card-foto">
                    <img src="imgs/231.jpg" alt="Interior da Farmácia Lâmia">
                    <div class="legenda"> Ambiente acolhedor e moderno</div>
                </div>
                <div class="card-foto">
                    <img src="imgs/flu-disease-healthcare-medicine-concept-happy-african-american-male-doctor-white-coat-present-new-drugs-cure-from-disease-viruses-showing-pills-guarantee-good-quality-treatment.jpg" alt="Balcão de atendimento">
                    <div class="legenda"> Atendimento personalizado</div>
                </div>
                <div class="card-foto">
                    <img src="imgs/empty-drugstore-with-bottles-packages-full-with-medicaments-retail-shop-shelves-with-pharmaceutical-products-pharmacy-space-filled-with-medical-drugs-pills-vitamins-boxes.jpg" alt="Seção de medicamentos">
                    <div class="legenda"> Estoque completo e confiável</div>
                </div>
            </div>
        </section>

        <section id="sobre" class="sobre container">
            <h2 class="section-title">Sobre a Farmácia Lâmia</h2>
            <div class="row align-items-center">
                <div class="col-md-6">
                    <div class="sobre-texto">
                        <p>Fundada em 2026 por Maria Cardoso, Andreia Rodrigues, Ilda Benjamim, Leonel Sampaio e Albino Calenga, a <strong>Farmácia Lâmia</strong> nasceu do sonho de oferecer um atendimento humano, próximo e de excelência.</p>
                        <p>Desde o primeiro dia, acreditamos que cada cliente merece ser tratado como parte da nossa família — com empatia, conhecimento técnico e um sorriso genuíno.</p>
                        <h5 class="mt-4"><i class="fas fa-star text-warning"></i> Os nossos pilares:</h5>
                        <ul>
                            <li><strong>Confiança:</strong> Trabalhamos apenas com medicamentos certificados e fornecedores de renome.</li>
                            <li><strong>Inovação:</strong> Investimos em tecnologia para agilizar o seu atendimento e garantir segurança.</li>
                            <li><strong>Comunidade:</strong> Realizamos ações de saúde, rastreios gratuitos e palestras educativas.</li>
                            <li><strong>Cuidado integral:</strong> Serviços farmacêuticos personalizados.</li>
                        </ul>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="sobre-imagem">
                        <img src="imgs/equipe_lamia.jpg" alt="Equipa da Farmácia Lâmia" class="img-fluid rounded shadow" style="border-radius: 12px;">
                        <p class="text-center mt-2"><em>A nossa equipa: profissionais dedicados ao seu bem-estar.</em></p>
                    </div>
                </div>
            </div>
            <div class="row mt-5">
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-bullseye fa-3x text-success mb-3"></i>
                            <h5>Missão</h5>
                            <p>Proporcionar saúde e bem-estar à comunidade, oferecendo medicamentos de qualidade, orientação farmacêutica especializada e um atendimento acolhedor.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-eye fa-3x text-success mb-3"></i>
                            <h5>Visão</h5>
                            <p>Ser a farmácia de referência em Luanda, reconhecida pela confiança, inovação nos serviços farmacêuticos e pelo impacto positivo na saúde.</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <i class="fas fa-heart fa-3x text-success mb-3"></i>
                            <h5>Valores</h5>
                            <p>Ética, respeito, compromisso com a vida, transparência, responsabilidade social e amor ao próximo.</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mt-5">
                <h4 class="text-center">A nossa história</h4>
                <ul class="timeline list-unstyled mt-3">
                    <li><strong>2026:</strong> Fundação da Farmácia Lâmia – um sonho de cinco amigos.</li>
                    <li><strong>2027:</strong> Implementação do serviço de entregas ao domicílio.</li>
                    <li><strong>2028:</strong> Parceria com clínicas locais para acompanhamento farmacoterapêutico.</li>
                    <li><strong>Hoje:</strong> Uma equipa de 12 profissionais, mais de 5.000 clientes fidelizados.</li>
                </ul>
            </div>
        </section>

        <section id="medicamentos" class="medicamentos container">
            <h2 class="section-title">Medicamentos em Destaque</h2>
            <div class="cards-container">
                <div class="med-card"><div class="med-icon"><i class="fas fa-capsules"></i></div><h3>Amoxacilina</h3><div class="med-desc">Antibiótico de amplo espectro. Eficaz em infecções respiratórias e urinárias.</div><div class="preco">800 KZ</div></div>
                <div class="med-card"><div class="med-icon"><i class="fas fa-tablets"></i></div><h3>Paracetamol 750mg</h3><div class="med-desc">Alívio rápido para febre e dores leves a moderadas. Ação segura.</div><div class="preco">1.000 KZ</div></div>
                <div class="med-card"><div class="med-icon"><i class="fas fa-syringe"></i></div><h3>Omeprazol 20mg</h3><div class="med-desc">Protetor gástrico, tratamento de refluxo e úlceras.</div><div class="preco">1.200 KZ</div></div>
                <div class="med-card"><div class="med-icon"><i class="fas fa-prescription-bottle"></i></div><h3>Losartana 50mg</h3><div class="med-desc">Controle da pressão arterial, proteção cardiovascular.</div><div class="preco">2.800 KZ</div></div>
                <div class="med-card"><div class="med-icon"><i class="fas fa-prescription-bottle"></i></div><h3>Metronidazol</h3><div class="med-desc">Antibiótico antiprotozoário, eficaz em infecções bacterianas anaeróbias e giardíase.</div><div class="preco">500 KZ</div></div>
                <div class="med-card"><div class="med-icon"><i class="fas fa-prescription-bottle"></i></div><h3>Hidrocurtisona</h3><div class="med-desc">Corticosteroide tópico para inflamações, eczema e dermatites.</div><div class="preco">2.500 KZ</div></div>
                <div class="med-card"><div class="med-icon"><i class="fas fa-prescription-bottle"></i></div><h3>Ibuprofeno</h3><div class="med-desc">Anti-inflamatório não hormonal, para dores musculares, articulares e febre.</div><div class="preco">500 KZ</div></div>
                <div class="med-card"><div class="med-icon"><i class="fas fa-prescription-bottle"></i></div><h3>Cetirizina</h3><div class="med-desc">Anti-histamínico para rinite alérgica, urticária e coceira.</div><div class="preco">950 KZ</div></div>
            </div>
            <p style="text-align: center; margin-top: 24px; font-size: 0.85rem; color: #766e5a;">*Consultar disponibilidade de genéricos e descontos para clientes fiéis.</p>
        </section>

        <section id="infoextra" class="info-extra container">
            <div class="info-box"><i class="fas fa-truck"></i><h4>Entrega Rápida</h4><p>Receba seus medicamentos em casa com segurança. Entregamos em até 2h na zona urbana.</p></div>
            <div class="info-box"><i class="fas fa-chalkboard-user"></i><h4>Farmácia Clínica</h4><p>Acompanhamento farmacoterapêutico e orientações sobre uso correto de medicamentos.</p></div>
            <div class="info-box"><i class="fas fa-hand-holding-heart"></i><h4>Programa de Fidelidade</h4><p>Acumule pontos a cada compra e troque por descontos ou brindes exclusivos.</p></div>
        </section>

        <section id="contato">
            <div class="container">
                <h3>Cadastrar Cliente</h3>
                <form action="#">
                    <input type="text" name="nome" placeholder="Insira o seu nome" required>
                    <input type="email" name="email" placeholder="Insira o seu E-mail" required>
                    <input type="date" name="data" placeholder="Insira a sua data de Nascimento" required>
                    <input type="number" name="numb" placeholder="Insira o seu Número" required>
                    <select class="form-control" name="tipo" id="tipo" required>
                        <option value="">Gênero:</option>
                        <option value="masculino">Masculino</option>
                        <option value="feminino">Feminino</option>
                    </select>
                    <button type="submit" class="botao">Cadastrar</button>
                </form>
            </div>
        </section>
    </main>

    <footer id="rodape">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <h3>Farmácia</h3>
                    <p><i class="fas fa-map-marker-alt"></i> KM30, Belas, Luanda - Angola</p>
                    <p><i class="fas fa-phone-alt"></i> +244 923 456 789</p>
                    <p><i class="fas fa-envelope"></i> contacto@farmacia.ao</p>
                </div>
                <div class="footer-col">
                    <h3>Horário de Funcionamento</h3>
                    <p>Segunda a Sexta: 8h – 20h</p>
                    <p>Sábado: 9h – 17h</p>
                    <p>Domingo: 9h – 13h (plantão)</p>
                    <p>🔒 Atendimento 24h para urgências</p>
                </div>
                <div class="footer-col">
                    <h3>Links Rápidos</h3>
                    <p><a href="#home">↳ Início</a></p>
                    <p><a href="#sobre">↳ Quem somos</a></p>
                    <p><a href="#medicamentos">↳ Catálogo</a></p>
                    <p><a href="#infoextra">↳ Benefícios</a></p>
                </div>
                <div class="footer-col">
                    <h3>Siga-nos</h3>
                    <p class="footer-note">Cuidar de você é a nossa essência.</p>
                </div>
            </div>
            <div class="copyright">
                <p>© 2026 Farmácia - Excelência e confiança. Todos os direitos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Modal de Medicamento -->
    <div id="medModal" class="modal">
        <div class="modal-content">
            <i class="fas fa-info-circle" style="font-size: 2rem; color:#2c5f4b;"></i>
            <h3 id="modalNomeMed">Medicamento</h3>
            <p style="margin: 12px 0;">Este medicamento está disponível em nossa loja física ou através do delivery.</p>
            <p><strong>Preço: </strong><span id="modalPreco"></span></p>
            <p><small>Consulte nossos farmacêuticos para mais orientações.</small></p>
            <button id="closeModalBtn">Fechar</button>
        </div>
    </div>

    <!-- Modal de Autenticação Profissional -->
    <div class="auth-modal-overlay" id="authModalOverlay">
        <div class="auth-modal-container">
            <button class="auth-modal-close" id="authModalClose">
                <i class="fas fa-times"></i>
            </button>
            <div class="auth-modal-header">
                <button class="auth-tab active" type="button">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Iniciar Sessão</span>
                </button>
            </div>
            <div class="auth-modal-body">
                <div class="auth-slider-wrapper">
                    <div class="auth-slider" id="authSlider" style="width:100%;">
                        <div class="auth-panel" data-panel="login" style="width:100%;">
                            <div class="auth-icon-circle">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <h3>Bem-vindo de volta!</h3>
                            <p class="auth-subtitle">Aceda à sua conta para continuar</p>
                            <?php if ($erro): ?>
                            <div class="alert-erro"><?php echo $erro; ?></div>
                            <?php endif; ?>
                            <form id="formLogin" class="auth-form" action="login.php" method="POST">
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" name="username" placeholder="Seu username" required autocomplete="username">
                                </div>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="senha" placeholder="Sua senha" required autocomplete="current-password">
                                    <button type="button" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <button type="submit" class="auth-submit-btn">
                                    <span>Entrar</span>
                                    <i class="fas fa-arrow-right"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="auth-modal-footer">
                <?php if (!$temAdmin): ?>
                <p>
                    Se precisa criar o primeiro administrador, use o acesso protegido por código.
                    <button class="switch-auth" type="button" id="btnAbrirAdminSetup">Configurar administrador</button>
                </p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Modal de Criação de Administrador -->
    <div class="auth-modal-overlay" id="adminSetupOverlay">
        <div class="auth-modal-container" style="max-width: 560px;">
            <button class="auth-modal-close" id="adminSetupClose">
                <i class="fas fa-times"></i>
            </button>
            <div class="auth-modal-header">
                <button class="auth-tab active" type="button">
                    <i class="fas fa-user-shield"></i>
                    <span>Configurar Administrador</span>
                </button>
            </div>
            <div class="auth-modal-body">
                <div class="auth-slider-wrapper">
                    <div class="auth-slider" style="width:100%;">
                        <div class="auth-panel" style="width:100%;">
                            <div class="auth-icon-circle">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <h3>Criar administrador</h3>
                            <p class="auth-subtitle">Use este acesso apenas com o código secreto.</p>
                            <form id="formAdminSetup" class="auth-form" action="login.php" method="POST">
                                <input type="hidden" name="action" value="create_admin">
                                <div class="input-group">
                                    <i class="fas fa-hashtag"></i>
                                    <input type="text" name="setup_code" placeholder="Código secreto" required autocomplete="off">
                                </div>
                                <div class="input-group">
                                    <i class="fas fa-id-badge"></i>
                                    <input type="number" name="idFuncionario" placeholder="ID do funcionário" required>
                                </div>
                                <div class="input-group">
                                    <i class="fas fa-user"></i>
                                    <input type="text" name="username" placeholder="Username do admin" required autocomplete="username">
                                </div>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="senha" placeholder="Senha" required autocomplete="new-password">
                                    <button type="button" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <div class="input-group">
                                    <i class="fas fa-lock"></i>
                                    <input type="password" name="confirmar_senha" placeholder="Confirmar senha" required autocomplete="new-password">
                                    <button type="button" class="toggle-password">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                                <button type="submit" class="auth-submit-btn">
                                    <span>Criar Administrador</span>
                                    <i class="fas fa-user-shield"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <div class="auth-modal-footer">
                <p>Este formulário é apenas para a configuração inicial e exige código secreto.</p>
            </div>
        </div>
    </div>
    <script src="js/index.js"></script>
    <script>
document.addEventListener('DOMContentLoaded', function() {
    const authModalOverlay = document.getElementById('authModalOverlay');
    const authModalClose = document.getElementById('authModalClose');
    const btnLoginModal = document.getElementById('botaoLoginModal');
    const btnAbrirAdminSetup = document.getElementById('btnAbrirAdminSetup');
    const adminSetupOverlay = document.getElementById('adminSetupOverlay');
    const adminSetupClose = document.getElementById('adminSetupClose');
    const abrirAuthNoLoad = <?php echo isset($_GET['erro']) ? 'true' : 'false'; ?>;

    function openAuthModal() {
        authModalOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeAuthModal() {
        authModalOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    function openAdminModal() {
        adminSetupOverlay.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeAdminModal() {
        adminSetupOverlay.classList.remove('active');
        document.body.style.overflow = '';
    }

    if (btnLoginModal) {
        btnLoginModal.addEventListener('click', function(e) {
            e.preventDefault();
            openAuthModal();
        });
    }

    if (btnAbrirAdminSetup) {
        btnAbrirAdminSetup.addEventListener('click', function(e) {
            e.preventDefault();
            openAdminModal();
        });
    }

    if (authModalClose) authModalClose.addEventListener('click', closeAuthModal);
    if (adminSetupClose) adminSetupClose.addEventListener('click', closeAdminModal);

    if (authModalOverlay) {
        authModalOverlay.addEventListener('click', function(e) {
            if (e.target === authModalOverlay) closeAuthModal();
        });
    }

    if (adminSetupOverlay) {
        adminSetupOverlay.addEventListener('click', function(e) {
            if (e.target === adminSetupOverlay) closeAdminModal();
        });
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (authModalOverlay && authModalOverlay.classList.contains('active')) {
                closeAuthModal();
            }
            if (adminSetupOverlay && adminSetupOverlay.classList.contains('active')) {
                closeAdminModal();
            }
        }
    });

    document.querySelectorAll('.toggle-password').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const input = this.parentElement.querySelector('input');
            const icon = this.querySelector('i');
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                if (icon) icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                if (icon) icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        });
    });

    if (abrirAuthNoLoad) {
        openAuthModal();
    }
});
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>
