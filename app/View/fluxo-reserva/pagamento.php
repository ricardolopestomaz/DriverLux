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
    <script>
        document.addEventListener('DOMContentLoaded', () => {
    // ==========================================================================
    // 1. RECUPERAÇÃO DE DADOS DA SESSÃO
    // ==========================================================================
    const dadosReserva = JSON.parse(sessionStorage.getItem('dados_reserva') || '{}');

    // Se o usuário cair aqui sem ter escolhido um carro, manda ele de volta
    if (!dadosReserva.veiculo_modelo) {
        alert("Nenhum veículo selecionado. Você será redirecionado para a busca.");
        window.location.href = '/DriverLux/app/View/fluxo-reserva/veiculos.php';
        return;
    }

    // ==========================================================================
    // 2. CÁLCULO DOS VALORES (Diárias, Proteção e Taxas)
    // ==========================================================================
    // Assume 1 diária se o cálculo de dias ainda não foi feito no Passo 2
    const numDiarias = dadosReserva.num_diarias ? parseInt(dadosReserva.num_diarias) : 1; 
    const valorDiaria = dadosReserva.valor_diaria || 0;
    
    // Se você tiver proteção escolhida no passo 2, pegue da sessão. Senão, 0.
    const valorProtecao = dadosReserva.valor_protecao ? parseFloat(dadosReserva.valor_protecao) : 0; 
    
    const subtotalDiarias = valorDiaria * numDiarias;
    const taxaAluguel = (subtotalDiarias + valorProtecao) * 0.15; // 15% de taxa
    const totalGeral = subtotalDiarias + valorProtecao + taxaAluguel;

    // Função auxiliar para formatar moeda (ex: 1500.5 -> "1.500,50")
    const formatarMoeda = (valor) => valor.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    // ==========================================================================
    // 3. ATUALIZAÇÃO DOS RESUMOS NO HTML
    // ==========================================================================
    
    // Atualiza a faixa de cabeçalho
    const resumoValores = document.querySelectorAll('.resumo-valor');
    if (resumoValores.length >= 2) {
        if (dadosReserva.local_retirada) resumoValores[0].innerHTML = `📍 ${dadosReserva.local_retirada}`;
        resumoValores[1].innerHTML = `📅 ${numDiarias} Diária(s)`;
    }

    // Atualiza o Card Lateral
    document.getElementById('resumo-modelo').textContent = dadosReserva.veiculo_modelo;
    document.getElementById('resumo-categoria').textContent = dadosReserva.categoria_nome;
    
    document.getElementById('label-diarias').textContent = `Diárias (${numDiarias}x)`;
    document.getElementById('val-diarias').textContent = `R$ ${formatarMoeda(subtotalDiarias)}`;
    document.getElementById('val-protecao').textContent = `R$ ${formatarMoeda(valorProtecao)}`;
    document.getElementById('val-taxa').textContent = `R$ ${formatarMoeda(taxaAluguel)}`;
    document.getElementById('total-exibido').textContent = formatarMoeda(totalGeral);

    // ==========================================================================
    // 4. LÓGICA DE FORMA DE PAGAMENTO E PARCELAMENTO
    // ==========================================================================
    const btnCredito = document.getElementById('btn-credito');
    const btnDebito = document.getElementById('btn-debito');
    const blocoParcelas = document.getElementById('bloco-parcelas');
    const listaParcelas = document.getElementById('lista-parcelas');
    const parcelaInfo = document.getElementById('parcela-info');

    // Monta os botões de parcelamento dinamicamente (até 6x, por exemplo)
    function renderizarParcelas() {
        listaParcelas.innerHTML = '';
        
        for (let i = 1; i <= 6; i++) {
            const valorParcela = totalGeral / i;
            const div = document.createElement('div');
            
            // Adiciona um estilo básico pelo JS (pode melhorar no seu CSS)
            div.style.padding = '10px';
            div.style.border = '1px solid #ccc';
            div.style.borderRadius = '6px';
            div.style.cursor = 'pointer';
            div.style.marginBottom = '8px';
            div.style.display = 'flex';
            div.style.justifyContent = 'space-between';
            
            if (i === 1) {
                div.style.borderColor = '#3b0567';
                div.style.background = '#f3e8ff';
                parcelaInfo.textContent = `à vista`;
            }

            div.innerHTML = `<strong>${i}x</strong> <span>R$ ${formatarMoeda(valorParcela)}</span>`;
            
            // Clique para selecionar a parcela
            div.addEventListener('click', () => {
                // Reseta visual de todos
                Array.from(listaParcelas.children).forEach(filho => {
                    filho.style.borderColor = '#ccc';
                    filho.style.background = 'transparent';
                });
                // Aplica visual de selecionado no atual
                div.style.borderColor = '#3b0567';
                div.style.background = '#f3e8ff';
                
                parcelaInfo.textContent = i === 1 ? 'à vista' : `${i}x de R$ ${formatarMoeda(valorParcela)}`;
                
                // Salva a escolha na sessão para a tela final
                dadosReserva.parcelamento = i;
                sessionStorage.setItem('dados_reserva', JSON.stringify(dadosReserva));
            });

            listaParcelas.appendChild(div);
        }
    }

    renderizarParcelas();

    // Eventos de clique nas Abas de Pagamento
    btnCredito.addEventListener('click', () => {
        btnCredito.classList.add('ativo');
        btnDebito.classList.remove('ativo');
        blocoParcelas.style.display = 'block';
        renderizarParcelas();
        dadosReserva.metodo_pagamento = 'Credito';
    });

    btnDebito.addEventListener('click', () => {
        btnDebito.classList.add('ativo');
        btnCredito.classList.remove('ativo');
        blocoParcelas.style.display = 'none';
        parcelaInfo.textContent = 'à vista no Débito';
        dadosReserva.metodo_pagamento = 'Debito';
        dadosReserva.parcelamento = 1;
    });

    // ==========================================================================
    // 5. MÁSCARAS DE INPUT PARA O CARTÃO E VALIDAÇÃO FINAL
    // ==========================================================================
    const inputCartao = document.getElementById('numero-cartao');
    const inputCvv = document.getElementById('cvv');

    // Máscara: Espaços a cada 4 números
    inputCartao.addEventListener('input', (e) => {
        let value = e.target.value.replace(/\D/g, ''); // Remove o que não é número
        value = value.replace(/(.{4})/g, '$1 ').trim(); // Adiciona espaço
        e.target.value = value;
    });

    // Máscara: Apenas números no CVV
    inputCvv.addEventListener('input', (e) => {
        e.target.value = e.target.value.replace(/\D/g, '');
    });

    // Lógica do botão de confirmar
    const btnPagar = document.getElementById('btn-pagar');
    btnPagar.addEventListener('click', () => {
        const nomeTitular = document.getElementById('nome-titular').value.trim();
        const numeroCartao = inputCartao.value.replace(/\s/g, '');
        const mesValidade = document.getElementById('mes-vencimento').value;
        const anoValidade = document.getElementById('ano-vencimento').value;
        const cvv = inputCvv.value;

        // Validação simples de campos
        if (!nomeTitular || numeroCartao.length < 13 || !mesValidade || !anoValidade || cvv.length < 3) {
            alert('Por favor, preencha corretamente todos os dados do cartão.');
            return;
        }

        // Salva os totais e encerra
        dadosReserva.total_pago = totalGeral;
        sessionStorage.setItem('dados_reserva', JSON.stringify(dadosReserva));

        // Aqui você pode redirecionar para a "Etapa 4" (Página de Conclusão/Sucesso)
        // Substitua pelo link real da sua página de resumo/conclusão
        btnPagar.textContent = "Processando...";
        btnPagar.disabled = true;

        setTimeout(() => {
            window.location.href = '/DriverLux/app/View/fluxo-reserva/conclusao.php'; // Ajuste este caminho
        }, 1500); // Simulando tempo de API
    });
});
    </script>
</body>
</html>