CREATE DATABASE IF NOT EXISTS driverlux;
USE driverlux;

-- 1. Tabela de Usuários
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha_hash VARCHAR(255) NOT NULL,
    foto_perfil VARCHAR(255), -- URL da imagem do usuário
    perfil ENUM('cliente', 'admin') DEFAULT 'cliente',
    ativo BOOLEAN DEFAULT TRUE, -- TRUE = Conta ativa, FALSE = Conta desativada
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 2. Tabela de Grupos/Categorias (Ex: "Grupo B - Compacto")
CREATE TABLE categorias_veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    valor_base_diaria DECIMAL(10, 2) NOT NULL,
    imagem_ilustrativa VARCHAR(255),
    ativo BOOLEAN DEFAULT TRUE
);

-- 3. Tabela de Veículos (O carro físico no pátio)
CREATE TABLE veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria_id INT NOT NULL,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    ano INT NOT NULL,
    placa VARCHAR(10) UNIQUE NOT NULL,
    chassi VARCHAR(50) UNIQUE,
    imagem_url VARCHAR(255), -- Foto específica do carro
    status_disponibilidade ENUM('livre', 'alugado', 'manutencao') DEFAULT 'livre',
    ativo BOOLEAN DEFAULT TRUE, -- FALSE se o carro for vendido/removido da frota
    FOREIGN KEY (categoria_id) REFERENCES categorias_veiculos(id)
);

-- 4. Tabela de Pacotes de Proteção (Padrão, Completo, Premium da sua imagem)
CREATE TABLE pacotes_protecao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    descricao TEXT,
    valor_diario DECIMAL(10, 2) NOT NULL,
    ativo BOOLEAN DEFAULT TRUE
);

-- 5. Tabela de Opções de Quilometragem (Econômica, Ilimitada da sua imagem)
CREATE TABLE opcoes_quilometragem (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(50) NOT NULL,
    limite_km INT, -- NULL se for ilimitado
    valor_diario DECIMAL(10, 2) NOT NULL,
    taxa_km_excedente DECIMAL(10, 2), -- Ex: R$ 0,50 por km excedente
    ativo BOOLEAN DEFAULT TRUE
);

-- 6. Tabela de Cupons de Desconto
CREATE TABLE cupons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) UNIQUE NOT NULL, -- Ex: 'PRIMEIRA10', 'BLACKFRIDAY'
    descricao VARCHAR(255),
    tipo_desconto ENUM('percentual', 'valor_fixo') NOT NULL,
    valor_desconto DECIMAL(10, 2) NOT NULL, -- Se for percentual (Ex: 10.00 para 10%), se for fixo (Ex: 50.00 para R$50)
    data_inicio DATETIME DEFAULT CURRENT_TIMESTAMP,
    data_validade DATETIME NOT NULL,
    limite_usos INT DEFAULT NULL, -- (NULL = ilimitado)
    usos_atuais INT DEFAULT 0, -- Contador de quantas vezes já foi utilizado
    ativo BOOLEAN DEFAULT TRUE,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- 7. Tabela de Reservas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    pacote_protecao_id INT NOT NULL,
    opcao_quilometragem_id INT NOT NULL,
    cupom_id INT NULL, -- Relacionamento com a tabela de cupões
    
    -- Datas e Locais
    local_retirada VARCHAR(100) NOT NULL,
    local_devolucao VARCHAR(100) NOT NULL,
    data_retirada DATETIME NOT NULL,
    data_devolucao DATETIME NOT NULL,
    
    -- Snapshot Financeiro
    valor_diarias DECIMAL(10, 2) NOT NULL, 
    valor_protecao DECIMAL(10, 2) NOT NULL,
    taxa_aluguel_percentual DECIMAL(5, 2) DEFAULT 15.00,
    valor_desconto DECIMAL(10, 2) DEFAULT 0.00, -- Valor subtraído pelo cupão
    valor_total_previsto DECIMAL(10, 2) NOT NULL,
    
    -- Controle de Status
    status ENUM('pendente', 'confirmada', 'em_andamento', 'concluida', 'cancelada') DEFAULT 'pendente',
    ativo BOOLEAN DEFAULT TRUE,
    
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    -- Relacionamentos (Foreign Keys)
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id),
    FOREIGN KEY (pacote_protecao_id) REFERENCES pacotes_protecao(id),
    FOREIGN KEY (opcao_quilometragem_id) REFERENCES opcoes_quilometragem(id),
    FOREIGN KEY (cupom_id) REFERENCES cupons(id)
);

-- 8. Tabela de Pagamento
CREATE TABLE pagamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reserva_id INT NOT NULL,
    usuario_id INT NOT NULL,

    tipo_cartao ENUM('credito', 'debito') NOT NULL,
    valor_total DECIMAL(10, 2) NOT NULL,
    parcelas TINYINT UNSIGNED DEFAULT 1,

    nome_titular VARCHAR(100) NOT NULL,
    numero_cartao VARCHAR(30) NOT NULL,
    mes_vencimento TINYINT UNSIGNED NOT NULL CHECK (mes_vencimento BETWEEN 1 AND 12),
    ano_vencimento SMALLINT UNSIGNED NOT NULL,

    status ENUM('pendente', 'aprovado', 'recusado', 'estornado') DEFAULT 'pendente',

    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (reserva_id) REFERENCES reservas(id),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),

    CONSTRAINT chk_parcelas_debito CHECK (
        tipo_cartao = 'credito' OR parcelas = 1
    ),

    CONSTRAINT chk_parcelas_limite CHECK (
        parcelas BETWEEN 1 AND 12
    )
);