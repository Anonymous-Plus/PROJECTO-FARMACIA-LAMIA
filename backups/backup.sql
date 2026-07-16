-- Backup SGF
-- Data: 2026-07-15 06:35:17
-- Base de dados: sgf

SET FOREIGN_KEY_CHECKS=0;

DROP TABLE IF EXISTS `categoria`;
CREATE TABLE `categoria` (
  `idCategoria` int NOT NULL AUTO_INCREMENT,
  `nomeCategoria` varchar(60) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idCategoria`),
  UNIQUE KEY `nomeCategoria` (`nomeCategoria`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `categoria` VALUES
('1', 'Analgésicos', 'Medicamentos para alívio da dor'),
('2', 'Antibióticos', 'Medicamentos para combater infeções bacterianas'),
('3', 'Anti-inflamatórios', 'Medicamentos para redução de inflamações'),
('4', 'Protetores Gástricos', 'Medicamentos para proteção do estômago'),
('5', 'Anti-histamínicos', 'Medicamentos para alergias');

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE `cliente` (
  `idCliente` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sexo` enum('Masculino','Feminino') COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataNascimento` date DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idCliente`),
  KEY `idx_cliente_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cliente` VALUES
('1', 'João Silva', 'Masculino', '1985-05-20', '+244 945 111 222', 'joao.silva@email.com', 'Rua 12, Casa 45, Belas, Luanda'),
('2', 'Ana Pereira', 'Feminino', '1992-11-10', '+244 956 222 333', 'ana.pereira@email.com', 'Avenida Principal, Nº 78, Talatona, Luanda'),
('3', 'Pedro Costa', 'Masculino', '1978-03-25', '+244 967 333 444', 'pedro.costa@email.com', 'Bairro Azul, Rua 5, Viana, Luanda'),
('4', 'Marta Santos', 'Feminino', '1995-08-14', '+244 978 444 555', 'marta.santos@email.com', 'Condomínio Verde, Bloco B, Kilamba, Luanda'),
('5', 'Carlos Oliveira', 'Masculino', '1980-01-30', '+244 989 555 666', 'carlos.oliveira@email.com', 'Rua do Comércio, Nº 23, Cacuaco, Luanda');

