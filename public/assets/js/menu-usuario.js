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
          method: 'POST',
          credentials: 'include'
        }
      );

      const dados = await resposta.json();

      if (dados.status === 'success') {

        window.location.href =
          '/DriverLux/public/';

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

// ======================================
// BUSCAR DISPONIBILIDADE
// ======================================

const btnBuscarDisponibilidade =
  document.getElementById(
    'btn-buscar-disponibilidade'
  );

if (btnBuscarDisponibilidade) {

  btnBuscarDisponibilidade
    .addEventListener('click', async () => {

      const local =
        document.getElementById(
          'local-retirada'
        ).value.trim();

      const data =
        document.getElementById(
          'data-retirada'
        ).value;

      const hora =
        document.getElementById(
          'hora-retirada'
        ).value;

      // VALIDAÇÃO
      if (!local || !data || !hora) {

        alert(
          'Preencha localização, data e hora.'
        );

        return;

      }

      try {

        // EXEMPLO DE BUSCA
        const resposta = await fetch(
          `/DriverLux/public/api/veiculos`
        );

        const veiculos =
          await resposta.json();

        console.log(
          'Veículos encontrados:',
          veiculos
        );

        // REDIRECIONAR
        const params = new URLSearchParams({

          retirada: local,
          data: data,
          hora: hora

        });

        window.location.href =
          `/DriverLux/public/veiculos?${params.toString()}`;

      } catch (erro) {

        console.error(
          'Erro ao buscar veículos:',
          erro
        );

        alert(
          'Erro ao buscar disponibilidade.'
        );

      }

    });

}

const inputLocal = document.getElementById('local-retirada');
const listaLocais = document.getElementById('lista-locais');
const opcoesLocais = document.querySelectorAll('.opcao-local');

if (inputLocal && listaLocais) {
  inputLocal.addEventListener('focus', () => {
    listaLocais.classList.remove('esconder');
  });

  inputLocal.addEventListener('input', () => {
    const texto = inputLocal.value.toLowerCase();

    opcoesLocais.forEach((opcao) => {
      const local = opcao.innerText.toLowerCase();

      if (local.includes(texto)) {
        opcao.style.display = 'flex';
      } else {
        opcao.style.display = 'none';
      }
    });

    listaLocais.classList.remove('esconder');
  });

  opcoesLocais.forEach((opcao) => {
    opcao.addEventListener('click', () => {
      inputLocal.value = opcao.dataset.local;
      listaLocais.classList.add('esconder');
    });
  });

  document.addEventListener('click', (evento) => {
    if (!evento.target.closest('.campo-localizacao')) {
      listaLocais.classList.add('esconder');
    }
  });
}