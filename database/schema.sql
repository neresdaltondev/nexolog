-- NexoLog - Gestão Logística
-- Importe este arquivo no phpMyAdmin (aba "Importar") ou pelo terminal do MySQL.
CREATE DATABASE IF NOT EXISTS nexolog CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE nexolog;

DROP TABLE IF EXISTS frete_itens;
DROP TABLE IF EXISTS fretes;
DROP TABLE IF EXISTS transportadores;
DROP TABLE IF EXISTS enderecos;
DROP TABLE IF EXISTS produtos;

CREATE TABLE produtos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  sku VARCHAR(30) NOT NULL UNIQUE,
  peso DECIMAL(10,3) NOT NULL DEFAULT 0,
  unidade VARCHAR(10) NOT NULL DEFAULT 'un',
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE enderecos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  cep VARCHAR(9) NOT NULL,
  logradouro VARCHAR(150) NOT NULL,
  numero VARCHAR(20) NOT NULL,
  complemento VARCHAR(80) NULL,
  bairro VARCHAR(80) NULL,
  cidade VARCHAR(80) NOT NULL,
  uf CHAR(2) NOT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE transportadores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nome VARCHAR(120) NOT NULL,
  documento VARCHAR(18) NOT NULL,
  telefone VARCHAR(20) NULL,
  email VARCHAR(120) NULL,
  veiculo VARCHAR(60) NULL,
  placa VARCHAR(10) NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE fretes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  codigo VARCHAR(12) NULL,
  origem_id INT NOT NULL,
  destino_id INT NOT NULL,
  transportador_id INT NOT NULL,
  data_frete DATE NOT NULL,
  status ENUM('Agendado','Em trânsito','Entregue','Cancelado') NOT NULL DEFAULT 'Agendado',
  valor DECIMAL(10,2) NOT NULL DEFAULT 0,
  observacoes TEXT NULL,
  criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (origem_id) REFERENCES enderecos(id),
  FOREIGN KEY (destino_id) REFERENCES enderecos(id),
  FOREIGN KEY (transportador_id) REFERENCES transportadores(id)
) ENGINE=InnoDB;

-- Relação 1 frete -> N produtos (e 1 transportador -> N fretes)
CREATE TABLE frete_itens (
  id INT AUTO_INCREMENT PRIMARY KEY,
  frete_id INT NOT NULL,
  produto_id INT NOT NULL,
  quantidade INT NOT NULL DEFAULT 1,
  FOREIGN KEY (frete_id) REFERENCES fretes(id) ON DELETE CASCADE,
  FOREIGN KEY (produto_id) REFERENCES produtos(id)
) ENGINE=InnoDB;

-- Dados de exemplo (iguais ao protótipo)
INSERT INTO produtos (id,nome,sku,peso,unidade) VALUES
(1,'Cadeira ergonômica','MOB-042',14.5,'un'),
(2,'Monitor LED 24 polegadas','ELT-118',4.8,'un'),
(3,'Café torrado premium','ALI-203',1,'cx'),
(4,'Kit de ferramentas','FER-090',8.2,'un');

INSERT INTO enderecos (id,nome,cep,logradouro,numero,bairro,cidade,uf) VALUES
(1,'Centro de Distribuição SP','01123-000','Av. Industrial','1240','Industrial','São Paulo','SP'),
(2,'Hub Campinas','13050-001','Rod. Anhanguera','km 98','Distrito','Campinas','SP'),
(3,'Filial Curitiba','80030-120','Rua das Araucárias','310','Centro','Curitiba','PR'),
(4,'Armazém Belo Horizonte','30421-026','Av. Amazonas','5520','Barro Preto','Belo Horizonte','MG');

INSERT INTO transportadores (id,nome,documento,telefone,email,veiculo,placa) VALUES
(1,'Rota Sul Transportes','12.345.678/0001-90','(41) 3333-1000','contato@rotasul.com.br','Caminhão baú','ABC-1D23'),
(2,'Expresso Horizonte','98.765.432/0001-10','(31) 3222-2000','op@expressohorizonte.com.br','Carreta','BHZ-4E56'),
(3,'João Mendes Logística','123.456.789-09','(41) 99999-3000','joao@mendeslog.com.br','Van','JMD-7F89');

INSERT INTO fretes (id,codigo,origem_id,destino_id,transportador_id,data_frete,status,valor) VALUES
(1,'FRT-1045',4,2,1,'2026-10-10','Entregue',2450.00),
(2,'FRT-1046',3,1,3,'2026-10-11','Entregue',1980.00),
(3,'FRT-1047',2,4,2,'2026-10-12','Agendado',1320.00),
(4,'FRT-1048',1,3,1,'2026-10-12','Em trânsito',2840.00);

INSERT INTO frete_itens (frete_id,produto_id,quantidade) VALUES
(1,4,10),(2,2,20),(3,1,5),(4,1,10),(4,3,30);
