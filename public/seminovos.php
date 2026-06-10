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
    <title>Seminovos de Luxo - DriverLux</title>
    <link rel="shortcut icon" href="/DriverLux/public/assets/img/corrida.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css?v=3">
    <style>
        .seminovos-hero {
            min-height: 400px;
            background: linear-gradient(135deg, rgba(68, 12, 119, 0.9) 0%, rgba(59, 5, 103, 0.8) 100%),
                url('/DriverLux/public/assets/img/hero.png') center center / cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
            padding: 100px 20px 60px;
        }

        .seminovos-hero-content h1 {
            font-size: 48px;
            font-weight: 800;
            margin-bottom: 20px;
            letter-spacing: 1.5px;
            line-height: 1.2;
        }

        .seminovos-hero-content h1 span {
            color: var(--dourado);
        }

        .seminovos-hero-content p {
            font-size: 18px;
            max-width: 700px;
            margin: 0 auto;
            opacity: 0.9;
        }

        .seminovos-container {
            max-width: 1200px;
            margin: 60px auto;
            padding: 0 20px;
        }

        .filter-nav {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: rgba(106, 13, 173, 0.05);
            border: 1.5px solid rgba(106, 13, 173, 0.15);
            padding: 10px 24px;
            border-radius: 30px;
            font-family: inherit;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            color: var(--roxo-escuro);
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: var(--roxo-medio);
            color: #fff;
            border-color: var(--roxo-medio);
            box-shadow: 0 4px 15px rgba(106, 13, 173, 0.3);
        }

        .seminovos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 30px;
        }

        .seminovo-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.05);
            overflow: hidden;
            border: 1px solid var(--borda-card);
            transition: all 0.3s ease;
            display: flex;
            flex-direction: column;
        }

        .seminovo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(106, 13, 173, 0.15);
        }

        .seminovo-img-wrapper {
            position: relative;
            padding: 20px;
            background: #fdfcff;
            height: 220px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .seminovo-img {
            max-width: 100%;
            max-height: 100%;
            object-fit: contain;
        }

        .seminovo-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--dourado);
            color: var(--roxo-escuro);
            font-weight: 800;
            font-size: 11px;
            padding: 5px 12px;
            border-radius: 20px;
            text-transform: uppercase;
        }

        .seminovo-info {
            padding: 25px;
            display: flex;
            flex-direction: column;
            flex-grow: 1;
        }

        .seminovo-brand {
            font-size: 12px;
            color: var(--cinza);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .seminovo-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--roxo-escuro);
            margin: 5px 0 15px;
        }

        .seminovo-specs {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #666;
            margin-bottom: 20px;
            border-bottom: 1px dashed rgba(0, 0, 0, 0.08);
            padding-bottom: 15px;
        }

        .spec-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .seminovo-price-box {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .seminovo-price {
            font-size: 24px;
            font-weight: 800;
            color: var(--roxo-medio);
        }

        .seminovo-price span {
            font-size: 14px;
            color: #777;
            font-weight: normal;
        }

        .btn-comprar {
            background: var(--dourado);
            color: var(--roxo-escuro);
            padding: 10px 20px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            font-size: 13px;
            transition: all 0.3s ease;
        }

        .btn-comprar:hover {
            background: var(--roxo-escuro);
            color: var(--dourado);
        }

        .contato-compra {
            background: #f6efff;
            border-radius: 24px;
            padding: 60px 40px;
            margin-top: 80px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 50px;
            align-items: center;
            border: 1px solid rgba(106, 13, 173, 0.1);
        }

        .contato-info h2 {
            font-size: 36px;
            color: var(--roxo-escuro);
            font-weight: 800;
            margin-bottom: 20px;
        }

        .contato-info p {
            color: #555;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .contato-cards {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .contato-card {
            background: #fff;
            padding: 15px 20px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.02);
        }

        .contato-icon {
            font-size: 24px;
        }

        .contato-details strong {
            display: block;
            font-size: 14px;
            color: var(--roxo-escuro);
        }

        .contato-details span {
            font-size: 13px;
            color: #777;
        }

        .contato-form {
            background: #fff;
            padding: 40px;
            border-radius: 16px;
            box-shadow: 0 15px 40px rgba(106, 13, 173, 0.08);
        }

        .contato-form h3 {
            font-size: 22px;
            color: var(--roxo-escuro);
            font-weight: 700;
            margin-bottom: 25px;
        }

        .form-grid {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .form-input {
            width: 100%;
            padding: 12px 16px;
            border: 1.5px solid #eee;
            border-radius: 8px;
            font-family: inherit;
            font-size: 14px;
            color: var(--roxo-escuro);
            outline: none;
            transition: border-color 0.3s;
        }

        .form-input:focus {
            border-color: var(--roxo-medio);
        }

        .btn-enviar-contato {
            background: linear-gradient(135deg, #7d12de, #5b00a5);
            color: #fff;
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-weight: 700;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-enviar-contato:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(106, 13, 173, 0.3);
        }

        @media (max-width: 900px) {
            .contato-compra {
                grid-template-columns: 1fr;
            }

            .seminovos-hero-content h1 {
                font-size: 36px;
            }
        }
    </style>
</head>

<body>
    <?php require_once __DIR__ . '/../app/View/components/header.php'; ?>
    <section class="seminovos-hero">
        <div class="seminovos-hero-content">
            <h1>Máquinas Extraordinárias Prontas para a <span>Sua Garagem</span></h1>
            <p>A frota de seminovos da DriverLux passa por um rigoroso processo de certificação para garantir que você
                leve apenas a excelência máxima.</p>
        </div>
    </section>
    <div class="seminovos-container">
        <div class="filter-nav">
            <button class="filter-btn active" onclick="filtrarMarca('todos')">Todos</button>
            <button class="filter-btn" onclick="filtrarMarca('porsche')">Porsche</button>
            <button class="filter-btn" onclick="filtrarMarca('bmw')">BMW</button>
            <button class="filter-btn" onclick="filtrarMarca('mercedes')">Mercedes</button>
            <button class="filter-btn" onclick="filtrarMarca('audi')">Audi</button>
        </div>
        <div class="seminovos-grid">
            <!-- Porsche 911 -->
            <article class="seminovo-card" data-marca="porsche">
                <div class="seminovo-img-wrapper">
                    <span class="seminovo-badge">Certificado</span>
                    <img class="seminovo-img" src="/DriverLux/public/assets/img/bmw-m4.png" alt="Porsche 911 Carrera S"
                        style="filter: hue-rotate(120deg)">
                </div>
                <div class="seminovo-info">
                    <span class="seminovo-brand">Porsche</span>
                    <h3 class="seminovo-title">911 Carrera S</h3>
                    <div class="seminovo-specs">
                        <div class="spec-item">📅 <span>2022</span></div>
                        <div class="spec-item">🛣️ <span>12.000 km</span></div>
                        <div class="spec-item">⚙️ <span>PDK</span></div>
                    </div>
                    <div class="seminovo-price-box">
                        <div class="seminovo-price"><span>R$</span> 890.000</div>
                        <a href="#interesse" class="btn-comprar"
                            onclick="preencherInteresse('Porsche 911 Carrera S')">Tenho Interesse</a>
                    </div>
                </div>
            </article>

            <!-- Mercedes C63s -->
            <article class="seminovo-card" data-marca="mercedes">
                <div class="seminovo-img-wrapper">
                    <span class="seminovo-badge">Garantia Fábrica</span>
                    <img src="/DriverLux/public/assets/img/mercedes.jpg" alt="Mercedes AMG GT"
                        style="width:280px;height:220px;object-fit:contain;display:block;margin:25px auto 0 auto;" />
                </div>
                <div class="seminovo-info">
                    <span class="seminovo-brand">Mercedes</span>
                    <h3 class="seminovo-title">AMG C63s Coupé</h3>
                    <div class="seminovo-specs">
                        <div class="spec-item">📅 <span>2021</span></div>
                        <div class="spec-item">🛣️ <span>18.000 km</span></div>
                        <div class="spec-item">⚙️ <span>AMG SPEEDSHIFT</span></div>
                    </div>
                    <div class="seminovo-price-box">
                        <div class="seminovo-price"><span>R$</span> 580.000</div>
                        <a href="#interesse" class="btn-comprar" onclick="preencherInteresse('Mercedes AMG C63s')">Tenho
                            Interesse</a>
                    </div>
                </div>
            </article>
            <!-- Audi RS5 -->
            <article class="seminovo-card" data-marca="audi">
                <div class="seminovo-img-wrapper">
                    <span class="seminovo-badge">Exclusivo</span>
                    <img src="/DriverLux/public/assets/img/audi-car.png" alt="Audi RS7"
                        style="width:280px;height:220px;object-fit:contain;display:block;margin:25px auto 0 auto;" />
                </div>
                <div class="seminovo-info">
                    <span class="seminovo-brand">Audi</span>
                    <h3 class="seminovo-title">RS5 Sportback</h3>
                    <div class="seminovo-specs">
                        <div class="spec-item">📅 <span>2022</span></div>
                        <div class="spec-item">🛣️ <span>10.500 km</span></div>
                        <div class="spec-item">⚙️ <span>Tiptronic</span></div>
                    </div>
                    <div class="seminovo-price-box">
                        <div class="seminovo-price"><span>R$</span> 610.000</div>
                        <a href="#interesse" class="btn-comprar"
                            onclick="preencherInteresse('Audi RS5 Sportback')">Tenho Interesse</a>
                    </div>
                </div>
            </article>
        </div>
        <!-- Seção de Contato -->
        <div class="contato-compra" id="interesse">
            <div class="contato-info">
                <h2>Gostaria de Obter Mais Informações?</h2>
                <p>Nossa equipe de consultoria de vendas DriverLux está à sua inteira disposição para agendar test
                    drives, simulações financeiras ou detalhar o histórico técnico de cada veículo.</p>
                <div class="contato-cards">
                    <div class="contato-card">
                        <div class="contato-icon">📞</div>
                        <div class="contato-details">
                            <strong>Telefone VIP</strong>
                            <span>(63) 99999-5555</span>
                        </div>
                    </div>
                    <div class="contato-card">
                        <div class="contato-icon">✉️</div>
                        <div class="contato-details">
                            <strong>E-mail de Vendas</strong>
                            <span>vendas@driverlux.com.br</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="contato-form">
                <h3>Fale com um Consultor</h3>
                <form onsubmit="enviarMensagem(event)">
                    <div class="form-grid">
                        <input class="form-input" id="nome-contato" type="text" placeholder="Nome Completo" required>
                        <input class="form-input" id="email-contato" type="email" placeholder="Seu Melhor E-mail"
                            required>
                        <input class="form-input" id="tel-contato" type="tel" placeholder="Telefone de Contato"
                            required>
                        <input class="form-input" id="veiculo-contato" type="text"
                            placeholder="Modelo do Carro de Interesse">
                        <textarea class="form-input" id="msg-contato" rows="4" placeholder="Sua mensagem ou proposta"
                            style="resize: none;"></textarea>
                        <button class="btn-enviar-contato" type="submit">Solicitar Atendimento</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php require_once __DIR__ . '/../app/View/components/footer.php'; ?>
    <script src="/DriverLux/public/assets/js/registro-login.js"></script>
    <script src="/DriverLux/public/assets/js/menu-usuario.js?v=3"></script>
    <script>
        function filtrarMarca(marca) {
            // Altera botão ativo
            document.querySelectorAll('.filter-btn').forEach(btn => {
                btn.classList.remove('active');
                if (btn.textContent.toLowerCase() === marca) {
                    btn.classList.add('active');
                } else if (marca === 'todos' && btn.textContent.toLowerCase() === 'todos') {
                    btn.classList.add('active');
                }
            });
            // Filtra cards
            document.querySelectorAll('.seminovo-card').forEach(card => {
                if (marca === 'todos' || card.dataset.marca === marca) {
                    card.style.display = 'flex';
                } else {
                    card.style.display = 'none';
                }
            });
        }
        function preencherInteresse(modelo) {
            const input = document.getElementById('veiculo-contato');
            if (input) {
                input.value = modelo;
            }
        }
        function enviarMensagem(event) {
            event.preventDefault();
            alert('Obrigado! Sua solicitação de atendimento VIP foi registrada. Um consultor entrará em contato em breve.');
            event.target.reset();
        }
    </script>
</body>

</html>