let usuario = {
    id: null,
    nome: '',
    email: '',
    cpf: '',
    foto_perfil: null
};

window.addEventListener('load', carregarUsuario);

async function carregarUsuario() {
    try {
        const res = await fetch('/DriverLux/public/api/usuarios/me');
        const data = await res.json();
        if (data.logado && data.usuario) {
            usuario = data.usuario;
            preencherTela();
        } else {
            window.location.href = '/DriverLux/index.html';
        }
    } catch (erro) {
        toast('Erro ao carregar perfil.', true);
        window.location.href = '/DriverLux/index.html';
    }
}

function preencherTela() {
    document.getElementById('exibe-nome').textContent = usuario.nome || 'Cliente';
    document.getElementById('exibe-email').textContent = usuario.email || '—';
    document.getElementById('f-nome').value = usuario.nome || '';
    document.getElementById('f-email').value = usuario.email || '';
    document.getElementById('f-cpf').value = usuario.cpf || '';

    const inicial = usuario.nome ? usuario.nome.charAt(0).toUpperCase() : '?';
    document.getElementById('foto-inicial').textContent = inicial;

    if (usuario.foto_perfil) {
        mostrarFotoImg(`${usuario.foto_perfil}?t=${new Date().getTime()}`);
    }
}

function mostrarFotoImg(src) {
    const img = document.getElementById('foto-img');
    const ini = document.getElementById('foto-inicial');
    if (img && ini) {
        img.src = src;
        img.style.display = 'block';
        ini.style.display = 'none';
    }
}

async function onSelecionarFoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    const strip = document.getElementById('foto-strip');
    const stripImg = document.getElementById('foto-strip-img');

    const reader = new FileReader();
    reader.onload = (e) => {
        mostrarFotoImg(e.target.result);
        if (stripImg) stripImg.src = e.target.result;
        if (strip) {
            strip.querySelector('p').innerHTML = '<strong>Enviando foto...</strong> Aguarde um momento.';
            strip.classList.add('visivel');
        }
    };
    reader.readAsDataURL(file);

    const formData = new FormData();
    formData.append('foto', file);

    try {
        const res = await fetch('/DriverLux/public/usuarios/salvar_foto.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.status === 'success') {
            usuario.foto_perfil = data.url;
            if (strip) strip.classList.remove('visivel');
            mostrarFotoImg(`${data.url}?t=${new Date().getTime()}`);
            toast('Foto de perfil atualizada com sucesso!');
        } else {
            toast(data.erro || 'Erro ao enviar foto.', true);
            if (strip) strip.classList.remove('visivel');
        }
    } catch (e) {
        toast('Erro ao conectar com o servidor para salvar a foto.', true);
        if (strip) strip.classList.remove('visivel');
    }
}

document.getElementById('f-cpf').addEventListener('input', function () {
    let v = this.value.replace(/\D/g, '').substring(0, 11);
    v = v
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d)/, '$1.$2')
        .replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    this.value = v;
});

