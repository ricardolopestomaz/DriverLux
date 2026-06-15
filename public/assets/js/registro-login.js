class RegistroLogin extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this.apiUrl = '/DriverLux/public/api/usuarios';
        this.emailRecuperacao = ''; // Guarda temporariamente o e-mail validado
    }

    connectedCallback() {
        this.render();
        this.setupEvents();
    }

    render() {
        const modo = this.getAttribute('modo') || 'popover';
        this.shadowRoot.innerHTML = `
        <style>
            @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;800&display=swap');

            :host { 
                --primary: #3b0567; 
                font-family: 'Poppins', sans-serif; 
            }

            * { box-sizing: border-box; margin: 0; padding: 0; }
            h2, h3, p { margin: 0; padding: 0; }

            .auth-overlay {
                position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
                background: rgba(0,0,0,0.6);
                display: flex; justify-content: center; align-items: center;
                z-index: 9999;
            }

            .auth-card { 
                background: #fff; padding: 40px; border-radius: 12px;
                width: 100%; max-width: 400px;
                box-shadow: 0 15px 35px rgba(0,0,0,0.2);
                position: relative;
                color: #333;
            }

            .auth-card h2 { text-align: center; font-weight: 800; font-size: 28px; margin-bottom: 5px; color: var(--primary); }
            .auth-card h3 { color: #747474; font-size: 14px; text-align: center; margin-bottom: 25px; font-weight: 400; }
            
            .auth-overlay.modo-fluxo { position: relative; background: #F4F7F6; min-height: 600px; padding: 50px 0; }
            .auth-overlay.modo-fluxo .auth-card { max-width: 600px; box-shadow: none; border: 1px solid #ddd; }
            .auth-overlay.modo-fluxo .close-btn { display: none; }

            .main-btn { 
                background: var(--primary); color: white; border: none; 
                padding: 14px; width: 100%; border-radius: 8px; 
                cursor: pointer; font-weight: 600; font-size: 16px;
                transition: background 0.3s, transform 0.2s; 
                margin-top: 10px; font-family: inherit;
            }
            .main-btn:hover { background: #6a0dad; transform: translateY(-2px); }

            input { 
                width: 100%; padding: 14px; margin-bottom: 12px; 
                border: 1px solid #ddd; border-radius: 6px; 
                font-family: inherit; font-size: 14px; outline: none; transition: border-color 0.3s;
            }
            input:focus { border-color: var(--primary); }
            
            .hidden { display: none !important; }
            
            .footer-text { text-align: center; width: 100%; margin-top: 25px; font-size: 14px; color: #555; }
            .footer-text span { color: var(--primary); cursor: pointer; font-weight: 600; text-decoration: underline; }
            
            .close-btn { 
                position: absolute; top: 15px; right: 20px; 
                background: none; border: none; font-size: 28px; 
                cursor: pointer; color: #aaa; transition: color 0.3s;
            }
            .close-btn:hover { color: #333; }
            
            .msg { margin-bottom: 15px; font-size: 14px; display: none; text-align: center; padding: 10px; border-radius: 6px; font-weight: 600; }
            .error { color: #d32f2f; background: #ffebee; border: 1px solid #ffcdd2; }
            .success { color: #2e7d32; background: #e8f5e9; border: 1px solid #c8e6c9; }
            
        </style>

        <div id="auth-container" class="auth-overlay hidden ${modo === 'fluxo' ? 'modo-fluxo' : ''}">
            <div class="auth-card">
                <button class="close-btn" id="close-auth" title="Fechar">&times;</button>
                
                <div id="tela-de-login">
                    <h2>Login</h2>
                    <h3>Acesse sua conta para continuar.</h3>
                    <form id="login-form">
                        <input type="email" name="email" placeholder="E-mail" required>
                        <input type="password" name="senha" placeholder="Senha" required>
                        
                        <p style="text-align: right; margin: -5px 0 15px 0; font-size: 13px;">
                            <span id="go-to-recover" style="color: var(--primary); cursor: pointer; text-decoration: underline;">Esqueci minha senha</span>
                        </p>

                        <div id="login-msg" class="msg"></div>
                        <button type="submit" class="main-btn">Entrar</button>
                    </form>
                    <p class="footer-text">Novo por aqui? <span id="go-to-register">Cadastre-se</span></p>
                </div>

                <div id="tela-de-cadastro" class="hidden">
                    <h2>Criar Conta</h2>
                    <h3>Preencha seus dados.</h3>
                    <form id="register-form">
                        <input type="text" name="nome" placeholder="Nome Completo" required>
                        <input type="text" name="cpf" placeholder="CPF" required>
                        <input type="email" name="email" placeholder="E-mail" required>
                        <input type="password" name="senha" placeholder="Senha" required>
                        <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
                        <div id="register-msg" class="msg"></div>
                        <button type="submit" class="main-btn">Cadastrar</button>
                    </form>
                    <p class="footer-text">Já tem conta? <span id="go-to-login">Entrar</span></p>
                </div>

                <div id="tela-de-recuperacao" class="hidden">
                    <h2>Recuperar Senha</h2>
                    <h3 id="recover-subtitle">Insira o seu e-mail cadastrado para redefinir a sua senha.</h3>
                    
                    <form id="recover-form">
                        <div id="etapa-email">
                            <input type="email" id="recover-email-field" placeholder="Digite seu e-mail">
                            <button type="button" id="btn-verificar-email" class="main-btn">Verificar Conta</button>
                        </div>

                        <div id="etapa-nova-senha" class="hidden">
                            <input type="password" id="recover-new-password" placeholder="Nova Senha">
                            <input type="password" id="recover-confirm-password" placeholder="Confirmar Nova Senha">
                            <button type="submit" class="main-btn">Salvar Nova Senha</button>
                        </div>

                        <div id="recover-msg" class="msg"></div>
                    </form>
                    <p class="footer-text"><span id="recover-to-login">Voltar para o Login</span></p>
                </div>
            </div>
        </div>
        `;
    }

    limparFormularios() {
        const shadow = this.shadowRoot;
        this.emailRecuperacao = '';

        const loginForm = shadow.getElementById('login-form');
        const registerForm = shadow.getElementById('register-form');

        if (loginForm) loginForm.reset();
        if (registerForm) registerForm.reset();

        const loginMsg = shadow.getElementById('login-msg');
        const registerMsg = shadow.getElementById('register-msg');
        const recoverMsg = shadow.getElementById('recover-msg');

        if (loginMsg) { loginMsg.style.display = 'none'; loginMsg.className = 'msg'; loginMsg.textContent = ''; }
        if (registerMsg) { registerMsg.style.display = 'none'; registerMsg.className = 'msg'; registerMsg.textContent = ''; }
        if (recoverMsg) { recoverMsg.style.display = 'none'; recoverMsg.className = 'msg'; recoverMsg.textContent = ''; }

        const campoEmail = shadow.getElementById('recover-email-field');
        if (campoEmail) campoEmail.value = '';
        const campoS1 = shadow.getElementById('recover-new-password');
        const campoS2 = shadow.getElementById('recover-confirm-password');
        if (campoS1) campoS1.value = '';
        if (campoS2) campoS2.value = '';

        const divEmail = shadow.getElementById('etapa-email');
        const divSenha = shadow.getElementById('etapa-nova-senha');
        if (divEmail) divEmail.classList.remove('hidden');
        if (divSenha) divSenha.classList.add('hidden');

        const sub = shadow.getElementById('recover-subtitle');
        if (sub) sub.textContent = "Insira o seu e-mail cadastrado para redefinir a sua senha.";

        const telaLogin = shadow.getElementById('tela-de-login');
        const telaCadastro = shadow.getElementById('tela-de-cadastro');
        const telaRecuperacao = shadow.getElementById('tela-de-recuperacao');

        if (telaLogin && telaCadastro && telaRecuperacao) {
            telaLogin.classList.remove('hidden');
            telaCadastro.classList.add('hidden');
            telaRecuperacao.classList.add('hidden');
        }
    }

    setupEvents() {
        const shadow = this.shadowRoot;

        shadow.getElementById('go-to-register').onclick = () => {
            this.limparFormularios();
            shadow.getElementById('tela-de-login').classList.add('hidden');
            shadow.getElementById('tela-de-cadastro').classList.remove('hidden');
        };

        shadow.getElementById('go-to-login').onclick = () => {
            this.limparFormularios();
        };

        shadow.getElementById('go-to-recover').onclick = () => {
            this.limparFormularios();
            shadow.getElementById('tela-de-login').classList.add('hidden');
            shadow.getElementById('tela-de-recuperacao').classList.remove('hidden');
        };

        shadow.getElementById('recover-to-login').onclick = () => {
            this.limparFormularios();
        };

        shadow.getElementById('close-auth').onclick = () => {
            shadow.getElementById('auth-container').classList.add('hidden');
            this.limparFormularios();
        };

        // EVENTO: ETAPA 1 - VERIFICAR SE EMAIL EXISTE
        shadow.getElementById('btn-verificar-email').onclick = async () => {
            const emailInput = shadow.getElementById('recover-email-field').value;
            const msgDiv = shadow.getElementById('recover-msg');

            msgDiv.style.display = 'none';
            msgDiv.className = 'msg';

            if (!emailInput) {
                msgDiv.textContent = "Por favor, introduza o seu e-mail.";
                msgDiv.classList.add('error');
                msgDiv.style.display = 'block';
                return;
            }

            try {
                const response = await fetch(this.apiUrl + '/verificar-email', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ email: emailInput })
                });

                const result = await response.json();

                if (response.ok) {
                    this.emailRecuperacao = result.email; // Salva o e-mail validado na memória

                    // Transiciona para a Etapa 2 de Senha na mesma caixinha
                    shadow.getElementById('etapa-email').classList.add('hidden');
                    shadow.getElementById('etapa-nova-senha').classList.remove('hidden');
                    shadow.getElementById('recover-subtitle').textContent = "Escolha a sua nova senha abaixo:";
                } else {
                    msgDiv.textContent = result.erro || "E-mail não cadastrado.";
                    msgDiv.classList.add('error');
                    msgDiv.style.display = 'block';
                }
            } catch (err) {
                msgDiv.textContent = "Erro ao conectar com o servidor.";
                msgDiv.classList.add('error');
                msgDiv.style.display = 'block';
            }
        };

        shadow.getElementById('recover-form').onsubmit = async (e) => {
            e.preventDefault();
            const novaSenha = shadow.getElementById('recover-new-password').value;
            const confirmarSenha = shadow.getElementById('recover-confirm-password').value;
            const msgDiv = shadow.getElementById('recover-msg');

            msgDiv.style.display = 'none';
            msgDiv.className = 'msg';

            if (novaSenha !== confirmarSenha) {
                msgDiv.textContent = "As senhas não coincidem!";
                msgDiv.classList.add('error');
                msgDiv.style.display = 'block';
                return;
            }

            try {
                const response = await fetch(this.apiUrl + '/atualizar-senha-direta', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        email: this.emailRecuperacao,
                        senha: novaSenha
                    })
                });

                const result = await response.json();

                if (response.ok) {
                    msgDiv.textContent = result.mensagem || "Senha redefinida com sucesso!";
                    msgDiv.classList.add('success');
                    msgDiv.style.display = 'block';

                    setTimeout(() => {
                        this.limparFormularios();
                    }, 2000);
                } else {
                    msgDiv.textContent = result.erro || "Erro ao salvar nova senha.";
                    msgDiv.classList.add('error');
                    msgDiv.style.display = 'block';
                }
            } catch (err) {
                msgDiv.textContent = "Erro de conexão com o servidor.";
                msgDiv.classList.add('error');
                msgDiv.style.display = 'block';
            }
        };

        // Login Original
        shadow.getElementById('login-form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const dados = Object.fromEntries(formData);
            const msgDiv = shadow.getElementById('login-msg');
            msgDiv.style.display = 'none';
            msgDiv.className = 'msg';

            try {
                const response = await fetch(this.apiUrl + '/login', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });
                const result = await response.json();
                if (response.ok && result.status === 'success') {
                    msgDiv.textContent = "Bem-vindo(a)! Redirecionando...";
                    msgDiv.classList.add('success');
                    msgDiv.style.display = 'block';
                    setTimeout(() => {
                        shadow.getElementById('auth-container').classList.add('hidden');
                        this.limparFormularios();
                        window.location.reload();
                    }, 1000);
                } else {
                    msgDiv.textContent = result.erro || "E-mail ou senha incorretos.";
                    msgDiv.classList.add('error');
                    msgDiv.style.display = 'block';
                }
            } catch (err) {
                msgDiv.textContent = "Erro de conexão com o servidor.";
                msgDiv.classList.add('error');
                msgDiv.style.display = 'block';
            }
        };

        // Cadastro Original
        shadow.getElementById('register-form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const dados = Object.fromEntries(formData);
            const msgDiv = shadow.getElementById('register-msg');
            msgDiv.style.display = 'none';
            msgDiv.className = 'msg';

            if (dados.senha !== dados.confirmar_senha) {
                msgDiv.textContent = "As senhas não coincidem!";
                msgDiv.classList.add('error');
                msgDiv.style.display = 'block';
                return;
            }

            try {
                const response = await fetch(this.apiUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });
                if (response.status === 201 || response.ok) {
                    msgDiv.textContent = "Cadastro realizado com sucesso! Faça o login.";
                    msgDiv.classList.add('success');
                    msgDiv.style.display = 'block';
                    setTimeout(() => {
                        this.limparFormularios();
                        shadow.getElementById('tela-de-cadastro').classList.add('hidden');
                        shadow.getElementById('tela-de-login').classList.remove('hidden');
                    }, 2000);
                    return;
                }
                const result = await response.json();
                msgDiv.textContent = result.erro || "Erro ao processar cadastro.";
                msgDiv.classList.add('error');
                msgDiv.style.display = 'block';
            } catch (err) {
                this.limparFormularios();
                shadow.getElementById('tela-de-cadastro').classList.add('hidden');
                shadow.getElementById('tela-de-login').classList.remove('hidden');
            }
        };
    }
}

customElements.define('registro-login', RegistroLogin);