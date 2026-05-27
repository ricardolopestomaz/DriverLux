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

  try {
    const res = await fetch('/DriverLux/public/api/usuarios/me');
    const data = await res.json();
    
    if (data.logado && data.usuario) {

      // Atualiza o menu superior com o nome do usuário
      if (nomeUsuario) {
        const primeiroNome = data.usuario.nome.split(' ')[0];
        nomeUsuario.textContent = primeiroNome;
      }
      
      // Alterna a visibilidade dos botões
      if (btnLogin) btnLogin.classList.add('esconder');
      if (btnMenuUsuario) btnMenuUsuario.classList.remove('esconder');
    }
  } catch (e) {
    console.error("Erro ao verificar sessão do usuário:", e);
  }

  // ==========================================================================
  // 2. COMPORTAMENTO DO MENU SUSPENSO
  // ==========================================================================
  if (btnMenuUsuario && menuUsuario) {
    btnMenuUsuario.addEventListener('click', (e) => {
      e.stopPropagation();
      menuUsuario.classList.toggle('esconder');
    });

    document.addEventListener('click', (e) => {
      if (!btnMenuUsuario.contains(e.target) && !menuUsuario.contains(e.target)) {
        menuUsuario.classList.add('esconder');
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
  // 4. LÓGICA DA BUSCA (HOME) - Mantida do seu arquivo original
  // ==========================================================================
  const btnBuscar = document.getElementById('btn-buscar-disponibilidade');
  if (btnBuscar) {
    btnBuscar.addEventListener('click', () => {
      const local = document.getElementById('local-retirada')?.value.trim();
      const data = document.getElementById('data-retirada')?.value;
      const hora = document.getElementById('hora-retirada')?.value;

      if (!local || !data || !hora) {
        alert('Preencha local, data e hora.');
        return;
      }

      const dadosReserva = {
        local_retirada: local,
        data_retirada: data,
        hora_retirada: hora
      };

      sessionStorage.setItem('dados_reserva', JSON.stringify(dadosReserva));
      window.location.href = '/DriverLux/app/View/fluxo-reserva/veiculos.php'; // MERDA PURA
    });
  }

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
  }
});