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
// AÇÃO: CADASTRAR NOVO CUPOM (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_cupom'])) {
    $codigo         = isset($_POST['codigo']) ? strtoupper(trim($_POST['codigo'])) : '';
    $descricao      = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $tipo_desconto  = isset($_POST['tipo_desconto']) ? $_POST['tipo_desconto'] : null;
    $valor_desconto = isset($_POST['valor_desconto']) ? (float)$_POST['valor_desconto'] : 0.00;
    $data_validade  = isset($_POST['data_validade']) ? $_POST['data_validade'] : null;
    $limite_usos    = !empty($_POST['limite_usos']) ? (int)$_POST['limite_usos'] : null;

    if (!empty($codigo) && !empty($tipo_desconto) && !empty($data_validade)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO cupons (codigo, descricao, tipo_desconto, valor_desconto, data_validade, limite_usos, ativo) 
                                   VALUES (:codigo, :descricao, :tipo_desconto, :valor_desconto, :data_validade, :limite_usos, 1)");
            
            $stmt->execute([
                ':codigo'         => $codigo,
                ':descricao'      => $descricao,
                ':tipo_desconto'  => $tipo_desconto,
                ':valor_desconto' => $valor_desconto,
                ':data_validade'  => $data_validade,
                ':limite_usos'    => $limite_usos
            ]);

            header("Location: cupons.php?sucesso=cadastrado");
            exit;
        } catch (PDOException $e) {
            die("Erro ao salvar cupom no banco de dados: " . $e->getMessage());
        }
    } else {
        die("Erro: Todos os campos obrigatórios (Código, Tipo e Validade) devem ser preenchidos.");
    }
}

// ==========================================
// NOVA AÇÃO: EDITAR CUPOM EXISTENTE (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_cupom'])) {
    $id_cupom       = isset($_POST['id_cupom']) ? (int)$_POST['id_cupom'] : 0;
    $codigo         = isset($_POST['codigo']) ? strtoupper(trim($_POST['codigo'])) : '';
    $descricao      = isset($_POST['descricao']) ? trim($_POST['descricao']) : '';
    $tipo_desconto  = isset($_POST['tipo_desconto']) ? $_POST['tipo_desconto'] : null;
    $valor_desconto = isset($_POST['valor_desconto']) ? (float)$_POST['valor_desconto'] : 0.00;
    $data_validade  = isset($_POST['data_validade']) ? $_POST['data_validade'] : null;
    $limite_usos    = !empty($_POST['limite_usos']) ? (int)$_POST['limite_usos'] : null;
    $ativo          = isset($_POST['ativo']) ? (int)$_POST['ativo'] : 0;

    if ($id_cupom > 0 && !empty($codigo) && !empty($tipo_desconto) && !empty($data_validade)) {
        try {
            $stmt = $pdo->prepare("UPDATE cupons SET 
                                    codigo = :codigo, 
                                    descricao = :descricao, 
                                    tipo_desconto = :tipo_desconto, 
                                    valor_desconto = :valor_desconto, 
                                    data_validade = :data_validade, 
                                    limite_usos = :limite_usos,
                                    ativo = :ativo
                                   WHERE id = :id");
            
            $stmt->execute([
                ':codigo'         => $codigo,
                ':descricao'      => $descricao,
                ':tipo_desconto'  => $tipo_desconto,
                ':valor_desconto' => $valor_desconto,
                ':data_validade'  => $data_validade,
                ':limite_usos'    => $limite_usos,
                ':ativo'          => $ativo,
                ':id'             => $id_cupom
            ]);

            header("Location: cupons.php?sucesso=editado");
            exit;
        } catch (PDOException $e) {
            die("Erro ao atualizar o cupom: " . $e->getMessage());
        }
    } else {
        die("Erro: Dados inválidos para edição do cupom.");
    }
}

