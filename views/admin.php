<?php
session_start();

// ==========================================
// 1. CONEXÃO COM O BANCO DE DADOS
// ==========================================
$host = 'localhost';
$dbname = 'driverlux';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão: " . $e->getMessage());
}

// ==========================================
// 1.5. CRIAÇÃO AUTOMÁTICA DE CATEGORIAS 
// ==========================================
$checkCategorias = $pdo->query("SELECT COUNT(*) FROM categorias_veiculos")->fetchColumn();
if ($checkCategorias == 0) {
    $sqlSeed = "INSERT IGNORE INTO categorias_veiculos (id, nome, descricao, valor_base_diaria) VALUES 
                (1, 'Econômico', 'Carros populares e eficientes', 150.00),
                (2, 'Plus', 'Carros executivos e confortáveis', 350.00),
                (3, 'Max', 'Supercarros e veículos exclusivos', 1200.00)";
    $pdo->exec($sqlSeed);
}

// ==========================================
// 2. CADASTRAR OU EDITAR VEÍCULO (POST)
// ==========================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $modelo = $_POST['modelo'];
    $marca = $_POST['marca'];
    $ano = $_POST['ano'];
    $placa = $_POST['placa'];
    $chassi = $_POST['chassi']; 
    $categoria_id = $_POST['categoria_id'];
    $status_disponibilidade = $_POST['status_disponibilidade']; 
    
    // Lógica de Upload da Imagem (Usada tanto para cadastrar quanto para editar)
    $imagem_url = null; 
    if (isset($_FILES['foto_carro']) && $_FILES['foto_carro']['error'] === UPLOAD_ERR_OK) {
        $pastaDestino = __DIR__ . '/../public/assets/img/veiculos/';
        if (!is_dir($pastaDestino)) mkdir($pastaDestino, 0755, true);
        
        $extensao = pathinfo($_FILES['foto_carro']['name'], PATHINFO_EXTENSION);
        $nomeArquivo = 'carro_' . uniqid() . '.' . strtolower($extensao);
        $caminhoCompleto = $pastaDestino . $nomeArquivo;
        
        if (move_uploaded_file($_FILES['foto_carro']['tmp_name'], $caminhoCompleto)) {
            $imagem_url = '/DriverLux/public/assets/img/veiculos/' . $nomeArquivo;
        }
    }

    // VERIFICA SE É CADASTRO OU EDIÇÃO
    if (isset($_POST['cadastrar_veiculo'])) {
        // --- NOVO CADASTRO ---
        if (empty($imagem_url)) {
            $imagem_url = '/DriverLux/public/assets/img/default-car.png'; 
        }

        $sql = "INSERT INTO veiculos (categoria_id, marca, modelo, ano, placa, chassi, imagem_url, status_disponibilidade) 
                VALUES (:categoria_id, :marca, :modelo, :ano, :placa, :chassi, :imagem_url, :status_disponibilidade)";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':categoria_id' => $categoria_id, ':marca' => $marca, ':modelo' => $modelo, ':ano' => $ano, 
            ':placa' => $placa, ':chassi' => $chassi, ':imagem_url' => $imagem_url, ':status_disponibilidade' => $status_disponibilidade
        ]);

        header("Location: admin.php?categoria_id=" . $categoria_id . "&sucesso=cadastrado");
        exit;

    } elseif (isset($_POST['editar_veiculo'])) {
        // --- EDIÇÃO ---
        $veiculo_id = $_POST['veiculo_id'];

        if ($imagem_url) {
            // Se mandou foto nova, atualiza a foto também
            $sql = "UPDATE veiculos SET categoria_id = :categoria_id, marca = :marca, modelo = :modelo, ano = :ano, 
                    placa = :placa, chassi = :chassi, imagem_url = :imagem_url, status_disponibilidade = :status_disponibilidade 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':categoria_id' => $categoria_id, ':marca' => $marca, ':modelo' => $modelo, ':ano' => $ano, 
                ':placa' => $placa, ':chassi' => $chassi, ':imagem_url' => $imagem_url, 
                ':status_disponibilidade' => $status_disponibilidade, ':id' => $veiculo_id
            ]);
        } else {
            // Se NÃO mandou foto nova, atualiza só os dados e mantém a foto antiga
            $sql = "UPDATE veiculos SET categoria_id = :categoria_id, marca = :marca, modelo = :modelo, ano = :ano, 
                    placa = :placa, chassi = :chassi, status_disponibilidade = :status_disponibilidade 
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':categoria_id' => $categoria_id, ':marca' => $marca, ':modelo' => $modelo, ':ano' => $ano, 
                ':placa' => $placa, ':chassi' => $chassi, ':status_disponibilidade' => $status_disponibilidade, ':id' => $veiculo_id
            ]);
        }

        header("Location: admin.php?categoria_id=" . $categoria_id . "&sucesso=editado");
        exit;
    }
}

