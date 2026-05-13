window.addEventListener('DOMContentLoaded', () => {

  const btnLogin = document.getElementById('btn-login-trigger');
  const modalAuth = document.getElementById('modal-auth');

  const btnMenuUsuario = document.getElementById('btn-menu-usuario');
  const menuUsuario = document.getElementById('menu-usuario');
  const nomeUsuario = document.getElementById('nome-usuario');
  const btnSair = document.getElementById('btn-sair');

  // LOGIN
  if (btnLogin && modalAuth) {

    btnLogin.addEventListener('click', () => {

      const container =
        modalAuth.shadowRoot?.getElementById('auth-container');

      if (container) {
        container.classList.remove('hidden');
      }

    });

  }

  // VERIFICAR USUÁRIO
  window.addEventListener('load', async () => {

    try {

      const resposta = await fetch(
        '/DriverLux/public/api/usuarios/me'
      );

      const dados = await resposta.json();

      if (dados.logado && dados.usuario) {

        const primeiroNome =
          dados.usuario.nome.split(' ')[0];

        nomeUsuario.textContent =
          `Olá, ${primeiroNome}`;

        btnMenuUsuario.classList.remove('esconder');

        if (btnLogin) {
          btnLogin.style.display = 'none';
        }

      }

    } catch (erro) {

      console.error(
        'Erro ao verificar usuário logado:',
        erro
      );

    }

  });

  // ABRIR MENU
  if (btnMenuUsuario && menuUsuario) {

    btnMenuUsuario.addEventListener('click', (evento) => {

      evento.stopPropagation();

      menuUsuario.classList.toggle('esconder');

    });

  }

  // FECHAR MENU
  document.addEventListener('click', (evento) => {

    if (
      btnMenuUsuario &&
      menuUsuario &&
      !btnMenuUsuario.contains(evento.target) &&
      !menuUsuario.contains(evento.target)
    ) {

      menuUsuario.classList.add('esconder');

    }

  });

  // LOGOUT
  if (btnSair) {

    btnSair.addEventListener('click', async (evento) => {

      evento.preventDefault();

      try {

        const resposta = await fetch(
          '/DriverLux/public/api/usuarios/logout',
          {
            method: 'POST'
          }
        );

        const dados = await resposta.json();

        if (dados.status === 'success') {

          window.location.href = '/DriverLux/';

        }

      } catch (erro) {

        console.error(
          'Erro ao sair:',
          erro
        );

      }

    });

  }

});