DROP TABLE IF EXISTS `fornecedor`;
CREATE TABLE `fornecedor` (
  `idFornecedor` int NOT NULL AUTO_INCREMENT,
  `empresa` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `representante` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `endereco` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idFornecedor`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `fornecedor` VALUES
('1', 'MedSupply Angola', 'Dr. Ricardo Fernandes', '+244 912 345 678', 'ricardo@medsupply.ao', 'Zona Industrial, Armazém 12, Viana, Luanda'),
('2', 'FarmaDistribui Lda', 'Sra. Isabel Matos', '+244 923 456 789', 'isabel@farmadistribui.ao', 'Parque Empresarial, Lote 5, Talatona, Luanda'),
('3', 'Global Pharma Angola', 'Eng. Paulo Ribeiro', '+244 934 567 890', 'paulo@globalpharma.ao', 'Edifício Comercial, 3º Andar, Marginal, Luanda');

DROP TABLE IF EXISTS `funcionario`;
CREATE TABLE `funcionario` (
  `idFuncionario` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `sexo` enum('Masculino','Feminino') COLLATE utf8mb4_unicode_ci NOT NULL,
  `dataNascimento` date DEFAULT NULL,
  `telefone` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `cargo` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `salario` decimal(10,2) DEFAULT NULL,
  `dataAdmissao` date DEFAULT NULL,
  `endereco` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`idFuncionario`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_funcionario_nome` (`nome`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `funcionario` VALUES
('1', 'Albino Calenga', 'Masculino', '1990-03-15', '+244 923 456 789', 'albino.calenga@farmacia.ao', 'Administrador de Sistemas', '850000.00', '2026-01-10', 'KM30, Belas, Luanda'),
('2', 'Maria Cardoso', 'Feminino', '1988-07-22', '+244 934 567 890', 'maria.cardoso@farmacia.ao', 'Farmacêutica Chefe', '650000.00', '2026-01-15', 'KM30, Belas, Luanda');

DROP TABLE IF EXISTS `item_venda`;
CREATE TABLE `item_venda` (
  `idItem` int NOT NULL AUTO_INCREMENT,
  `idVenda` int NOT NULL,
  `idMedicamento` int NOT NULL,
  `quantidade` int NOT NULL,
  `precoUnitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  PRIMARY KEY (`idItem`),
  KEY `fk_item_venda` (`idVenda`),
  KEY `fk_item_medicamento` (`idMedicamento`),
  CONSTRAINT `fk_item_medicamento` FOREIGN KEY (`idMedicamento`) REFERENCES `medicamento` (`idMedicamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_item_venda` FOREIGN KEY (`idVenda`) REFERENCES `venda` (`idVenda`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DROP TABLE IF EXISTS `medicamento`;
CREATE TABLE `medicamento` (
  `idMedicamento` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(120) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descricao` text COLLATE utf8mb4_unicode_ci,
  `principioAtivo` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dosagem` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `precoCompra` decimal(10,2) NOT NULL,
  `precoVenda` decimal(10,2) NOT NULL,
  `quantidadeEstoque` int NOT NULL DEFAULT '0',
  `estoqueMinimo` int NOT NULL DEFAULT '10',
  `dataFabricacao` date DEFAULT NULL,
  `dataValidade` date NOT NULL,
  `necessitaReceita` tinyint(1) DEFAULT '0',
  `idCategoria` int NOT NULL,
  `idFornecedor` int NOT NULL,
  PRIMARY KEY (`idMedicamento`),
  KEY `fk_categoria` (`idCategoria`),
  KEY `fk_fornecedor` (`idFornecedor`),
  KEY `idx_medicamento_nome` (`nome`),
  KEY `idx_medicamento_validade` (`dataValidade`),
  CONSTRAINT `fk_categoria` FOREIGN KEY (`idCategoria`) REFERENCES `categoria` (`idCategoria`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_fornecedor` FOREIGN KEY (`idFornecedor`) REFERENCES `fornecedor` (`idFornecedor`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `medicamento` VALUES
('1', 'Paracetamol 750mg', 'Analgésico e antipirético para dores leves a moderadas', 'Paracetamol', '750mg', '450.00', '800.00', '150', '20', '2025-12-01', '2028-12-01', '0', '1', '1'),
('2', 'Amoxacilina 500mg', 'Antibiótico de amplo espectro para infeções respiratórias', 'Amoxacilina', '500mg', '600.00', '1000.00', '80', '15', '2025-11-15', '2027-11-15', '1', '2', '1'),
('3', 'Omeprazol 20mg', 'Protetor gástrico para refluxo e úlceras', 'Omeprazol', '20mg', '350.00', '600.00', '200', '25', '2026-01-05', '2029-01-05', '0', '4', '2'),
('4', 'Ibuprofeno 400mg', 'Anti-inflamatório não esteroide para dores e inflamações', 'Ibuprofeno', '400mg', '300.00', '500.00', '120', '20', '2025-10-20', '2028-10-20', '0', '3', '2'),
('5', 'Losartana 50mg', 'Anti-hipertensivo para controle da pressão arterial', 'Losartana Potássica', '50mg', '800.00', '1200.00', '90', '15', '2025-09-10', '2028-09-10', '1', '3', '3'),
('6', 'Cetirizina 10mg', 'Anti-histamínico para rinite alérgica e urticária', 'Cetirizina', '10mg', '250.00', '450.00', '6', '10', '2026-01-20', '2029-01-20', '0', '5', '1'),
('7', 'Metronidazol 250mg', 'Antibiótico e antiprotozoário para infeções', 'Metronidazol', '250mg', '200.00', '350.00', '3', '10', '2025-08-15', '2027-08-15', '1', '2', '2'),
('8', 'Dipirona 500mg', 'Analgésico e antipirético potente', 'Dipirona Sódica', '500mg', '180.00', '300.00', '0', '10', '2025-07-01', '2028-07-01', '0', '1', '3'),
('9', 'Diclofenaco 50mg', 'Anti-inflamatório para dores musculares e articulares', 'Diclofenaco de Sódio', '50mg', '400.00', '700.00', '45', '15', '2025-11-01', '2028-11-01', '0', '3', '3'),
('10', 'Ranitidina 150mg', 'Antiácido para redução da acidez estomacal', 'Ranitidina', '150mg', '280.00', '480.00', '8', '10', '2025-06-15', '2027-06-15', '0', '4', '1');

DROP TABLE IF EXISTS `receita`;
CREATE TABLE `receita` (
  `idReceita` int NOT NULL AUTO_INCREMENT,
  `numeroReceita` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `medico` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `crm` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `dataReceita` date DEFAULT NULL,
  `observacao` text COLLATE utf8mb4_unicode_ci,
  `idCliente` int NOT NULL,
  PRIMARY KEY (`idReceita`),
  UNIQUE KEY `numeroReceita` (`numeroReceita`),
  KEY `fk_receita_cliente` (`idCliente`),
  CONSTRAINT `fk_receita_cliente` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `receita` VALUES
('1', 'RX-2026-001', 'Dr. António Mendes', 'CRM-12345-LU', '2026-02-15', 'Tomar 1 comprimido de 8h em 8h durante 7 dias', '1'),
('2', 'RX-2026-002', 'Dra. Sofia Carvalho', 'CRM-67890-LU', '2026-03-10', 'Uso contínuo para controle de pressão arterial', '2'),
('3', 'RX-2026-003', 'Dr. António Mendes', 'CRM-12345-LU', '2026-04-05', 'Aplicar pomada 2x ao dia', '3');

DROP TABLE IF EXISTS `receita_medicamento`;
CREATE TABLE `receita_medicamento` (
  `idReceitaMedicamento` int NOT NULL AUTO_INCREMENT,
  `idReceita` int NOT NULL,
  `idMedicamento` int NOT NULL,
  `quantidade` int NOT NULL,
  PRIMARY KEY (`idReceitaMedicamento`),
  KEY `fk_rm_receita` (`idReceita`),
  KEY `fk_rm_medicamento` (`idMedicamento`),
  CONSTRAINT `fk_rm_medicamento` FOREIGN KEY (`idMedicamento`) REFERENCES `medicamento` (`idMedicamento`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_rm_receita` FOREIGN KEY (`idReceita`) REFERENCES `receita` (`idReceita`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `receita_medicamento` VALUES
('1', '1', '1', '21'),
('2', '1', '3', '14'),
('3', '2', '5', '30'),
('4', '3', '9', '10');

DROP TABLE IF EXISTS `utilizador`;
CREATE TABLE `utilizador` (
  `idUtilizador` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `nivel` enum('Administrador','Farmaceutico','Atendente') COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` enum('Ativo','Inativo') COLLATE utf8mb4_unicode_ci DEFAULT 'Ativo',
  `idFuncionario` int NOT NULL,
  PRIMARY KEY (`idUtilizador`),
  UNIQUE KEY `username` (`username`),
  KEY `fk_utilizador_funcionario` (`idFuncionario`),
  CONSTRAINT `fk_utilizador_funcionario` FOREIGN KEY (`idFuncionario`) REFERENCES `funcionario` (`idFuncionario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `utilizador` VALUES
('2', 'admin', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', 'Administrador', 'Ativo', '1'),
('3', 'farmaceutico', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Farmaceutico', 'Ativo', '2');

DROP TABLE IF EXISTS `venda`;
CREATE TABLE `venda` (
  `idVenda` int NOT NULL AUTO_INCREMENT,
  `dataVenda` datetime DEFAULT CURRENT_TIMESTAMP,
  `valorTotal` decimal(10,2) DEFAULT '0.00',
  `formaPagamento` enum('Dinheiro','Cartao','Transferencia','Multicaixa Express') COLLATE utf8mb4_unicode_ci NOT NULL,
  `idFuncionario` int NOT NULL,
  `idCliente` int DEFAULT NULL,
  PRIMARY KEY (`idVenda`),
  KEY `fk_venda_funcionario` (`idFuncionario`),
  KEY `fk_venda_cliente` (`idCliente`),
  KEY `idx_venda_data` (`dataVenda`),
  CONSTRAINT `fk_venda_cliente` FOREIGN KEY (`idCliente`) REFERENCES `cliente` (`idCliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_venda_funcionario` FOREIGN KEY (`idFuncionario`) REFERENCES `funcionario` (`idFuncionario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `venda` VALUES
('1', '2026-05-10 09:30:00', '2300.00', 'Dinheiro', '2', '1'),
('2', '2026-05-10 10:15:00', '1500.00', 'Multicaixa Express', '2', '2'),
('3', '2026-05-10 14:00:00', '3800.00', 'Cartao', '2', '3'),
('4', '2026-05-11 08:45:00', '1250.00', 'Dinheiro', '2', '4'),
('5', '2026-05-11 16:30:00', '5200.00', 'Transferencia', '2', '5');

SET FOREIGN_KEY_CHECKS=1;
