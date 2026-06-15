<?php
session_start();
require_once __DIR__ . '/../../../config/db_connect.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// ==========================================
// AÇÃO: CONFIRMAR DEVOLUÇÃO DO VEÍCULO
// ==========================================
if (isset($_GET['finalizar_reserva']) && isset($_GET['id']) && isset($_GET['veiculo_id'])) {
    $idReserva = (int) $_GET['id'];
    $idVeiculo = (int) $_GET['veiculo_id'];

    $pdo->beginTransaction();
    try {
        // 1. Atualiza status da reserva para 'concluida'
        $stmtReserva = $pdo->prepare("UPDATE reservas SET status = 'concluida' WHERE id = :id");
        $stmtReserva->execute([':id' => $idReserva]);

        // 2. Libera o veículo na frota de volta para 'livre'
        $stmtVeiculo = $pdo->prepare("UPDATE veiculos SET status_disponibilidade = 'livre' WHERE id = :veiculo_id");
        $stmtVeiculo->execute([':veiculo_id' => $idVeiculo]);

        $pdo->commit();
        header("Location: reservas.php?sucesso=devolvido");
        exit;
    } catch (Exception $e) {
        $pdo->rollBack();
        die("Erro ao processar devolução: " . $e->getMessage());
    }
}

// ==========================================
// BUSCAR RESERVAS (Ajustado com os campos corretos)
// ==========================================
$stmt = $pdo->query("SELECT r.id, r.data_retirada, r.data_devolucao, r.status, r.valor_total_previsto, u.nome as cliente, v.marca, v.modelo, v.id as veiculo_id 
                     FROM reservas r 
                     INNER JOIN usuarios u ON r.usuario_id = u.id 
                     INNER JOIN veiculos v ON r.veiculo_id = v.id 
                     ORDER BY r.id DESC");
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Controle de Reservas — DriverLux Admin</title>
    <link rel="shortcut icon" href="../../../public/assets/img/corrida.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">

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
                    <h1>Controle de Reservas</h1>
                    <p>Verifique o andamento de locações e confirme a devolução dos veículos à frota.</p>
                </div>
            </header>

            <div class="content-wrapper">

                <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'devolvido'): ?>
                    <div class="alerta-sucesso"
                        style="background: #edf7ed; border: 1.5px solid #c3e6cb; color: #1e4620; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                        <span class="alerta-icon">✅</span>
                        Devolução confirmada! O veículo foi liberado e está disponível para novos aluguéis.
                    </div>
                <?php endif; ?>

                <div class="fleet-section"
                    style="background:#fff; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                    <table style="width: 100%; border-collapse: collapse; text-align: left;">
                        <thead>
                            <tr style="border-bottom: 2px solid #eee; height: 40px; color:#777; font-size:14px;">
                                <th>ID</th>
                                <th>Cliente</th>
                                <th>Veículo</th>
                                <th>Retirada / Devolução</th>
                                <th>Total Previsto</th>
                                <th>Status</th>
                                <th>Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($reservas) > 0): ?>
                                <?php foreach ($reservas as $res):
                                    // Mapeamento de cores baseado no seu ENUM
                                    $statusMap = [
                                        'pendente' => ['cor' => '#e67e22', 'txt' => 'Pendente'],
                                        'confirmada' => ['cor' => '#3498db', 'txt' => 'Confirmada'],
                                        'em_andamento' => ['cor' => '#f1c40f', 'txt' => 'Em Andamento'],
                                        'concluida' => ['cor' => '#2ecc71', 'txt' => 'Concluída'],
                                        'cancelada' => ['cor' => '#95a5a6', 'txt' => 'Cancelada']
                                    ];
                                    $st = $statusMap[$res['status']] ?? ['cor' => '#7f8c8d', 'txt' => $res['status']];
                                    ?>
                                    <tr style="border-bottom: 1px solid #eee; height: 55px; font-size:14px;">
                                        <td>#<?= $res['id'] ?></td>
                                        <td><strong><?= htmlspecialchars($res['cliente']) ?></strong></td>
                                        <td><?= htmlspecialchars($res['marca'] . ' ' . $res['modelo']) ?></td>
                                        <td style="font-size: 13px; color:#555;">
                                            📅 <?= date('d/m/Y H:i', strtotime($res['data_retirada'])) ?><br>
                                            🏁 <?= date('d/m/Y H:i', strtotime($res['data_devolucao'])) ?>
                                        </td>
                                        <td><strong>R$ <?= number_format($res['valor_total_previsto'], 2, ',', '.') ?></strong>
                                        </td>
                                        <td><span style="color: <?= $st['cor'] ?>; font-weight: bold;"><?= $st['txt'] ?></span>
                                        </td>
                                        <td>
                                            <?php if ($res['status'] === 'em_andamento'): ?>
                                                <a href="reservas.php?finalizar_reserva=1&id=<?= $res['id'] ?>&veiculo_id=<?= $res['veiculo_id'] ?>"
                                                    onclick="return confirm('Confirmar que o cliente devolveu o veículo com sucesso?')"
                                                    style="padding: 6px 12px; background-color: #2ecc71; color: #fff; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; display: inline-block;">
                                                    ✓ Confirmar Devolução
                                                </a>
                                            <?php else: ?>
                                                <span style="color:#aaa; font-size:12px;">Sem pendências</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" style="text-align: center; padding: 30px; color: #777;">
                                        Nenhuma reserva registrada no sistema.
                                    </td>
                                </tr>
                            <?php endif; ?>
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