let usuario = {
    id: null,
    nome: '',
    email: '',
    cpf: '',
    foto_perfil: null
};

let novaFotoBase64 = null;

window.addEventListener('load', carregarUsuario);

async function carregarUsuario() {
    try {
        const res = await fetch('/DriverLux/public/api/usuarios/me');
        const data = await res.json();
        if (data.logado && data.usuario) {
            usuario = data.usuario;
            preencherTela();
        }
    } catch (erro) {
        toast('Erro ao carregar perfil.', true);
    }
}

function preencherTela() {
    document.getElementById('exibe-nome').textContent =
        usuario.nome || 'Cliente';

    document.getElementById('exibe-email').textContent =
        usuario.email || '—';

    document.getElementById('f-nome').value =
        usuario.nome || '';

    document.getElementById('f-email').value =
        usuario.email || '';

    document.getElementById('f-cpf').value =
        usuario.cpf || '';

    const inicial = usuario.nome
        ? usuario.nome.charAt(0).toUpperCase()
        : '?';

    document.getElementById('foto-inicial').textContent = inicial;

    if (usuario.foto_perfil) {
        mostrarFotoImg(usuario.foto_perfil);
    }
}

function mostrarFotoImg(src) {
    const img = document.getElementById('foto-img');
    const ini = document.getElementById('foto-inicial');
    img.src = src;
    img.style.display = 'block';
    ini.style.display = 'none';
}

async function onSelecionarFoto(event) {
    const file = event.target.files[0];
    if (!file) return;

    // Preview imediato
    const reader = new FileReader();
    reader.onload = (e) => {
        novaFotoBase64 = e.target.result;
        mostrarFotoImg(e.target.result);
        document.getElementById('foto-strip-img').src = e.target.result;
        document.getElementById('foto-strip').classList.add('visivel');
    };
    reader.readAsDataURL(file);

    // Upload para o servidor
    const formData = new FormData();
    formData.append('foto', file);

    try {
        const res = await fetch('/DriverLux/public/salvar_foto.php', {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        if (data.status === 'ok') {
            usuario.foto_perfil = data.url;
            toast('Foto atualizada!');
            document.getElementById('foto-strip').classList.remove('visivel');
        } else {
            toast(data.erro || 'Erro ao enviar foto.', true);
        }
    } catch (e) {
        toast('Erro ao enviar foto.', true);
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
    // ← bug corrigido: btn estava indefinido
    const btn = document.getElementById('btn-salvar');
    btn.classList.add('carregando');

    const dados = {
        nome: document.getElementById('f-nome').value.trim(),
        cpf:  document.getElementById('f-cpf').value.trim()
    };

    try {
        const res = await fetch(`/DriverLux/public/api/usuarios/${usuario.id}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(dados)
        });

        const data = await res.json();

        if (data.status === 'error') {
            toast(data.erro, true);
            btn.classList.remove('carregando');
            return;
        }

        usuario = { ...usuario, ...dados };

        document.getElementById('exibe-nome').textContent = usuario.nome;
        document.getElementById('foto-strip').classList.remove('visivel');

        toast('Perfil atualizado com sucesso!');

    } catch (erro) {
        toast('Erro ao salvar.', true);
    }

    btn.classList.remove('carregando');
}

let timeoutToast;

function toast(msg, erro = false) {
    const el = document.getElementById('toast');
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