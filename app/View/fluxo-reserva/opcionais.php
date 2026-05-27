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
    <a href="/DriverLux/public/">Aluguel de Carros</a>
    <a href="#">Gestão de Frotas</a>
    <a href="#">Seminovos</a>
    <a href="#">Lux - Carro por Assinatura</a>
    <a href="javascript:void(0)" id="btn-login-trigger">LOGIN</a>
  </nav>
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
    <div class="step">
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

</body>
</html>