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

            <div class="lista-reservas" id="lista-reservas">
                <div style="text-align: center; padding: 40px; color: rgba(255,255,255,0.6);">
                    Carregando suas reservas...
                </div>
            </div>

            <div class="divisor-painel"></div>

            <a href="/DriverLux/index.html" class="btn-voltar-home">← Voltar para a Página Inicial</a>
        </div>
    </main>

    <?php require_once __DIR__ . '/../components/footer.php'; ?>

    <script src="/DriverLux/public/assets/js/menu-usuario.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', async () => {
            // Verifica sessão do usuário
            try {
                const resMe = await fetch('/DriverLux/public/api/usuarios/me');
                const dataMe = await resMe.json();
                if (!dataMe.logado || !dataMe.usuario) {
                    window.location.href = '/DriverLux/index.html';
                    return;
                }
            } catch (err) {
                window.location.href = '/DriverLux/index.html';
                return;
            }

            const container = document.getElementById('lista-reservas');
            if (!container) return;

            try {
                const res = await fetch('/DriverLux/public/api/reservas/minhas');
                const data = await res.json();

                if (data.status === 'success' && data.data && data.data.length > 0) {
                    container.innerHTML = '';

                    const formatarData = (dt) => {
                        if (!dt) return '—';
                        const parts = dt.split(' ');
                        const datePart = parts[0].split('-').reverse().join('/');
                        const timePart = parts[1] ? parts[1].substring(0, 5) : '12:00';
                        return `${datePart} às ${timePart}`;
                    };

                    const formatarMoeda = (val) => parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

                    data.data.forEach(reserva => {
                        const card = document.createElement('div');
                        card.className = 'card-reserva';

                        const statusClass = reserva.status.toLowerCase();
                        const fotoCarro = reserva.veiculo_imagem || 'https://cdn-icons-png.flaticon.com/512/3202/3202003.png';

                        card.innerHTML = `
                            <div class="reserva-detalhes">
                                <img class="carro-img-reserva" src="${fotoCarro}" alt="Carro">
                                <div class="reserva-textos">
                                    <h3>${reserva.veiculo_modelo || 'Veículo'}</h3>
                                    <div class="reserva-periodo">
                                        <strong>Retirada:</strong> ${formatarData(reserva.data_retirada)}<br>
                                        <strong>Devolução:</strong> ${formatarData(reserva.data_devolucao)}
                                    </div>
                                    <div class="reserva-locais">
                                        📍 Retirada: ${reserva.local_retirada || 'Não inf.'} | Devolução: ${reserva.local_devolucao || 'Não inf.'}
                                    </div>
                                </div>
                            </div>
                            <div class="reserva-status-preco">
                                <span class="status-badge ${statusClass}">${reserva.status}</span>
                                <div class="preco-reserva"><span>Total:</span> ${formatarMoeda(reserva.valor_total_previsto)}</div>
                            </div>
                        `;
                        container.appendChild(card);
                    });
                } else {
                    container.innerHTML = `
                        <div class="reserva-vazia">
                            <div class="reserva-vazia-icone">🚗</div>
                            <p>Você ainda não realizou nenhuma reserva de veículo de luxo.</p>
                            <a href="/DriverLux/index.html" class="btn-acao-painel">Alugar um Carro</a>
                        </div>
                    `;
                }
            } catch (e) {
                console.error("Erro ao carregar reservas:", e);
                container.innerHTML = `
                    <div style="text-align: center; padding: 20px; color: #ff4d4d; font-weight: bold;">
                        Erro ao conectar ao servidor. Tente novamente mais tarde.
                    </div>
                `;
            }
        });
    </script>
</body>

</html>