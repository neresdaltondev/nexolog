<?php
require_once __DIR__ . '/functions.php';
$active = $active ?? '';
$secao  = $secao ?? '';
$titulo = $titulo ?? '';
$menu = [
    'inicio'          => ['Visão geral',     'bi-grid',               'index.php'],
    'fretes'          => ['Fretes',          'bi-truck',              'fretes/index.php'],
    'produtos'        => ['Produtos',        'bi-box-seam',           'produtos/index.php'],
    'enderecos'       => ['Endereços',       'bi-geo-alt',            'enderecos/index.php'],
    'transportadores' => ['Transportadores', 'bi-people',             'transportadores/index.php'],
    'relatorios'      => ['Relatórios',      'bi-file-earmark-text',  'relatorios/index.php'],
];
$flash = pegar_flash();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($titulo) ?> · NexoLog</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
  <link href="<?= url('assets/css/style.css') ?>" rel="stylesheet">
</head>
<body>
<aside class="sidebar" id="sidebar">
  <a class="brand" href="<?= url('index.php') ?>">
    <span class="brand-logo"><i class="bi bi-signpost-split"></i></span>
    <span><strong>NexoLog</strong><small>GESTÃO LOGÍSTICA</small></span>
  </a>
  <div class="menu-label">MENU PRINCIPAL</div>
  <nav class="nav flex-column">
    <?php foreach ($menu as $key => [$nome, $icone, $link]): ?>
      <a class="nav-link <?= $active === $key ? 'active' : '' ?>" href="<?= url($link) ?>">
        <i class="bi <?= $icone ?>"></i> <?= e($nome) ?>
        <?php if ($active === $key): ?><span class="dot"></span><?php endif; ?>
      </a>
    <?php endforeach; ?>
  </nav>
  <div class="sidebar-user">
    <span class="avatar">AL</span>
    <span><strong>Admin Logística</strong><small>Operações</small></span>
  </div>
</aside>

<div class="main">
  <header class="topbar">
    <div class="d-flex align-items-center gap-3">
      <button class="btn btn-sm btn-light d-lg-none" id="toggleMenu" type="button" aria-label="Abrir menu"><i class="bi bi-list"></i></button>
      <div>
        <div class="topbar-section"><?= e($secao) ?></div>
        <div class="topbar-title"><?= e($titulo) ?></div>
      </div>
    </div>
    <div class="d-flex align-items-center gap-3">
      <span class="pill-ok"><span class="dot"></span> Sistema operacional</span>
      <span class="avatar avatar-dark">AL</span>
    </div>
  </header>
  <main class="content">
    <?php if ($flash): ?>
      <div class="alert alert-<?= e($flash['tipo']) ?> alert-dismissible fade show" role="alert">
        <?= e($flash['msg']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
      </div>
    <?php endif; ?>
