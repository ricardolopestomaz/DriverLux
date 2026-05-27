document.addEventListener('DOMContentLoaded', () => {
  let reserva = JSON.parse(sessionStorage.getItem('dados_reserva') || '{}');

  const protecoes = document.querySelectorAll('.opcao-protecao');
  const opcoesKm = document.querySelectorAll('.opcao-km');

  const inputLocalDevolucao = document.getElementById('local-devolucao');
  const inputDataDevolucao = document.getElementById('data-devolucao');
  const inputHoraDevolucao = document.getElementById('hora-devolucao');
  const inputCupom = document.getElementById('cupom-codigo');

  function dinheiro(valor) {
    return 'R$ ' + Number(valor || 0).toFixed(2).replace('.', ',');
  }

  function calcularDiarias() {
    if (!reserva.data_retirada || !inputDataDevolucao.value) {
      return 1;
    }

    const retirada = new Date(
      reserva.data_retirada + 'T' + (reserva.hora_retirada || '00:00')
    );

    const devolucao = new Date(
      inputDataDevolucao.value + 'T' + (inputHoraDevolucao.value || '00:00')
    );

    const diff = devolucao - retirada;

    if (diff <= 0) {
      return 1;
    }

    return Math.ceil(diff / (1000 * 60 * 60 * 24));
  }

  function getSelecionado(seletor) {
    return document.querySelector(seletor + '.ativa');
  }

  function atualizarResumo() {
    const protecao = getSelecionado('.opcao-protecao');
    const km = getSelecionado('.opcao-km');

    const diarias = calcularDiarias();

    const valorDiaria = Number(reserva.valor_diaria || 0);
    const valorProtecao = Number(protecao?.dataset.valor || 0);
    const valorKm = Number(km?.dataset.valor || 0);

    const totalDiarias = valorDiaria * diarias;
    const totalProtecao = valorProtecao * diarias;
    const totalKm = valorKm * diarias;
    const total = totalDiarias + totalProtecao + totalKm;

    document.getElementById('pill-veiculo').textContent =
      '🚗 ' + (reserva.veiculo_modelo || '—');

    document.getElementById('pill-retirada').textContent =
      '📍 ' + (reserva.local_retirada || '—');

    document.getElementById('resumo-veiculo').textContent =
      reserva.veiculo_modelo || '—';

    document.getElementById('resumo-retirada').textContent =
      reserva.local_retirada || '—';

    document.getElementById('resumo-devolucao').textContent =
      inputLocalDevolucao.value || '—';

    document.getElementById('resumo-diarias').textContent =
      diarias + ' diária(s)';

    document.getElementById('valor-diarias').textContent =
      dinheiro(totalDiarias);

    document.getElementById('valor-protecao').textContent =
      dinheiro(totalProtecao);

    document.getElementById('valor-km').textContent =
      dinheiro(totalKm);

    document.getElementById('valor-total').textContent =
      dinheiro(total);

    reserva.pacote_protecao_id = protecao?.dataset.id || null;
    reserva.protecao_nome = protecao?.dataset.nome || null;
    reserva.valor_protecao = totalProtecao;

    reserva.opcao_quilometragem_id = km?.dataset.id || null;
    reserva.km_nome = km?.dataset.nome || null;
    reserva.valor_km = totalKm;

    reserva.local_devolucao = inputLocalDevolucao.value;
    reserva.data_devolucao = inputDataDevolucao.value;
    reserva.hora_devolucao = inputHoraDevolucao.value;
    reserva.cupom_codigo = inputCupom.value.trim();

    reserva.total_dias = diarias;
    reserva.valor_diarias = totalDiarias;
    reserva.valor_total_previsto = total;

    sessionStorage.setItem('dados_reserva', JSON.stringify(reserva));
  }

  protecoes.forEach((card) => {
    card.addEventListener('click', () => {
      protecoes.forEach((c) => c.classList.remove('ativa'));
      card.classList.add('ativa');
      atualizarResumo();
    });
  });

  opcoesKm.forEach((card) => {
    card.addEventListener('click', () => {
      opcoesKm.forEach((c) => c.classList.remove('ativa'));
      card.classList.add('ativa');
      atualizarResumo();
    });
  });

  [inputLocalDevolucao, inputDataDevolucao, inputHoraDevolucao, inputCupom]
    .forEach((input) => {
      input.addEventListener('input', atualizarResumo);
      input.addEventListener('change', atualizarResumo);
    });

  document.getElementById('btn-continuar-pagamento')
    .addEventListener('click', () => {
      if (!reserva.veiculo_id) {
        alert('Selecione um veículo antes de continuar.');
        return;
      }

      if (
        !inputLocalDevolucao.value ||
        !inputDataDevolucao.value ||
        !inputHoraDevolucao.value
      ) {
        alert('Preencha local, data e hora de devolução.');
        return;
      }

      atualizarResumo();

      window.location.href = 'pagamento.php';
    });

  atualizarResumo();
});