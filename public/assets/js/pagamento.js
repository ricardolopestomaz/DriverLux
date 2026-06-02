// public/assets/js/pagamento.js

document.addEventListener('DOMContentLoaded', async () => {
    // 1. RECUPERAÇÃO DE DADOS DA SESSÃO
    const dadosReserva = JSON.parse(sessionStorage.getItem('dados_reserva') || '{}');

    // Se o usuário cair aqui sem ter escolhido um carro, manda ele de volta
    if (!dadosReserva.veiculo_modelo) {
        alert("Nenhum veículo selecionado. Você será redirecionado para a busca.");
        window.location.href = '/DriverLux/app/View/fluxo-reserva/veiculos.php';
        return;
    }

    // 2. CÁLCULO DOS VALORES INICIAIS
    const numDiarias = dadosReserva.num_diarias ? parseInt(dadosReserva.num_diarias) : 1; 
    const valorDiaria = dadosReserva.valor_diaria || 0;
    const valorProtecao = dadosReserva.valor_protecao_total ? parseFloat(dadosReserva.valor_protecao_total) : 0; 
    
    const subtotalDiarias = valorDiaria * numDiarias;
    const taxaAluguel = (subtotalDiarias + valorProtecao) * 0.15; // 15% de taxa
    let totalGeral = subtotalDiarias + valorProtecao + taxaAluguel;
    
    let desconto = 0;
    let cupomId = null;
    let cupomCodigo = dadosReserva.cupom || '';

    // Variáveis de controle de pagamento
    let parcelasSelecionadas = 1;
    let tipoAtual = 'credito';
    const TAXA_JUROS = 0.0299; // taxa com juros
    const SEM_JUROS_ATE = 6; // sem juros

    // Função auxiliar para formatar moeda
    const fmt = (n) => parseFloat(n).toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    const setText = (id, v) => { const el = document.getElementById(id); if (el) el.textContent = v; };
    const show = (id) => { const el = document.getElementById(id); if (el) el.style.display = 'flex'; };

    // 3. BUSCA O CUPOM CASO DIGITADO
    if (cupomCodigo) {
        try {
            const resCupom = await fetch(`/DriverLux/public/api/cupons/${cupomCodigo}`);
            const dataCupom = await resCupom.json();
            
            if (resCupom.ok && dataCupom.status === 'success' && dataCupom.data) {
                const cupomInfo = dataCupom.data;
                cupomId = cupomInfo.id;
                
                if (cupomInfo.tipo_desconto === 'percentual') {
                    desconto = totalGeral * (parseFloat(cupomInfo.valor_desconto) / 100);
                } else if (cupomInfo.tipo_desconto === 'valor_fixo') {
                    desconto = parseFloat(cupomInfo.valor_desconto);
                }
                
                totalGeral = Math.max(0, totalGeral - desconto);
            }
        } catch (e) {
            console.error("Erro ao verificar cupom:", e);
        }
    }

    // 4. ATUALIZAÇÃO DOS RESUMOS NO HTML
    function preencherResumo() {
        setText('resumo-modelo', dadosReserva.veiculo_modelo || '—');
        setText('resumo-categoria', dadosReserva.categoria_nome || '—');
        setText('pill-veiculo', '🚗 ' + (dadosReserva.veiculo_modelo || '—'));
        
        // Atualiza a faixa de cabeçalho
        const resumoValores = document.querySelectorAll('.resumo-valor');
        if (resumoValores.length >= 2) {
            if (dadosReserva.local_retirada) resumoValores[0].innerHTML = `📍 ${dadosReserva.local_retirada}`;
            resumoValores[1].innerHTML = `📅 ${numDiarias} Diária(s)`;
        }

        const fotoCarro = dadosReserva.veiculo_imagem;
        const imgFoto = document.getElementById('resumo-veiculo-foto');
        const spanEmoji = document.getElementById('resumo-veiculo-emoji');
        if (fotoCarro && imgFoto && spanEmoji) {
            imgFoto.src = fotoCarro;
            imgFoto.style.display = 'block';
            spanEmoji.style.display = 'none';
        }

        setText('label-diarias', `Diárias (${numDiarias}x)`);
        setText('val-diarias', 'R$ ' + fmt(subtotalDiarias));
        setText('val-protecao', 'R$ ' + fmt(valorProtecao));
        setText('val-taxa', 'R$ ' + fmt(taxaAluguel));
        setText('total-exibido', fmt(totalGeral));

        if (desconto > 0) {
            show('linha-desconto');
            show('badge-desconto');
            setText('label-cupom', 'Cupom ' + cupomCodigo);
            setText('val-desconto', '− R$ ' + fmt(desconto));
            setText('texto-desconto', 'Desconto de R$ ' + fmt(desconto) + ' aplicado');
        }
    }

    // 5. LISTA DAS PARCELAS
    function renderParcelas() {
        const lista = document.getElementById('lista-parcelas');
        if (!lista) return;
        lista.innerHTML = '';

        for (let i = 1; i <= 12; i++) {
            let valorParcela, totalFinal, semJuros;

            if (i <= SEM_JUROS_ATE) {
                valorParcela = totalGeral / i;
                totalFinal = totalGeral;
                semJuros = true;
            } else {
                const fator = (TAXA_JUROS * Math.pow(1 + TAXA_JUROS, i)) / (Math.pow(1 + TAXA_JUROS, i) - 1);
                valorParcela = totalGeral * fator;
                totalFinal = valorParcela * i;
                semJuros = false;
            }

            const el = document.createElement('div');
            el.className = 'parcela-opcao' + (i === 1 ? ' ativa' : '');
            el.dataset.parcelas = i;
            el.dataset.valor = valorParcela.toFixed(2);
            el.style.padding = '12px';
            el.style.border = '1px solid #eee';
            el.style.borderRadius = '8px';
            el.style.cursor = 'pointer';
            el.style.marginBottom = '8px';
            el.style.display = 'flex';
            el.style.justifyContent = 'space-between';
            el.style.alignItems = 'center';
            el.style.fontSize = '13px';
            el.style.background = i === 1 ? '#f3e8ff' : 'transparent';
            el.style.borderColor = i === 1 ? '#3b0567' : '#eee';

            el.innerHTML = `
                <div>
                    <span class="parcela-num" style="font-weight: 800; color: #2b0647;">${i}x</span>
                    <span class="parcela-valor" style="color: #6a0dad; font-weight: 700;"> R$ ${fmt(valorParcela)}</span>
                    ${semJuros ? '<span class="tag-sem-juros" style="background: #2ecc71; color: white; font-size: 9px; padding: 2px 6px; border-radius: 4px; margin-left: 8px; font-weight: bold;">sem juros</span>' : ''}
                </div>
                <span class="parcela-total" style="color: #888; font-size: 11px;">Total: R$ ${fmt(totalFinal)}</span>
            `;

            el.addEventListener('click', () => selecionarParcela(el));
            lista.appendChild(el);
        }
    }

    function selecionarParcela(el) {
        document.querySelectorAll('#lista-parcelas > div').forEach(p => {
            p.classList.remove('ativa');
            p.style.background = 'transparent';
            p.style.borderColor = '#eee';
        });
        el.classList.add('ativa');
        el.style.background = '#f3e8ff';
        el.style.borderColor = '#3b0567';

        parcelasSelecionadas = parseInt(el.dataset.parcelas);
        const valor = parseFloat(el.dataset.valor);
        setText('parcela-info', 'em ' + parcelasSelecionadas + 'x de R$ ' + fmt(valor));
    }

    // ALTERNA ENTRE CRÉDITO E DÉBITO
    function selecionarTipo(tipo) {
        tipoAtual = tipo;

        document.getElementById('btn-credito').classList.toggle('ativo', tipo === 'credito');
        document.getElementById('btn-debito').classList.toggle('ativo', tipo === 'debito');

        const blocoParcelas = document.getElementById('bloco-parcelas');

        if (tipo === 'credito') {
            if (blocoParcelas) blocoParcelas.style.display = 'block';
            const ativa = document.querySelector('.parcela-opcao.ativa');
            if (ativa) selecionarParcela(ativa);
            else renderParcelas();
        } else {
            if (blocoParcelas) blocoParcelas.style.display = 'none';
            setText('parcela-info', 'à vista no Débito');
            parcelasSelecionadas = 1;
        }
    }

    document.getElementById('btn-credito').addEventListener('click', () => selecionarTipo('credito'));
    document.getElementById('btn-debito').addEventListener('click', () => selecionarTipo('debito'));

    // MÁSCARAS
    document.getElementById('numero-cartao').addEventListener('input', function () {
        let v = this.value.replace(/\D/g, '').substring(0, 16);
        v = v.replace(/(.{4})/g, '$1 ').trim();
        this.value = v;
    });

    document.getElementById('cvv').addEventListener('input', function () {
        this.value = this.value.replace(/\D/g, '').substring(0, 3);
    });

    // CONFIRMAÇÃO DE PAGAMENTO E FLUXO DE RESERVA
    document.getElementById('btn-pagar').addEventListener('click', async () => {
        const nomeTitular = document.getElementById('nome-titular').value.trim();
        const numeroCartao = document.getElementById('numero-cartao').value.replace(/\s/g, '');
        const mesValidade = document.getElementById('mes-vencimento').value;
        const anoValidade = document.getElementById('ano-vencimento').value;
        const cvv = document.getElementById('cvv').value.trim();

        if (!nomeTitular) { alert('Informe o nome do titular do cartão.'); return; }
        if (numeroCartao.length !== 16) { alert('Número do cartão inválido (deve ter 16 dígitos).'); return; }
        if (!mesValidade || !anoValidade) { alert('Informe a data de validade do cartão.'); return; }
        if (cvv.length !== 3) { alert('CVV inválido (deve ter 3 dígitos).'); return; }

        const btn = document.getElementById('btn-pagar');
        btn.disabled = true;
        btn.textContent = 'Processando...';

        try {
            // A. CRIA A RESERVA NO BANCO
            const reservaPayload = {
                veiculo_id: parseInt(dadosReserva.veiculo_id),
                pacote_protecao_id: dadosReserva.protecao_id ? parseInt(dadosReserva.protecao_id) : null,
                opcao_quilometragem_id: dadosReserva.km_id ? parseInt(dadosReserva.km_id) : null,
                cupom_id: cupomId,
                local_retirada: dadosReserva.local_retirada,
                local_devolucao: dadosReserva.local_devolucao,
                data_retirada: dadosReserva.data_retirada + ' ' + (dadosReserva.hora_retirada || '12:00') + ':00',
                data_devolucao: dadosReserva.data_devolucao + ' ' + (dadosReserva.hora_devolucao || '12:00') + ':00',
                valor_diarias: subtotalDiarias,
                valor_protecao: valorProtecao,
                taxa_aluguel_percentual: 15.00,
                valor_desconto: desconto,
                valor_total_previsto: totalGeral
            };

            const resReserva = await fetch('/DriverLux/public/api/reservas', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(reservaPayload)
            });

            const dataReserva = await resReserva.json();

            if (!resReserva.ok) {
                throw new Error(dataReserva.erro || 'Erro ao criar a reserva.');
            }

            const reservaId = dataReserva.id;

            // B. EFETUA O PAGAMENTO
            const pagamentoPayload = {
                reserva_id: reservaId,
                tipo_cartao: tipoAtual,
                valor_total: totalGeral,
                parcelas: parcelasSelecionadas,
                nome_titular: nomeTitular,
                numero_cartao: numeroCartao,
                cvv: cvv,
                mes_vencimento: mesValidade,
                ano_vencimento: anoValidade
            };

            const resPagamento = await fetch('/DriverLux/public/api/pagamentos', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(pagamentoPayload)
            });

            const dataPagamento = await resPagamento.json();

            if (!resPagamento.ok) {
                throw new Error(dataPagamento.erro || 'Erro ao registrar o pagamento.');
            }

            // C. SALVA INFORMAÇÕES FINAIS E DIRECIONA PARA O RESUMO
            const dadosFinais = {
                reserva_id: reservaId,
                veiculo_modelo: dadosReserva.veiculo_modelo,
                veiculo_imagem: dadosReserva.veiculo_imagem,
                local_retirada: dadosReserva.local_retirada,
                local_devolucao: dadosReserva.local_devolucao,
                data_retirada: dadosReserva.data_retirada,
                hora_retirada: dadosReserva.hora_retirada,
                data_devolucao: dadosReserva.data_devolucao,
                hora_devolucao: dadosReserva.hora_devolucao,
                num_diarias: numDiarias,
                metodo_pagamento: tipoAtual === 'credito' ? 'Crédito' : 'Débito',
                parcelas: parcelasSelecionadas,
                total_pago: totalGeral,
                desconto: desconto
            };

            sessionStorage.setItem('resumo_reserva_final', JSON.stringify(dadosFinais));
            sessionStorage.removeItem('dados_reserva'); // Limpa a sacola para futuras compras

            window.location.href = '/DriverLux/app/View/fluxo-reserva/conclusao.php';

        } catch (e) {
            alert(e.message || 'Erro de conexão. Tente novamente.');
            btn.disabled = false;
            btn.textContent = 'Confirmar Pagamento';
        }
    });

    // Inicialização da tela
    preencherResumo();
    renderParcelas();
    selecionarTipo('credito');
});