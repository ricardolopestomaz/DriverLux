<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../Controller/ProtecaoController.php';
require_once __DIR__ . '/../../Controller/KMController.php';

ob_start();
$protecaoController = new ProtecaoController();
$protecaoController->handleRequest('GET', null);
$protecoesJson = ob_get_clean();

$protecoesResponse = json_decode($protecoesJson, true);
$protecoes = $protecoesResponse['data'] ?? [];

ob_start();
$kmController = new KMController();
$kmController->handleRequest('GET', null);
$kmJson = ob_get_clean();

$kmResponse = json_decode($kmJson, true);
$opcoesKm = $kmResponse['data'] ?? [];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Opcionais - DriverLux</title>

  <link rel="stylesheet" href="../../../public/assets/css/veiculos.css">
  <link rel="stylesheet" href="../../../public/assets/css/opcionais.css?v=30">
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
            <a href="/DriverLux/app/View/painel_cliente.php">Minha conta</a>
            <a href="#">Minhas reservas</a>
            <a href="#" id="btn-sair">Sair</a>
        </div>

        <registro-login id="modal-auth" modo="popover"></registro-login>
    </div>
</header>

<div class="faixa-header">
  <div class="faixa-inner">
    <div>
      <div class="faixa-label">Passo 2 de 4</div>
      <h1 class="faixa-titulo">Escolha seus opcionais</h1>
    </div>

    <div class="resumo-pill">
      <div class="resumo-item">
        <span class="resumo-label">Veículo</span>
        <span class="resumo-valor" id="pill-veiculo">🚗 —</span>
      </div>

      <div class="resumo-sep"></div>

      <div class="resumo-item">
        <span class="resumo-label">Retirada</span>
        <span class="resumo-valor" id="pill-retirada">📍 —</span>
      </div>
    </div>
  </div>
</div>

<div class="stepper-wrap">
  <div class="stepper-container">
    <div class="step active done">
      <div class="step-bolinha">1</div>
      <div class="step-info">
        <span class="step-sub">Etapa</span>
        <span class="step-nome">Carro</span>
      </div>
    </div>

    <div class="step active">
      <div class="step-bolinha">2</div>
      <div class="step-info">
        <span class="step-sub">Etapa</span>
        <span class="step-nome">Opcionais</span>
      </div>
    </div>

    <div class="step">
      <div class="step-bolinha">3</div>
      <div class="step-info">
        <span class="step-sub">Etapa</span>
        <span class="step-nome">Pagamento</span>
      </div>
    </div>

    <div class="step">
      <div class="step-bolinha">4</div>
      <div class="step-info">
        <span class="step-sub">Etapa</span>
        <span class="step-nome">Resumo</span>
      </div>
    </div>
  </div>
</div>

<div class="opcionais-layout">

  <main>

    <section class="bloco-opcional">
      <h2>Dados da devolução</h2>

      <div class="form-reserva">
        <div>
          <label>Local de devolução</label>
          <select id="local-devolucao">
            <option value="">Selecione</option>
            <option value="Agência Aeroporto Palmas">Agência Aeroporto Palmas</option>
            <option value="Agência Centro Palmas">Agência Centro Palmas</option>
          </select>
        </div>

        <div>
          <label>Cupom</label>
          <input id="cupom-codigo" type="text" placeholder="Ex: PRIMEIRA10">
        </div>

        <div>
          <label>Data de devolução</label>
          <input id="data-devolucao" type="date">
        </div>

        <div>
          <label>Hora de devolução</label>
          <input id="hora-devolucao" type="time">
        </div>
      </div>
    </section>

    <section class="bloco-opcional">
      <h2>Pacote de proteção</h2>

      <?php foreach ($protecoes as $index => $protecao): ?>
        <?php if ($protecao['ativo']): ?>
          <div 
            class="opcao-card opcao-protecao <?= $index === 0 ? 'ativa' : '' ?>"
            data-id="<?= $protecao['id'] ?>"
            data-nome="<?= htmlspecialchars($protecao['nome']) ?>"
            data-valor="<?= $protecao['valor_diario'] ?>"
          >
            <div>
              <h3><?= htmlspecialchars($protecao['nome']) ?></h3>
              <p><?= htmlspecialchars($protecao['descricao'] ?? 'Proteção para sua reserva.') ?></p>
            </div>

            <div class="opcao-preco">
              R$ <?= number_format($protecao['valor_diario'], 2, ',', '.') ?>/dia
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </section>

    <section class="bloco-opcional">
      <h2>Quilometragem</h2>

      <?php foreach ($opcoesKm as $index => $km): ?>
        <?php if ($km['ativo']): ?>
          <div 
            class="opcao-card opcao-km <?= $index === 0 ? 'ativa' : '' ?>"
            data-id="<?= $km['id'] ?>"
            data-nome="<?= htmlspecialchars($km['nome']) ?>"
            data-valor="<?= $km['valor_diario'] ?>"
          >
            <div>
              <h3><?= htmlspecialchars($km['nome']) ?></h3>
              <p>
                <?= $km['limite_km'] ? $km['limite_km'] . ' km inclusos' : 'Quilometragem ilimitada' ?>
              </p>
            </div>

            <div class="opcao-preco">
              R$ <?= number_format($km['valor_diario'], 2, ',', '.') ?>/dia
            </div>
          </div>
        <?php endif; ?>
      <?php endforeach; ?>
    </section>

  </main>

  <aside class="resumo-reserva">
    <h2>Resumo</h2>

    <div class="linha-resumo">
      <span>Veículo</span>
      <strong id="resumo-veiculo">—</strong>
    </div>

    <div class="linha-resumo">
      <span>Retirada</span>
      <strong id="resumo-retirada">—</strong>
    </div>

    <div class="linha-resumo">
      <span>Devolução</span>
      <strong id="resumo-devolucao">—</strong>
    </div>

    <div class="linha-resumo">
      <span>Diárias</span>
      <strong id="resumo-diarias">—</strong>
    </div>

    <div class="linha-resumo">
      <span>Valor diárias</span>
      <strong id="valor-diarias">R$ 0,00</strong>
    </div>

    <div class="linha-resumo">
      <span>Proteção</span>
      <strong id="valor-protecao">R$ 0,00</strong>
    </div>

    <div class="linha-resumo">
      <span>Quilometragem</span>
      <strong id="valor-km">R$ 0,00</strong>
    </div>

    <div class="total-reserva">
      <span>Total</span>
      <strong id="valor-total">R$ 0,00</strong>
    </div>

    <button class="btn-continuar" id="btn-continuar-pagamento">
      Continuar para pagamento
    </button>
  </aside>

