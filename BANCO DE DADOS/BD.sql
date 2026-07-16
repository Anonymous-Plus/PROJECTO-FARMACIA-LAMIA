DROP DATABASE IF EXISTS sgf;
CREATE DATABASE sgf
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE sgf;

-- =====================================================
-- TABELA FUNCIONARIO
-- =====================================================

CREATE TABLE funcionario (
    idFuncionario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sexo ENUM('Masculino','Feminino') NOT NULL,
    dataNascimento DATE,
    telefone VARCHAR(20),
    email VARCHAR(100) UNIQUE,
    cargo VARCHAR(50) NOT NULL,
    salario DECIMAL(10,2),
    dataAdmissao DATE,
    endereco VARCHAR(200)
);

-- =====================================================
-- TABELA UTILIZADOR
-- =====================================================

CREATE TABLE utilizador (
    idUtilizador INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    nivel ENUM(
        'Administrador',
        'Farmaceutico',
        'Atendente'
    ) NOT NULL,
    estado ENUM(
        'Ativo',
        'Inativo'
    ) DEFAULT 'Ativo',

    idFuncionario INT NOT NULL,

    CONSTRAINT fk_utilizador_funcionario
        FOREIGN KEY (idFuncionario)
        REFERENCES funcionario(idFuncionario)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
-- CLIENTE
-- =====================================================

CREATE TABLE cliente (
    idCliente INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    sexo ENUM('Masculino','Feminino'),
    dataNascimento DATE,
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(200)
);

-- =====================================================
-- FORNECEDOR
-- =====================================================

CREATE TABLE fornecedor (
    idFornecedor INT AUTO_INCREMENT PRIMARY KEY,
    empresa VARCHAR(120) NOT NULL,
    representante VARCHAR(100),
    telefone VARCHAR(20),
    email VARCHAR(100),
    endereco VARCHAR(200)
);

-- =====================================================
-- CATEGORIA
-- =====================================================

CREATE TABLE categoria (
    idCategoria INT AUTO_INCREMENT PRIMARY KEY,
    nomeCategoria VARCHAR(60) NOT NULL UNIQUE,
    descricao VARCHAR(200)
);

-- =====================================================
-- MEDICAMENTOS
-- =====================================================

CREATE TABLE medicamento (
    idMedicamento INT AUTO_INCREMENT PRIMARY KEY,

    nome VARCHAR(120) NOT NULL,

    descricao TEXT,

    principioAtivo VARCHAR(100),

    dosagem VARCHAR(50),

    precoCompra DECIMAL(10,2) NOT NULL,

    precoVenda DECIMAL(10,2) NOT NULL,

    quantidadeEstoque INT NOT NULL DEFAULT 0,

    estoqueMinimo INT NOT NULL DEFAULT 10,

    dataFabricacao DATE,

    dataValidade DATE NOT NULL,

    necessitaReceita BOOLEAN DEFAULT FALSE,

    idCategoria INT NOT NULL,

    idFornecedor INT NOT NULL,

    CONSTRAINT fk_categoria
        FOREIGN KEY(idCategoria)
        REFERENCES categoria(idCategoria)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_fornecedor
        FOREIGN KEY(idFornecedor)
        REFERENCES fornecedor(idFornecedor)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
-- RECEITA
-- =====================================================

CREATE TABLE receita (

    idReceita INT AUTO_INCREMENT PRIMARY KEY,

    numeroReceita VARCHAR(50) UNIQUE,

    medico VARCHAR(100),

    crm VARCHAR(50),

    dataReceita DATE,

    observacao TEXT,

    idCliente INT NOT NULL,

    CONSTRAINT fk_receita_cliente
        FOREIGN KEY(idCliente)
        REFERENCES cliente(idCliente)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
-- RECEITA_MEDICAMENTO
-- =====================================================

CREATE TABLE receita_medicamento (

    idReceitaMedicamento INT AUTO_INCREMENT PRIMARY KEY,

    idReceita INT NOT NULL,

    idMedicamento INT NOT NULL,

    quantidade INT NOT NULL,

    CONSTRAINT fk_rm_receita
        FOREIGN KEY(idReceita)
        REFERENCES receita(idReceita)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_rm_medicamento
        FOREIGN KEY(idMedicamento)
        REFERENCES medicamento(idMedicamento)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
-- VENDA
-- =====================================================

CREATE TABLE venda (

    idVenda INT AUTO_INCREMENT PRIMARY KEY,

    dataVenda DATETIME DEFAULT CURRENT_TIMESTAMP,

    valorTotal DECIMAL(10,2) DEFAULT 0,

    formaPagamento ENUM(
        'Dinheiro',
        'Cartao',
        'Transferencia',
        'Multicaixa Express'
    ) NOT NULL,

    idFuncionario INT NOT NULL,

    idCliente INT NULL,

    CONSTRAINT fk_venda_funcionario
        FOREIGN KEY(idFuncionario)
        REFERENCES funcionario(idFuncionario)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_venda_cliente
        FOREIGN KEY(idCliente)
        REFERENCES cliente(idCliente)
        ON UPDATE CASCADE
        ON DELETE SET NULL
);

-- =====================================================
-- ITEM VENDA
-- =====================================================

CREATE TABLE item_venda (

    idItem INT AUTO_INCREMENT PRIMARY KEY,

    idVenda INT NOT NULL,

    idMedicamento INT NOT NULL,

    quantidade INT NOT NULL,

    precoUnitario DECIMAL(10,2) NOT NULL,

    subtotal DECIMAL(10,2) NOT NULL,

    CONSTRAINT fk_item_venda
        FOREIGN KEY(idVenda)
        REFERENCES venda(idVenda)
        ON UPDATE CASCADE
        ON DELETE CASCADE,

    CONSTRAINT fk_item_medicamento
        FOREIGN KEY(idMedicamento)
        REFERENCES medicamento(idMedicamento)
        ON UPDATE CASCADE
        ON DELETE CASCADE
);

-- =====================================================
-- ÍNDICES
-- =====================================================

CREATE INDEX idx_medicamento_nome
ON medicamento(nome);

CREATE INDEX idx_medicamento_validade
ON medicamento(dataValidade);

CREATE INDEX idx_cliente_nome
ON cliente(nome);

CREATE INDEX idx_funcionario_nome
ON funcionario(nome);

CREATE INDEX idx_venda_data
ON venda(dataVenda);
