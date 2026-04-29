class RegistroLogin extends HTMLElement {
    constructor() {
        super();
        this.attachShadow({ mode: 'open' });
        this.apiUrl = 'http://localhost/DriverLux/public/api/usuarios';
    }

    connectedCallback() {
        this.render();
        this.setupEvents();
    }

    render() {
        const modo = this.getAttribute('modo') || 'popover';
        this.shadowRoot.innerHTML = `
        <style>
            :host { --primary: #6B00CC; font-family: 'Poppins', sans-serif; }

            .auth-overlay {
                position: fixed; top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(0,0,0,0.5);
                display: flex; justify-content: center; align-items: center;
                z-index: 9999;
            }

            .auth-card { 
                background: #fff; padding: 40px; border-radius: 12px;
                width: 100%; max-width: 400px;
                box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                position: relative;
            }

            .auth-card h2 { text-align: center; margin-top: 0; }
            .auth-card h3 { color: #747474; font-size: 0.9rem; text-align: center; margin-bottom: 20px; font-weight: normal; }
            .auth-overlay.modo-fluxo { position: relative; background: #F4F7F6; min-height: 600px; padding: 50px 0; }
            .auth-overlay.modo-fluxo .auth-card { max-width: 600px; box-shadow: none; border: 1px solid #ddd; }
            .auth-overlay.modo-fluxo .close-btn { display: none; }

            .main-btn { background: var(--primary); color: white; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; font-weight: bold; transition: background 0.3s; }
            .main-btn:hover { background: #5600a3; }

            input { width: 100%; padding: 12px; margin: 8px 0; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; font-family: inherit; }
            .hidden { display: none; }
            
            .footer-text { text-align: center; width: 100%; margin-top: 20px; font-size: 14px; }
            .footer-text span { color: var(--primary); cursor: pointer; font-weight: bold; }
            
            .close-btn { position: absolute; top: 10px; right: 15px; background: none; border: none; font-size: 24px; cursor: pointer; color: #999; }
            
            .msg { margin-top: 15px; font-size: 14px; display: none; text-align: center; padding: 10px; border-radius: 6px; border: 1px solid transparent; }
            .error { color: #d32f2f; background: #ffebee; border-color: #ffcdd2; }
            
        </style>

        <div id="auth-container" class="auth-overlay ${modo === 'fluxo' ? 'modo-fluxo' : ''}">
            <div class="auth-card">
                <button class="close-btn" id="close-auth">&times;</button>
                
                <div id="tela-de-login">
                    <h2>Login</h2>
                    <h3>Acesse sua conta para continuar.</h3>
                    <form id="login-form">
                        <input type="email" name="email" placeholder="E-mail" required>
                        <input type="password" name="senha" placeholder="Senha" required>
                        <button type="submit" class="main-btn">Entrar</button>
                    </form>
                    <div id="login-msg" class="msg error"></div>
                    <p class="footer-text">Novo por aqui? <span id="go-to-register">Cadastre-se</span></p>
                </div>

                <div id="tela-de-cadastro" class="hidden">
                    <h2>Crie sua conta</h2>
                    <form id="register-form">
                        <input type="text" name="nome" placeholder="Nome Completo" required>
                        <input type="text" name="cpf" placeholder="CPF" required>
                        <input type="email" name="email" placeholder="E-mail" required>
                        <input type="password" name="senha" placeholder="Senha" required>
                        <input type="password" name="confirmar_senha" placeholder="Confirmar Senha" required>
                        <button type="submit" class="main-btn">Cadastrar</button>
                    </form>
                    <div id="register-msg" class="msg error"></div>
                    <p class="footer-text">Já tem conta? <span id="go-to-login">Entrar</span></p>
                </div>
            </div>
        </div>
        `;
    }

    setupEvents() {
        const shadow = this.shadowRoot;

        // navegação de telas
        shadow.getElementById('go-to-register').onclick = () => {
            shadow.getElementById('login-form').reset();
            shadow.getElementById('login-msg').style.display = 'none';
            shadow.getElementById('tela-de-login').classList.add('hidden');
            shadow.getElementById('tela-de-cadastro').classList.remove('hidden');
        };
        shadow.getElementById('go-to-login').onclick = () => {
            shadow.getElementById('register-form').reset();
            shadow.getElementById('register-msg').style.display = 'none';
            shadow.getElementById('tela-de-cadastro').classList.add('hidden');
            shadow.getElementById('tela-de-login').classList.remove('hidden');
        };
        shadow.getElementById('close-auth').onclick = () => {
            shadow.getElementById('auth-container').classList.add('hidden');
        };

        // login
        shadow.getElementById('login-form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const dados = Object.fromEntries(formData);
            const msgDiv = shadow.getElementById('login-msg');
            msgDiv.style.display = 'none';

            try {
                const response = await fetch(this.apiUrl + '/login', { 
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(dados)
                });

                const result = await response.json();

                if (response.ok && result.status === 'success') {
                    alert("Bem-vindo(a), " + (result.nome || "Login realizado!"));
                    shadow.getElementById('auth-container').classList.add('hidden');
                    window.location.reload();

                } else {
                    msgDiv.textContent = result.erro || "E-mail ou senha incorretos.";
                    msgDiv.style.display = 'block';
                }

            } catch (err) {
                console.error("Erro ao fazer login:", err);
                msgDiv.textContent = "Erro de conexão com o servidor.";
                msgDiv.style.display = 'block';
            }
        };

        // cadastro
        shadow.getElementById('register-form').onsubmit = async (e) => {
            e.preventDefault();
            const formData = new FormData(e.target);
            const dados = Object.fromEntries(formData);
            const msgDiv = shadow.getElementById('register-msg');
            msgDiv.style.display = 'none';

            if (dados.senha !== dados.confirmar_senha) {
                msgDiv.textContent = "As senhas não coincidem!";
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
                    alert("Cadastro realizado com sucesso!");

                    e.target.reset();

                    shadow.getElementById('tela-de-cadastro').classList.add('hidden');
                    shadow.getElementById('tela-de-login').classList.remove('hidden');

                    return;
                }

                const result = await response.json();
                msgDiv.textContent = result.erro || "Erro ao processar cadastro.";
                msgDiv.style.display = 'block';

            } catch (err) {
                // força a troca de tela
                console.error("Erro de leitura, mas verificando status...");
                shadow.getElementById('tela-de-cadastro').classList.add('hidden');
                shadow.getElementById('tela-de-login').classList.remove('hidden');
            }
        };
    }
}

customElements.define('registro-login', RegistroLogin);