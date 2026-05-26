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
        }
    } catch (erro) {
        toast('Erro ao carregar perfil.', true);
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

    // Mostrar estado de carregamento visual temporário usando a própria barra informativa
    const strip = document.getElementById('foto-strip');
    const stripImg = document.getElementById('foto-strip-img');

    // Preview em base64 local rápido enquanto faz o upload
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

    // Preparar envio multipart/form-data
    const formData = new FormData();
    formData.append('foto', file);

    try {
        const res = await fetch('/DriverLux/public/api/usuarios/salvar_foto.php', {
            method: 'POST',
            body: formData
        });

        const data = await res.json();
        if (data.status === 'ok') {
            usuario.foto_perfil = data.url;

            // Oculta a barra já que o salvamento foi concluído com sucesso no servidor
            if (strip) strip.classList.remove('visivel');

            // Atualiza a imagem oficial forçando a quebra de cache do navegador
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

        toast('Perfil atualizado com sucesso!');
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