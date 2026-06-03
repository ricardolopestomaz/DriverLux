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
    <link rel="shortcut icon" href="../../../public/assets/img/corrida.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/conclusao.css">


</head>

<body class="concluido-body">

<?php require_once __DIR__ . '/../components/header.php'; ?>


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
            <p class="subtitulo-sucesso">Seu aluguel foi confirmado e seu pagamento aprovado com sucesso. Prepare-se
                para guiar o extraordinário.</p>

            <div class="codigo-reserva">
                CÓDIGO DA RESERVA: #<span id="reserva-id">00000</span>
            </div>

            <div class="divisor-sucesso"></div>

            <div class="bloco-info">
                <div class="titulo-bloco">Resumo da Reserva</div>

                <div class="carro-resumo-sucesso">
                    <img id="carro-foto" class="carro-img-sucesso"
                        src="https://cdn-icons-png.flaticon.com/512/3202/3202003.png" alt="Carro">
                    <div class="carro-detalhes-sucesso">
                        <h3 id="carro-modelo">—</h3>
                        <p id="carro-categoria">—</p>
                    </div>
                </div>

                <div class="resumo-detalhes-grid">
                    <div class="detalhe-item">
                        <label>Retirada</label>
                        <span id="retirada-local">—</span><br>
                        <span id="retirada-data-hora"
                            style="font-size: 12px; color: rgba(255,255,255,0.6); font-weight: normal;">—</span>
                    </div>

                    <div class="detalhe-item">
                        <label>Devolução</label>
                        <span id="devolucao-local">—</span><br>
                        <span id="devolucao-data-hora"
                            style="font-size: 12px; color: rgba(255,255,255,0.6); font-weight: normal;">—</span>
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
                <a href="/DriverLux/app/View/fluxo-reserva/minhas-reservas.php"
                    class="btn-sucesso-acao principal">Minhas Reservas</a>
                <a href="/DriverLux/index.html" class="btn-sucesso-acao secundario">Voltar para o Início</a>
            </div>
        </div>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>

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