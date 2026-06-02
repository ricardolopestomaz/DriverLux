<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>DriverLux — Meu Perfil</title>
  <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css?v=3">
  <link rel="stylesheet" href="/DriverLux/public/assets/css/perfil_cliente.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

</head>
<body>

<header class="topo">
  <a href="/DriverLux/index.html">
    <img src="/DriverLux/public/assets/img/logo.png"
         class="logo"
         alt="DriverLux">
  </a>
  <nav>
    <a href="#">ALUGUEL DE CARROS</a>
    <a href="#">GESTÃO DE FROTAS</a>
    <a href="#">SEMINOVOS</a>
    <a href="#">LUX - CARRO POR ASSINATURA</a>
    <a href="javascript:void(0)" id="btn-login-trigger" class="esconder">LOGIN</a>
  </nav>
  
  <div style="display: flex; align-items: center; gap: 24px;">
    <div class="breadcrumb" style="display: inline-block;">
      <a href="/DriverLux/index.html" style="color: white; text-decoration: none;">Início</a>
      <span class="sep" style="color: white;"> › </span>
      <span class="atual" style="color: white; font-weight: bold;">Meu Perfil</span>
    </div>

    <div class="area-usuario">
      <button id="btn-menu-usuario" class="btn-usuario esconder">
        <span id="nome-usuario">Usuário</span>
        <span class="seta-menu">▼</span>
      </button>

      <div id="menu-usuario" class="menu-usuario esconder">
        <a href="/DriverLux/app/View/painel_cliente.php">Minha conta</a>
        <a href="/DriverLux/app/View/fluxo-reserva/minhas-reservas.php">Minhas reservas</a>
        <a href="#" id="btn-sair">Sair</a>
      </div>

      <registro-login id="modal-auth" modo="popover"></registro-login>
    </div>
  </div>
</header>

<div class="page">
  <div class="perfil-hero">
    <div class="foto-wrap">
      <div class="foto-circulo">
        <span class="foto-inicial" id="foto-inicial">?</span>
        <img id="foto-img"
             src=""
             alt="Foto"
             style="display:none">
      </div>
      <div class="btn-troca-foto"
           onclick="document.getElementById('input-foto').click()">
        📷
      </div>
      <input type="file"
             id="input-foto"
             accept="image/*"
             onchange="onSelecionarFoto(event)">
    </div>
    <div class="perfil-info">
      <div class="tag-topo">
        Painel do Cliente
      </div>
      <h1 id="exibe-nome">Carregando...</h1>
      <div class="email-tag" id="exibe-email">—</div>
    </div>
  </div>

  <div class="divisor"></div>

  <!-- ABAS DE NAVEGAÇÃO -->
  <div class="tabs-container">
    <button class="tab-btn active" id="btn-tab-cadastro" onclick="switchTab('cadastro')">Meu Cadastro</button>
    <button class="tab-btn" id="btn-tab-reservas" onclick="switchTab('reservas')">Minhas Reservas</button>
  </div>

  <!-- CONTEÚDO: CADASTRO -->
  <div id="tab-cadastro" class="tab-content active">
    <div class="foto-preview-strip" id="foto-strip">
      <img id="foto-strip-img"
           src=""
           alt="Preview">
      <p>
        <strong>Nova foto selecionada</strong>
        Salve para confirmar a alteração.
      </p>
    </div>

    <div class="secao-label">
      Dados Pessoais
    </div>

    <div class="form-grid">
      <div class="campo">
        <label>Nome Completo</label>
        <input type="text" id="f-nome">
      </div>
      <div class="campo">
        <label>CPF</label>
        <input type="text" id="f-cpf">
      </div>
      <div class="campo span2">
        <label>E-mail</label>
        <input type="email"
               id="f-email"
               readonly>
      </div>
    </div>

    <div class="rodape-form">
      <button class="btn-salvar"
              id="btn-salvar"
              onclick="salvar()">
        <div class="spinner"></div>
        <span class="txt-btn">
          SALVAR ALTERAÇÕES
        </span>
      </button>
    </div>
  </div>

  <!-- CONTEÚDO: RESERVAS -->
  <div id="tab-reservas" class="tab-content">
    <div class="secao-label">Histórico de Reservas</div>
    <div class="reserva-lista" id="lista-reservas">
      <div style="text-align: center; padding: 40px; color: #888;">
        Carregando suas reservas...
      </div>
    </div>
  </div>
</div>

<div class="toast" id="toast"></div>

<script src="/DriverLux/public/assets/js/registro-login.js"></script>
<script src="/DriverLux/public/assets/js/menu-usuario.js"></script>
<script src="/DriverLux/public/assets/js/perfil.js"></script>

</body>
</html>