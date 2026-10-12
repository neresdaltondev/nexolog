<?php
require_once __DIR__ . '/../includes/functions.php';

$data = $_GET['data'] ?? date('Y-m-d');
if (!DateTime::createFromFormat('Y-m-d', $data)) { $data = date('Y-m-d'); }
$fretes = fretes_do_dia($data);
$total = 0; $peso = 0;
foreach ($fretes as $f) { if ($f['status'] !== 'Cancelado') { $total += $f['valor']; } $peso += $f['peso_total']; }

$active = 'relatorios'; $secao = 'ANÁLISE'; $titulo = 'Relatórios';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Relatórios</h1>
<p class="page-sub">Consulte e baixe os fretes de um dia específico.</p>

<form method="get" class="toolbar d-print-none">
  <div class="d-flex gap-2 align-items-end">
    <div>
      <label class="form-label mb-1" for="data">Data</label>
      <input type="date" class="form-control" id="data" name="data" value="<?= e($data) ?>">
    </div>
    <button class="btn btn-dark">Consultar</button>
  </div>
  <div class="d-flex gap-2">
    <button type="button" class="btn btn-light" onclick="window.print()"><i class="bi bi-printer"></i> Imprimir</button>
    <a class="btn btn-dark <?= $fretes ? '' : 'disabled' ?>" href="download.php?data=<?= e($data) ?>"><i class="bi bi-download"></i> Baixar CSV</a>
  </div>
</form>

<div class="row g-3 mb-3">
  <div class="col-md-4"><div class="card-x stat"><span class="stat-label">Fretes no dia</span><div class="stat-value"><?= count($fretes) ?></div></div></div>
  <div class="col-md-4"><div class="card-x stat"><span class="stat-label">Valor total (sem cancelados)</span><div class="stat-value"><?= moeda($total) ?></div></div></div>
  <div class="col-md-4"><div class="card-x stat"><span class="stat-label">Peso transportado</span><div class="stat-value"><?= numero_br($peso, 3) ?> kg</div></div></div>
</div>

<div class="card-x p-0 table-wrap">
  <table class="table table-x align-middle mb-0">
    <thead><tr><th>Frete</th><th>Rota</th><th>Transportador</th><th>Produtos</th><th>Status</th><th class="text-end">Valor</th></tr></thead>
    <tbody>
      <?php foreach ($fretes as $f): ?>
        <tr>
          <td><strong><?= e($f['codigo']) ?></strong></td>
          <td><?= e($f['origem']) ?> <span class="card-meta">→ <?= e($f['destino']) ?></span></td>
          <td><?= e($f['transportador']) ?></td>
          <td><?= e($f['itens']) ?></td>
          <td><?= status_badge($f['status']) ?></td>
          <td class="text-end"><strong><?= moeda($f['valor']) ?></strong></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$fretes): ?><tr><td colspan="6"><div class="empty m-2">Nenhum frete em <?= data_br($data) ?>. Escolha outra data.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
