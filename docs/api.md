# 🏎️ DriverLux API

![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-00000F?style=for-the-badge&logo=mysql&logoColor=white)
![JSON](https://img.shields.io/badge/JSON-000000?style=for-the-badge&logo=json&logoColor=white)

DriverLux é uma API RESTful para gestão de frotas e locação de veículos. O sistema permite o gerenciamento completo de veículos, categorias, usuários, cupons de desconto e reservas.

---

## 🚀 Começando

### Pré-requisitos
* Servidor Local (XAMPP, WAMP ou Laragon)
* PHP 8.x ou superior
* Banco de Dados MySQL

### Instalação
1. Clone este repositório no seu diretório `htdocs` ou `www`.
2. Importe o arquivo SQL (se disponível) para o seu banco de dados MySQL.
3. Configure as credenciais do banco no arquivo de conexão da API.
4. Acesse via: `http://localhost/DriverLux/public/api/`

---

## 🧭 Endpoints Principais

A URL base da API é: `http://localhost/DriverLux/public/api/`

| Recurso | Endpoint | Método | Descrição |
| :--- | :--- | :--- | :--- |
| **Categorias** | `/categorias` | GET/POST | Gerencia tipos de veículos (Luxo, SUV, etc) |
| **Veículos** | `/veiculos` | GET/POST | Gerencia a frota de carros |
| **Usuários** | `/usuarios` | GET/POST | Cadastro de clientes e admins |
| **Cupons** | `/cupons` | GET/POST | Gestão de códigos promocionais |
| **KM** | `/km` | GET/POST | Configurações de limite de quilometragem |
| **Reservas** | `/reservas` | GET/POST | Fluxo de locação completo |

---

## 🛠️ Como Utilizar

### 1. Listando Dados (GET)
Basta realizar uma chamada simples para o endpoint desejado.
**Exemplo:** `GET /api/categorias`

**Resposta de sucesso:**
```json
{
  "status": "success",
  "total": 2,
  "data": [
    { "id": 1, "nome": "Sedan Executive" },
    { "id": 2, "nome": "SUV Premium" }
  ]
}
```

### 2. Cadastrando Dados (POST)
Para cadastrar novos registros, envie um JSON no corpo da requisição com o Header Content-Type: application/json.

**Exemplo de Payload (Cadastro de Veículo):**
```json
{
  "categoria_id": 1,
  "marca": "Chevrolet",
  "modelo": "Onix",
  "ano": 2023,
  "placa": "ABC-1234",
  "chassi": "9BG1234567890ABCD"
}
```
---

## 🐘 Integração com PHP (Server-to-Server)
Caso o seu site esteja processando o formulário via PHP, você deve utilizar o cURL para enviar os dados para a API. Abaixo, um exemplo de uma função reutilizável:
```php
<?php

/**
 * Função para enviar dados (POST) para a API DriverLux
 */
function enviarParaAPI($endpoint, $dados) {
    $url = "http://localhost/DriverLux/public/api/" . $endpoint;
    
    // Transforma o array PHP em JSON
    $payload = json_encode($dados);

    $ch = curl_init($url);
    
    // Configurações do cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Content-Length: ' . strlen($payload)
    ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [
        'status_code' => $httpCode,
        'response' => json_decode($result, true)
    ];
}

// --- EXEMPLO DE USO ---
$novaCategoria = [
    "nome" => "SUV Premium",
    "descricao" => "Carros grandes e confortáveis",
    "valor_base_diaria" => 250.00
];

$resposta = enviarParaAPI('categorias', $novaCategoria);

if ($resposta['status_code'] == 201 || $resposta['status_code'] == 200) {
    echo "Sucesso ao cadastrar!";
} else {
    echo "Erro: " . $resposta['response']['message'];
}
```

Exemplo de busca (GET) em PHP
Se precisar apenas listar as categorias no seu arquivo .php:
```php
<?php
$json = file_get_contents('http://localhost/DriverLux/public/api/categorias');
$dados = json_decode($json, true);

foreach ($dados['data'] as $categoria) {
    echo "<li>" . $categoria['nome'] . "</li>";
}
?>
```

---

## 🔒 Segurança e Regras de Negócio
Validação: A API valida campos obrigatórios e formatos de data/valor.<br>
Integridade: Veículos dependem de um categoria_id válido já existente no banco.<br>
Cupons: Verificação automática de validade e limite de usos.

