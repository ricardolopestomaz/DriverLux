<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Driverlux - Locação de Veículos</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>
    <h1>Bem-vindo à Driverlux!</h1>
    <p>Alugue os melhores carros premium do mercado.</p>
    
    <button id="btnUsuarios">Ver lista de Usuários no Console</button>

    <script>
        // Exemplo de como o site se comunica com a SUA API no mesmo servidor
        document.getElementById('btnUsuarios').addEventListener('click', function() {
            fetch('/api/usuarios')
                .then(response => response.json())
                .then(data => console.log(data))
                .catch(error => console.error('Erro:', error));
        });
    </script>
</body>
</html>