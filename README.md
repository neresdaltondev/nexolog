# NexoLog — Gestão Logística

Gerenciador logístico web desenvolvido para a disciplina **Fundamentos de Programação para Internet** (UNIPAR EAD).
Permite cadastrar produtos, endereços (origem/destino) e transportadores, gerar **fretes** que relacionam esses dados
e baixar o **relatório de fretes de um dia**.

## Tecnologias

- **PHP 8** (backend, PDO)
- **MySQL / MariaDB** (XAMPP)
- **HTML5, CSS3 e JavaScript** (campos dinâmicos, máscara de CEP, API ViaCEP)
- **Bootstrap 5** + Bootstrap Icons
- **Git / GitHub** para versionamento

## Funcionalidades

| Módulo | O que faz |
|---|---|
| Visão geral | Indicadores (fretes ativos, entregas, transportadores, valor) e fretes recentes |
| Produtos | Cadastro, edição, exclusão e busca por nome/SKU |
| Endereços | Cadastro, edição, exclusão e busca; CEP preenche o endereço automaticamente |
| Transportadores | Cadastro, edição, exclusão e busca |
| Fretes | Gera frete com origem, destino, transportador e **vários produtos** (1 frete → N produtos); detalhe, edição, mudança de status e exclusão |
| Relatórios | Consulta os fretes de uma data, imprime e **baixa em CSV** (abre no Excel) |

## Como executar (XAMPP)

1. Instale o [XAMPP](https://www.apachefriends.org/) e inicie **Apache** e **MySQL**.
2. Copie a pasta do projeto para `C:\xampp\htdocs\nexolog`.
3. Abra `http://localhost/phpmyadmin`, vá em **Importar** e selecione `database/schema.sql`
   (cria o banco `nexolog`, as tabelas e dados de exemplo).
4. Acesse **http://localhost/nexolog/**.

Se o MySQL tiver senha, ajuste `config/database.php`.

## Estrutura de pastas

```
nexolog/
├── index.php                 # Visão geral (dashboard)
├── config/database.php       # Conexão PDO
├── database/schema.sql       # Banco de dados + dados de exemplo
├── includes/                 # header, footer e funções auxiliares
├── assets/css, assets/js     # Estilos e scripts
├── produtos/                 # index.php (lista) + form.php (criar/editar)
├── enderecos/
├── transportadores/
├── fretes/                   # index, form (criar/editar), ver (detalhe)
└── relatorios/               # index (consulta) + download.php (CSV)
```

## Modelo de dados

`produtos`, `enderecos`, `transportadores`, `fretes` (origem, destino e transportador) e `frete_itens`
(tabela de ligação: um frete tem vários produtos, e um transportador pode ter vários fretes).

## Autor

- Nome: Dalton José Neres
- RA: 09065530
- Curso / Polo: Análise e Desenvolvimento de Sistemas (ADS) | Francisco Beltrão
