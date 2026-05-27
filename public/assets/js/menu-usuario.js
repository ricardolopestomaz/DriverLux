document.addEventListener('DOMContentLoaded', () => {
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

      window.location.href = '/DriverLux/app/View/Fluxo%20de%20Reserva/veiculos.php';
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
});