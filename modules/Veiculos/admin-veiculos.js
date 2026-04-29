// ============================================
// ADMIN VEÍCULOS - GESTÃO DE FROTAS
// Integração com API VeiculoController
// ============================================

const API_BASE_URL = 'http://localhost/DriverLux'; // 🔧 Ajuste com sua URL
let selectedCategory = {
    id: 1,
    nome: 'ECONÔMICO'
};
let allVehicles = [];
let editingVehicleId = null;

// ============================================
// 1️⃣ INICIALIZAÇÃO E CARREGAMENTO DE DADOS
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    loadVehicles();
});

/**
 * Carrega todos os veículos da API
 */
function loadVehicles() {
    const container = document.getElementById('vehiclesContainer');
    container.innerHTML = '<div class="loading"><div class="spinner"></div></div>';

    fetch(`${API_BASE_URL}/veiculos`, {
        method: 'GET',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include' // Envia cookies de sessão
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Erro HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.status === 'success') {
            allVehicles = data.data || [];
            renderVehicles();
        } else {
            showError('Erro ao carregar veículos');
            container.innerHTML = '<div class="empty-state"><p>Erro ao carregar dados</p></div>';
        }
    })
    .catch(error => {
        console.error('Erro ao carregar veículos:', error);
        showError('Erro de conexão com a API');
        container.innerHTML = '<div class="empty-state"><p>Falha ao conectar à API</p></div>';
    });
}

/**
 * Renderiza os veículos filtrados por categoria
 */
function renderVehicles() {
    const container = document.getElementById('vehiclesContainer');
    
    // Filtra veículos pela categoria selecionada
    const vehiclesFiltered = allVehicles.filter(v => {
        const categoryMap = {
            1: 'ECONÔMICO',
            2: 'PLUS',
            3: 'MAX'
        };
        return categoryMap[selectedCategory.id] === selectedCategory.nome;
    });

    if (vehiclesFiltered.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-2h2v2zm0-4h-2V7h2v6z"/>
                </svg>
                <h3>Nenhum veículo encontrado</h3>
                <p>Adicione o primeiro veículo desta categoria clicando em "Adicionar Veículo"</p>
            </div>
        `;
        return;
    }

    const tableHTML = `
        <table class="vehicles-table">
            <thead>
                <tr>
                    <th>Veículo</th>
                    <th>Placa</th>
                    <th>Ano</th>
                    <th>Preço Diária</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                ${vehiclesFiltered.map(vehicle => createVehicleRow(vehicle)).join('')}
            </tbody>
        </table>
    `;

    container.innerHTML = tableHTML;
}

/**
 * Cria uma linha da tabela para um veículo
 */
function createVehicleRow(vehicle) {
    const statusClass = vehicle.status_disponibilidade === 'disponivel' 
        ? 'status-disponivel' 
        : 'status-indisponivel';
    
    const statusText = vehicle.status_disponibilidade === 'disponivel' 
        ? 'DISPONÍVEL' 
        : vehicle.status_disponibilidade.toUpperCase();

    const priceDaily = vehicle.valor_base_diaria 
        ? `R$ ${parseFloat(vehicle.valor_base_diaria).toFixed(2).replace('.', ',')}`
        : 'N/A';

    return `
        <tr>
            <td>
                <div class="vehicle-info">
                    <div class="vehicle-thumb">🚗</div>
                    <div class="vehicle-details">
                        <h4>${vehicle.marca} ${vehicle.modelo}</h4>
                        <small>ID: ${vehicle.id}</small>
                    </div>
                </div>
            </td>
            <td>${vehicle.placa || '-'}</td>
            <td>${vehicle.ano || '-'}</td>
            <td>
                <div class="price-input">
                    <span>${priceDaily}</span>
                </div>
            </td>
            <td>
                <span class="status-badge ${statusClass}">${statusText}</span>
            </td>
            <td>
                <div class="actions">
                    <button class="action-btn edit-btn" onclick="editVehicle(${vehicle.id})">✏️ EDITAR</button>
                    <button class="action-btn delete-btn" onclick="deleteVehicle(${vehicle.id})">🗑️ DELETAR</button>
                </div>
            </td>
        </tr>
    `;
}

// ============================================
// 2️⃣ SELEÇÃO DE CATEGORIA
// ============================================

/**
 * Seleciona uma categoria de veículos
 */
function selectCategory(id, nome, button) {
    // Remove a classe 'active' de todos os botões
    document.querySelectorAll('.category-btn').forEach(btn => {
        btn.classList.remove('active');
    });

    // Adiciona a classe 'active' ao botão clicado
    button.classList.add('active');

    // Atualiza a categoria selecionada
    selectedCategory = { id, nome };

    // Atualiza o título da seção
    const categoryNames = {
        1: 'ECONÔMICO',
        2: 'PLUS',
        3: 'MAX'
    };

    document.getElementById('fleetTitle').textContent = `Carros - ${categoryNames[id]}`;

    // Mostra a seção de frotas
    document.getElementById('fleetSection').classList.add('active');

    // Renderiza os veículos da categoria
    renderVehicles();
}

// ============================================
// 3️⃣ MODAL - ADICIONAR/EDITAR VEÍCULO
// ============================================

/**
 * Abre o modal para adicionar um novo veículo
 */
function openModal() {
    editingVehicleId = null;
    document.getElementById('modalTitle').textContent = 'Adicionar Veículo';
    document.getElementById('vehicleForm').reset();
    document.getElementById('vehicleModal').classList.add('active');
}

/**
 * Fecha o modal
 */
function closeModal() {
    document.getElementById('vehicleModal').classList.remove('active');
    document.getElementById('vehicleForm').reset();
    editingVehicleId = null;
}

/**
 * Abre o modal para editar um veículo
 */
function editVehicle(vehicleId) {
    const vehicle = allVehicles.find(v => v.id === vehicleId);
    
    if (!vehicle) {
        showError('Veículo não encontrado');
        return;
    }

    editingVehicleId = vehicleId;
    document.getElementById('modalTitle').textContent = 'Editar Veículo';

    // Preenche o formulário com os dados do veículo
    document.getElementById('marca').value = vehicle.marca || '';
    document.getElementById('modelo').value = vehicle.modelo || '';
    document.getElementById('ano').value = vehicle.ano || '';
    document.getElementById('placa').value = vehicle.placa || '';
    document.getElementById('chassi').value = vehicle.chassi || '';
    document.getElementById('valor_base_diaria').value = vehicle.valor_base_diaria || '';
    document.getElementById('status_disponibilidade').value = vehicle.status_disponibilidade || 'disponivel';
    document.getElementById('imagem_url').value = vehicle.imagem_url || '';

    document.getElementById('vehicleModal').classList.add('active');
}

/**
 * Salva um novo veículo ou atualiza um existente
 */
function saveVehicle(event) {
    event.preventDefault();

    const formData = {
        categoria_id: selectedCategory.id,
        marca: document.getElementById('marca').value.trim(),
        modelo: document.getElementById('modelo').value.trim(),
        ano: parseInt(document.getElementById('ano').value),
        placa: document.getElementById('placa').value.trim().toUpperCase(),
        chassi: document.getElementById('chassi').value.trim(),
        valor_base_diaria: parseFloat(document.getElementById('valor_base_diaria').value),
        status_disponibilidade: document.getElementById('status_disponibilidade').value,
        imagem_url: document.getElementById('imagem_url').value.trim()
    };

    // Validação básica
    if (!formData.marca || !formData.modelo || !formData.placa || !formData.chassi) {
        showError('Preencha todos os campos obrigatórios');
        return;
    }

    if (editingVehicleId) {
        updateVehicle(editingVehicleId, formData);
    } else {
        createVehicle(formData);
    }
}

/**
 * Cria um novo veículo via API
 */
function createVehicle(data) {
    fetch(`${API_BASE_URL}/veiculos`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success' || result.mensagem) {
            showSuccess('Veículo adicionado com sucesso!');
            closeModal();
            loadVehicles();
        } else {
            showError(result.erro || 'Erro ao adicionar veículo');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showError('Erro ao conectar com a API');
    });
}

/**
 * Atualiza um veículo via API
 */
function updateVehicle(vehicleId, data) {
    fetch(`${API_BASE_URL}/veiculos/${vehicleId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showSuccess('Veículo atualizado com sucesso!');
            closeModal();
            loadVehicles();
        } else {
            showError(result.erro || result.mensagem || 'Erro ao atualizar veículo');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showError('Erro ao conectar com a API');
    });
}

