<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minhas Reservas - DriverLux</title>
    <link rel="shortcut icon" href="../../../public/assets/img/corrida.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/minhas-reservas.css">
</head>

<body class="reservas-body">

    <?php require_once __DIR__ . '/../components/header.php'; ?>

    <main class="main-reservas">
        <div class="container-reservas">
            <div class="header-painel">
                <h1>Minhas Reservas</h1>
                <p class="subtitulo-painel">Acompanhe seus aluguéis extraordinários na DriverLux.</p>
            </div>

            <div class="abas-reservas">
                <button class="aba-btn ativa" onclick="alternarAba('ativas')">Reservas Ativas</button>
                <button class="aba-btn" onclick="alternarAba('canceladas')">Histórico de Canceladas</button>
            </div>

            <div class="lista-reservas" id="lista-ativas">
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.6);">
                    Carregando suas reservas...
                </div>
            </div>

            <!-- preenchido pelo JS -->
            <div class="lista-reservas" id="lista-canceladas" style="display: none;">
            </div>

            <div class="divisor-painel"></div>

            <a href="/DriverLux/index.html" class="btn-voltar-home"> Voltar para a Página Inicial</a>
        </div>
    </main>

    <div id="modalConfirmacao" class="modal-confirmacao">
        <div class="modal-confirmacao-box">

            <h3>Cancelar Reserva</h3>

            <p>
                Tem certeza que deseja cancelar esta reserva?
                Esta ação não poderá ser desfeita.
            </p>

            <div class="modal-acoes">
                <button id="btnManterReserva" class="btn-modal-secundario">
                    Voltar
                </button>

                <button id="btnConfirmarCancelamento" class="btn-modal-perigo">
                    Cancelar Reserva
                </button>
            </div>
        </div>
    </div>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>
    <script src="/DriverLux/public/assets/js/menu-usuario.js"></script>
    <script src="/DriverLux/public/assets/js/minhas-reservas.js"></script>
</body>

</html>