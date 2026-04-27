USE driverlux;

-- 1. Populando Usuários (Senhas em hash fictícias)
INSERT INTO usuarios (nome, cpf, email, senha_hash, perfil) VALUES 
('Admin DriverLux', '000.000.000-00', 'admin@driverlux.com.br', '$2b$10$K9p/vH...ficticio', 'admin'),
('Ricardo Lopes', '111.222.333-44', 'ricardo@email.com', '$2b$10$J8q/wI...ficticio', 'cliente'),
('Ana Silva', '555.666.777-88', 'ana.silva@email.com', '$2b$10$L7r/xJ...ficticio', 'cliente');

-- 2. Populando Categorias de Veículos
INSERT INTO categorias_veiculos (nome, descricao, valor_base_diaria) VALUES 
('Grupo B - Compacto', 'Carros econômicos com motor 1.0, ar-condicionado e 4 portas.', 89.90),
('Grupo C - Sedã', 'Carros com porta-malas amplo, motor 1.6 e maior conforto.', 129.90),
('Grupo L - Luxo', 'Veículos premium, bancos de couro e câmbio automático.', 259.90),
('Grupo SUV', 'Utilitários esportivos, ideais para viagens e terrenos mistos.', 189.90);

-- 3. Populando Veículos (Frota inicial)
INSERT INTO veiculos (categoria_id, marca, modelo, ano, placa, chassi, status_disponibilidade) VALUES 
(1, 'Volkswagen', 'Gol', 2023, 'ABC-1234', '9BW-ZZZ123-ABC-001', 'livre'),
(1, 'Fiat', 'Mobi', 2022, 'DFG-5678', '9BW-ZZZ123-DEF-002', 'livre'),
(2, 'Chevrolet', 'Onix Plus', 2024, 'GHI-9012', '9BW-ZZZ123-GHI-003', 'alugado'),
(3, 'BMW', '320i', 2023, 'LUX-0001', '9BW-ZZZ123-LUX-004', 'livre'),
(4, 'Jeep', 'Compass', 2024, 'SUV-2024', '9BW-ZZZ123-SUV-005', 'manutencao');

-- 4. Populando Pacotes de Proteção
INSERT INTO pacotes_protecao (nome, descricao, valor_diario) VALUES 
('Proteção Padrão', 'Cobre apenas colisões e roubo com franquia alta.', 25.00),
('Proteção Completa', 'Cobre danos a terceiros e vidros, franquia reduzida.', 45.00),
('Proteção Premium', 'Isenção total de franquia e assistência 24h VIP.', 75.00);

-- 5. Populando Opções de Quilometragem
INSERT INTO opcoes_quilometragem (nome, limite_km, valor_diario, taxa_km_excedente) VALUES 
('Econômica (100km/dia)', 100, 0.00, 0.55),
('Intermediária (250km/dia)', 250, 20.00, 0.45),
('Quilometragem Ilimitada', NULL, 55.00, 0.00);

-- 6. Populando Cupons de Desconto
INSERT INTO cupons (codigo, descricao, tipo_desconto, valor_desconto, data_validade) VALUES 
('PRIMEIRA10', '10% de desconto na primeira reserva', 'percentual', 10.00, '2026-12-31 23:59:59'),
('BEMVINDO50', 'R$ 50 de desconto fixo', 'valor_fixo', 50.00, '2026-06-30 23:59:59'),
('FERIAS2026', 'Desconto especial de férias', 'percentual', 15.00, '2026-07-31 23:59:59');

-- 7. Exemplo de uma Reserva (Para testar relacionamentos)
-- Simulando que o usuário 2 reservou o carro 1 com proteção padrão e KM ilimitada
INSERT INTO reservas (
    usuario_id, veiculo_id, pacote_protecao_id, opcao_quilometragem_id, 
    local_retirada, local_devolucao, data_retirada, data_devolucao, 
    valor_diarias, valor_protecao, valor_total_previsto, status
) VALUES (
    2, 1, 1, 3, 
    'Aeroporto de Palmas', 'Aeroporto de Palmas', 
    '2026-05-10 08:00:00', '2026-05-13 08:00:00', 
    269.70, 75.00, 396.40, 'confirmada'
);