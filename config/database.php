<?php
// Configuração padrão do XAMPP (usuário root, sem senha). Ajuste se necessário.
const DB_HOST = 'localhost';
const DB_NAME = 'nexolog';
const DB_USER = 'root';
const DB_PASS = '';

function db(): PDO
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                ]
            );
        } catch (PDOException $e) {
            http_response_code(500);
            exit('<h3>Erro ao conectar no banco de dados</h3><p>Verifique se o MySQL está ligado no XAMPP e se o arquivo <code>database/schema.sql</code> foi importado.</p>');
        }
    }
    return $pdo;
}