/**
 * Deleta um veículo
 */
function deleteVehicle(vehicleId) {
    if (!confirm('Tem certeza que deseja deletar este veículo?')) {
        return;
    }

    fetch(`${API_BASE_URL}/veiculos/${vehicleId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json'
        },
        credentials: 'include',
        body: JSON.stringify({ ativo: false })
    })
    .then(response => response.json())
    .then(result => {
        if (result.status === 'success') {
            showSuccess('Veículo deletado com sucesso!');
            loadVehicles();
        } else {
            showError(result.erro || 'Erro ao deletar veículo');
        }
    })
    .catch(error => {
        console.error('Erro:', error);
        showError('Erro ao conectar com a API');
    });
}

// ============================================
// 4️⃣ AUTENTICAÇÃO
// ============================================

/**
 * Faz logout do usuário
 */
function logout() {
    if (confirm('Deseja realmente sair?')) {
        // 🔧 Ajuste a URL de logout conforme sua aplicação
        fetch(`${API_BASE_URL}/logout`, {
            method: 'POST',
            credentials: 'include'
        })
        .then(() => {
            window.location.href = '/login'; // Redireciona para login
        })
        .catch(() => {
            window.location.href = '/login'; // Redireciona mesmo se houver erro
        });
    }
}

// ============================================
// 5️⃣ NOTIFICAÇÕES
// ============================================

/**
 * Exibe uma mensagem de sucesso
 */
function showSuccess(message) {
    showToast(message, 'success');
}

/**
 * Exibe uma mensagem de erro
 */
function showError(message) {
    showToast(message, 'error');
}

/**
 * Exibe um toast (notificação)
 */
function showToast(message, type = 'success') {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    
    document.body.appendChild(toast);

    // Remove o toast após 3 segundos
    setTimeout(() => {
        toast.remove();
    }, 3000);
}

// ============================================
// 6️⃣ UTILITÁRIOS
// ============================================

/**
 * Formata moeda brasileira
 */
function formatBRL(value) {
    return new Intl.NumberFormat('pt-BR', {
        style: 'currency',
        currency: 'BRL'
    }).format(value);
}

/**
 * Fecha o modal ao clicar fora dele
 */
document.addEventListener('click', function(event) {
    const modal = document.getElementById('vehicleModal');
    if (event.target === modal) {
        closeModal();
    }
});