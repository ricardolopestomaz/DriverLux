<?php
session_start();

// ==========================================
// 1. CONEXÃO COM O BANCO DE DADOS
// ==========================================
require_once __DIR__ . '/../../../config/db_connect.php';

try {
    $database = new Database();
    $pdo = $database->getConnection();
} catch (Exception $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}

// ==========================================
// AÇÃO: OCULTAR/EXIBIR VEÍCULO (Preparado/Parametrizado)
// ==========================================
if (isset($_GET['toggle_ativo']) && isset($_GET['id'])) {
    $idToggle = (int)$_GET['id'];
    
    $stmtToggle = $pdo->prepare("UPDATE veiculos SET ativo = IF(ativo = 1, 0, 1) WHERE id = :id");
    $stmtToggle->execute([':id' => $idToggle]);
    
    // Recarrega a página para atualizar a tabela
    header("Location: admin.php");
    exit;
}

// ==========================================
// 2. CADASTRAR OU EDITAR VEÍCULO (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo                  = $_POST['modelo'];
    $marca                   = $_POST['marca'];
    $ano                     = $_POST['ano'];
    $placa                   = $_POST['placa'];
    $chassi                  = $_POST['chassi'];
    $categoria_id            = $_POST['categoria_id'];
    $status_disponibilidade  = $_POST['status_disponibilidade'];

    $imagem_url = null;
    if (isset($_FILES['foto_carro']) && $_FILES['foto_carro']['error'] === UPLOAD_ERR_OK) {
        $pastaDestino = __DIR__ . '/../../../public/assets/img/veiculos/';
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0755, true);
        }
        $extensao      = pathinfo($_FILES['foto_carro']['name'], PATHINFO_EXTENSION);
        $nomeArquivo   = 'carro_' . uniqid() . '.' . strtolower($extensao);
        $caminhoCompleto = $pastaDestino . $nomeArquivo;
        if (move_uploaded_file($_FILES['foto_carro']['tmp_name'], $caminhoCompleto)) {
            $imagem_url = '/DriverLux/public/assets/img/veiculos/' . $nomeArquivo;
        }
    }

    if (isset($_POST['cadastrar_veiculo'])) {
        if (empty($imagem_url)) {
            $imagem_url = '/DriverLux/public/assets/img/default-car.png';
        }
        $stmt = $pdo->prepare("INSERT INTO veiculos (categoria_id, marca, modelo, ano, placa, chassi, imagem_url, status_disponibilidade)
                               VALUES (:categoria_id, :marca, :modelo, :ano, :placa, :chassi, :imagem_url, :status_disponibilidade)");
        $stmt->execute([
            ':categoria_id' => $categoria_id,
            ':marca' => $marca,
            ':modelo' => $modelo,
            ':ano' => $ano,
            ':placa' => $placa,
            ':chassi' => $chassi,
            ':imagem_url' => $imagem_url,
            ':status_disponibilidade' => $status_disponibilidade
        ]);
        header("Location: admin.php?categoria_id=$categoria_id&sucesso=cadastrado");
        exit;

    } elseif (isset($_POST['editar_veiculo'])) {
        $veiculo_id = $_POST['veiculo_id'];
        if ($imagem_url) {
            $stmt = $pdo->prepare("UPDATE veiculos SET categoria_id=:categoria_id, marca=:marca, modelo=:modelo, ano=:ano,
                                   placa=:placa, chassi=:chassi, imagem_url=:imagem_url, status_disponibilidade=:status_disponibilidade WHERE id=:id");
            $stmt->execute([
                ':categoria_id' => $categoria_id,
                ':marca' => $marca,
                ':modelo' => $modelo,
                ':ano' => $ano,
                ':placa' => $placa,
                ':chassi' => $chassi,
                ':imagem_url' => $imagem_url,
                ':status_disponibilidade' => $status_disponibilidade,
                ':id' => $veiculo_id
            ]);
        } else {
            $stmt = $pdo->prepare("UPDATE veiculos SET categoria_id=:categoria_id, marca=:marca, modelo=:modelo, ano=:ano,
                                   placa=:placa, chassi=:chassi, status_disponibilidade=:status_disponibilidade WHERE id=:id");
            $stmt->execute([
                ':categoria_id' => $categoria_id,
                ':marca' => $marca,
                ':modelo' => $modelo,
                ':ano' => $ano,
                ':placa' => $placa,
                ':chassi' => $chassi,
                ':status_disponibilidade' => $status_disponibilidade,
                ':id' => $veiculo_id
            ]);
        }
        header("Location: admin.php?categoria_id=$categoria_id&sucesso=editado");
        exit;
    }
}

// ==========================================
// 3. BUSCAR VEÍCULOS
// ==========================================
$categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 1;
$nomes_categorias = [1 => 'Econômico', 2 => 'Plus', 3 => 'Max'];
$nome_categoria_atual = $nomes_categorias[$categoria_id] ?? 'Econômico';

$stmtBusca = $pdo->prepare("SELECT v.*, c.valor_base_diaria
                             FROM veiculos v INNER JOIN categorias_veiculos c ON v.categoria_id = c.id
                             WHERE v.categoria_id = :categoria_id ORDER BY v.id DESC");
$stmtBusca->execute([':categoria_id' => $categoria_id]);
$veiculos = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Frotas — DriverLux Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/admin.css">

    <script>
        window.addEventListener('load', async () => {
            try {
                const res  = await fetch('/DriverLux/public/api/usuarios/me');
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

    <!-- ═══ SIDEBAR ═══ -->
    <aside id="sidebar">
        <div class="sidebar-logo">
            <img src="/DriverLux/public/assets/img/logo.png" alt="DriverLux">
        </div>

        <div class="sidebar-perfil">
            <img id="sidebar-avatar" class="sidebar-avatar" src="/DriverLux/public/assets/img/default-avatar.png" alt="Admin">
            <div class="sidebar-perfil-info">
                <div class="sidebar-perfil-tag">Administrador</div>
                <div class="sidebar-perfil-nome" id="exibe-nome-admin">Carregando…</div>
            </div>
        </div>

        <nav>
            <div class="nav-label">Menu</div>
            <a href="admin.php" class="ativo">
                <span class="nav-icon">🚗</span> Gestão de Frotas
            </a>
            <a href="#">
                <span class="nav-icon">📋</span> Reservas
            </a>
            <a href="#">
                <span class="nav-icon">👥</span> Usuários
            </a>
            <a href="#">
                <span class="nav-icon">🏷️</span> Cumpons
            </a>
            <a href="#">
                <span class="nav-icon">🛡️</span> Proteções
            </a>
            <div class="nav-sep"></div>
        </nav>

        <button class="logout-btn" onclick="logout()">
            <span>🚪</span> Sair do Sistema
        </button>
    </aside>

    <!-- ═══ CONTEÚDO ═══ -->
    <main class="main-content">

        <!-- topbar -->
        <header class="admin-topbar">
            <div class="topbar-titulo">
                <h1>Gestão de Frotas</h1>
                <p>Gerencie todos os veículos da frota DriverLux</p>
            </div>
            <div class="topbar-acoes">
                <button class="btn-adicionar" onclick="abrirModalNovo()">
                    <span>＋</span> Adicionar Veículo
                </button>
            </div>
        </header>

        <div class="content-wrapper">

            <!-- Alertas de sucesso -->
            <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] === 'cadastrado'): ?>
                <div class="alerta-sucesso">
                    <span class="alerta-icon">✅</span>
                    Veículo cadastrado com sucesso!
                </div>
            <?php elseif (isset($_GET['sucesso']) && $_GET['sucesso'] === 'editado'): ?>
                <div class="alerta-sucesso">
                    <span class="alerta-icon">✏️</span>
                    Veículo atualizado com sucesso!
                </div>
            <?php endif; ?>

            <!-- Filtros de categoria -->
            <div class="filtros">
                <span class="filtros-label">Categoria:</span>
                <a href="admin.php?categoria_id=1" class="category-btn <?= $categoria_id == 1 ? 'active' : '' ?>">Econômico</a>
                <a href="admin.php?categoria_id=2" class="category-btn <?= $categoria_id == 2 ? 'active' : '' ?>">Plus</a>
                <a href="admin.php?categoria_id=3" class="category-btn <?= $categoria_id == 3 ? 'active' : '' ?>">Max</a>
                <button class="btn-adicionar" onclick="abrirModalNovo()" style="margin-left:auto">
                    <span>＋</span> Novo Veículo
                </button>
            </div>

            <!-- Grid de veículos -->
            <div class="fleet-section">
                <div class="section-header">
                    <h3><?= htmlspecialchars($nome_categoria_atual) ?></h3>
                    <span class="section-count"><?= count($veiculos) ?> veículo<?= count($veiculos) !== 1 ? 's' : '' ?></span>
                </div>

                <div class="grid-veiculos">
                    <?php if (count($veiculos) > 0): ?>
                        <?php foreach ($veiculos as $carro):
                            $statusMap = [
                                'livre'      => ['classe' => 'status-livre',      'label' => 'Disponível'],
                                'alugado'    => ['classe' => 'status-alugado',    'label' => 'Alugado'],
                                'manutencao' => ['classe' => 'status-manutencao', 'label' => 'Manutenção'],
                            ];
                            $st = $statusMap[$carro['status_disponibilidade']] ?? ['classe'=>'status-livre','label'=>'Livre'];
                        ?>
                            <div class="card-veiculo">
                                <div class="card-img-wrapper">
                                    <img src="<?= htmlspecialchars($carro['imagem_url']) ?>"
                                         alt="<?= htmlspecialchars($carro['modelo']) ?>">
                                    <span class="status-badge <?= $st['classe'] ?>"><?= $st['label'] ?></span>
                                </div>
                                <div class="card-info">
                                    <span class="tag-categoria"><?= htmlspecialchars($nome_categoria_atual) ?></span>
                                    <h3><?= htmlspecialchars($carro['marca']) ?> <?= htmlspecialchars($carro['modelo']) ?></h3>
                                    <div class="card-meta">
                                        <span><?= htmlspecialchars($carro['ano']) ?></span>
                                        <span class="dot"></span>
                                        <span><?= htmlspecialchars($carro['placa']) ?></span>
                                    </div>
                                    <div class="card-footer">
                                        <div class="card-preco">
                                            <span class="preco-rs">R$</span>
                                            <span class="preco-val"><?= number_format($carro['valor_base_diaria'], 0, ',', '.') ?></span>
                                            <span class="preco-per">/dia</span>
                                        </div>
                                        <button type="button" class="btn-editar"
                                                onclick='abrirModalEditar(<?= json_encode($carro, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                            ✏ Editar
                                        </button>
                                        <?php 
                                        // Verifica se o carro está ativo (se for null, assume 1 por padrão)
                                        $isAtivo = !isset($carro['ativo']) || $carro['ativo'] == 1; 
                                        $corBtn = $isAtivo ? 'background-color: #e74c3c;' : 'background-color: #2ecc71;'; // Vermelho para Ocultar, Verde para Exibir
                                        $textoBtn = $isAtivo ? 'Ocultar do Site' : 'Devolver ao Site';
                                        ?>
                                        <a href="admin.php?toggle_ativo=1&id=<?= $carro['id'] ?>" 
                                            style="padding: 6px 12px; color: #fff; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; margin-right: 5px; <?= $corBtn ?>">
                                        <?= $textoBtn ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="grid-vazio">
                            <div class="grid-vazio-icon">🚗</div>
                            <h4>Nenhum veículo nesta categoria</h4>
                            <p>Clique em "Novo Veículo" para adicionar o primeiro.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

        </div><!-- /content-wrapper -->
    </main>
</div><!-- /admin-container -->

<!-- ═══ MODAL ═══ -->
<div id="modalVeiculo" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="modal-titulo">Novo Veículo</h2>
            <button type="button" class="close-modal"
                    onclick="document.getElementById('modalVeiculo').classList.remove('active')">×</button>
        </div>

        <form id="form-veiculo" method="POST" action="admin.php" enctype="multipart/form-data">
            <input type="hidden" name="cadastrar_veiculo" id="acao-form" value="1">
            <input type="hidden" name="veiculo_id" id="v_id" value="">

            <div class="modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" id="v_marca" required placeholder="Ex: Toyota">
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" id="v_modelo" required placeholder="Ex: Corolla">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ano</label>
                        <input type="number" name="ano" id="v_ano" required placeholder="Ex: 2024">
                    </div>
                    <div class="form-group">
                        <label>Placa</label>
                        <input type="text" name="placa" id="v_placa" required placeholder="Ex: ABC-1234">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Chassi</label>
                        <input type="text" name="chassi" id="v_chassi" required placeholder="17 caracteres">
                    </div>
                    <div class="form-group">
                        <label>Categoria</label>
                        <select name="categoria_id" id="v_categoria" required>
                            <option value="1">Econômico</option>
                            <option value="2">Plus</option>
                            <option value="3">Max</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Status de Disponibilidade</label>
                        <select name="status_disponibilidade" id="v_status">
                            <option value="livre">Livre</option>
                            <option value="alugado">Alugado</option>
                            <option value="manutencao">Em Manutenção</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Foto do Veículo</label>
                        <input type="file" name="foto_carro" id="v_foto"
                               accept="image/png, image/jpeg, image/webp"
                               required onchange="previewImagem(event)">
                        <small id="dica-foto" class="dica-foto" style="display:none">
                            Deixe em branco para manter a foto atual.
                        </small>
                    </div>
                </div>

                <!-- Preview da imagem -->
                <div class="preview-wrap" id="preview-wrap" style="display:none">
                    <img id="preview-img" src="" alt="Preview">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancel"
                        onclick="document.getElementById('modalVeiculo').classList.remove('active')">
                    Cancelar
                </button>
                <button type="submit" class="btn-save">💾 Salvar Veículo</button>
            </div>
        </form>
    </div>
</div>

<script>
    /* Preview de imagem */
    function previewImagem(event) {
        const preview  = document.getElementById('preview-img');
        const wrap     = document.getElementById('preview-wrap');
        if (event.target.files && event.target.files[0]) {
            const reader = new FileReader();
            reader.onload = e => { preview.src = e.target.result; wrap.style.display = 'flex'; };
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    /* Abre modal para novo veículo */
    function abrirModalNovo() {
        document.getElementById('modal-titulo').innerText = "Novo Veículo";
        document.getElementById('acao-form').name = "cadastrar_veiculo";
        document.getElementById('v_id').value = "";
        document.getElementById('form-veiculo').reset();
        document.getElementById('v_foto').required = true;
        document.getElementById('dica-foto').style.display = 'none';
        document.getElementById('preview-wrap').style.display = 'none';
        document.getElementById('v_categoria').value = "<?= $categoria_id ?>";
        document.getElementById('modalVeiculo').classList.add('active');
    }

    /* Abre modal para editar */
    function abrirModalEditar(carro) {
        document.getElementById('modal-titulo').innerText = "Editar Veículo";
        document.getElementById('acao-form').name = "editar_veiculo";
        document.getElementById('v_id').value        = carro.id;
        document.getElementById('v_modelo').value    = carro.modelo;
        document.getElementById('v_marca').value     = carro.marca;
        document.getElementById('v_ano').value       = carro.ano;
        document.getElementById('v_placa').value     = carro.placa;
        document.getElementById('v_chassi').value    = carro.chassi;
        document.getElementById('v_categoria').value = carro.categoria_id;
        document.getElementById('v_status').value    = carro.status_disponibilidade;
        document.getElementById('v_foto').required   = false;
        document.getElementById('dica-foto').style.display = 'block';
        const wrap = document.getElementById('preview-wrap');
        const img  = document.getElementById('preview-img');
        img.src = carro.imagem_url; wrap.style.display = 'flex';
        document.getElementById('modalVeiculo').classList.add('active');
    }

    /* Carrega dados do admin na sidebar */
    window.addEventListener('DOMContentLoaded', async () => {
        try {
            const res  = await fetch('/DriverLux/public/api/usuarios/me');
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
        } catch (_) {}
        window.location.href = '/DriverLux/index.html';
    }

    /* Fechar modal clicando no overlay */
    document.getElementById('modalVeiculo').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
</script>
</body>
</html>