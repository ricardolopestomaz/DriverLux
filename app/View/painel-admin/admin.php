<?php
session_start();

// ==========================================
// 1. CONEXÃO COM O BANCO DE DADOS
// ==========================================
$host   = 'localhost';
$dbname = 'driverlux';
$user   = 'root';
$pass   = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// ==========================================
// PROTEÇÃO: VERIFICAÇÃO DE ADMIN (ANTES DE QUALQUER OUTPUT) ✅ CRÍTICO
// ==========================================
$isAdmin = false;
$adminNome = "";

if (isset($_SESSION['usuario_id']) && isset($_SESSION['usuario_perfil'])) {
    // Usar dados da sessão PHP
    $isAdmin = ($_SESSION['usuario_perfil'] === 'administrador' || $_SESSION['usuario_perfil'] === 'admin');
    $adminNome = $_SESSION['usuario_nome'] ?? 'Admin';
} else {
    // Sessão não encontrada - Redirecionar para home
    header('Location: /DriverLux/');
    exit;
}

// Se não for admin, redirecionar imediatamente
if (!$isAdmin) {
    header('Location: /DriverLux/');
    exit;
}

// ==========================================
// PROTEÇÃO: GARANTE QUE AS COLUNAS EXISTAM
// ==========================================
try {
    $pdo->exec("ALTER TABLE veiculos ADD COLUMN ativo TINYINT(1) DEFAULT 1");
} catch (PDOException $e) { 
    // Se der erro, é porque a coluna já existe, então ignoramos.
}

try {
    $pdo->exec("ALTER TABLE veiculos ADD COLUMN preco_diaria DECIMAL(10,2) DEFAULT 1500.00");
} catch (PDOException $e) { 
    // Se der erro, é porque a coluna já existe, então ignoramos.
}