// ==========================================
// 3. BUSCAR VEÍCULOS POR CATEGORIA (GET)
// ==========================================
$categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : 1;
$nomes_categorias = [1 => 'ECONÔMICO', 2 => 'PLUS', 3 => 'MAX'];
$nome_categoria_atual = $nomes_categorias[$categoria_id] ?? 'ECONÔMICO';

$sqlBusca = "SELECT v.*, c.valor_base_diaria 
             FROM veiculos v 
             INNER JOIN categorias_veiculos c ON v.categoria_id = c.id 
             WHERE v.categoria_id = :categoria_id 
             ORDER BY v.id DESC";
             
$stmtBusca = $pdo->prepare($sqlBusca);
$stmtBusca->execute([':categoria_id' => $categoria_id]);
$veiculos = $stmtBusca->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Frotas - DriverLux Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../public/assets/css/admin.css">

    <script>
        window.addEventListener('load', async () => {
            try {
                const res = await fetch('/DriverLux/public/api/usuarios/me');
                const data = await res.json();
                if (!data || !data.logado || !data.usuario) {
                    window.location.href = '/DriverLux/views/home.html';
                    return;
                }
                const perfil = data.usuario.perfil;
                if (perfil !== 'administrador' && perfil !== 'admin') {
                    window.location.href = '/DriverLux/views/home.html'; 
                }
            } catch (err) {
                window.location.href = '/DriverLux/views/home.html';
            }
        });
    </script>
