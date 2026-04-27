# 🚀 Sprint 01: Fundações, Banco de Dados e API Base

## 📅 Período
- **Início:** 06/04/2026
- **Fim:** 15/04/2026

## 🎯 Valor da Sprint
Estruturar a base do projeto através da prototipação visual, modelagem de dados e criação do núcleo da API. Entregar as funcionalidades de backend que permitam o gerenciamento básico (CRUD) de **usuários** e **veículos**, garantindo a persistência das informações no banco de dados.

## 📋 Quadro de Tarefas (Tasks)

| Task | Responsável | Ref. Requisito |
| :--- | :--- | :--- |
| **Prototipação de telas - FIGMA** | Equipe | US02 / RNF04 |
| **Criação do DATABASE** | Ricardo Lopes | Geral |
| **Desenvolvimento da API (CRUDs Base)** | Ricardo Lopes | RF01 / RF08 / US04 / US13 |


---

## ✅ Critérios de Aceite
- O Banco de Dados está modelado, criado e conectando corretamente com a aplicação.

- É possível cadastrar um usuário via API e os dados aparecem corretamente no banco.

- É possível cadastrar e listar os veículos via API.

- O sistema impede o cadastro de usuários com e-mails duplicados (RNF08).

- O sistema impede o cadastro de veículos com placas duplicadas (RNF07).

- O protótipo do Figma reflete o catálogo e a estrutura inicial para desenvolvimento front-end.

---

### 📦 Entregáveis da Sprint:
- Script SQL de criação das tabelas (v1.0).
- Endpoints de API para: `Usuários`, `Veículos`, `Categorias`, `Proteções`, `KM` e `Cupons`.
- **Dívida Técnica:** A prototipação no Figma foi iniciada, mas o refinamento das telas de "Pagamento" e "Dashboard Admin" foi movido para a Sprint 02.
---

## 📝 Notas
- **Foco de Segurança:** A API foi construída com validação de dados, mas não incluiremos Autenticação, Login e Sessões de Usuário (RF02 / US05) nesta sprint. As requisições ainda não exigem token/sessão para serem testadas.

- **Senhas:** Mesmo sem o login ativo, a API de cadastro de usuários (RF01) já deve criptografar a senha no banco de dados (ex: `password_verify`).

- **Planejamento:** A próxima sprint abordará as travas de segurança (RBAC) e o fluxo do usuário logado.