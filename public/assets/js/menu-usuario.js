// public/assets/js/menu-usuario.js

document.addEventListener('DOMContentLoaded', async () => {
  // ==========================================================================
  // 1. VERIFICAÇÃO DE SESSÃO GLOBAL
  // ==========================================================================
  const btnLogin = document.getElementById('btn-login-trigger');
  const btnMenuUsuario = document.getElementById('btn-menu-usuario');
  const nomeUsuario = document.getElementById('nome-usuario');
  const menuUsuario = document.getElementById('menu-usuario');
  const btnSair = document.getElementById('btn-sair');
  
  let usuarioLogado = null;

  // -- A. Abrir o modal de login quando clicar em "LOGIN" (Com a limpeza integrada!)
  const modalAuth = document.getElementById('modal-auth');
  if (btnLogin && modalAuth) {
    btnLogin.addEventListener('click', (e) => {
      e.preventDefault(); // Evita qualquer comportamento padrão do link

      // 1. Acessa o contêiner interno do Web Component e abre o modal
      const container = modalAuth.shadowRoot ? modalAuth.shadowRoot.getElementById('auth-container') : null;
      if (container) {
        container.classList.remove('hidden');
      }

      // 2. Executa a limpeza para que os dados e erros antigos sumam ao abrir
      if (typeof modalAuth.limparFormularios === 'function') {
        modalAuth.limparFormularios();
      }
    });
  }

  try {
    const res = await fetch('/DriverLux/public/api/usuarios/me');
    const data = await res.json();
    
    if (data.logado && data.usuario) {
      usuarioLogado = data.usuario;

      // Atualiza o menu superior com o nome do usuário
      if (nomeUsuario) {
        const primeiroNome = data.usuario.nome.split(' ')[0];
        nomeUsuario.textContent = primeiroNome;
      }
      
      // Alterna a visibilidade dos botões
      if (btnLogin) btnLogin.classList.add('esconder');
      if (btnMenuUsuario) btnMenuUsuario.classList.remove('esconder');

      // Se for admin, adiciona a opção "Administração" no menu suspenso do usuário
      if (data.usuario.perfil === 'administrador' || data.usuario.perfil === 'admin') {
        if (menuUsuario && !document.getElementById('lnk-admin')) {
          const lnkAdmin = document.createElement('a');
          lnkAdmin.href = '/DriverLux/app/View/painel-admin/admin.php';
          lnkAdmin.id = 'lnk-admin';
          lnkAdmin.textContent = 'Administração';
          
          if (btnSair) {
            menuUsuario.insertBefore(lnkAdmin, btnSair);
          } else {
            menuUsuario.appendChild(lnkAdmin);
          }
        }
      }
    }
  } catch (e) {
    console.error("Erro ao verificar sessão do usuário:", e);
  }

  // Intercepta o link "GESTÃO DE FROTAS" na navbar
  const linkGestao = Array.from(document.querySelectorAll('nav a')).find(a => a.textContent.trim() === 'GESTÃO DE FROTAS');
  if (linkGestao) {
    linkGestao.addEventListener('click', (e) => {
      e.preventDefault();
      if (usuarioLogado && (usuarioLogado.perfil === 'administrador' || usuarioLogado.perfil === 'admin')) {
        window.location.href = '/DriverLux/app/View/painel-admin/admin.php';
      } else if (usuarioLogado) {
        alert('Acesso restrito a administradores.');
      } else {
        // Abre o modal de login se não estiver logado
        if (modalAuth) {
          const container = modalAuth.shadowRoot ? modalAuth.shadowRoot.getElementById('auth-container') : null;
          if (container) container.classList.remove('hidden');
        }
      }
    });
  }

  // ==========================================================================
  // 2. COMPORTAMENTO DO MENU SUSPENSO
  // ==========================================================================
  if (btnMenuUsuario && menuUsuario) {
    btnMenuUsuario.addEventListener('click', (e) => {
      e.stopPropagation();
      menuUsuario.classList.toggle('esconder');
      btnMenuUsuario.classList.toggle('ativo');
    });

    document.addEventListener('click', (e) => {
      if (!btnMenuUsuario.contains(e.target) && !menuUsuario.contains(e.target)) {
        menuUsuario.classList.add('esconder');
        btnMenuUsuario.classList.remove('ativo');
      }
    });
  }

  // ==========================================================================
  // 3. LOGOUT (SAIR)
  // ==========================================================================
  if (btnSair) {
    btnSair.addEventListener('click', async (e) => {
      e.preventDefault();
      try {
        const resLogout = await fetch('/DriverLux/public/api/usuarios/logout', { method: 'POST' });
        if (resLogout.ok) {
          window.location.href = '/DriverLux/public/'; // Redireciona para a home ao deslogar
        }
      } catch (error) {
        console.error("Erro ao tentar sair:", error);
      }
    });
  }

  // ==========================================================================
  // 4. LÓGICA DA BUSCA (HOME) - AJUSTADA E CORRIGIDA
  // ==========================================================================
  const btnBuscar = document.getElementById('btn-buscar-disponibilidade');
  if (btnBuscar) {
    btnBuscar.addEventListener('click', () => {
      const localRet = document.getElementById('local-retirada')?.value.trim();
      const localDev = document.getElementById('local-devolucao')?.value.trim();
      const dataRet = document.getElementById('data-retirada')?.value;
      const horaRet = document.getElementById('hora-retirada')?.value;
      const dataDev = document.getElementById('data-devolucao')?.value;
      const horaDev = document.getElementById('hora-devolucao')?.value;
      const cupom = document.getElementById('cupom-home')?.value.trim();

      if (!localRet || !localDev || !dataRet || !horaRet || !dataDev || !horaDev) {
        alert('Por favor, preencha todos os campos do formulário (Locais, Datas e Horários de Retirada e Devolução).');
        return;
      }

      // CORREÇÃO: Força a criação da data no contexto LOCAL do navegador
      const dtRet = new Date(`${dataRet}T${horaRet}:00`);
      const dtDev = new Date(`${dataDev}T${horaDev}:00`);
      
      // Dá uma tolerância de 5 minutos para o passado, evitando bugs de segundos decorridos
      const agoraComTolerancia = new Date();
      agoraComTolerancia.setMinutes(agoraComTolerancia.getMinutes() - 5);

      // Validação de Data/Hora de Retirada
      if (dtRet < agoraComTolerancia) {
        alert('A data e hora de retirada não podem ser no passado.');
        return;
      }

      // Validação de Data/Hora de Devolução
      if (dtDev <= dtRet) {
        alert('A data e hora de devolução devem ser posteriores à data e hora de retirada.');
        return;
      }

      const dadosReserva = {
        local_retirada: localRet,
        local_devolucao: localDev,
        data_retirada: dataRet,
        hora_retirada: horaRet,
        data_devolucao: dataDev,
        hora_devolucao: horaDev,
        cupom: cupom || ''
      };

      sessionStorage.setItem('dados_reserva', JSON.stringify(dadosReserva));
      window.location.href = '/DriverLux/app/View/fluxo-reserva/veiculos.php';
    });
  }

  // Controle do dropdown de Local de Retirada
  const inputLocal = document.getElementById('local-retirada');
  const listaLocais = document.getElementById('lista-locais');
  const opcoesLocais = document.querySelectorAll('.opcao-local');

  if (inputLocal && listaLocais) {
    inputLocal.addEventListener('focus', () => listaLocais.classList.remove('esconder'));
    inputLocal.addEventListener('input', () => listaLocais.classList.remove('esconder'));

    opcoesLocais.forEach((opcao) => {
      opcao.addEventListener('click', () => {
        inputLocal.value = opcao.dataset.local;
        listaLocais.classList.add('esconder');
      });
    });

    document.addEventListener('click', (e) => {
      if (!inputLocal.contains(e.target) && !listaLocais.contains(e.target)) {
        listaLocais.classList.add('esconder');
      }
    });
  }

  // Controle do dropdown de Local de Devolução
  const inputLocalDevolucao = document.getElementById('local-devolucao');
  const listaLocaisDevolucao = document.getElementById('lista-locais-devolucao');
  const opcoesLocaisDevolucao = document.querySelectorAll('.opcao-local-devolucao');

  if (inputLocalDevolucao && listaLocaisDevolucao) {
    inputLocalDevolucao.addEventListener('focus', () => listaLocaisDevolucao.classList.remove('esconder'));
    inputLocalDevolucao.addEventListener('input', () => listaLocaisDevolucao.classList.remove('esconder'));

    opcoesLocaisDevolucao.forEach((opcao) => {
      opcao.addEventListener('click', () => {
        inputLocalDevolucao.value = opcao.dataset.local;
        listaLocaisDevolucao.classList.add('esconder');
      });
    });

    document.addEventListener('click', (e) => {
      if (!inputLocalDevolucao.contains(e.target) && !listaLocaisDevolucao.contains(e.target)) {
        listaLocaisDevolucao.classList.add('esconder');
      }
    });
  }
});