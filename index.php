<?php
require_once __DIR__ . '/includes/functions.php';

$pdo = db();
$ativos     = (int)$pdo->query("SELECT COUNT(*) FROM fretes WHERE status IN ('Agendado','Em trânsito')")->fetchColumn();
$emTransito = (int)$pdo->query("SELECT COUNT(*) FROM fretes WHERE status = 'Em trânsito'")->fetchColumn();
$entregues  = (int)$pdo->query("SELECT COUNT(*) FROM fretes WHERE status = 'Entregue'")->fetchColumn();
$transps    = (int)$pdo->query('SELECT COUNT(*) FROM transportadores')->fetchColumn();
$valorTotal = (float)$pdo->query("SELECT COALESCE(SUM(valor),0) FROM fretes WHERE status <> 'Cancelado'")->fetchColumn();

$recentes = $pdo->query("SELECT f.*, o.nome AS origem, d.nome AS destino
                           FROM fretes f
                           JOIN enderecos o ON o.id = f.origem_id
                           JOIN enderecos d ON d.id = f.destino_id
                       ORDER BY f.data_frete DESC, f.id DESC LIMIT 4")->fetchAll();

$active = 'inicio'; $secao = 'RESUMO OPERACIONAL'; $titulo = 'Visão geral';
require __DIR__ . '/includes/header.php';
?>
<h1 class="page-title">Visão geral</h1>
<p class="page-sub">Acompanhe sua operação logística em um só lugar.</p>

<div class="row g-3 mb-3">
  <div class="col-6 col-xl-3"><div class="card-x stat">
    <div class="d-flex justify-content-between"><span class="stat-label">Fretes ativos</span><span class="icon-box icon-blue"><i class="bi bi-truck"></i></span></div>
    <div class="stat-value"><?= $ativos ?></div><div class="stat-note"><?= $emTransito ?> em trânsito</div>
  </div></div>
  <div class="col-6 col-xl-3"><div class="card-x stat">
    <div class="d-flex justify-content-between"><span class="stat-label">Entregas concluídas</span><span class="icon-box icon-green"><i class="bi bi-check2"></i></span></div>
    <div class="stat-value"><?= $entregues ?></div><div class="stat-note">histórico total</div>
  </div></div>
  <div class="col-6 col-xl-3"><div class="card-x stat">
    <div class="d-flex justify-content-between"><span class="stat-label">Transportadores</span><span class="icon-box icon-purple"><i class="bi bi-people"></i></span></div>
    <div class="stat-value"><?= $transps ?></div><div class="stat-note">parceiros cadastrados</div>
  </div></div>
  <div class="col-6 col-xl-3"><div class="card-x stat">
    <div class="d-flex justify-content-between"><span class="stat-label">Valor movimentado</span><span class="icon-box icon-orange"><i class="bi bi-file-earmark-text"></i></span></div>
    <div class="stat-value"><?= moeda($valorTotal) ?></div><div class="stat-note">em todos os fretes</div>
  </div></div>
</div>

<div class="row g-3">
  <div class="col-xl-8">
    <div class="card-x p-0">
      <div class="d-flex justify-content-between align-items-center p-3">
        <div><h2 class="card-name mb-0">Fretes recentes</h2><div class="card-meta">Últimas movimentações registradas</div></div>
        <a href="fretes/index.php" class="link-muted">Ver todos <i class="bi bi-arrow-right"></i></a>
      </div>
      <?php foreach ($recentes as $f): ?>
        <a href="fretes/ver.php?id=<?= $f['id'] ?>" class="recent-row">
          <span class="icon-box icon-gray"><i class="bi bi-truck"></i></span>
          <div class="flex-grow-1">
            <strong><?= e($f['codigo']) ?></strong>
            <div class="card-meta"><?= e($f['origem']) ?> → <?= e($f['destino']) ?></div>
          </div>
          <?= status_badge($f['status']) ?>
          <strong class="recent-valor"><?= moeda($f['valor']) ?></strong>
        </a>
      <?php endforeach; ?>
      <?php if (!$recentes): ?><div class="empty m-3">Nenhum frete gerado ainda.</div><?php endif; ?>
    </div>
  </div>
  <div class="col-xl-4">
    <div class="quick">
      <span class="brand-logo mb-3"><i class="bi bi-signpost-split"></i></span>
      <div class="quick-label">ACESSO RÁPIDO</div>
      <h2>Nova ordem de frete</h2>
      <p>Associe rotas, produtos e um transportador em poucos passos.</p>
      <a href="fretes/form.php" class="btn btn-light w-100"><i class="bi bi-plus-lg"></i> Gerar frete</a>
    </div>
  </div>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