// ==========================================
// BUSCAR TODOS OS CUPONS DO BANCO
// ==========================================
$stmt = $pdo->query("SELECT id, codigo, descricao, tipo_desconto, valor_desconto, data_validade, limite_usos, usos_atuais, ativo FROM cupons ORDER BY id DESC");
$cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Cupons — DriverLux Admin</title>
    <link rel="shortcut icon" href="../../../public/assets/img/corrida.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">
    
    <script>
        // Middleware de Autenticação
        window.addEventListener('load', async () => {
            try {
                const res  = await fetch('/DriverLux/public/api/usuarios/me');
                const data = await res.json();
                if (!data || !data.logado || !data.usuario) { window.location.href = '/DriverLux/index.html'; return; }
                const perfil = data.usuario.perfil;
                if (perfil !== 'administrador' && perfil !== 'admin') { window.location.href = '/DriverLux/index.html'; }
            } catch (err) { window.location.href = '/DriverLux/index.html'; }
        });

        // Função JavaScript para abrir o modal preenchido com os dados do cupom
        function abrirModalEditar(cupom) {
            document.getElementById('edit_id_cupom').value = cupom.id;
            document.getElementById('edit_codigo').value = cupom.codigo;
            document.getElementById('edit_descricao').value = cupom.descricao || '';
            document.getElementById('edit_tipo_desconto').value = cupom.tipo_desconto;
            document.getElementById('edit_valor_desconto').value = cupom.valor_desconto;
            document.getElementById('edit_limite_usos').value = cupom.limite_usos || '';
            document.getElementById('edit_data_validade').value = cupom.data_validade;
            document.getElementById('edit_ativo').value = cupom.ativo;

            document.getElementById('modalEditarCupom').classList.add('active');
        }
    </script>
</head>
<body>
<div class="admin-container">

    <aside id="sidebar">
        <div class="sidebar-logo"><img src="/DriverLux/public/assets/img/DriverLux2.png" alt="DriverLux"></div>
        <nav>
            <div class="nav-label">Menu</div>
            <a href="admin.php"><span class="nav-icon">🚗</span> Gestão de Frotas</a>
            <a href="reservas.php"><span class="nav-icon">📋</span> Reservas</a>
            <a href="usuarios.php"><span class="nav-icon">👥</span> Usuários</a>
            <a href="cupons.php" class="ativo"><span class="nav-icon">🏷️</span> Cupons</a>
        </nav>
    </aside>

    <main class="main-content">
        <header class="admin-topbar">
            <div class="topbar-titulo">
                <h1>Gestão de Cupons</h1>
                <p>Crie e monitorize os códigos promocionais de desconto vigentes no DriverLux.</p>
            </div>
            <div class="topbar-acoes">
                <button class="btn-adicionar" onclick="document.getElementById('modalCupom').classList.add('active')">＋ Novo Cupom</button>
            </div>
        </header>

        <div class="content-wrapper">
            
            <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastrado'): ?>
                <div class="alerta-sucesso" style="background: #edf7ed; border: 1.5px solid #c3e6cb; color: #1e4620; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                    <span>✅</span> Cupom promocional adicionado e ativado com sucesso!
                </div>
            <?php endif; ?>

            <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'editado'): ?>
                <div class="alerta-sucesso" style="background: #edf7ed; border: 1.5px solid #c3e6cb; color: #1e4620; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 600; display: flex; align-items: center; gap: 10px;">
                    <span>🔄</span> Cupom atualizado com sucesso!
                </div>
            <?php endif; ?>

            <div class="fleet-section" style="background:#fff; padding:20px; border-radius:8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <table style="width: 100%; border-collapse: collapse; text-align: left;">
                    <thead>
                        <tr style="border-bottom: 2px solid #eee; height: 40px; color:#777; font-size:14px;">
                            <th>Código</th>
                            <th>Descrição</th>
                            <th>Desconto</th>
                            <th>Usos / Limite</th>
                            <th>Validade</th>
                            <th>Status</th>
                            
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($cupons) > 0): ?>
                            <?php foreach($cupons as $cupom): 
                                if ($cupom['tipo_desconto'] === 'percentual') {
                                    $labelDesconto = (int)$cupom['valor_desconto'] . '%';
                                } else {
                                    $labelDesconto = 'R$ ' . number_format($cupom['valor_desconto'], 2, ',', '.');
                                }
                                
                                $limite = $cupom['limite_usos'] ?? '∞ (Ilimitado)';
                                $isAtivo = $cupom['ativo'] == 1 && strtotime($cupom['data_validade']) >= time();
                            ?>
                                <tr style="border-bottom: 1px solid #eee; height: 50px; font-size:14px;">
                                    <td><span class="tag-categoria" style="background: #e8f0fe; color: #1a73e8; padding: 4px 8px; font-family: monospace; font-size: 14px; border-radius: 4px; font-weight: bold;"><?= htmlspecialchars($cupom['codigo']) ?></span></td>
                                    <td><span style="color:#666; font-size:13px;"><?= htmlspecialchars($cupom['descricao'] ?? 'Sem descrição') ?></span></td>
                                    <td><strong><?= $labelDesconto ?></strong></td>
                                    <td><?= $cupom['usos_atuais'] ?> / <?= $limite ?></td>
                                    <td>📅 <?= date('d/m/Y', strtotime($cupom['data_validade'])) ?></td>
                                    <td>
                                        <span style="color: <?= $isAtivo ? '#2ecc71' : '#e74c3c' ?>; font-weight: bold;">
                                            <?= $isAtivo ? 'Ativo' : 'Inativo/Expirado' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button class="btn-adicionar" style="padding: 5px 10px; font-size: 12px; background-color: #3498db;" onclick='abrirModalEditar(<?= json_encode($cupom); ?>)'>Editar</button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" style="text-align: center; padding: 30px; color: #777;">Nenhum cupom cadastrado ainda.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<div id="modalCupom" class="modal">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
            <h2>Criar Cupom Promocional</h2>
            <button type="button" class="close-modal" onclick="document.getElementById('modalCupom').classList.remove('active')">×</button>
        </div>
        <form method="POST" action="cupons.php">
            <input type="hidden" name="cadastrar_cupom" value="1">
            <div class="modal-body" style="display:flex; flex-direction:column; gap:15px;">
                
                <div class="form-group">
                    <label style="font-weight: 500; font-size:14px;">Código do Cupom</label>
                    <input type="text" name="codigo" required placeholder="Ex: DRIVERLUX10" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; font-weight: bold; text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label style="font-weight: 500; font-size:14px;">Descrição / Motivo</label>
                    <input type="text" name="descricao" placeholder="Ex: Desconto de Boas-vindas" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Tipo de Desconto</label>
                        <select name="tipo_desconto" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; background:#fff;">
                            <option value="percentual">Percentual (%)</option>
                            <option value="valor_fixo">Valor Fixo (R$)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Valor do Desconto</label>
                        <input type="number" step="0.01" name="valor_desconto" required placeholder="Ex: 15.00" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Limite de Usos (Opcional)</label>
                        <input type="number" name="limite_usos" placeholder="Vazio para ilimitado" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Data de Validade</label>
                        <input type="date" name="data_validade" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                    </div>
                </div>

            </div>
            <div class="modal-footer" style="margin-top:20px;">
                <button type="submit" class="btn-save" style="width:100%; padding:12px;">Salvar e Ativar Cupom</button>
            </div>
        </form>
    </div>
