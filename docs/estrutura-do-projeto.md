## 📁 Estrutura do Projeto

```text
DRIVERLUX/
│
├── app/                              # Core da aplicação (MVC)
│   ├── Controller/                   # Controladores (recebem requisições e coordenam ações)
│   ├── Model/                        # Modelos e acesso aos dados
│   ├── Service/                      # Regras de negócio e serviços
│   └── View/                         # Interfaces e páginas da aplicação
│
├── config/                           # Configurações do sistema
│   └── db_connect.php                # Conexão com o banco de dados
│
├── docs/                             # Documentação do projeto
│   ├── database/                     # Modelagem e scripts do banco
│   └── sprints/                      # Planejamento e histórico de sprints
│
├── public/                           # Arquivos públicos acessíveis pelo navegador
│   ├── assets/                       # CSS, JavaScript, imagens e fontes
│   ├── usuarios/                     # Uploads e arquivos dos usuários
│   ├── .htaccess                     # Regras de URL amigável
│   └── index.php                     # Front Controller da aplicação
│
├── .htaccess                         # Configurações do Apache
├── index.html                        # Página inicial estática
├── LICENSE                           # Licença do projeto
└── README.md                         # Documentação principal
```
---
## 🔁 Como ocorre o fluxo MVC

```text
Usuário
   │
   ▼
public/index.php
   │
   ▼
Controller
   │
   ├──► Service
   │        │
   │        ▼
   │      Model
   │        │
   │        ▼
   │    Banco de Dados
   │
   ▼
View
   │
   ▼
Resposta ao Usuário
```