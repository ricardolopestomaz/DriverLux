<header class="topo">
    <a href="/DriverLux/index.html">
        <img src="/DriverLux/public/assets/img/DriverLux.png" class="logo" alt="DriverLux" />
    </a>

    <nav>
        <a href="/DriverLux/index.html">ALUGUEL DE CARROS</a>
        <a href="#">GESTÃO DE FROTAS</a>
        <a href="#">SEMINOVOS</a>
        <a href="#">LUX - CARRO POR ASSINATURA</a>
        <a href="javascript:void(0)" id="btn-login-trigger">LOGIN</a>
    </nav>

    <div class="area-usuario-wrapper" style="display: flex; align-items: center; gap: 24px;">
        <?php if (isset($breadcrumb_title)): ?>
            <div class="breadcrumb" style="display: inline-block;">
                <span class="atual" style="color: white; font-weight: bold;"><?= htmlspecialchars($breadcrumb_title) ?></span>
            </div>
        <?php endif; ?>

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