</head>
<body>
    <div class="admin-container">
        <aside id="sidebar">
            <div class="sidebar-logo">
                <img src="/DriverLux/public/assets/img/logo.png" class="logo" alt="DriverLux" style="width: 100%; max-width: 170px;">
            </div>
            <nav>
                <a href="admin.php" class="ativo">Gestão de Frotas</a>
                <a href="#">Reservas</a>
                <a href="#">Usuários</a>
                <a href="/DriverLux/views/home.html">Voltar para Home</a>
            </nav>
            <button class="logout-btn" onclick="logout()">Sair do Sistema</button>
        </aside>

        <main class="main-content">
            <header class="admin-header-premium">
                <div class="perfil-admin-top">
                    <div class="avatar-wrapper">
                        <img id="admin-foto-preview" src="../public/assets/img/default-avatar.png" alt="Admin">
                    </div>
                    <div class="admin-txt">
                        <span class="tag-admin">Painel Administrativo</span>
                        <h1 id="exibe-nome-admin">Administrador</h1>
                    </div>
                </div>
            </header>

            <div class="content-wrapper">
                
                <?php if(isset($_GET['sucesso']) && $_GET['sucesso'] == 'cadastrado'): ?>
                    <div style="background: var(--sucesso); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        ✅ Veículo cadastrado com sucesso!
                    </div>
                <?php elseif(isset($_GET['sucesso']) && $_GET['sucesso'] == 'editado'): ?>
                    <div style="background: var(--sucesso); color: white; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                        ✏️ Veículo atualizado com sucesso!
                    </div>
                <?php endif; ?>

                <div class="filtros">
                    <span style="font-weight: 600;">Categoria:</span>
                    <a href="admin.php?categoria_id=1" class="category-btn <?= $categoria_id == 1 ? 'active' : '' ?>">Econômico</a>
                    <a href="admin.php?categoria_id=2" class="category-btn <?= $categoria_id == 2 ? 'active' : '' ?>">Plus</a>
                    <a href="admin.php?categoria_id=3" class="category-btn <?= $categoria_id == 3 ? 'active' : '' ?>">Max</a>
                    <button class="btn-adicionar" onclick="abrirModalNovo()">+ Adicionar Veículo</button>
                </div>

                <div class="fleet-section">
                    <div class="section-header">
                        <h3>Carros - <?= $nome_categoria_atual ?></h3>
                    </div>
                    
                    <div class="grid-veiculos">
                        <?php if(count($veiculos) > 0): ?>
                            <?php foreach($veiculos as $carro): ?>
                                <div class="card-veiculo">
                                    <div class="card-img-wrapper">
                                        <img src="<?= htmlspecialchars($carro['imagem_url']) ?>" alt="<?= htmlspecialchars($carro['modelo']) ?>">
                                    </div>
                                    <div class="card-info">
                                        <span class="tag-categoria"><?= $nome_categoria_atual ?></span>
                                        <h3><?= htmlspecialchars($carro['marca']) ?> <?= htmlspecialchars($carro['modelo']) ?></h3>
                                        <p>Ano: <?= htmlspecialchars($carro['ano']) ?> | Placa: <?= htmlspecialchars($carro['placa']) ?></p>
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <div class="card-preco">
                                                R$ <?= number_format($carro['valor_base_diaria'], 2, ',', '.') ?><span>/dia</span>
                                            </div>
                                            <button type="button" style="padding: 5px 15px; border: none; background: #eee; cursor: pointer; border-radius: 5px; font-weight: 600;" 
                                                    onclick='abrirModalEditar(<?= json_encode($carro, JSON_HEX_APOS | JSON_HEX_QUOT) ?>)'>
                                                Editar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p style="grid-column: 1/-1; text-align: center; color: var(--cinza);">Nenhum veículo encontrado nesta categoria.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <div id="modalVeiculo" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2 id="modal-titulo">Novo Veículo</h2>
                <button type="button" class="close-modal" onclick="document.getElementById('modalVeiculo').classList.remove('active')">&times;</button>
            </div>
            
            <form id="form-veiculo" method="POST" action="admin.php" enctype="multipart/form-data">
                <input type="hidden" name="cadastrar_veiculo" id="acao-form" value="1">
                <input type="hidden" name="veiculo_id" id="v_id" value="">
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Modelo</label>
                        <input type="text" name="modelo" id="v_modelo" required placeholder="Ex: Corolla">
                    </div>
                    <div class="form-group">
                        <label>Marca</label>
                        <input type="text" name="marca" id="v_marca" required placeholder="Ex: Toyota">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Ano</label>
                        <input type="number" name="ano" id="v_ano" required placeholder="Ex: 2023">
                    </div>
                    <div class="form-group">
                        <label>Placa</label>
                        <input type="text" name="placa" id="v_placa" required placeholder="Ex: ABC-1234">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Chassi</label>
                        <input type="text" name="chassi" id="v_chassi" required placeholder="Ex: 12345678901234567">
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
                        <label>Foto do Veículo</label>
                        <input type="file" name="foto_carro" id="v_foto" accept="image/png, image/jpeg, image/webp" required onchange="previewImagem(event)" style="padding: 10px; border: 1.5px solid var(--borda2); border-radius: 10px; background: var(--cinza-bg); width: 100%;">
                        <small id="dica-foto" style="display: none; color: #666; margin-top: 5px;">Deixe em branco para manter a foto atual.</small>
                        
                        <div style="margin-top: 15px; text-align: center;">
                            <img id="preview-img" src="" style="display: none; width: 100%; max-height: 180px; object-fit: contain; border-radius: 10px; border: 1px solid #ddd;">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>Status de Disponibilidade</label>
                        <select name="status_disponibilidade" id="v_status">
                            <option value="livre">Livre</option>
                            <option value="alugado">Alugado</option>
                            <option value="manutencao">Em Manutenção</option>
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn-cancel" onclick="document.getElementById('modalVeiculo').classList.remove('active')">Cancelar</button>
                    <button type="submit" class="btn-save">Salvar Veículo</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // PREVIEW DE IMAGEM
        function previewImagem(event) {
            const input = event.target;
            const preview = document.getElementById('preview-img');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                // Se cancelar a seleção no input, e for edição, pode esconder ou manter vazio.
                if (document.getElementById('acao-form').name === 'cadastrar_veiculo') {
                    preview.src = '';
                    preview.style.display = 'none';
                }
            }
        }

        // PREPARA O MODAL PARA UM NOVO CARRO
        function abrirModalNovo() {
            document.getElementById('modal-titulo').innerText = "Novo Veículo";
            document.getElementById('acao-form').name = "cadastrar_veiculo"; // Avisa o PHP que é um Cadastro
            document.getElementById('v_id').value = ""; // Limpa ID
            
            document.getElementById('form-veiculo').reset(); // Limpa todos os campos
            
            document.getElementById('v_foto').required = true; // Foto é obrigatória no cadastro
            document.getElementById('dica-foto').style.display = 'none';
            document.getElementById('preview-img').style.display = 'none';
            document.getElementById('preview-img').src = '';
            
            // Força a categoria no select para ser a categoria que estamos a visualizar na grelha
            document.getElementById('v_categoria').value = "<?= $categoria_id ?>";

            document.getElementById('modalVeiculo').classList.add('active');
        }

        // PREPARA O MODAL PARA EDITAR UM CARRO
        function abrirModalEditar(carro) {
            document.getElementById('modal-titulo').innerText = "Editar Veículo";
            document.getElementById('acao-form').name = "editar_veiculo"; // Avisa o PHP que é uma Edição
            document.getElementById('v_id').value = carro.id; // Envia o ID para o PHP saber qual atualizar
            
            // Preenche os campos com os dados do banco de dados
            document.getElementById('v_modelo').value = carro.modelo;
            document.getElementById('v_marca').value = carro.marca;
            document.getElementById('v_ano').value = carro.ano;
            document.getElementById('v_placa').value = carro.placa;
            document.getElementById('v_chassi').value = carro.chassi;
            document.getElementById('v_categoria').value = carro.categoria_id;
            document.getElementById('v_status').value = carro.status_disponibilidade;
            
            // Na edição, a foto NÃO é obrigatória
            document.getElementById('v_foto').required = false; 
            document.getElementById('dica-foto').style.display = 'block';
            
            // Mostra a foto atual que veio do banco de dados
            const preview = document.getElementById('preview-img');
            preview.src = carro.imagem_url;
            preview.style.display = 'block';

            document.getElementById('modalVeiculo').classList.add('active');
        }

        // CARREGA DADOS DO ADMIN
        window.addEventListener('DOMContentLoaded', async () => {
            try {
                const res = await fetch('/DriverLux/public/api/usuarios/me');
                const data = await res.json();
                if (data.logado && data.usuario) {
                    document.getElementById('exibe-nome-admin').textContent = data.usuario.nome;
                    if (data.usuario.foto_perfil) {
                        document.getElementById('admin-foto-preview').src = data.usuario.foto_perfil;
                    }
                }
            } catch (e) { console.error("Erro", e); }
        });

        // LOGOUT
        async function logout() {
            try {
                await fetch('/DriverLux/public/api/usuarios/logout', { method: 'POST' });
                window.location.href = '/DriverLux/views/home.html';
            } catch (error) {
                window.location.href = '/DriverLux/views/home.html';
            }
        }
    </script>
</body>
</html>