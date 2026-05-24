// /public/assets/js/pagamento.js

document.addEventListener('DOMContentLoaded', () => {

    const reserva = JSON.parse(sessionStorage.getItem('reserva_atual') || '{}');
    const TOTAL = parseFloat(reserva.valor_total_previsto || 0);
    const TAXA_JUROS = 0.0299; // taxa com juros
    const SEM_JUROS_ATE = 6; // sem juros
    // valores iniciais
    let parcelasSelecionadas = 1;
    let tipoAtual = 'credito';

    //preenche os dados na tela
    function preencherResumo() {
        setText('resumo-modelo', reserva.veiculo_modelo || '—');
        setText('resumo-categoria', reserva.categoria_nome || '—');
        setText('pill-veiculo', '🚗 ' + (reserva.veiculo_modelo || '—'));
        setText('pill-periodo', '📅 ' + (reserva.total_dias || '—') + ' diária(s)');
        setText('label-diarias', (reserva.total_dias || '—') + ' diária(s) × R$ ' + fmt(reserva.valor_diaria || 0));
        setText('val-diarias', 'R$ ' + fmt(reserva.valor_diarias || 0));
        setText('val-protecao', 'R$ ' + fmt(reserva.valor_protecao || 0));
        setText('val-taxa', 'R$ ' + fmt(reserva.valor_taxa || 0));
        setText('total-exibido', fmt(TOTAL));

        // desconto
        const desconto = parseFloat(reserva.valor_desconto || 0);
        // caso tenha desconto/cupom
        if (desconto > 0) {
            show('linha-desconto');
            show('badge-desconto');
            setText('label-cupom', 'Cupom ' + (reserva.cupom_codigo || ''));
            setText('val-desconto', '− R$ ' + fmt(desconto));
            setText('texto-desconto', 'Desconto de R$ ' + fmt(desconto) + ' aplicado');
        }
    }
    // lista das parcelas
    function renderParcelas() {
        const lista = document.getElementById('lista-parcelas');
        lista.innerHTML = '';

        for (let i = 1; i <= 12; i++) {
            let valorParcela, totalFinal, semJuros;

            if (i <= SEM_JUROS_ATE) {
                valorParcela = TOTAL / i;
                totalFinal = TOTAL;
                semJuros = true;
            } else {
                const fator = (TAXA_JUROS * Math.pow(1 + TAXA_JUROS, i)) / (Math.pow(1 + TAXA_JUROS, i) - 1);
                valorParcela = TOTAL * fator;
                totalFinal = valorParcela * i;
                semJuros = false;
            }

            const el = document.createElement('div');
            el.className = 'parcela-opcao' + (i === 1 ? ' ativa' : '');
            el.dataset.parcelas = i;
            el.dataset.valor = valorParcela.toFixed(2);

            el.innerHTML = `
                <div>
                    <span class="parcela-num">${i}x</span>
                    <span class="parcela-valor"> R$ ${fmt(valorParcela)}</span>
                    ${semJuros ? '<span class="tag-sem-juros">sem juros</span>' : ''}
                </div>
                <span class="parcela-total">Total: R$ ${fmt(totalFinal)}</span>
            `;

            el.addEventListener('click', () => selecionarParcela(el));
            lista.appendChild(el);
        }
    }
    
    function selecionarParcela(el) {
        document.querySelectorAll('.parcela-opcao').forEach(p => p.classList.remove('ativa'));
        el.classList.add('ativa');
        parcelasSelecionadas = parseInt(el.dataset.parcelas);
        const valor = parseFloat(el.dataset.valor);
        setText('parcela-info', 'em ' + parcelasSelecionadas + 'x de R$ ' + fmt(valor));
    }

    // alterna entre crédito e débito
    function selecionarTipo(tipo) {
        tipoAtual = tipo;

        document.getElementById('btn-credito').classList.toggle('ativo', tipo === 'credito');
        document.getElementById('btn-debito').classList.toggle('ativo', tipo === 'debito');

        const blocoParcelas = document.getElementById('bloco-parcelas');

        if (tipo === 'credito') {
            blocoParcelas.classList.remove('esconder');
            const ativa = document.querySelector('.parcela-opcao.ativa');
            if (ativa) selecionarParcela(ativa);
        } else {
            blocoParcelas.classList.add('esconder');
            setText('parcela-info', 'à vista');
            parcelasSelecionadas = 1;
        }
    }

    document.getElementById('btn-credito').addEventListener('click', () => selecionarTipo('credito'));
    document.getElementById('btn-debito').addEventListener('click', () => selecionarTipo('debito'));

    // remove tudo que não é número e aceita apenas 16 dígitos
    document.getElementById('numero-cartao').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 16);
        v = v.replace(/(.{4})/g, '$1 ').trim();
        this.value = v;
    });
    // aceita apenas 3 dígitos
    document.getElementById('cvv').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').substring(0, 3);
    });


    document.getElementById('btn-pagar').addEventListener('click', async () => {
        const payload = {
            reserva_id: reserva.id,
            tipo_cartao: tipoAtual,
            valor_total: TOTAL,
            parcelas: parcelasSelecionadas,
            nome_titular: document.getElementById('nome-titular').value.trim(),
            numero_cartao: document.getElementById('numero-cartao').value.replace(/\s/g, ''),
            cvv: document.getElementById('cvv').value.trim(),
            mes_vencimento: document.getElementById('mes-vencimento').value,
            ano_vencimento: document.getElementById('ano-vencimento').value,
        };

        if (!validar(payload)) return;

        const btn = document.getElementById('btn-pagar');
        btn.disabled = true;
        btn.textContent = 'Processando...';

        try {
            const res = await fetch('/DriverLux/public/api/pagamentos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();

            if (res.ok) {
                window.location.href = 'resumo.php';
            } else {
                alert(data.erro || 'Erro ao processar pagamento.');
                btn.disabled = false;
                btn.textContent = 'Confirmar Pagamento';
            }
        } catch (e) {
            alert('Erro de conexão. Tente novamente.');
            btn.disabled = false;
            btn.textContent = 'Confirmar Pagamento';
        }
    });

    // caso tenha algum erro nas infos
    function validar(p) {
        if (!p.nome_titular) return erro('Informe o nome do titular.');
        if (p.numero_cartao.length !== 16) return erro('Número do cartão inválido.');
        if (p.cvv.length !== 3) return erro('CVV inválido.');
        if (!p.mes_vencimento || !p.ano_vencimento) return erro('Informe a validade do cartão.');
        return true;
    }

    function erro(msg) { alert(msg); return false; }

    // formata dinheiro
    function fmt(n) { return parseFloat(n).toFixed(2).replace('.', ','); }
    function setText(id, v) { const el = document.getElementById(id); if (el) el.textContent = v; }
    function show(id) { const el = document.getElementById(id); if (el) el.style.display = 'flex'; }

    // executa tudo ao abrir página
    preencherResumo();
    renderParcelas();
    selecionarTipo('credito');

});