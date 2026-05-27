<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Controller/VeiculoController.php';

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

// === INTELIGÊNCIA DOS FILTROS ===
$categoriasUnicas = [];
$marcasUnicas = [];
$maiorPreco = 0;

if (!empty($veiculos)) {
    foreach ($veiculos as $v) {
        $cat = trim($v['categoria_nome'] ?? 'Premium');
        $marca = trim($v['marca'] ?? 'Outras');
        $preco = floatval($v['valor_base_diaria'] ?? 0);
        
        if (!in_array($cat, $categoriasUnicas)) $categoriasUnicas[] = $cat;
        if (!in_array($marca, $marcasUnicas)) $marcasUnicas[] = $marca;
        if ($preco > $maiorPreco) $maiorPreco = $preco;
    }
    sort($categoriasUnicas);
    sort($marcasUnicas);
    $maiorPreco = ceil($maiorPreco / 100) * 100; 
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nossa Frota - DriverLux</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/veiculos.css">
</head>
<body>

    <header class="topo">
        <img src="/DriverLux/public/assets/img/logo.png" class="logo" alt="DriverLux">
        <nav>
           <a href="#">ALUGUEL DE CARROS</a>
            <a href="#">GESTÃO DE FROTAS</a>
            <a href="#">SEMINOVOS</a>
            <a href="#">LUX - CARRO POR ASSINATURA</a>
            <a href="javascript:void(0)" id="btn-login-trigger">LOGIN</a>
        </nav>
        <div class="area-usuario">
            <button id="btn-menu-usuario" class="btn-usuario esconder">
                <span id="nome-usuario">Usuário</span>
                <span class="seta-menu">&#9660;</span>
            </button>
            <div id="menu-usuario" class="menu-usuario esconder">
                <a href="/DriverLux/views/painel_cliente.html">Minha conta</a>
                <a href="#">Minhas reservas</a>
                <a href="#" id="btn-sair">Sair</a>
            </div>
            <registro-login id="modal-auth" modo="popover"></registro-login>
        </div>
    </header>

    <div class="faixa-header">
        <div class="faixa-inner">
            <div>
                <div class="faixa-label">Passo 1 de 4</div>
                <h1 class="faixa-titulo">Selecione seu ve&#237;culo</h1>
            </div>
            <div class="resumo-pill">
                <div class="resumo-item">
                    <span class="resumo-label">Retirada</span>
                    <span class="resumo-valor">&#128205; Ag&#234;ncia Central</span>
                </div>
                <div class="resumo-sep"></div>
                <div class="resumo-item">
                    <span class="resumo-label">Per&#237;odo</span>
                    <span class="resumo-valor">&#128197; 3 Di&#225;rias</span>
                </div>
            </div>
        </div>
    </div>

    <div class="stepper-wrap">
        <div class="stepper-container">
            <div class="step active">
                <div class="step-bolinha">1</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Carro</span></div>
            </div>
            <div class="step">
                <div class="step-bolinha">2</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Opcionais</span></div>
            </div>
            <div class="step">
                <div class="step-bolinha">3</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Pagamento</span></div>
            </div>
            <div class="step">
                <div class="step-bolinha">4</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Resumo</span></div>
            </div>
        </div>
    </div>

    <div class="grid-header">
        <h2 class="grid-titulo">Frota <span>Dispon&#237;vel</span></h2>
        <span class="grid-count"><strong><?= count($veiculos) ?></strong> ve&#237;culos encontrados</span>
    </div>

    <div class="main-layout">
        
        <aside class="sidebar">
            <h3>Filtrar por</h3>

            <div class="filter-group">
                <div class="filter-title">Categoria</div>
                <?php foreach ($categoriasUnicas as $cat): ?>
                    <label class="filter-option">
                        <input type="checkbox" class="filtro-cat" value="<?= htmlspecialchars($cat) ?>">
                        <?= htmlspecialchars($cat) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="filter-group">
                <div class="filter-title">Marca</div>
                <?php foreach ($marcasUnicas as $marca): ?>
                    <label class="filter-option">
                        <input type="checkbox" class="filtro-marca" value="<?= htmlspecialchars($marca) ?>">
                        <?= htmlspecialchars($marca) ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="filter-group">
                <div class="filter-title">Preço Diário (Máx)</div>
                <input type="range" id="filtro-preco" class="price-range" min="0" max="<?= $maiorPreco ?>" value="<?= $maiorPreco ?>" step="50">
                <div class="price-values">
                    <span>R$ 0</span>
                    <span id="preco-exibicao">R$ <?= $maiorPreco ?></span>
                </div>
            </div>

            <button class="btn-limpar" id="btn-limpar-filtros">Limpar Filtros</button>
        </aside>

        <div class="grid-wrapper">
            <div class="grid-veiculos" id="lista-veiculos">
                <?php if (!empty($veiculos)): ?>
                    <?php foreach ($veiculos as $carro):
                        $status = isset($carro['status_disponibilidade']) ? trim(strtolower($carro['status_disponibilidade'])) : '';
                        $estaDisponivel = ($status === 'livre');
                        $imagem = !empty($carro['imagem_url']) ? $carro['imagem_url'] : 'https://cdn-icons-png.flaticon.com/512/3202/3202003.png';
                        $valorOriginal = floatval($carro['valor_base_diaria'] ?? 0);
                        $valorFormatado = number_format($valorOriginal, 2, ',', '.');
                    ?>
                        <div class="card-carro <?= !$estaDisponivel ? 'card-esgotado' : '' ?>"
                             data-categoria="<?= htmlspecialchars($carro['categoria_nome'] ?? 'Premium') ?>"
                             data-marca="<?= htmlspecialchars($carro['marca'] ?? '') ?>"
                             data-preco="<?= $valorOriginal ?>">
                            <div>
                                <div class="badge-wrapper">
                                    <span class="badge-categoria"><?= htmlspecialchars($carro['categoria_nome'] ?? 'Premium') ?></span>
                                    <?php if ($estaDisponivel): ?>
                                        <span class="badge-disponivel">Dispon&#237;vel</span>
                                    <?php else: ?>
                                        <span class="badge-indisponivel">Manuten&#231;&#227;o</span>
                                    <?php endif; ?>
                                </div>
                                <div class="carro-modelo"><?= htmlspecialchars($carro['marca'] ?? '') ?> <?= htmlspecialchars($carro['modelo'] ?? '') ?></div>
                                <div class="carro-desc">
                                    <span><?= htmlspecialchars($carro['ano'] ?? '') ?></span>
                                    <span class="dot"></span>
                                    <span><?= htmlspecialchars($carro['placa'] ?? '') ?></span>
                                </div>
                                <div class="container-img">
                                    <img class="carro-img"
                                         src="<?= htmlspecialchars($imagem) ?>"
                                         alt="<?= htmlspecialchars(($carro['marca'] ?? '') . ' ' . ($carro['modelo'] ?? '')) ?>"
                                         loading="lazy">
                                </div>
                            </div>
                            <div>
                                <div class="box-preco">
                                    <div class="preco-label">Di&#225;ria a partir de</div>
                                    <div class="preco-valor">
                                        <span class="preco-rs">R$</span>
                                        <span class="preco-num"><?= $valorFormatado ?></span>
                                        <span class="preco-period">/dia</span>
                                    </div>
                                </div>
                                <?php if ($estaDisponivel): ?>
                                    <a href="opcionais.php?carro_id=<?= $carro['id'] ?>" class="btn-acao"><span>Reservar Agora</span></a>
                                <?php else: ?>
                                    <button class="btn-acao btn-esgotado" disabled><span>Em Manuten&#231;&#227;o</span></button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">&#128663;</div>
                        <h3>Nenhum ve&#237;culo encontrado</h3>
                        <p>No momento n&#227;o h&#225; ve&#237;culos dispon&#237;veis. Tente novamente mais tarde.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div> 

    <script src="/DriverLux/public/assets/js/registro-login.js"></script>
    <script src="/DriverLux/public/assets/js/menu-usuario.js?v=3"></script>
    
    <script>
        window.addEventListener('load', async () => {
            const btnLogin  = document.getElementById('btn-login-trigger');
            const modalAuth = document.getElementById('modal-auth');
            if (btnLogin && modalAuth) {
                btnLogin.addEventListener('click', () => {
                    const container = modalAuth.shadowRoot.getElementById('auth-container');
                    if (container) container.classList.remove('hidden');
                });
            }
            try {
                const res  = await fetch('/DriverLux/public/api/usuarios/me');
                const data = await res.json();
                if (data.logado && data.usuario) {
                    if (data.usuario.perfil === 'administrador' || data.usuario.perfil === 'admin') {
                        window.location.href = '/DriverLux/app/View/painel-admin/admin.php';
                        return;
                    }
                    const primeiroNome = data.usuario.nome.split(' ')[0];
                    document.getElementById('nome-usuario').textContent = primeiroNome;
                    if (btnLogin) btnLogin.classList.add('esconder');
                    const btnMenuUsuario = document.getElementById('btn-menu-usuario');
                    if (btnMenuUsuario) btnMenuUsuario.classList.remove('esconder');
                }
            } catch (e) { console.error("Erro ao verificar sessao do usuario:", e); }
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const checkboxesCat = document.querySelectorAll('.filtro-cat');
            const checkboxesMarca = document.querySelectorAll('.filtro-marca');
            const sliderPreco = document.getElementById('filtro-preco');
            const exibicaoPreco = document.getElementById('preco-exibicao');
            const btnLimpar = document.getElementById('btn-limpar-filtros');
            const cards = document.querySelectorAll('.card-carro');

            function aplicarFiltros() {
                const catSelecionadas = Array.from(checkboxesCat).filter(cb => cb.checked).map(cb => cb.value);
                const marcasSelecionadas = Array.from(checkboxesMarca).filter(cb => cb.checked).map(cb => cb.value);
                const precoMaximo = parseFloat(sliderPreco.value);
                
                exibicaoPreco.textContent = 'R$ ' + precoMaximo;

                cards.forEach(card => {
                    const catCard = card.getAttribute('data-categoria');
                    const marcaCard = card.getAttribute('data-marca');
                    const precoCard = parseFloat(card.getAttribute('data-preco'));

                    const passaCat = catSelecionadas.length === 0 || catSelecionadas.includes(catCard);
                    const passaMarca = marcasSelecionadas.length === 0 || marcasSelecionadas.includes(marcaCard);
                    const passaPreco = precoCard <= precoMaximo;

                    if (passaCat && passaMarca && passaPreco) {
                        card.style.display = 'flex';
                    } else {
                        card.style.display = 'none';
                    }
                });
            }

            checkboxesCat.forEach(cb => cb.addEventListener('change', aplicarFiltros));
            checkboxesMarca.forEach(cb => cb.addEventListener('change', aplicarFiltros));
            if(sliderPreco) sliderPreco.addEventListener('input', aplicarFiltros);

            if(btnLimpar) {
                btnLimpar.addEventListener('click', () => {
                    checkboxesCat.forEach(cb => cb.checked = false);
                    checkboxesMarca.forEach(cb => cb.checked = false);
                    if(sliderPreco) {
                        sliderPreco.value = sliderPreco.max;
                    }
                    aplicarFiltros();
                });
            }
        });
    </script>
</body>
</html>