</div>

<div id="modalEditarCupom" class="modal">
    <div class="modal-content" style="max-width: 480px;">
        <div class="modal-header">
            <h2>Editar Cupom Promocional</h2>
            <button type="button" class="close-modal" onclick="document.getElementById('modalEditarCupom').classList.remove('active')">×</button>
        </div>
        <form method="POST" action="cupons.php">
            <input type="hidden" name="editar_cupom" value="1">
            <input type="hidden" id="edit_id_cupom" name="id_cupom">
            
            <div class="modal-body" style="display:flex; flex-direction:column; gap:15px;">
                
                <div class="form-group">
                    <label style="font-weight: 500; font-size:14px;">Código do Cupom</label>
                    <input type="text" id="edit_codigo" name="codigo" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; font-weight: bold; text-transform: uppercase;">
                </div>

                <div class="form-group">
                    <label style="font-weight: 500; font-size:14px;">Descrição / Motivo</label>
                    <input type="text" id="edit_descricao" name="descricao" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Tipo de Desconto</label>
                        <select id="edit_tipo_desconto" name="tipo_desconto" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; background:#fff;">
                            <option value="percentual">Percentual (%)</option>
                            <option value="valor_fixo">Valor Fixo (R$)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Valor do Desconto</label>
                        <input type="number" step="0.01" id="edit_valor_desconto" name="valor_desconto" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                    </div>
                </div>

                <div style="display:grid; grid-template-columns: 1fr 1fr; gap:15px;">
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Limite de Usos (Opcional)</label>
                        <input type="number" id="edit_limite_usos" name="limite_usos" placeholder="Vazio para ilimitado" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                    </div>
                    <div class="form-group">
                        <label style="font-weight: 500; font-size:14px;">Data de Validade</label>
                        <input type="date" id="edit_data_validade" name="data_validade" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc;">
                    </div>
                </div>

                <div class="form-group">
                    <label style="font-weight: 500; font-size:14px;">Status do Cupom</label>
                    <select id="edit_ativo" name="ativo" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ccc; background:#fff; font-weight: bold;">
                        <option value="1" style="color: #2ecc71;">Ativo</option>
                        <option value="0" style="color: #e74c3c;">Desativado</option>
                    </select>
                </div>

            </div>
            <div class="modal-footer" style="margin-top:20px;">
                <button type="submit" class="btn-save" style="width:100%; padding:12px; background-color: #3498db;">Atualizar Alterações</button>
            </div>
        </form>
    </div>
</div>
</body>
</html>