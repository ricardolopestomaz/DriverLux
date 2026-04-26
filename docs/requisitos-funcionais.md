# 📚 Requisitos Funcionais 

| **Id**       | **RF01** |
|--------------|----------|
| **Nome**     | Cadastro de Usuários
| **Descição** | O sistema deve permitir que visitantes se cadastrem informando nome, e-mail e senha.
| **Tarefa**   | - Criar formulário de login <br> - Validar campos (nome, e-mail, senha) <br> - Salvar usuário no banco de dados <br> - Salvar usuário no banco de dados <br> - Exibir mensagem de erro de forma visual na interface, em caso de falha 

<br>

| **Id**       | **RF02** |
|--------------|----------|
| **Nome**     | Autenticação e Login
| **Descição** | O sistema deve permitir que usuários cadastrados realizem login informando e-mail e senha, garantindo acesso às funcionalidades restritas.
| **Tarefa**   | - Criar formulário de login <br> - Implementar sessão de usuário <br> - Criar função de logout <br> - Criar o visual da tela de login com formulário que envia dados via método **POST** 

<br>

| **Id**       | **RF03** |
|--------------|----------|
| **Nome**     | Catálogo de Veículos
| **Descição** | O sistema deve exibir um catálogo de veículos disponíveis para locação, com informações como modelo, marca, preço e disponibilidade.
| **Tarefa**   | - Criar listagem de veículos <br> - Exibir detalhes do veículo <br> - Implementar filtro (categoria)

<br>

| **Id**       | **RF04** |
|--------------|----------|
| **Nome**     | Fluxo de Reserva
| **Descição** | O sistema deve guiar o cliente pelo fluxo: seleção da data/local ⇨ veículo ⇨ tarifa e adicionais ⇨ dados pessoais ⇨ pagamento ⇨ confirmação.
| **Tarefa**   | - Criar tela de seleção de data (retirada/devolução) <br> - Criar seleção de local (se aplicável) <br> - Listar veículos disponíveis conforme data <br> - Permitir escolha do veículo <br> - Exibir preço da diária e cálculo total <br> - Criar formulário de dados pessoais <br> - Criar resumo da reserva  <br> - Integrar com pagamento <br> - Salvar no banco de dados <br> - Gerar confirmação da reserva 

<br>

| **Id**       | **RF05** |
|--------------|----------|
| **Nome**     | Cancelamento de Reserva
| **Descição** | O sistema deve permitir que o usuário cancele uma reserva previamente realizada, respeitando regras de prazo e possíveis taxas. 
| **Tarefa**   | - Criar opção de cancelamento <br> - Atualizar status da reserva

<br>

| **Id**       | **RF06** |
|--------------|----------|
| **Nome**     | Painel do Administrador
| **Descição** | O sistema deve disponibilizar um painel administrativo para gerenciamento geral da aplicação, incluindo usuários, veículos e reservas.
| **Tarefa**   | - Criar dashboard simplificada <br> - Criar gerenciamento de veículos <br> - Criar gerenciamento de usuários <br> - Visualizar todas as reservas <br> - Alterar status da reserva (confirmada, cancelada, concluída) 

<br>

| **Id**       | **RF07** |
|--------------|----------|
| **Nome**     | Pagamento Online 
| **Descição** | O sistema deve permitir que o usuário realize o pagamento da reserva de forma online, garantindo a confirmação após a transação.
| **Tarefa**   | - Criar tela de pagamento <br> - Validar transação <br> - Exibir confirmação de pagamento para o usuário 

<br>

| **Id**       | **RF08** |
|--------------|----------|
| **Nome**     | Cadastro de Veículos 
| **Descição** | O sistema deve permitir que o administrador cadastre novos veículos informando dados como marca, modelo, ano, placa, categoria, preço da diária e status de disponibilidade. 
| **Tarefa**   | - Criar formulário de cadastro de veículos <br> - Exibir mensagem de sucesso/erro na verificação <br> - Implementar upload de imagem do veículo <br> - Exibir mensagem de sucesso/erro 

<br>

| **Id**       | **RF09** |
|--------------|----------|
| **Nome**     | Busca e Filtros 
| **Descição** | O sistema deve permitir que o usuário pesquise e filtre veículos disponíveis com base em critérios como nome, categoria, preço e características.  
| **Tarefa**   | - Criar campo de busca por nome/modelo do veículo <br> - Implementar filtro por faixa de preço <br> - Criar filtro por marca <br> - Exibir mensagem quando não houver resultados

<br>

| **Id**       | **RF10** |
|--------------|----------|
| **Nome**     | Controle de Disponibilidade 
| **Descição** | O sistema deve garantir que um veículo só possa ser reservado se estiver disponível no período selecionado, evitando conflitos de datas entre reservas.
| **Tarefa**   | - Verificar disponibilidade do veículo antes de confirmar a reserva <br> - Bloquear reserva em caso de conflito de datas <br> - Permitir reserva apenas se o veículo estiver livre no período <br> - Exibir mensagem de erro em caso de indisponibilidade 

<br>

| **Id**       | **RF11** |
|--------------|----------|
| **Nome**     | Minhas Reservas 
| **Descição** | O sistema deve permitir que o usuário visualize suas reservas realizadas.
| **Tarefa**   | - Mostrar status da reserva <br> - Permitir acesso ao cancelamento <br> - Exibir dados (veículo, datas, valor)
