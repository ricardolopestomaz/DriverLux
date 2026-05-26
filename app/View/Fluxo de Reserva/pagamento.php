<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <title>Pagamento - DriverLux</title>
   <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
   <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css">
   <link rel="stylesheet" href="/DriverLux/public/assets/css/pagamento.css">
   


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
                <div class="faixa-label">Passo 3 de 4</div>
                <h1 class="faixa-titulo">Pagamento</h1>
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
            <div class="step done">
                <div class="step-bolinha">1</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Carro</span></div>
            </div>
            <div class="step done">
                <div class="step-bolinha">2</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Opcionais</span></div>
            </div>
            <div class="step active">
                <div class="step-bolinha">3</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Pagamento</span></div>
            </div>
            <div class="step">
                <div class="step-bolinha">4</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Resumo</span></div>
            </div>
        </div>
    </div>

    <div class="pg-content">

        <div class="card-form">

            <div class="section-title">Forma de Pagamento</div>

            <div class="tipo-cartao">
                <button class="tipo-btn ativo" id="btn-credito">Crédito</button>
                <button class="tipo-btn" id="btn-debito">Débito</button>
            </div>

            <div class="campo-grupo">
                <label class="campo-label">Nome do Titular</label>
                <input class="campo-input" id="nome-titular" type="text" placeholder="Como aparece no cartão do dono" />
            </div>

            <div class="campo-grupo">
                <label class="campo-label">Número do Cartão</label>
                <div class="campo-cartao-wrap">
                    <input class="campo-input" id="numero-cartao" type="text" placeholder="0000 0000 0000 0000" maxlength="19" />
                </div>
            </div>

            <div class="row-3">
                <div class="campo-grupo">
                    <label class="campo-label">Mês</label>
                    <select class="campo-select" id="mes-vencimento">
                        <option value="">MM</option>
                        <option>01</option><option>02</option><option>03</option>
                        <option>04</option><option>05</option><option>06</option>
                        <option>07</option><option>08</option><option>09</option>
                        <option>10</option><option>11</option><option>12</option>
                    </select>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Ano</label>
                    <select class="campo-select" id="ano-vencimento">
                        <option value="">AAAA</option>
                        <option>2025</option><option>2026</option><option>2027</option>
                        <option>2028</option><option>2029</option><option>2030</option>
                    </select>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">CVV</label>
                    <input class="campo-input" id="cvv" type="text" placeholder="123" maxlength="3" />
                </div>
            </div>

            <div class="divider"></div>

            <div id="bloco-parcelas">
                <div class="section-title" style="margin-bottom: 14px;">Parcelamento</div>
                <div class="parcelas-wrap">
                    <div class="parcelas-grid" id="lista-parcelas"></div>
                </div>
            </div>

        </div>

           <!-- resumo da compra -->
        <div class="card-resumo">

            <div class="section-title" style="margin-bottom: 16px;">Resumo do Pagamento</div>

            <div class="resumo-veiculo">
                <div class="resumo-veiculo-img">&#128663;</div>
                <div>
                    <div class="resumo-veiculo-nome" id="resumo-modelo">—</div>
                    <div class="resumo-veiculo-cat" id="resumo-categoria">—</div>
                </div>
            </div>

            <div class="resumo-linha">
                <span id="label-diarias">Diárias</span>
                <span id="val-diarias">R$ —</span>
            </div>
            <div class="resumo-linha">
                <span>Proteção</span>
                <span id="val-protecao">R$ —</span>
            </div>
            <div class="resumo-linha">
                <span>Taxa de aluguel (15%)</span>
                <span id="val-taxa">R$ —</span>
            </div>

            <div class="resumo-linha" id="linha-desconto" style="display: none;">
                <span id="label-cupom">Cupom</span>
                <span id="val-desconto" style="color: #18a058;">— R$ —</span>
            </div>

            <div class="resumo-total">
                <span class="total-label">Total</span>
                <div style="text-align: right;">
                    <div class="total-valor">
                        <span class="total-rs">R$</span>
                        <span id="total-exibido">—</span>
                    </div>
                    <div id="parcela-info" class="parcela-info-texto">à vista</div>
                </div>
            </div>

            <div id="badge-desconto" class="badge-desconto" style="display: none;">
                &#127991; <span id="texto-desconto">Desconto aplicado</span>
            </div>

            <button class="btn-pagar" id="btn-pagar">Confirmar Pagamento</button>

        </div>
    </div>

    <script src="/DriverLux/public/assets/js/registro-login.js"></script>
    <script src="/DriverLux/public/assets/js/menu-usuario.js"></script>
    <script src="/DriverLux/public/assets/js/pagamento.js"></script>
    
</body>
</html>