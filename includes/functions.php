<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../config/database.php';

// URL base do projeto (ex.: /nexolog), detectada automaticamente
$__root = str_replace('\\', '/', realpath(__DIR__ . '/..'));
$__doc  = str_replace('\\', '/', realpath($_SERVER['DOCUMENT_ROOT'] ?? '') ?: '');
define('BASE_URL', $__doc !== '' ? rtrim(str_replace($__doc, '', $__root), '/') : '');

const STATUS_FRETE = [
    'Agendado'    => 'agendado',
    'Em trânsito' => 'transito',
    'Entregue'    => 'entregue',
    'Cancelado'   => 'cancelado',
];

const UFS = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];

function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    return BASE_URL . '/' . ltrim($path, '/');
}

function redirect(string $path): void
{
    header('Location: ' . url($path));
    exit;
}

function flash(string $tipo, string $msg): void
{
    $_SESSION['flash'] = ['tipo' => $tipo, 'msg' => $msg];
}

function pegar_flash(): ?array
{
    $f = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $f;
}

function moeda($v): string
{
    return 'R$ ' . number_format((float)$v, 2, ',', '.');
}

function data_br(string $d): string
{
    return date('d/m/Y', strtotime($d));
}

// Aceita "1.320,50" ou "1320.50"
function decimal_br($v): float
{
    $v = trim((string)$v);
    if (strpos($v, ',') !== false) {
        $v = str_replace('.', '', $v);
        $v = str_replace(',', '.', $v);
    }
    return (float)$v;
}

function numero_br($v, int $casas = 2): string
{
    $s = number_format((float)$v, $casas, ',', '.');
    return strpos($s, ',') !== false ? rtrim(rtrim($s, '0'), ',') : $s;
}

function status_badge(string $status): string
{
    $cls = STATUS_FRETE[$status] ?? 'agendado';
    return '<span class="status status-' . $cls . '">' . e($status) . '</span>';
}

function frete_codigo(int $id): string
{
    return 'FRT-' . (1044 + $id);
}

// Fretes de um dia, com origem, destino, transportador, itens e peso total
function fretes_do_dia(string $data): array
{
    $sql = "SELECT f.*, o.nome AS origem, d.nome AS destino, t.nome AS transportador,
              (SELECT GROUP_CONCAT(CONCAT(p.nome, ' x', i.quantidade) SEPARATOR '; ')
                 FROM frete_itens i JOIN produtos p ON p.id = i.produto_id
                WHERE i.frete_id = f.id) AS itens,
              (SELECT COALESCE(SUM(p.peso * i.quantidade), 0)
                 FROM frete_itens i JOIN produtos p ON p.id = i.produto_id
                WHERE i.frete_id = f.id) AS peso_total
            FROM fretes f
            JOIN enderecos o ON o.id = f.origem_id
            JOIN enderecos d ON d.id = f.destino_id
            JOIN transportadores t ON t.id = f.transportador_id
            WHERE f.data_frete = ?
            ORDER BY f.id";
    $st = db()->prepare($sql);
    $st->execute([$data]);
    return $st->fetchAll();
}