// ==========================================
// AÇÃO: OCULTAR/EXIBIR VEÍCULO (COM PREPARED STATEMENT)
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
// 1.5. SEED DE CATEGORIA ÚNICA (LUXO)
// ==========================================
$checkCategorias = $pdo->query("SELECT COUNT(*) FROM categorias_veiculos")->fetchColumn();
if ($checkCategorias == 0) {
    $pdo->exec("INSERT IGNORE INTO categorias_veiculos (id, nome, descricao, valor_base_diaria) VALUES
        (1, 'Luxo', 'Carros de luxo e exclusivos', 1500.00)");
}

// Categoria fixa para luxo
$categoria_id = 1;
$nome_categoria_atual = 'Luxo';

// ==========================================
// 2. CADASTRAR OU EDITAR VEÍCULO (POST)
// ==========================================
$erro_mensagem = null;
$sucesso_mensagem = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo                  = trim($_POST['modelo']);
    $marca                   = trim($_POST['marca']);
    $ano                     = (int)$_POST['ano'];
    $placa                   = trim(strtoupper($_POST['placa']));
    $chassi                  = trim(strtoupper($_POST['chassi']));
    $preco_diaria            = isset($_POST['preco_diaria']) ? (float)str_replace(',', '.', $_POST['preco_diaria']) : 1500.00;
    $status_disponibilidade  = $_POST['status_disponibilidade'];
    $veiculo_id              = isset($_POST['veiculo_id']) ? (int)$_POST['veiculo_id'] : null;

    // Validações básicas
    if (empty($modelo) || empty($marca) || $ano < 2000 || $ano > 2100 || empty($placa) || empty($chassi)) {
        $erro_mensagem = "❌ Dados inválidos. Por favor, preencha todos os campos corretamente.";
    } else {
        // Verificar se placa já existe (exceto para o veículo sendo editado)
        $stmtPlaca = $pdo->prepare("SELECT COUNT(*) FROM veiculos WHERE placa = :placa AND id != :id");
        $stmtPlaca->execute([':placa' => $placa, ':id' => $veiculo_id ?? 0]);
        $placaExiste = $stmtPlaca->fetchColumn() > 0;

        if ($placaExiste) {
            $erro_mensagem = "❌ Erro: A placa '{$placa}' já está cadastrada no sistema!";
        } else {
            // Verificar se chassi já existe (exceto para o veículo sendo editado)
            $stmtChassi = $pdo->prepare("SELECT COUNT(*) FROM veiculos WHERE chassi = :chassi AND id != :id");
            $stmtChassi->execute([':chassi' => $chassi, ':id' => $veiculo_id ?? 0]);
            $chassiExiste = $stmtChassi->fetchColumn() > 0;

            if ($chassiExiste) {
                $erro_mensagem = "❌ Erro: O chassi '{$chassi}' já está cadastrado no sistema!";
            }
        }
    }

    // Se passou nas validações, processar imagem
    $imagem_url = null;
    if (!$erro_mensagem && isset($_FILES['foto_carro']) && $_FILES['foto_carro']['error'] === UPLOAD_ERR_OK) {
        $pastaDestino = __DIR__ . '/../public/assets/img/veiculos/';
        if (!is_dir($pastaDestino)) mkdir($pastaDestino, 0755, true);
        
        $extensao      = strtolower(pathinfo($_FILES['foto_carro']['name'], PATHINFO_EXTENSION));
        $extensoesValidas = ['jpg', 'jpeg', 'png', 'webp'];
        
        if (!in_array($extensao, $extensoesValidas)) {
            $erro_mensagem = "❌ Formato de imagem inválido. Use PNG, JPG, JPEG ou WebP.";
        } else {
            $nomeArquivo   = 'carro_' . uniqid() . '.' . $extensao;
            $caminhoCompleto = $pastaDestino . $nomeArquivo;
            
            if (move_uploaded_file($_FILES['foto_carro']['tmp_name'], $caminhoCompleto)) {
                $imagem_url = '/DriverLux/public/assets/img/veiculos/' . $nomeArquivo;
            } else {
                $erro_mensagem = "❌ Erro ao fazer upload da imagem. Tente novamente.";
            }
        }
    }

    // ═══ CADASTRAR NOVO VEÍCULO ═══
    if (!$erro_mensagem && isset($_POST['cadastrar_veiculo'])) {
        if (empty($imagem_url)) {
            $imagem_url = '/DriverLux/public/assets/img/default-car.png';
        }
        
        try {
            $stmt = $pdo->prepare("INSERT INTO veiculos 
                                   (categoria_id, marca, modelo, ano, placa, chassi, imagem_url, status_disponibilidade, preco_diaria) 
                                   VALUES (:categoria_id, :marca, :modelo, :ano, :placa, :chassi, :imagem_url, :status_disponibilidade, :preco_diaria)");
            
            $stmt->execute([
                ':categoria_id' => 1,
                ':marca' => $marca,
                ':modelo' => $modelo,
                ':ano' => $ano,
                ':placa' => $placa,
                ':chassi' => $chassi,
                ':imagem_url' => $imagem_url,
                ':status_disponibilidade' => $status_disponibilidade,
                ':preco_diaria' => $preco_diaria
            ]);
            
            header("Location: admin.php?sucesso=cadastrado");
            exit;
        } catch (PDOException $e) {
            $erro_mensagem = "❌ Erro ao cadastrar: A placa pode estar duplicada ou dados inválidos.";
        }

    // ═══ EDITAR VEÍCULO ═══
    } elseif (!$erro_mensagem && isset($_POST['editar_veiculo'])) {
        $veiculo_id = (int)$_POST['veiculo_id'];
        
        try {
            if ($imagem_url) {
                // Com imagem nova
                $stmt = $pdo->prepare("UPDATE veiculos 
                                       SET categoria_id = :categoria_id, marca = :marca, modelo = :modelo, 
                                           ano = :ano, placa = :placa, chassi = :chassi, 
                                           imagem_url = :imagem_url, status_disponibilidade = :status_disponibilidade,
                                           preco_diaria = :preco_diaria
                                       WHERE id = :id");
                $stmt->execute([
                    ':categoria_id' => 1,
                    ':marca' => $marca,
                    ':modelo' => $modelo,
                    ':ano' => $ano,
                    ':placa' => $placa,
                    ':chassi' => $chassi,
                    ':imagem_url' => $imagem_url,
                    ':status_disponibilidade' => $status_disponibilidade,
                    ':preco_diaria' => $preco_diaria,
                    ':id' => $veiculo_id
                ]);
            } else {
                // Sem imagem nova (manter a atual)
                $stmt = $pdo->prepare("UPDATE veiculos 
                                       SET categoria_id = :categoria_id, marca = :marca, modelo = :modelo, 
                                           ano = :ano, placa = :placa, chassi = :chassi, 
                                           status_disponibilidade = :status_disponibilidade,
                                           preco_diaria = :preco_diaria
                                       WHERE id = :id");
                $stmt->execute([
                    ':categoria_id' => 1,
                    ':marca' => $marca,
                    ':modelo' => $modelo,
                    ':ano' => $ano,
                    ':placa' => $placa,
                    ':chassi' => $chassi,
                    ':status_disponibilidade' => $status_disponibilidade,
                    ':preco_diaria' => $preco_diaria,
                    ':id' => $veiculo_id
                ]);
            }
            
            header("Location: admin.php?sucesso=editado");
            exit;
        } catch (PDOException $e) {
            $erro_mensagem = "❌ Erro ao atualizar: A placa pode estar duplicada ou dados inválidos.";
        }
    }
}

// ==========================================
// 3. BUSCAR VEÍCULOS (APENAS LUXO)
// ==========================================
$stmtBusca = $pdo->prepare("SELECT v.* FROM veiculos v WHERE v.categoria_id = :categoria_id ORDER BY v.id DESC");
$stmtBusca->execute([':categoria_id' => 1]);
$veiculos = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Frotas — DriverLux Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/DriverLux/public/assets/css/admin.css">

    <script>
        /* Função para voltar à home com controle */
        function voltarParaHome(event) {
            event.preventDefault();
            sessionStorage.setItem('voltandoDoAdmin', 'true');
            // Use relative navigation so it resolves regardless of server base
            window.location.href = '../home.html';
        }

        /* Verificação de admin - Usar dados do PHP ✅ REDUNDÂNCIA SEGURA */
        const usuarioAdminNome = "<?= htmlspecialchars($adminNome, ENT_QUOTES, 'UTF-8') ?>";
        const isAdminUser = <?= $isAdmin ? 'true' : 'false' ?>;

        // Se não for admin (redundante, mas por segurança)
        if (!isAdminUser) {
            window.location.href = './';
        }
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
                <div class="sidebar-perfil-nome" id="exibe-nome-admin"><?= htmlspecialchars($adminNome) ?></div>
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
            <div class="nav-sep"></div>
            <a href="../home.html" onclick="voltarParaHome(event)">
                <span class="nav-icon">🏠</span> Voltar para Home
            </a>
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
                <p>Gerencie todos os veículos de luxo DriverLux</p>
            </div>
            <div class="topbar-acoes">
                <button class="btn-adicionar" onclick="abrirModalNovo()">
                    <span>＋</span> Adicionar Veículo
                </button>
            </div>
        </header>

        <div class="content-wrapper">

            <!-- Alertas de erro -->
            <?php if ($erro_mensagem): ?>
                <div class="alerta-erro" style="background-color: #fee; border: 1px solid #fcc; color: #c33; padding: 15px; border-radius: 6px; margin-bottom: 20px;">
                    <span style="font-weight: bold;">
                        <?= htmlspecialchars($erro_mensagem) ?>
                    </span>
                </div>
            <?php endif; ?>

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
                            $isAtivo = (isset($carro['ativo']) && $carro['ativo'] == 1) || !isset($carro['ativo']);
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
                                            <span class="preco-val"><?= number_format($carro['preco_diaria'] ?? 1500, 0, ',', '.') ?></span>
                                            <span class="preco-per">/dia</span>
                                        </div>
                                        <button type="button" class="btn-editar"
                                                onclick='abrirModalEditar(<?= htmlspecialchars(json_encode($carro, JSON_HEX_APOS | JSON_HEX_QUOT), ENT_QUOTES, "UTF-8") ?>)'>
                                            ✏ Editar
                                        </button>
                                        <a href="admin.php?toggle_ativo=1&id=<?= $carro['id'] ?>" 
                                            style="padding: 6px 12px; color: #fff; border-radius: 6px; text-decoration: none; font-size: 12px; font-weight: bold; margin-right: 5px; background-color: <?= $isAtivo ? '#e74c3c' : '#2ecc71' ?>;">
                                        <?= $isAtivo ? 'Ocultar do Site' : 'Devolver ao Site' ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="grid-vazio">
                            <div class="grid-vazio-icon">🚗</div>
                            <h4>Nenhum veículo de luxo cadastrado</h4>
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
            <h2 id="modal-titulo">Novo Veículo de Luxo</h2>
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
                        <input type="text" name="marca" id="v_marca" required placeholder="Ex: Mercedes-Benz">
                    </div>
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" id="v_modelo" required placeholder="Ex: S-Class">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ano</label>
                        <input type="number" name="ano" id="v_ano" required placeholder="Ex: 2024" min="2000" max="2100">
                    </div>
                    <div class="form-group">
                        <label>Placa</label>
                        <input type="text" name="placa" id="v_placa" required placeholder="Ex: ABC-1234">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 1;">
                        <label>Chassi</label>
                        <input type="text" name="chassi" id="v_chassi" required placeholder="17 caracteres">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Preço Diário (R$)</label>
                        <input type="number" name="preco_diaria" id="v_preco" step="0.01" min="0" value="1500.00" required placeholder="Ex: 1500.00">
                    </div>
                    <div class="form-group">
                        <label>Status de Disponibilidade</label>
                        <select name="status_disponibilidade" id="v_status" required>
                            <option value="livre">Livre</option>
                            <option value="alugado">Alugado</option>
                            <option value="manutencao">Em Manutenção</option>
                        </select>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Foto do Veículo</label>
                        <input type="file" name="foto_carro" id="v_foto"
                               accept="image/png, image/jpeg, image/webp"
                               onchange="previewImagem(event)">
                        <small id="dica-foto" class="dica-foto" style="display:none; color:#999; font-size:11px; margin-top:5px;">
                            Deixe em branco para manter a foto atual.
                        </small>
                    </div>
                </div>

                <!-- Preview da imagem -->
                <div class="preview-wrap" id="preview-wrap" style="display:none; margin-top:15px;">
                    <img id="preview-img" src="" alt="Preview" style="max-width:100%; border-radius:6px;">
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
            reader.onload = e => { 
                preview.src = e.target.result; 
                wrap.style.display = 'block'; 
            };
            reader.readAsDataURL(event.target.files[0]);
        }
    }

    /* Abre modal para novo veículo */
    function abrirModalNovo() {
        document.getElementById('modal-titulo').innerText = "Novo Veículo de Luxo";
        document.getElementById('acao-form').name = "cadastrar_veiculo";
        document.getElementById('acao-form').value = "1";
        document.getElementById('v_id').value = "";
        document.getElementById('form-veiculo').reset();
        document.getElementById('v_foto').required = true;
        document.getElementById('dica-foto').style.display = 'none';
        document.getElementById('preview-wrap').style.display = 'none';
        document.getElementById('v_status').value = "livre";
        document.getElementById('modalVeiculo').classList.add('active');
    }

    /* Abre modal para editar */
    function abrirModalEditar(carro) {
        document.getElementById('modal-titulo').innerText = "Editar Veículo de Luxo";
        document.getElementById('acao-form').name = "editar_veiculo";
        document.getElementById('acao-form').value = "1";
        document.getElementById('v_id').value = carro.id;
        document.getElementById('v_modelo').value = carro.modelo;
        document.getElementById('v_marca').value = carro.marca;
        document.getElementById('v_ano').value = carro.ano;
        document.getElementById('v_placa').value = carro.placa;
        document.getElementById('v_chassi').value = carro.chassi;
        document.getElementById('v_preco').value = carro.preco_diaria || 1500.00;
        document.getElementById('v_status').value = carro.status_disponibilidade;
        document.getElementById('v_foto').required = false;
        document.getElementById('dica-foto').style.display = 'block';
        
        const wrap = document.getElementById('preview-wrap');
        const img  = document.getElementById('preview-img');
        img.src = carro.imagem_url; 
        wrap.style.display = 'block';
        
        document.getElementById('modalVeiculo').classList.add('active');
    }

/* Logout */
    async function logout() {
        try {
            await fetch('/DriverLux/public/api/usuarios/logout', { method: 'POST' });
        } catch (_) {}
        window.location.href = '/DriverLux/app/View/home.html';
    }

    /* Fechar modal clicando no overlay */
    document.getElementById('modalVeiculo').addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
</script>
</body>
</html>