</div>

<script src="../../../public/assets/js/opcionais.js?v=30"></script>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    // 1. Recupera os dados que vieram do Passo 1 (Veículos e Home)
    const dadosReserva = JSON.parse(sessionStorage.getItem('dados_reserva') || '{}');

    // Se o usuário cair aqui de paraquedas sem carro, volta pra tela anterior
    if (!dadosReserva.veiculo_modelo) {
        alert("Nenhum veículo selecionado! Voltando ao catálogo.");
        window.location.href = '/DriverLux/public/fluxo-reserva/veiculos';
        return;
    }

    // ==========================================
    // 2. MAPEAMENTO DE ELEMENTOS DO HTML
    // ==========================================
    const pillVeiculo = document.getElementById('pill-veiculo');
    const pillRetirada = document.getElementById('pill-retirada');
    
    const resumoVeiculo = document.getElementById('resumo-veiculo');
    const resumoRetirada = document.getElementById('resumo-retirada');
    const resumoDevolucao = document.getElementById('resumo-devolucao');
    const resumoDiarias = document.getElementById('resumo-diarias');
    
    const textValorDiarias = document.getElementById('valor-diarias');
    const textValorProtecao = document.getElementById('valor-protecao');
    const textValorKm = document.getElementById('valor-km');
    const textValorTotal = document.getElementById('valor-total');

    const inputLocalDevolucao = document.getElementById('local-devolucao');
    const inputDataDevolucao = document.getElementById('data-devolucao');
    const inputHoraDevolucao = document.getElementById('hora-devolucao');
    const inputCupom = document.getElementById('cupom-codigo');
    
    const btnContinuar = document.getElementById('btn-continuar-pagamento');

    // Função utilitária para formatar valores em Reais (R$)
    const formatarMoeda = (valor) => valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

    // ==========================================
    // 3. PREENCHIMENTO INICIAL DA TELA
    // ==========================================
    if (pillVeiculo) pillVeiculo.textContent = `🚗 ${dadosReserva.veiculo_modelo}`;
    if (pillRetirada) pillRetirada.textContent = `📍 ${dadosReserva.local_retirada || 'Não informado'}`;
    if (resumoVeiculo) resumoVeiculo.textContent = dadosReserva.veiculo_modelo;
    if (resumoRetirada) resumoRetirada.textContent = dadosReserva.local_retirada || 'Não informado';

    // Pré-preenche a data de devolução (1 dia a mais que a retirada, por padrão)
    if (dadosReserva.data_retirada) {
        let dataRet = new Date(dadosReserva.data_retirada + 'T' + (dadosReserva.hora_retirada || '12:00'));
        dataRet.setDate(dataRet.getDate() + 1); // Adiciona 1 dia
        
        inputDataDevolucao.value = dataRet.toISOString().split('T')[0];
        inputHoraDevolucao.value = dadosReserva.hora_retirada || '12:00';
    }

    // Tenta pré-selecionar o local de devolução igual ao de retirada
    if (dadosReserva.local_retirada) {
        let options = Array.from(inputLocalDevolucao.options).map(o => o.value);
        if(options.includes(dadosReserva.local_retirada)) {
            inputLocalDevolucao.value = dadosReserva.local_retirada;
        }
    }

    // ==========================================
    // 4. LÓGICA DE CÁLCULO E OPÇÕES
    // ==========================================
    let numDiarias = 1;
    let valorDiariaCarro = dadosReserva.valor_diaria || 0;
    
    let valorProtecao = 0;
    let protecaoId = null;
    let protecaoNome = "";
    
    let valorKm = 0;
    let kmId = null;
    let kmNome = "";

    // Pega as opções que o PHP marcou com a classe 'ativa' ao carregar a tela
    const iniciarOpcoesAtivas = () => {
        const activeProt = document.querySelector('.opcao-protecao.ativa');
        if (activeProt) {
            valorProtecao = parseFloat(activeProt.dataset.valor || 0);
            protecaoId = activeProt.dataset.id;
            protecaoNome = activeProt.dataset.nome;
        }

        const activeKm = document.querySelector('.opcao-km.ativa');
        if (activeKm) {
            valorKm = parseFloat(activeKm.dataset.valor || 0);
            kmId = activeKm.dataset.id;
            kmNome = activeKm.dataset.nome;
        }
    };

    // Recalcula tudo e atualiza o resumo lateral
    const calcularTotais = () => {
        resumoDevolucao.textContent = inputLocalDevolucao.value || 'Não selecionado';

        // Calcula os dias entre a retirada e a devolução
        if (dadosReserva.data_retirada && inputDataDevolucao.value) {
            const start = new Date(`${dadosReserva.data_retirada}T${dadosReserva.hora_retirada || '00:00'}`);
            const end = new Date(`${inputDataDevolucao.value}T${inputHoraDevolucao.value || '00:00'}`);
            
            const diffTime = end - start;
            numDiarias = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            if (numDiarias < 1 || isNaN(numDiarias)) numDiarias = 1; // Mínimo de 1 diária cobrada
        }

        resumoDiarias.textContent = `${numDiarias}x`;

        // Multiplica os valores pelas diárias
        const totalDiarias = valorDiariaCarro * numDiarias;
        const totalProtecao = valorProtecao * numDiarias;
        const totalKm = valorKm * numDiarias;
        const subtotal = totalDiarias + totalProtecao + totalKm;

        textValorDiarias.textContent = formatarMoeda(totalDiarias);
        textValorProtecao.textContent = formatarMoeda(totalProtecao);
        textValorKm.textContent = formatarMoeda(totalKm);
        textValorTotal.textContent = formatarMoeda(subtotal);

        return { numDiarias, totalDiarias, totalProtecao, totalKm, subtotal };
    };

    // Monitora alterações nos inputs de devolução
    inputLocalDevolucao.addEventListener('change', calcularTotais);
    inputDataDevolucao.addEventListener('change', calcularTotais);
    inputHoraDevolucao.addEventListener('change', calcularTotais);

    // ==========================================
    // 5. CLIQUES NOS CARDS DE OPCIONAIS
    // ==========================================
    document.querySelectorAll('.opcao-protecao').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.opcao-protecao').forEach(c => c.classList.remove('ativa'));
            card.classList.add('ativa'); // Marca este card como ativo
            
            valorProtecao = parseFloat(card.dataset.valor || 0);
            protecaoId = card.dataset.id;
            protecaoNome = card.dataset.nome;
            calcularTotais();
        });
    });

    document.querySelectorAll('.opcao-km').forEach(card => {
        card.addEventListener('click', () => {
            document.querySelectorAll('.opcao-km').forEach(c => c.classList.remove('ativa'));
            card.classList.add('ativa'); // Marca este card como ativo
            
            valorKm = parseFloat(card.dataset.valor || 0);
            kmId = card.dataset.id;
            kmNome = card.dataset.nome;
            calcularTotais();
        });
    });

    // Roda os cálculos iniciais assim que a tela abre
    iniciarOpcoesAtivas();
    calcularTotais();

    // ==========================================
    // 6. AVANÇAR PARA O PAGAMENTO
    // ==========================================
    if (btnContinuar) {
        btnContinuar.addEventListener('click', () => {
            if (!inputLocalDevolucao.value || !inputDataDevolucao.value || !inputHoraDevolucao.value) {
                alert('Por favor, preencha o local, data e hora de devolução.');
                return;
            }

            const totais = calcularTotais();

            // Adiciona todas as escolhas novas na nossa sacola (sessionStorage)
            dadosReserva.local_devolucao = inputLocalDevolucao.value;
            dadosReserva.data_devolucao = inputDataDevolucao.value;
            dadosReserva.hora_devolucao = inputHoraDevolucao.value;
            dadosReserva.cupom = inputCupom.value;
            
            dadosReserva.num_diarias = totais.numDiarias;
            dadosReserva.valor_diarias_total = totais.totalDiarias;
            
            dadosReserva.protecao_id = protecaoId;
            dadosReserva.protecao_nome = protecaoNome;
            dadosReserva.valor_protecao_total = totais.totalProtecao;
            
            dadosReserva.km_id = kmId;
            dadosReserva.km_nome = kmNome;
            dadosReserva.valor_km_total = totais.totalKm;

            // Salva as escolhas
            sessionStorage.setItem('dados_reserva', JSON.stringify(dadosReserva));

            // Redireciona para a tela de pagamento usando a rota do seu index.php
            window.location.href = '/DriverLux/app/View/fluxo-reserva/pagamento.php';
        });
    }
});
</script>
<script src="/DriverLux/public/assets/js/menu-usuario.js?v=4"></script>
</body>
</html>