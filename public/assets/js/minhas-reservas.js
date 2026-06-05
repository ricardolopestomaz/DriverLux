// Função para alternar visualmente entre as abas de ativas e canceladas
function alternarAba(aba) {
    const listaAtivas = document.getElementById('lista-ativas');
    const listaCanceladas = document.getElementById('lista-canceladas');
    const botoes = document.querySelectorAll('.aba-btn');

    botoes.forEach(btn => btn.classList.remove('ativa'));

    if (aba === 'ativas') {
        listaAtivas.style.display = 'flex';
        listaCanceladas.style.display = 'none';
        botoes[0].classList.add('ativa');
    } else {
        listaAtivas.style.display = 'none';
        listaCanceladas.style.display = 'flex';
        botoes[1].classList.add('ativa');
    }
}

// Função para cancelar a reserva através da API
async function cancelarReserva(reservaId) {
    if (!confirm('Tem certeza que deseja cancelar esta reserva?')) {
        return;
    }

    try {
        const response = await fetch(`/DriverLux/public/api/reservas/${reservaId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ 
                status: 'Cancelada' 
            })
        });

        const resultado = await response.json();

        if (response.ok || resultado.status === 'success') {
            alert('Reserva cancelada com sucesso!');
            window.location.reload(); 
        } else {
            alert(resultado.erro || resultado.message || 'Erro ao cancelar a reserva.');
        }
    } catch (error) {
        console.error('Erro ao cancelar:', error);
        alert('Não foi possível conectar ao servidor para cancelar.');
    }
}

// Carregamento inicial dos dados ao abrir a página
document.addEventListener('DOMContentLoaded', async () => {
    try {
        const resMe = await fetch('/DriverLux/public/api/usuarios/me');
        const dataMe = await resMe.json();
        if (!dataMe.logado || !dataMe.usuario) {
            window.location.href = '/DriverLux/index.html';
            return;
        }
    } catch (err) {
        window.location.href = '/DriverLux/index.html';
        return;
    }

    const containerAtivas = document.getElementById('lista-ativas');
    const containerCanceladas = document.getElementById('lista-canceladas');
    if (!containerAtivas || !containerCanceladas) return;

    try {
        const res = await fetch('/DriverLux/public/api/reservas/minhas');
        const data = await res.json();

        if (data.status === 'success' && data.data && data.data.length > 0) {
            containerAtivas.innerHTML = '';
            containerCanceladas.innerHTML = '';

            const formatarData = (dt) => {
                if (!dt) return '—';
                const parts = dt.split(' ');
                const datePart = parts[0].split('-').reverse().join('/');
                const timePart = parts[1] ? parts[1].substring(0, 5) : '12:00';
                return `${datePart} às ${timePart}`;
            };

            const formatarMoeda = (val) => parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            // Filtros baseados no status vindo do banco de dados
            const reservasAtivas = data.data.filter(r => r.status.toLowerCase() !== 'cancelada');
            const reservasCanceladas = data.data.filter(r => r.status.toLowerCase() === 'cancelada');

            // Função para gerar o HTML do card de reserva
            const criarCardHTML = (reserva) => {
                const statusClass = reserva.status.toLowerCase();
                const fotoCarro = reserva.veiculo_imagem || 'https://cdn-icons-png.flaticon.com/512/3202/3202003.png';
                const idAtual = reserva.id || reserva.id_reserva;

                const botaoCancelar = statusClass !== 'cancelada' 
                    ? `<button class="btn-cancelar-reserva" onclick="cancelarReserva('${idAtual}')">Cancelar Reserva</button>` 
                    : '';

                return `
                    <div class="card-reserva">
                        <div class="reserva-detalhes">
                            <img class="carro-img-reserva" src="${fotoCarro}" alt="Carro">
                            <div class="reserva-textos">
                                <h3>${reserva.veiculo_modelo || 'Veículo'}</h3>
                                <div class="reserva-periodo">
                                    <strong>Retirada:</strong> ${formatarData(reserva.data_retirada)}<br>
                                    <strong>Devolução:</strong> ${formatarData(reserva.data_devolucao)}
                                </div>
                                <div class="reserva-locais">
                                    📍 Retirada: ${reserva.local_retirada || 'Não inf.'} | Devolução: ${reserva.local_devolucao || 'Não inf.'}
                                </div>
                            </div>
                        </div>
                        <div class="reserva-status-preco">
                            <span class="status-badge ${statusClass}">${reserva.status}</span>
                            <div class="preco-reserva"><span>Total:</span> ${formatarMoeda(reserva.valor_total_previsto)}</div>
                            ${botaoCancelar}
                        </div>
                    </div>
                `;
            };

            // Exibir reservas ativas
            if (reservasAtivas.length > 0) {
                reservasAtivas.forEach(reserva => {
                    containerAtivas.insertAdjacentHTML('beforeend', criarCardHTML(reserva));
                });
            } else {
                containerAtivas.innerHTML = `
                    <div class="reserva-vazia">
                        <div class="reserva-vazia-icone">🚗</div>
                        <p>Você não possui nenhuma reserva ativa ou pendente no momento.</p>
                        <a href="/DriverLux/index.html" class="btn-acao-painel">Alugar um Carro</a>
                    </div>
                `;
            }

            // Exibir reservas canceladas
            if (reservasCanceladas.length > 0) {
                reservasCanceladas.forEach(reserva => {
                    containerCanceladas.insertAdjacentHTML('beforeend', criarCardHTML(reserva));
                });
            } else {
                containerCanceladas.innerHTML = `
                    <div class="reserva-vazia">
                        <div class="reserva-vazia-icone">✔️</div>
                        <p>Seu histórico de cancelamentos está limpo.</p>
                    </div>
                `;
            }

        } else {
            const telaVaziaGeral = `
                <div class="reserva-vazia">
                    <div class="reserva-vazia-icone">🚗</div>
                    <p>Você ainda não realizou nenhuma reserva de veículo de luxo.</p>
                    <a href="/DriverLux/index.html" class="btn-acao-painel">Alugar um Carro</a>
                </div>
            `;
            containerAtivas.innerHTML = telaVaziaGeral;
            containerCanceladas.innerHTML = telaVaziaGeral;
        }
    } catch (e) {
        console.error("Erro ao carregar reservas:", e);
        containerAtivas.innerHTML = `
            <div style="text-align: center; padding: 20px; color: #ff4d4d; font-weight: bold;">
                Erro ao conectar ao servidor. Tente novamente mais tarde.
            </div>
        `;
    }
});