<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>DriverLux</title>

<link rel="stylesheet" href="assets/css/style.css">

<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap" rel="stylesheet">

</head>
<body>

<header class="header">
    <div class="logo">DRIVERLUX</div>

    <nav>
        <a href="#">Aluguel de carros</a>
        <a href="#">Gestão de frotas</a>
        <a href="#">Seminovos</a>
    </nav>

    <div class="auth">
        <registro-login modo="popover"></registro-login>
    </div>
</header>

<section class="hero">

    <div class="hero-text">
        <span class="sub">DIRIJA O</span>
        <h1>EXTRAORDINÁRIO</h1>

        <p>Aluguel de carros de luxo para quem exige mais do que o comum.</p>

        <div class="buttons">
            <button class="primary">Reservar agora</button>
            <button class="outline">Ver frota</button>
        </div>
    </div>

    <div class="hero-img">
        <img src="assets/img/fundo inicial.jpg">
    </div>

</section>

<section class="destaques">

    <h2>Carros em destaque</h2>

    <div class="cards">

        <div class="card">
            <img src="assets/img/bmw.jpg">
            <h3>BMW M4 COMPETITION</h3>
            <p>A partir de</p>
            <span>R$ 1.200/dia</span>
        </div>

        <div class="card">
            <img src="assets/img/AUDI RS7.jpg">
            <h3>AUDI RS7</h3>
            <p>A partir de</p>
            <span>R$ 1.500/dia</span>
        </div>

        <div class="card">
            <img src="assets/img/MERCEDES AMG GT.jpg">
            <h3>MERCEDES AMG GT</h3>
            <p>A partir de</p>
            <span>R$ 1.800/dia</span>
        </div>

    </div>

</section>

<script src="assets/js/registro-login.js"></script>

</body>
</html>