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
// BUSCAR DISPONIBILIDADE - HOME
// ======================================

const btnBuscarDisponibilidade = document.getElementById('btn-buscar-disponibilidade');
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
      const conteudo = opcao.innerText.toLowerCase();
      opcao.style.display = conteudo.includes(texto) ? 'flex' : 'none';
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

if (btnBuscarDisponibilidade) {
  btnBuscarDisponibilidade.addEventListener('click', () => {
    const localRetirada = document.getElementById('local-retirada').value.trim();
    const dataRetirada = document.getElementById('data-retirada').value;
    const horaRetirada = document.getElementById('hora-retirada').value;

    if (!localRetirada || !dataRetirada || !horaRetirada) {
      alert('Preencha o local, a data e o horário de retirada.');
      return;
    }

    const dadosBusca = {
      local_retirada: localRetirada,
      data_retirada: dataRetirada,
      hora_retirada: horaRetirada
    };

    sessionStorage.setItem('dados_reserva', JSON.stringify(dadosBusca));

    window.location.href = '/DriverLux/app/View/Fluxo%20de%20Reserva/veiculos.php';
  });
}