<?php
session_start();
require_once __DIR__ . '/../../../config/db_connect.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// AÇÃO: ATIVAR / DESATIVAR USUÁRIO
if (isset($_GET['toggle_status']) && isset($_GET['id'])) {
    $idUser = (int) $_GET['id'];
    // Inverte o status (se ativo = 1 vai para 0, se 0 vai para 1)
    $stmtToggle = $pdo->prepare("UPDATE usuarios SET ativo = IF(ativo = 1, 0, 1) WHERE id = :id");
    $stmtToggle->execute([':id' => $idUser]);
    header("Location: usuarios.php?sucesso=status_alterado");
    exit;
}

// BUSCA E FILTRO DE USUÁRIOS
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
if (!empty($search)) {
    $stmt = $pdo->prepare("SELECT id, nome, email, perfil, ativo, criado_em FROM usuarios WHERE nome LIKE :search OR email LIKE :search ORDER BY id DESC");
    $stmt->execute([':search' => "%$search%"]);
} else {
    $stmt = $pdo->query("SELECT id, nome, email, perfil, ativo, criado_em FROM usuarios ORDER BY id DESC");
}
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Usuários — DriverLux</title>
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <script>
        window.addEventListener('load', async () => {
            try {
                const res = await fetch('/DriverLux/public/api/usuarios/me');
                const data = await res.json();
                if (!data || !data.logado || !data.usuario) { window.location.href = '/DriverLux/index.html'; return; }
                const perfil = data.usuario.perfil;
                if (perfil !== 'administrador' && perfil !== 'admin') { window.location.href = '/DriverLux/index.html'; }
            } catch (err) { window.location.href = '/DriverLux/index.html'; }
        });
    </script>
</head>

<body>
    <div class="admin-container">
        <aside id="sidebar">
            <div class="sidebar-logo">
                <img src="/DriverLux/public/assets/img/DriverLux2.png" alt="DriverLux">
            </div>

            <div class="sidebar-perfil">
                <img id="sidebar-avatar" class="sidebar-avatar" src="/DriverLux/public/assets/img/default-avatar.png"
                    alt="Admin">
                <div class="sidebar-perfil-info">
                    <div class="sidebar-perfil-tag">Administrador</div>
                    <div class="sidebar-perfil-nome" id="exibe-nome-admin">Carregando…</div>
                </div>
            </div>

            <nav>
                <div class="nav-label">Menu</div>

                <a href="admin.php" class="<?= basename($_SERVER['PHP_SELF']) == 'admin.php' ? 'ativo' : '' ?>">
                    <span class="nav-icon">🚗</span> Gestão de Frotas
                </a>

                <a href="reservas.php" class="<?= basename($_SERVER['PHP_SELF']) == 'reservas.php' ? 'ativo' : '' ?>">
                    <span class="nav-icon">📋</span> Reservas
                </a>

                <a href="/DriverLux/app/View/painel-admin/usuarios.php"
                    class="<?= basename($_SERVER['PHP_SELF']) == 'usuarios.php' ? 'ativo' : '' ?>">
                    <span class="nav-icon">👥</span> Usuários
                </a>

                <a href="cupons.php" class="<?= basename($_SERVER['PHP_SELF']) == 'cupons.php' ? 'ativo' : '' ?>">
                    <span class="nav-icon">🏷️</span> Cupons
                </a>

                <a href="/DriverLux/index.html">
                    <span class="nav-icon">🏠</span> Home
                </a>

                <div class="nav-sep"></div>
            </nav>

            <button class="logout-btn" onclick="logout()">
                <span>🚪</span> Sair do Sistema
            </button>
        </aside>


        <main class="main-content">
            <header class="admin-topbar">
                <div class="topbar-titulo">
                    <h1>Gestão de Usuários</h1>
                    <p>Controle permissões, busque e ative/desative contas de clientes e administradores.</p>
                </div>
            </header>

            <div class="content-wrapper">
                <div class="filtros" style="display: flex; gap: 10px; align-items: center;">
                    <form method="GET" action="usuarios.php" style="display:flex; gap:10px; width:100%;">
                        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>"
                            placeholder="Buscar por nome ou e-mail..."
                            style="padding: 10px; border-radius: 6px; border: 1px solid #ccc; flex: 1;">
                        <button type="submit" class="btn-adicionar" style="padding: 10px 20px;">Buscar</button>
                        <?php if (!empty($search)): ?>
                            <a href="usuarios.php" class="category-btn"
                                style="text-decoration:none; line-height:2.5;">Limpar</a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="fleet-section"
                    style="margin-top: 20px; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #eee; height: 40px; color: #777;">
                                <th>ID</th>
                                <th>Nome</th>
                                <th>E-mail</th>
                                <th>Perfil</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($usuarios as $user):
                                $isAtivo = (int) $user['ativo'] === 1;

                                $corStatus = $isAtivo ? '#2ecc71' : '#e74c3c';
                                $txtStatus = $isAtivo ? 'Ativo' : 'Inativo';
                                $corBtn = $isAtivo ? 'background-color: #e74c3c;' : 'background-color: #2ecc71;';
                                $txtBtn = $isAtivo ? 'Desativar' : 'Ativar';
                                ?>
                                <tr style="border-bottom: 1px solid #eee; height: 50px;">
                                    <td>#<?= $user['id'] ?></td>
                                    <td><strong><?= htmlspecialchars($user['nome']) ?></strong></td>
                                    <td><?= htmlspecialchars($user['email']) ?></td>
                                    <td><span class="tag-categoria"><?= ucfirst(htmlspecialchars($user['perfil'])) ?></span>
                                    </td>
                                    <td><span style="color: <?= $corStatus ?>; font-weight: bold;"><?= $txtStatus ?></span>
                                    </td>
                                    <td>
                                        <a href="usuarios.php?toggle_status=1&id=<?= $user['id'] ?>"
                                            style="padding: 6px 12px; color: #fff; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; <?= $corBtn ?>">
                                            <?= $txtBtn ?>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>
</body>

<script>

    /* Carrega dados do admin na sidebar */
    window.addEventListener('DOMContentLoaded', async () => {
        try {
            const res = await fetch('/DriverLux/public/api/usuarios/me');
            const data = await res.json();
            if (data.logado && data.usuario) {
                document.getElementById('exibe-nome-admin').textContent = data.usuario.nome;
                if (data.usuario.foto_perfil) {
                    document.getElementById('sidebar-avatar').src = data.usuario.foto_perfil;
                }
            }
        } catch (e) { console.error("Erro", e); }
    });

    /* Logout */
    async function logout() {
        try {
            await fetch('/DriverLux/public/api/usuarios/logout', { method: 'POST' });
        } catch (_) { }
        window.location.href = '/DriverLux/index.html';
    }
</script>

</html>