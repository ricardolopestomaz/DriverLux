<?php
// views/veiculos.php

// 1. Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Instancia o Controller para buscar os veículos
require_once __DIR__ . '/../modules/Veiculos/VeiculoController.php';

ob_start();
$controller = new VeiculoController();
$controller->handleRequest('GET', null);
$responseJson = ob_get_clean();

$response = json_decode($responseJson, true);
$veiculos = [];

if (isset($response['status']) && $response['status'] === 'success' && isset($response['data'])) {
    $veiculos = $response['data'];
} elseif (is_array($response) && !isset($response['status'])) {
    $veiculos = $response;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossa Frota - DriverLux</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800;900&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --roxo-escuro: #1c003e; 
            --roxo-primario: #6a0dad; 
            --roxo-claro: #9b51e0;
            --dourado: #eab308;
            --bg-cinza: #f8f9fa;
            --texto-escuro: #1a1a1a;
            --texto-claro: #ffffff;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; font-family: 'Montserrat', sans-serif; }
        body { background-color: var(--bg-cinza); color: var(--texto-escuro); }
        
        /* Utilitário essencial para o JS da Home funcionar */
        .esconder { display: none !important; }

        /* Navbar Integrada */
        .navbar { 
            background-color: var(--roxo-escuro); 
            padding: 15px 5%; 
            display: flex; 
            justify-content: space-between; 
            align-items: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 100;
        }
        .navbar .logo { color: var(--texto-claro); font-size: 22px; font-weight: 800; font-style: italic; letter-spacing: 1px; }
        .nav-links { display: flex; gap: 25px; align-items: center; }
        .nav-links a { color: var(--texto-claro); text-decoration: none; font-size: 13px; font-weight: 700; text-transform: uppercase; transition: color 0.2s; }
        .nav-links a:hover { color: var(--dourado); }

        /* Estilo do Botão LOGIN no Nav */
        #btn-login-trigger {
            background-color: var(--dourado);
            color: var(--roxo-escuro) !important;
            padding: 8px 18px;
            border-radius: 20px;
        }
        #btn-login-trigger:hover { opacity: 0.9; }

        /* MENU DE USUÁRIO (Copiado os mesmos estilos mas adaptado visualmente) */
        .area-usuario { position: relative; display: flex; align-items: center; }
        
        .btn-usuario { 
            background: rgba(255, 255, 255, 0.08); 
            border: 1px solid rgba(255, 255, 255, 0.15);
            color: var(--texto-claro);
            padding: 8px 16px;
            border-radius: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 13px;
            font-weight: 600;
            transition: background 0.2s;
        }
        .btn-usuario:hover { background: rgba(255, 255, 255, 0.15); }
        .seta-menu { font-size: 10px; color: var(--dourado); transition: transform 0.2s; }
        .btn-usuario.ativo .seta-menu { transform: rotate(180deg); }

        .menu-usuario { 
            position: absolute; 
            top: 110%; 
            right: 0; 
            background: white; 
            border-radius: 8px; 
            box-shadow: 0 10px 25px rgba(0,0,0,0.15); 
            min-width: 160px;
            overflow: hidden;
            display: none; 
            flex-direction: column;
            border: 1px solid #eee;
        }
        .menu-usuario.mostrar { display: flex; }
        .menu-usuario a { color: var(--texto-escuro) !important; padding: 12px 16px; font-size: 13px !important; text-transform: none !important; font-weight: 500 !important; border-bottom: 1px solid #f5f5f5; text-decoration: none; display: block; }
        .menu-usuario a:last-child { border-bottom: none; color: #dc2626 !important; }
        .menu-usuario a:hover { background-color: #f8f9fa; color: var(--roxo-primario) !important; }

        /* Header Compacto */
        .section-header { background-color: var(--roxo-escuro); padding: 20px 5% 60px; display: flex; justify-content: space-between; align-items: center; }
        .header-info { text-align: left; }
        .section-subtitle { color: var(--dourado); font-size: 11px; font-weight: 800; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 2px; }
        .section-title { color: var(--texto-claro); font-size: 22px; font-weight: 800; }
        
        .search-summary { background: rgba(255, 255, 255, 0.08); padding: 8px 16px; border-radius: 20px; color: var(--texto-claro); font-size: 12px; font-weight: 500; display: flex; gap: 15px; align-items: center; border: 1px solid rgba(255, 255, 255, 0.15); }
        .search-summary span strong { color: var(--dourado); }

        /* LINHA DO TEMPO */
        .stepper-container { max-width: 800px; margin: -30px auto 40px; background: white; padding: 18px 30px; border-radius: 12px; box-shadow: 0 10px 25px rgba(0,0,0,0.06); display: flex; justify-content: space-between; position: relative; z-index: 10; }
        .step { display: flex; flex-direction: column; align-items: center; flex: 1; position: relative; }
        .step:not(:last-child)::after { content: ''; position: absolute; top: 15px; left: 50%; width: 100%; height: 2px; background-color: #e5e7eb; z-index: 1; }
        .step-bolinha { width: 30px; height: 30px; border-radius: 50%; background-color: #f3f4f6; color: #6b7280; display: flex; align-items: center; justify-content: center; font-size: 13px; font-weight: 700; position: relative; z-index: 2; margin-bottom: 6px; border: 2px solid #e5e7eb; }
        .step.active .step-bolinha { background-color: var(--roxo-primario); color: white; border-color: var(--roxo-primario); }
        .step-texto { font-size: 11px; font-weight: 700; color: #9ca3af; text-transform: uppercase; letter-spacing: 0.5px; }
        .step.active .step-texto { color: var(--roxo-primario); }

        /* Cards */
        .container { max-width: 1200px; margin: 0 auto 60px; padding: 0 20px; }
        .grid-veiculos { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 30px; }
        .card-carro { background: var(--texto-claro); border-radius: 16px; padding: 25px; display: flex; flex-direction: column; justify-content: space-between; box-shadow: 0 4px 15px rgba(0,0,0,0.02); transition: transform 0.3s, box-shadow 0.3s; min-height: 440px; }
        .card-carro:hover { transform: translateY(-5px); box-shadow: 0 12px 30px rgba(106, 13, 173, 0.08); }
        .badge-wrapper { display: flex; justify-content: flex-start; margin-bottom: 15px; }
        .badge-categoria { background-color: var(--roxo-primario); color: var(--texto-claro); font-size: 10px; font-weight: 800; text-transform: uppercase; padding: 4px 12px; border-radius: 4px; letter-spacing: 0.5px; }
        .carro-modelo { font-size: 18px; font-weight: 800; color: var(--roxo-escuro); margin-bottom: 4px; text-transform: uppercase; }
        .carro-desc { font-size: 12px; color: #777; font-weight: 500; margin-bottom: 20px; }
        .container-img { text-align: center; margin-bottom: 20px; height: 130px; display: flex; align-items: center; justify-content: center; }
        .carro-img { max-height: 100%; max-width: 100%; object-fit: contain; }
        .box-preco { border-top: 1px solid #eee; padding-top: 15px; display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .preco-label { font-size: 11px; color: #888; font-weight: 600; text-transform: uppercase; }
        .preco-valor { font-size: 20px; font-weight: 800; color: var(--roxo-escuro); }
        .preco-valor span { font-size: 12px; color: #666; font-weight: 600; }
        .btn-acao { background: linear-gradient(135deg, var(--roxo-primario), var(--roxo-claro)); color: var(--texto-claro); border: none; padding: 14px; width: 100%; font-size: 13px; font-weight: 700; border-radius: 8px; cursor: pointer; text-transform: uppercase; text-align: center; text-decoration: none; display: block; }
        .btn-acao:hover { opacity: 0.9; }
        .card-esgotado { background-color: #fafafa; border: 1px solid #f0f0f0; box-shadow: none; }
        .card-esgotado .carro-img { filter: grayscale(100%) opacity(0.3); }
        .card-esgotado .carro-modelo, .card-esgotado .preco-valor { color: #999; }
        .btn-esgotado { background: #d1d5db !important; color: #6b7280 !important; cursor: not-allowed; pointer-events: none; }
    </style>
</head>
<body>

    <header class="topo">
        <nav class="navbar">
            <div class="logo">DRIVERLUX</div>
            
            <div class="nav-links">
                <a href="#">ALUGUEL</a>
                <a href="#">FROTAS</a>
                <a href="#">ASSINATURA</a>
                <a href="javascript:void(0)" id="btn-login-trigger">LOGIN</a>
            </div>

            <div class="area-usuario">
                <button id="btn-menu-usuario" class="btn-usuario esconder">
                    <span id="nome-usuario">Usuário</span>
                    <span class="seta-menu">▼</span>
                </button>

                <div id="menu-usuario" class="menu-usuario esconder">
                    <a href="/DriverLux/views/painel_cliente.html">Minha conta</a>
                    <a href="#">Minhas reservas</a>
                    <a href="#" id="btn-sair">Sair</a>
                </div>

                <registro-login id="modal-auth" modo="popover"></registro-login>
            </div>
        </nav>
    </header>

    <header class="section-header">
        <div class="header-info">
            <div class="section-subtitle">Passo 1 de 4</div>
            <h1 class="section-title">Selecione o veículo</h1>
        </div>
        <div class="search-summary">
            <span>📍 Retirada: <strong>Agência Central</strong></span>
            <span>📅 Período: <strong>3 Diárias</strong></span>
        </div>
    </header>

    <div class="stepper-container">
        <div class="step active"><div class="step-bolinha">1</div><div class="step-texto">Carro</div></div>
        <div class="step"><div class="step-bolinha">2</div><div class="step-texto">Opcionais</div></div>
        <div class="step"><div class="step-bolinha">3</div><div class="step-texto">Pagamento</div></div>
        <div class="step"><div class="step-bolinha">4</div><div class="step-texto">Resumo</div></div>
    </div>

    <div class="container">
        <div class="grid-veiculos">
            <?php if (!empty($veiculos)): ?>
                <?php foreach ($veiculos as $carro): 
                    $status = isset($carro['status_disponibilidade']) ? trim(strtolower($carro['status_disponibilidade'])) : '';
                    $estaDisponivel = ($status === 'livre');
                    $imagem = !empty($carro['imagem_url']) ? $carro['imagem_url'] : 'https://cdn-icons-png.flaticon.com/512/3202/3202003.png';
                ?>
                    <div class="card-carro <?= !$estaDisponivel ? 'card-esgotado' : '' ?>">
                        <div>
                            <div class="badge-wrapper"><span class="badge-categoria"><?= htmlspecialchars($carro['categoria_nome'] ?? 'Premium') ?></span></div>
                            <div class="carro-modelo"><?= htmlspecialchars($carro['marca'] ?? '') ?> <?= htmlspecialchars($carro['modelo'] ?? '') ?></div>
                            <div class="carro-desc">Ano: <?= htmlspecialchars($carro['ano'] ?? '') ?> • Placa: <?= htmlspecialchars($carro['placa'] ?? '') ?></div>
                            <div class="container-img"><img class="carro-img" src="<?= $imagem ?>" alt="<?= htmlspecialchars($carro['modelo'] ?? '') ?>"></div>
                        </div>
                        <div>
                            <div class="box-preco">
                                <div class="preco-label">Diária de</div>
                                <div class="preco-valor">R$ <?= number_format($carro['valor_base_diaria'] ?? 0, 2, ',', '.') ?><span>/dia</span></div>
                            </div>
                            <?php if ($estaDisponivel): ?>
                                <a href="opcionais.php?carro_id=<?= $carro['id'] ?>" class="btn-acao">Reservar Agora</a>
                            <?php else: ?>
                                <button class="btn-acao btn-esgotado" disabled>Em Manutenção</button>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align: center; font-size: 16px; color: #666; padding: 40px;">Nenhum veículo encontrado.</p>
            <?php endif; ?>
        </div>
    </div>

    <script src="/DriverLux/public/assets/js/registro-login.js"></script>
    <script src="/DriverLux/public/assets/js/menu-usuario.js?v=3"></script>

    <script>
        window.addEventListener('load', async () => {
            // ABRIR O MODAL
            const btnLogin = document.getElementById('btn-login-trigger');
            const modalAuth = document.getElementById('modal-auth');

            if (btnLogin && modalAuth) {
                btnLogin.addEventListener('click', () => {
                    // Aqui estava o segredo: Acessar a Shadow Root
                    const container = modalAuth.shadowRoot.getElementById('auth-container');
                    if (container) container.classList.remove('hidden');
                });
            }

            // VERIFICAÇÃO DE SESSÃO NA API E TROCA DE BOTÕES
            try {
                const res = await fetch('/DriverLux/public/api/usuarios/me');
                const data = await res.json();
                
                if (data.logado && data.usuario) {
                    
                    // Redireciona o Admin se ele cair aqui
                    if (data.usuario.perfil === 'administrador' || data.usuario.perfil === 'admin') {
                        window.location.href = '/DriverLux/views/admin.php';
                        return; 
                    }

                    // Se for cliente, atualiza o nome
                    const primeiroNome = data.usuario.nome.split(' ')[0];
                    document.getElementById('nome-usuario').textContent = primeiroNome;
                    
                    // Esconde LOGIN, mostra o Dropdown
                    if (btnLogin) btnLogin.classList.add('esconder');
                    const btnMenuUsuario = document.getElementById('btn-menu-usuario');
                    if (btnMenuUsuario) btnMenuUsuario.classList.remove('esconder');
                }
            } catch (e) {
                console.error("Erro ao verificar sessão do usuário:", e);
            }
        });
    </script>
</body>
</html>