async function salvar() {
    const btn = document.getElementById('btn-salvar');
    if (!btn) return;

    btn.classList.add('carregando');

    const dados = {
        nome: document.getElementById('f-nome').value.trim(),
        cpf: document.getElementById('f-cpf').value.trim()
    };

    try {
        const res = await fetch(`/DriverLux/public/api/usuarios/${usuario.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        const data = await res.json();

        if (data.status === 'error' || data.erro) {
            toast(data.erro || 'Erro ao salvar os dados.', true);
            btn.classList.remove('carregando');
            return;
        }

        usuario = { ...usuario, ...dados };
        document.getElementById('exibe-nome').textContent = usuario.nome;
        toast('Perfil updated com sucesso!');
    } catch (erro) {
        toast('Erro ao salvar os dados cadastrais.', true);
    }
    btn.classList.remove('carregando');
}

let timeoutToast;
function toast(msg, erro = false) {
    const el = document.getElementById('toast');
    if (!el) return;

    el.textContent = msg;
    el.className = 'toast' + (erro ? ' erro' : '');
    clearTimeout(timeoutToast);
    requestAnimationFrame(() => {
        el.classList.add('show');
    });
    timeoutToast = setTimeout(() => {
        el.classList.remove('show');
    }, 3500);
}

// Controle de Abas Principais
function switchTab(tabName) {
    const tabCadastro = document.getElementById('tab-cadastro');
    const tabReservas = document.getElementById('tab-reservas');
    const btnCadastro = document.getElementById('btn-tab-cadastro');
    const btnReservas = document.getElementById('btn-tab-reservas');

    if (!tabCadastro || !tabReservas || !btnCadastro || !btnReservas) return;

    if (tabName === 'cadastro') {
        tabCadastro.classList.add('active');
        tabReservas.classList.remove('active');
        btnCadastro.classList.add('active');
        btnReservas.classList.remove('active');
    } else if (tabName === 'reservas') {
        tabCadastro.classList.remove('active');
        tabReservas.classList.add('active');
        btnCadastro.classList.remove('active');
        btnReservas.classList.add('active');
        carregarReservas();
    }
}

// Controle das Sub-abas de Reservas (Ativas / Canceladas)
function filtrarReservasPerfil(tipo) {
    const listaAtivas = document.getElementById('lista-ativas-perfil');
    const listaCanceladas = document.getElementById('lista-canceladas-perfil');
    const botoes = document.querySelectorAll('.aba-filtro-btn');

    botoes.forEach(btn => btn.classList.remove('ativa'));

    if (tipo === 'ativas') {
        listaAtivas.style.display = 'flex';
        listaCanceladas.style.display = 'none';
        botoes[0].classList.add('ativa');
    } else {
        listaAtivas.style.display = 'none';
        listaCanceladas.style.display = 'flex';
        botoes[1].classList.add('ativa');
    }
}

// Carregar e Separar Reservas
async function carregarReservas() {
    const containerAtivas = document.getElementById('lista-ativas-perfil');
    const containerCanceladas = document.getElementById('lista-canceladas-perfil');
    if (!containerAtivas || !containerCanceladas) return;

    try {
        const res = await fetch('/DriverLux/public/api/reservas/minhas');
        const data = await res.json();

        if (data.status === 'success' && data.data && data.data.length > 0) {
            containerAtivas.innerHTML = '';
            containerCanceladas.innerHTML = '';
            
            const formatarData = (dt) => {
                if(!dt) return '—';
                const parts = dt.split(' ');
                const datePart = parts[0].split('-').reverse().join('/');
                const timePart = parts[1] ? parts[1].substring(0, 5) : '12:00';
                return `${datePart} às ${timePart}`;
            };

            const formatarMoeda = (val) => parseFloat(val).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

            // Divisão lógica das reservas por status
            const reservasAtivas = data.data.filter(r => r.status.toLowerCase() !== 'cancelada');
            const reservasCanceladas = data.data.filter(r => r.status.toLowerCase() === 'cancelada');

            // Renderizador HTML reutilizável para os cartões
            const criarCardHTML = (reserva) => {
                const statusClass = reserva.status.toLowerCase(); 
                const fotoCarro = reserva.veiculo_imagem || 'https://cdn-icons-png.flaticon.com/512/3202/3202003.png';

                return `
                    <div class="reserva-card">
                        <div class="reserva-main">
                            <img class="reserva-carro-img" src="${fotoCarro}" alt="Carro">
                            <div class="reserva-info">
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
                            <span class="reserva-status ${statusClass}">${reserva.status}</span>
                            <div class="reserva-preco">${formatarMoeda(reserva.valor_total_previsto)}</div>
                        </div>
                    </div>
                `;
            };

            // Processar Reservas Ativas
            if (reservasAtivas.length > 0) {
                reservasAtivas.forEach(reserva => {
                    containerAtivas.insertAdjacentHTML('beforeend', criarCardHTML(reserva));
                });
            } else {
                containerAtivas.innerHTML = `
                    <div class="reserva-vazia">
                        <p>Você não possui nenhuma reserva ativa ou pendente no momento.</p>
                        <a href="/DriverLux/index.html" class="btn-reservar-agora">Alugar um Carro</a>
                    </div>
                `;
            }

            // Processar Reservas Canceladas
            if (reservasCanceladas.length > 0) {
                reservasCanceladas.forEach(reserva => {
                    containerCanceladas.insertAdjacentHTML('beforeend', criarCardHTML(reserva));
                });
            } else {
                containerCanceladas.innerHTML = `
                    <div class="reserva-vazia">
                        <p>Seu histórico de cancelamentos está limpo.</p>
                    </div>
                `;
            }

        } else {
            const telaVaziaGeral = `
                <div class="reserva-vazia">
                    <p>Você ainda não realizou nenhuma reserva de carro.</p>
                    <a href="/DriverLux/index.html" class="btn-reservar-agora">Fazer Minha Primeira Reserva</a>
                </div>
            `;
            containerAtivas.innerHTML = telaVaziaGeral;
            containerCanceladas.innerHTML = telaVaziaGeral;
        }
    } catch (e) {
        console.error("Erro ao carregar reservas:", e);
        containerAtivas.innerHTML = `
            <div style="text-align: center; padding: 20px; color: var(--perigo);">
                Ocorreu um erro ao carregar suas reservas. Tente novamente mais tarde.
            </div>
        `;
    }
}

window.addEventListener('load', () => {
    const params = new URLSearchParams(window.location.search);
    if (params.get('tab') === 'reservas') {
        switchTab('reservas');
    }
});