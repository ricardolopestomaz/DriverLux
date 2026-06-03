<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagamento - DriverLux</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/pagamento.css">



</head>

<body>
    <header class="topo">
        <a href="/DriverLux/index.html">
            <img src="/DriverLux/public/assets/img/logo.png" class="logo" alt="DriverLux">
        </a>
        <nav>
            <a href="/DriverLux/index.html">ALUGUEL DE CARROS</a>
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
                <a href="/DriverLux/app/View/fluxo-reserva/minhas-reservas.php">Minhas reservas</a>
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
                    <input class="campo-input" id="numero-cartao" type="text" placeholder="0000 0000 0000 0000"
                        maxlength="19" />
                </div>
            </div>

            <div class="row-3">
                <div class="campo-grupo">
                    <label class="campo-label">Mês</label>
                    <select class="campo-select" id="mes-vencimento">
                        <option value="">MM</option>
                        <option>01</option>
                        <option>02</option>
                        <option>03</option>
                        <option>04</option>
                        <option>05</option>
                        <option>06</option>
                        <option>07</option>
                        <option>08</option>
                        <option>09</option>
                        <option>10</option>
                        <option>11</option>
                        <option>12</option>
                    </select>
                </div>
                <div class="campo-grupo">
                    <label class="campo-label">Ano</label>
                    <select class="campo-select" id="ano-vencimento">
                        <option value="">AAAA</option>
                        <option>2025</option>
                        <option>2026</option>
                        <option>2027</option>
                        <option>2028</option>
                        <option>2029</option>
                        <option>2030</option>
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
                <div class="resumo-veiculo-img"
                    style="background: transparent; width: 65px; height: 50px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                    <img id="resumo-veiculo-foto" src="" alt="Foto"
                        style="width: 100%; height: 100%; object-fit: contain; display: none;">
                    <span id="resumo-veiculo-emoji" style="font-size: 26px;">&#128663;</span>
                </div>
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

            <button class="btn-voltar" id="btn-voltar-opcionais"
                style="width: 100%; margin-top: 10px; background: transparent; border: 1px solid #555; color: #bbb; padding: 12px; border-radius: 8px; font-weight: bold; cursor: pointer; transition: all 0.3s ease;">
                Voltar para Opcionais
            </button>

        </div>
    </div>

    <script src="/DriverLux/public/assets/js/registro-login.js"></script>
    <script src="/DriverLux/public/assets/js/menu-usuario.js"></script>
    <script src="/DriverLux/public/assets/js/pagamento.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // ==========================================================================
            // 0. VERIFICAÇÃO OBRIGATÓRIA DE LOGIN
            // ==========================================================================
            fetch('/DriverLux/public/api/usuarios/me')
                .then(res => res.json())
                .then(data => {
                    if (!data.logado || !data.usuario) {
                        const modalAuth = document.getElementById('modal-auth');
                        if (modalAuth) {
                            const abrirModal = () => {
                                const container = modalAuth.shadowRoot ? modalAuth.shadowRoot.getElementById('auth-container') : null;
                                if (container) {
                                    container.classList.remove('hidden');

                                    // Oculta o botão "X" de fechar
                                    const closeBtn = modalAuth.shadowRoot.getElementById('close-auth');
                                    if (closeBtn) {
                                        closeBtn.style.display = 'none';
                                    }

                                    // Adiciona o botão voltar dentro do card de login se não houver
                                    const card = modalAuth.shadowRoot.querySelector('.auth-card');
                                    if (card && !modalAuth.shadowRoot.getElementById('auth-btn-voltar')) {
                                        const btnVoltarAuth = document.createElement('button');
                                        btnVoltarAuth.id = 'auth-btn-voltar';
                                        btnVoltarAuth.textContent = '← Voltar para Opcionais';
                                        btnVoltarAuth.style.width = '100%';
                                        btnVoltarAuth.style.marginTop = '15px';
                                        btnVoltarAuth.style.background = 'transparent';
                                        btnVoltarAuth.style.border = '1px solid #6B00CC';
                                        btnVoltarAuth.style.color = '#6B00CC';
                                        btnVoltarAuth.style.padding = '12px';
                                        btnVoltarAuth.style.borderRadius = '8px';
                                        btnVoltarAuth.style.fontWeight = 'bold';
                                        btnVoltarAuth.style.cursor = 'pointer';
                                        btnVoltarAuth.style.fontFamily = 'inherit';
                                        btnVoltarAuth.style.transition = 'all 0.3s ease';

                                        btnVoltarAuth.addEventListener('mouseenter', () => {
                                            btnVoltarAuth.style.background = '#6B00CC';
                                            btnVoltarAuth.style.color = '#fff';
                                        });
                                        btnVoltarAuth.addEventListener('mouseleave', () => {
                                            btnVoltarAuth.style.background = 'transparent';
                                            btnVoltarAuth.style.color = '#6B00CC';
                                        });
                                        btnVoltarAuth.addEventListener('click', () => {
                                            window.location.href = '/DriverLux/app/View/fluxo-reserva/opcionais.php';
                                        });
                                        card.appendChild(btnVoltarAuth);
                                    }
                                }
                            };

                            abrirModal();
                            const interval = setInterval(() => {
                                const container = modalAuth.shadowRoot ? modalAuth.shadowRoot.getElementById('auth-container') : null;
                                if (container && !container.classList.contains('hidden')) {
                                    const closeBtn = modalAuth.shadowRoot.getElementById('close-auth');
                                    if (closeBtn) {
                                        closeBtn.style.display = 'none';
                                    }
                                    clearInterval(interval);
                                } else {
                                    abrirModal();
                                }
                            }, 200);
                        }
                    }
                })
                .catch(err => console.error('Erro na checagem de login:', err));

            // Botão Voltar da página principal
            const btnVoltarOpcionais = document.getElementById('btn-voltar-opcionais');
            if (btnVoltarOpcionais) {
                btnVoltarOpcionais.addEventListener('click', () => {
                    window.location.href = '/DriverLux/app/View/fluxo-reserva/opcionais.php';
                });
            }
        });
    </script>
</body>

</html>