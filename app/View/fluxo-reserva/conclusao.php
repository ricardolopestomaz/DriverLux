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
    <title>Reserva Confirmada - DriverLux</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css">
    <style>
        .concluido-body {
            background: linear-gradient(135deg, #3b0567 0%, #1c0035 100%);
            min-height: 100vh;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .main-conclusao {
            max-width: 750px;
            margin: 60px auto;
            padding: 20px;
        }
        .card-sucesso {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .icone-sucesso {
            width: 80px;
            height: 80px;
            background: #2ecc71;
            color: #fff;
            font-size: 40px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
            box-shadow: 0 0 30px rgba(46, 204, 113, 0.5);
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0.7); }
            70% { box-shadow: 0 0 0 15px rgba(46, 204, 113, 0); }
            100% { box-shadow: 0 0 0 0 rgba(46, 204, 113, 0); }
        }
        .card-sucesso h1 {
            font-size: 32px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 8px;
        }
        .subtitulo-sucesso {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            margin-bottom: 30px;
        }
        .codigo-reserva {
            background: rgba(255, 196, 0, 0.1);
            border: 1.5px dashed var(--dourado);
            border-radius: 12px;
            padding: 12px 24px;
            display: inline-block;
            color: var(--dourado);
            font-weight: 800;
            font-size: 18px;
            letter-spacing: 1px;
            margin-bottom: 35px;
        }
        .divisor-sucesso {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 30px 0;
        }
        .bloco-info {
            text-align: left;
        }
        .titulo-bloco {
            font-size: 14px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: var(--dourado);
            margin-bottom: 20px;
        }
        .carro-resumo-sucesso {
            display: flex;
            align-items: center;
            gap: 20px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px;
            margin-bottom: 25px;
        }
        .carro-img-sucesso {
            width: 140px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.3));
        }
        .carro-detalhes-sucesso h3 {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 4px;
        }
        .carro-detalhes-sucesso p {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.5);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        .resumo-detalhes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }
        .detalhe-item {
            background: rgba(255, 255, 255, 0.02);
            border-radius: 12px;
            padding: 16px;
            border: 1px solid rgba(255, 255, 255, 0.03);
        }
        .detalhe-item label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.4);
            letter-spacing: 1px;
            display: block;
            margin-bottom: 6px;
        }
        .detalhe-item span {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }
        .valores-pagamento {
            background: rgba(106, 13, 173, 0.15);
            border: 1px solid rgba(128, 20, 232, 0.25);
            border-radius: 16px;
            padding: 22px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .pagamento-info-lbl {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .pagamento-info-val {
            font-size: 14px;
            font-weight: 600;
            color: #fff;
        }
        .total-pago-box {
            text-align: right;
        }
        .total-pago-lbl {
            font-size: 11px;
            font-weight: 700;
            color: var(--dourado);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 4px;
        }
        .total-pago-val {
            font-size: 26px;
            font-weight: 900;
            color: #fff;
            line-height: 1;
        }
        .botoes-sucesso {
            display: flex;
            gap: 20px;
            margin-top: 40px;
        }
        .btn-sucesso-acao {
            flex: 1;
            padding: 16px;
            border-radius: 12px;
            font-weight: 800;
            font-size: 14px;
            font-family: inherit;
            cursor: pointer;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-sucesso-acao.principal {
            background: linear-gradient(135deg, var(--roxo-medio) 0%, var(--roxo-claro) 100%);
            color: #fff;
            border: none;
            box-shadow: 0 8px 20px rgba(106, 13, 173, 0.4);
        }
        .btn-sucesso-acao.principal:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(106, 13, 173, 0.6);
        }
        .btn-sucesso-acao.secundario {
            background: transparent;
            color: rgba(255, 255, 255, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        .btn-sucesso-acao.secundario:hover {
            background: rgba(255, 255, 255, 0.05);
            border-color: rgba(255, 255, 255, 0.4);
            color: #fff;
        }
        @media (max-width: 600px) {
            .resumo-detalhes-grid {
                grid-template-columns: 1fr;
            }
            .botoes-sucesso {
                flex-direction: column;
                gap: 12px;
            }
            .valores-pagamento {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            .total-pago-box {
                text-align: left;
            }
        }
    </style>
</head>
<body class="concluido-body">

    <header class="topo">
        <img src="/DriverLux/public/assets/img/logo.png" class="logo" alt="DriverLux">
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
        </div>
    </header>

    <div class="stepper-wrap" style="margin-top: 40px; margin-bottom: 20px;">
        <div class="stepper-container">
            <div class="step done">
                <div class="step-bolinha">1</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Carro</span></div>
            </div>
            <div class="step done">
                <div class="step-bolinha">2</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Opcionais</span></div>
            </div>
            <div class="step done">
                <div class="step-bolinha">3</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Pagamento</span></div>
            </div>
            <div class="step active done">
                <div class="step-bolinha">4</div>
                <div class="step-info"><span class="step-sub">Etapa</span><span class="step-nome">Resumo</span></div>
            </div>
        </div>
    </div>

    <main class="main-conclusao">
        <div class="card-sucesso">
            <div class="icone-sucesso">✓</div>
            <h1>Reserva Confirmada!</h1>
            <p class="subtitulo-sucesso">Seu aluguel foi confirmado e seu pagamento aprovado com sucesso. Prepare-se para guiar o extraordinário.</p>
            
            <div class="codigo-reserva">
                CÓDIGO DA RESERVA: #<span id="reserva-id">00000</span>
            </div>

            <div class="divisor-sucesso"></div>

            <div class="bloco-info">
                <div class="titulo-bloco">Resumo da Reserva</div>
                
                <div class="carro-resumo-sucesso">
                    <img id="carro-foto" class="carro-img-sucesso" src="https://cdn-icons-png.flaticon.com/512/3202/3202003.png" alt="Carro">
                    <div class="carro-detalhes-sucesso">
                        <h3 id="carro-modelo">—</h3>
                        <p id="carro-categoria">—</p>
                    </div>
                </div>

                <div class="resumo-detalhes-grid">
                    <div class="detalhe-item">
                        <label>Retirada</label>
                        <span id="retirada-local">—</span><br>
                        <span id="retirada-data-hora" style="font-size: 12px; color: rgba(255,255,255,0.6); font-weight: normal;">—</span>
                    </div>

                    <div class="detalhe-item">
                        <label>Devolução</label>
                        <span id="devolucao-local">—</span><br>
                        <span id="devolucao-data-hora" style="font-size: 12px; color: rgba(255,255,255,0.6); font-weight: normal;">—</span>
                    </div>

                    <div class="detalhe-item">
                        <label>Período Total</label>
                        <span id="reserva-diarias">—</span>
                    </div>

                    <div class="detalhe-item">
                        <label>Desconto Aplicado</label>
                        <span id="reserva-desconto">—</span>
                    </div>
                </div>

                <div class="valores-pagamento">
                    <div>
                        <div class="pagamento-info-lbl">Método de Pagamento</div>
                        <div class="pagamento-info-val" id="pagamento-metodo">—</div>
                    </div>
                    <div class="total-pago-box">
                        <div class="total-pago-lbl">Valor Total Pago</div>
                        <div class="total-pago-val">R$ <span id="total-pago">—</span></div>
                    </div>
                </div>
            </div>

            <div class="botoes-sucesso">
                <a href="/DriverLux/app/View/fluxo-reserva/minhas-reservas.php" class="btn-sucesso-acao principal">Minhas Reservas</a>
                <a href="/DriverLux/index.html" class="btn-sucesso-acao secundario">Voltar para o Início</a>
            </div>
        </div>
    </main>

    <script src="/DriverLux/public/assets/js/menu-usuario.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dadosFinais = JSON.parse(sessionStorage.getItem('resumo_reserva_final') || 'null');

            if (!dadosFinais) {
                // Se não houver dados, manda pra home
                window.location.href = '/DriverLux/index.html';
                return;
            }

            // Utilitário formatação
            const formatarDataBR = (dataStr) => {
                if (!dataStr) return '—';
                return dataStr.split('-').reverse().join('/');
            };

            // Preenche os campos
            document.getElementById('reserva-id').textContent = dadosFinais.reserva_id;
            document.getElementById('carro-modelo').textContent = dadosFinais.veiculo_modelo;
            
            if (dadosFinais.veiculo_imagem) {
                document.getElementById('carro-foto').src = dadosFinais.veiculo_imagem;
            }

            document.getElementById('retirada-local').textContent = dadosFinais.local_retirada;
            document.getElementById('retirada-data-hora').textContent = `${formatarDataBR(dadosFinais.data_retirada)} às ${dadosFinais.hora_retirada || '12:00'}`;
            
            document.getElementById('devolucao-local').textContent = dadosFinais.local_devolucao;
            document.getElementById('devolucao-data-hora').textContent = `${formatarDataBR(dadosFinais.data_devolucao)} às ${dadosFinais.hora_devolucao || '12:00'}`;
            
            document.getElementById('reserva-diarias').textContent = `${dadosFinais.num_diarias} diária(s)`;
            
            const descontoNum = parseFloat(dadosFinais.desconto || 0);
            document.getElementById('reserva-desconto').textContent = descontoNum > 0 
                ? `R$ ${descontoNum.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`
                : 'Nenhum';

            const metodoTexto = dadosFinais.metodo_pagamento === 'credito' || dadosFinais.metodo_pagamento === 'Crédito'
                ? (dadosFinais.parcelas > 1 ? `Crédito (${dadosFinais.parcelas}x)` : 'Crédito')
                : 'Débito';
            document.getElementById('pagamento-metodo').textContent = metodoTexto;

            const totalNum = parseFloat(dadosFinais.total_pago || 0);
            document.getElementById('total-pago').textContent = totalNum.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        });
    </script>
</body>
</html>
