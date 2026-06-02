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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css">
    <style>
        .reservas-body {
            background: linear-gradient(135deg, #3b0567 0%, #1c0035 100%);
            min-height: 100vh;
            color: #fff;
            font-family: 'Poppins', sans-serif;
        }
        .main-reservas {
            max-width: 900px;
            margin: 60px auto;
            padding: 20px;
        }
        .container-reservas {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
            padding: 40px;
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
            animation: fadeIn 0.6s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header-painel h1 {
            font-size: 32px;
            font-weight: 900;
            color: #fff;
            margin-bottom: 8px;
            text-align: center;
        }
        .subtitulo-painel {
            color: rgba(255, 255, 255, 0.7);
            font-size: 16px;
            margin-bottom: 40px;
            text-align: center;
        }
        .divisor-painel {
            height: 1px;
            background: rgba(255, 255, 255, 0.1);
            margin: 30px 0;
        }
        .lista-reservas {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        .card-reserva {
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 18px;
            padding: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: all 0.3s ease;
        }
        .card-reserva:hover {
            transform: translateY(-3px);
            border-color: rgba(255, 196, 0, 0.3);
            box-shadow: 0 10px 25px rgba(106, 13, 173, 0.3);
        }
        .reserva-detalhes {
            display: flex;
            align-items: center;
            gap: 24px;
        }
        .carro-img-reserva {
            width: 140px;
            height: 80px;
            object-fit: contain;
            filter: drop-shadow(0 6px 12px rgba(0, 0, 0, 0.3));
        }
        .reserva-textos h3 {
            font-size: 18px;
            font-weight: 800;
            color: #fff;
            margin-bottom: 6px;
            text-transform: uppercase;
        }
        .reserva-periodo {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 6px;
        }
        .reserva-locais {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.45);
        }
        .reserva-status-preco {
            text-align: right;
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            gap: 10px;
        }
        .status-badge {
            font-size: 9px;
            font-weight: 800;
            text-transform: uppercase;
            padding: 4px 10px;
            border-radius: 6px;
            letter-spacing: 0.5px;
        }
        .status-badge.confirmada {
            background: rgba(46, 204, 113, 0.15);
            color: #2ecc71;
            border: 1px solid rgba(46, 204, 113, 0.3);
        }
        .status-badge.pendente {
            background: rgba(241, 196, 15, 0.15);
            color: #f1c40f;
            border: 1px solid rgba(241, 196, 15, 0.3);
        }
        .status-badge.cancelada {
            background: rgba(231, 76, 60, 0.12);
            color: #e74c3c;
            border: 1px solid rgba(231, 76, 60, 0.25);
        }
        .preco-reserva {
            font-size: 20px;
            font-weight: 900;
            color: var(--dourado);
        }
        .preco-reserva span {
            font-size: 12px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
        }
        .reserva-vazia {
            text-align: center;
            padding: 50px 20px;
            color: rgba(255, 255, 255, 0.6);
        }
        .reserva-vazia-icone {
            font-size: 48px;
            margin-bottom: 16px;
        }
        .reserva-vazia p {
            font-size: 15px;
            margin-bottom: 24px;
        }
        .btn-acao-painel {
            display: inline-block;
            background: linear-gradient(135deg, var(--roxo-medio) 0%, var(--roxo-claro) 100%);
            color: #fff;
            padding: 12px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 800;
            font-size: 13px;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(108, 47, 255, 0.35);
        }
        .btn-acao-painel:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(108, 47, 255, 0.5);
        }
        .btn-voltar-home {
            display: block;
            text-align: center;
            margin-top: 30px;
            color: rgba(255, 255, 255, 0.6);
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: color 0.3s;
        }
        .btn-voltar-home:hover {
            color: #fff;
        }
        @media (max-width: 768px) {
            .card-reserva {
                flex-direction: column;
                align-items: flex-start;
                gap: 20px;
            }
            .reserva-status-preco {
                text-align: left;
                align-items: flex-start;
                width: 100%;
                border-top: 1px solid rgba(255, 255, 255, 0.1);
                padding-top: 15px;
            }
            .reserva-detalhes {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
    </style>
</head>
<body class="reservas-body">

    <header class="topo">
        <a href="/DriverLux/index.html">
            <img src="/DriverLux/public/assets/img/logo.png" class="logo" alt="DriverLux">
        </a>
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
