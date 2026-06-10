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
    <title>LUX - Carro por Assinatura - DriverLux</title>
    <link rel="shortcut icon" href="/DriverLux/public/assets/img/corrida.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/style.css?v=3">
    <style>
        .assinatura-hero {
            min-height: 450px;
            background: linear-gradient(135deg, rgba(68, 12, 119, 0.92) 0%, rgba(30, 0, 60, 0.9) 100%), 
                        url('/DriverLux/public/assets/img/hero.png') center center / cover no-repeat;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            color: #fff;
            padding: 120px 20px 70px;
        }
        .assinatura-hero h1 {
            font-size: 52px;
            font-weight: 950;
            margin-bottom: 20px;
            letter-spacing: 1.5px;
            line-height: 1.1;
        }
        .assinatura-hero h1 span {
            color: var(--dourado);
        }
        .assinatura-hero p {
            font-size: 19px;
            max-width: 800px;
            margin: 0 auto 30px;
            opacity: 0.95;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .sessao-titulo {
            text-align: center;
            margin-bottom: 60px;
        }
        .sessao-titulo h2 {
            font-size: 36px;
            color: var(--roxo-escuro);
            font-weight: 800;
        }
        .sessao-titulo h2 span {
            color: var(--roxo-medio);
        }
        .sessao-titulo p {
            font-size: 16px;
            color: #666;
            margin-top: 10px;
        }
        
        /* Como funciona */
        .como-funciona {
            padding: 80px 0;
            background: #fff;
        }
        .passos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
            margin-top: 40px;
        }
        .passo-card {
            background: #fdfcff;
            border: 1px solid var(--borda-card);
            border-radius: 16px;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            transition: all 0.3s;
        }
        .passo-card:hover {
            transform: translateY(-5px);
            border-color: var(--roxo-medio);
            box-shadow: 0 10px 30px rgba(106,13,173,0.05);
        }
        .passo-num {
            width: 50px;
            height: 50px;
            background: var(--roxo-medio);
            color: #fff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 20px;
            margin: 0 auto 20px;
            box-shadow: 0 4px 10px rgba(106,13,173,0.25);
        }
        .passo-card h3 {
            font-size: 20px;
            color: var(--roxo-escuro);
            font-weight: 700;
            margin-bottom: 15px;
        }
        .passo-card p {
            color: #555;
            font-size: 14px;
            line-height: 1.6;
        }
        /* Vantagens */
        .vantagens {
            padding: 80px 0;
            background: #f9f6fc;
        }
        .vantagens-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
        }
        .vantagem-item {
            background: #fff;
            padding: 30px 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.02);
            border: 1px solid rgba(106, 13, 173, 0.05);
        }
        .vantagem-icon {
            font-size: 32px;
            margin-bottom: 15px;
        }
        .vantagem-item h4 {
            font-size: 16px;
            font-weight: 700;
            color: var(--roxo-escuro);
            margin-bottom: 10px;
        }
        .vantagem-item p {
            font-size: 13px;
            color: #666;
            line-height: 1.5;
        }
        /* Planos */
        .planos {
            padding: 80px 0;
            background: #fff;
        }
        .planos-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 30px;
        }
        .plano-card {
            background: #fff;
            border-radius: 20px;
            border: 1.5px solid var(--borda-card);
            padding: 45px 35px;
            display: flex;
            flex-direction: column;
            position: relative;
            transition: all 0.3s;
        }
        .plano-card.destaque {
            border-color: var(--roxo-medio);
            box-shadow: 0 15px 40px rgba(106,13,173,0.1);
        }
        .plano-card.destaque .badge-plano {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--roxo-medio);
            color: #fff;
            font-weight: 800;
            font-size: 11px;
            padding: 6px 18px;
            border-radius: 30px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }
        .plano-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(106,13,173,0.12);
        }
        .plano-nome {
            font-size: 22px;
            font-weight: 800;
            color: var(--roxo-escuro);
            margin-bottom: 15px;
        }
        .plano-preco-box {
            margin-bottom: 30px;
        }
        .plano-preco {
            font-size: 42px;
            font-weight: 900;
            color: var(--roxo-medio);
        }
        .plano-preco span {
            font-size: 14px;
            color: #777;
            font-weight: normal;
        }
        .plano-exemplos {
            font-size: 13px;
            color: #555;
            background: rgba(106, 13, 173, 0.05);
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            font-weight: 600;
        }
        .plano-caracteristicas {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-bottom: 40px;
            font-size: 14px;
            color: #444;
        }
        .plano-caracteristicas li::before {
            content: "✓  ";
            color: var(--sucesso);
            font-weight: bold;
        }
        .btn-assinar {
            display: block;
            text-align: center;
            background: var(--roxo-escuro);
            color: #fff;
            padding: 14px;
            border-radius: 8px;
            font-weight: 700;
            text-decoration: none;
            transition: all 0.3s;
            margin-top: auto;
        }
        .plano-card.destaque .btn-assinar {
            background: linear-gradient(135deg, #7d12de, #5b00a5);
        }
        .btn-assinar:hover {
            opacity: 0.9;
            transform: translateY(-2px);
        }
        /* FAQ */
        .faq {
            padding: 80px 0;
            background: #fdfcff;
        }
        .faq-accordion {
            max-width: 800px;
            margin: 40px auto 0;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        .faq-item {
            background: #fff;
            border: 1px solid var(--borda-card);
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.01);
        }
        .faq-pergunta {
            padding: 20px 25px;
            font-weight: 700;
            color: var(--roxo-escuro);
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s;
        }
        .faq-pergunta:hover {
            background: #fdfa1f9;
        }
        .faq-resposta {
            padding: 0 25px 20px;
            color: #555;
            font-size: 14px;
            line-height: 1.6;
            display: none;
        }
        /* Formulario de Contato */
        .contato-assinatura {
            background: linear-gradient(135deg, #3b0567 0%, #1a0033 100%);
            color: #fff;
            padding: 80px 0;
        }
        .contato-assinatura-wrap {
            max-width: 600px;
            margin: 0 auto;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(10px);
            padding: 50px;
            border-radius: 20px;
            border: 1px solid rgba(255,255,255,0.1);
            box-shadow: 0 20px 50px rgba(0,0,0,0.3);
        }
        .contato-assinatura-wrap h3 {
            font-size: 26px;
            text-align: center;
            font-weight: 800;
            margin-bottom: 10px;
        }
        .contato-assinatura-wrap p {
            text-align: center;
            color: rgba(255,255,255,0.7);
            font-size: 14px;
            margin-bottom: 30px;
        }
        .btn-enviar-assinatura {
            background: var(--dourado);
            color: var(--roxo-escuro);
            border: none;
            padding: 14px;
            border-radius: 8px;
            font-weight: 800;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 15px;
            width: 100%;
        }
        .btn-enviar-assinatura:hover {
            background: #fff;
            color: var(--roxo-escuro);
            transform: translateY(-2px);
        }
        @media (max-width: 900px) {
            .passos-grid, .planos-grid {
                grid-template-columns: 1fr;
            }
            .vantagens-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            .assinatura-hero h1 {
                font-size: 38px;
            }
        }
    </style>
</head>
<body>
<?php require_once __DIR__ . '/../app/View/components/header.php'; ?>
<section class="assinatura-hero">
    <div>
        <h1>LUX - Carro por Assinatura</h1>
        <p>A exclusividade de dirigir um veículo premium zero quilômetro sem se preocupar com desvalorização, burocracia, impostos ou manutenções. Assine mensalmente a DriverLux.</p>
        <a href="#planos" class="btn roxo">CONHECER PLANOS</a>
    </div>
</section>
<!-- Como funciona -->
<section class="como-funciona">
    <div class="container">
        <div class="sessao-titulo">
            <h2>Como Funciona a <span>Assinatura LUX</span>?</h2>
            <p>O jeito mais moderno e inteligente de usufruir de carros extraordinários.</p>
        </div>
        <div class="passos-grid">
            <div class="passo-card">
                <div class="passo-num">1</div>
                <h3>Escolha o Modelo</h3>
                <p>Navegue pelo nosso portfólio e selecione o veículo zero quilômetro dos seus sonhos, personalizado do seu jeito.</p>
            </div>
            <div class="passo-card">
                <div class="passo-num">2</div>
                <h3>Defina as Regras</h3>
                <p>Escolha o período ideal de duração (12, 24 ou 36 meses) e o pacote de quilometragem mensal que melhor se adequa à sua rotina.</p>
            </div>
            <div class="passo-card">
                <div class="passo-num">3</div>
                <h3>Guie o Extraordinário</h3>
                <p>Retire o carro emplacado, assegurado e pronto para rodar. Nós cuidamos de todas as revisões periódicas e impostos.</p>
            </div>
        </div>
    </div>
</section>
<!-- Vantagens -->
<section class="vantagens">
    <div class="container">
        <div class="sessao-titulo">
            <h2>Por que Assinar em vez de <span>Comprar</span>?</h2>
            <p>Economize tempo, evite burocracias e otimize seus investimentos.</p>
        </div>
        <div class="vantagens-grid">
            <div class="vantagem-item">
                <div class="vantagem-icon">🛡️</div>
                <h4>Seguro Premium Total</h4>
                <p>Cobertura completa contra colisões, roubo, terceiros e assistência 24h VIP.</p>
            </div>
            <div class="vantagem-item">
                <div class="vantagem-icon">🔧</div>
                <h4>Manutenção Inclusa</h4>
                <p>Revisões preventivas e trocas de peças de desgaste natural garantidas.</p>
            </div>
            <div class="vantagem-item">
                <div class="vantagem-icon">💵</div>
                <h4>Zero IPVA e Licenciamento</h4>
                <p>Nenhuma taxa anual de documentação de veículos é de sua responsabilidade.</p>
            </div>
            <div class="vantagem-item">
                <div class="vantagem-icon">📈</div>
                <h4>Livre da Depreciação</h4>
                <p>A desvalorização natural do mercado automotivo não atinge o seu bolso.</p>
            </div>
        </div>
    </div>
</section>
<!-- Planos -->
<section class="planos" id="planos">
    <div class="container">
        <div class="sessao-titulo">
            <h2>Escolha a sua <span>Categoria LUX</span></h2>
            <p>Selecione o plano ideal para elevar seu estilo e experiência.</p>
        </div>
        <div class="planos-grid">
            <!-- Premium -->
            <div class="plano-card">
                <div class="plano-nome">LUX Premium</div>
                <div class="plano-preco-box">
                    <div class="plano-preco">R$ 8.900 <span>/mês</span></div>
                </div>
                <div class="plano-exemplos">
                    Modelos: BMW Série 3, Mercedes Classe C ou Audi A4
                </div>
                <ul class="plano-caracteristicas">
                    <li>Franquia de 1.000km/mês</li>
                    <li>Seguro VIP com franquia reduzida</li>
                    <li>Manutenção de frota oficial</li>
                    <li>IPVA e Emplacamento pagos</li>
                    <li>Carro Reserva em caso de reparos</li>
                </ul>
                <a href="#cadastro" class="btn-assinar" onclick="preencherPlano('LUX Premium - R$ 8.900/mês')">Solicitar Assinatura</a>
            </div>
            <!-- Super Esporte -->
            <div class="plano-card destaque">
                <div class="badge-plano">Mais Procurado</div>
                <div class="plano-nome">LUX Super Esporte</div>
                <div class="plano-preco-box">
                    <div class="plano-preco">R$ 14.500 <span>/mês</span></div>
                </div>
                <div class="plano-exemplos">
                    Modelos: Porsche Macan, BMW X5 ou Audi Q8
                </div>
                <ul class="plano-caracteristicas">
                    <li>Franquia de 1.500km/mês</li>
                    <li>Seguro Total Isento de Franquia</li>
                    <li>Assistência 24h Especializada VIP</li>
                    <li>Revisões inclusas em concessionária</li>
                    <li>Entrega personalizada em domicílio</li>
                </ul>
                <a href="#cadastro" class="btn-assinar" onclick="preencherPlano('LUX Super Esporte - R$ 14.500/mês')">Solicitar Assinatura</a>
            </div>
            <!-- Performance -->
            <div class="plano-card">
                <div class="plano-nome">LUX High Performance</div>
                <div class="plano-preco-box">
                    <div class="plano-preco">R$ 22.000 <span>/mês</span></div>
                </div>
                <div class="plano-exemplos">
                    Modelos: Porsche 911, Audi RS6 ou BMW M5
                </div>
                <ul class="plano-caracteristicas">
                    <li>Franquia de 2.000km/mês</li>
                    <li>Seguro Pista e Rodovias Premium</li>
                    <li>Revisões de alta performance</li>
                    <li>Suporte dedicado VIP Concierge</li>
                    <li>Convites para eventos DriverLux Track</li>
                </ul>
                <a href="#cadastro" class="btn-assinar" onclick="preencherPlano('LUX High Performance - R$ 22.000/mês')">Solicitar Assinatura</a>
            </div>
        </div>
    </div>
</section>
<!-- FAQ -->
<section class="faq">
    <div class="container">
        <div class="sessao-titulo">
            <h2>Perguntas <span>Frequentes</span></h2>
            <p>Esclareça suas principais dúvidas sobre o plano de assinatura DriverLux.</p>
        </div>
        <div class="faq-accordion">
            <div class="faq-item">
                <div class="faq-pergunta">Quem pode assinar o plano LUX? <span>▼</span></div>
                <div class="faq-resposta">Pessoas físicas ou jurídicas com CNH definitiva e válida há pelo menos 3 anos, sujeitas à aprovação de análise de crédito vip realizada pela nossa mesa financeira.</div>
            </div>
            <div class="faq-item">
                <div class="faq-pergunta">Qual o prazo mínimo do contrato de assinatura? <span>▼</span></div>
                <div class="faq-resposta">Os prazos padrão do contrato de assinatura LUX são de 12, 24 ou 36 meses. Quanto maior o prazo do contrato, menor a parcela mensal paga.</div>
            </div>
            <div class="faq-item">
                <div class="faq-pergunta">O carro reserva é de qual categoria? <span>▼</span></div>
                <div class="faq-resposta">O carro reserva disponibilizado será sempre um veículo equivalente de mesma categoria premium ou categoria superior, garantindo sua plena locomoção.</div>
            </div>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../app/View/components/footer.php'; ?>
<script src="/DriverLux/public/assets/js/registro-login.js"></script>
<script src="/DriverLux/public/assets/js/menu-usuario.js?v=3"></script>
<script>
    // Accordion FAQ
    document.querySelectorAll('.faq-pergunta').forEach(item => {
        item.addEventListener('click', () => {
            const resposta = item.nextElementSibling;
            const span = item.querySelector('span');
            if (resposta.style.display === 'block') {
                resposta.style.display = 'none';
                span.textContent = '▼';
            } else {
                resposta.style.display = 'block';
                span.textContent = '▲';
            }
        });
    });
    function preencherPlano(plano) {
        const input = document.getElementById('plano-ass');
        if (input) {
            input.value = plano;
        }
    }
    function enviarAssinatura(event) {
        event.preventDefault();
        alert('Obrigado! Sua solicitação de assinatura DriverLux foi recebida com sucesso. Nosso VIP Concierge retornará o contato em até 1 hora útil.');
        event.target.reset();
    }
</script>
</body>
</html>