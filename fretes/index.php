<?php
require_once __DIR__ . '/../includes/functions.php';

$q = trim($_GET['q'] ?? '');
$sql = "SELECT f.*, o.nome AS origem, d.nome AS destino, t.nome AS transportador,
          (SELECT COUNT(*) FROM frete_itens i WHERE i.frete_id = f.id) AS qtd_produtos
        FROM fretes f
        JOIN enderecos o ON o.id = f.origem_id
        JOIN enderecos d ON d.id = f.destino_id
        JOIN transportadores t ON t.id = f.transportador_id
        WHERE f.codigo LIKE :a OR o.nome LIKE :b OR d.nome LIKE :c OR t.nome LIKE :d
        ORDER BY f.data_frete DESC, f.id DESC";
$st = db()->prepare($sql);
$st->execute([':a' => "%$q%", ':b' => "%$q%", ':c' => "%$q%", ':d' => "%$q%"]);
$fretes = $st->fetchAll();

$active = 'fretes'; $secao = 'OPERAÇÃO'; $titulo = 'Fretes';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Fretes</h1>
<p class="page-sub">Crie e acompanhe todas as ordens de transporte.</p>

<div class="toolbar">
  <form class="search" method="get">
    <i class="bi bi-search"></i>
    <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Buscar por código, rota...">
  </form>
  <a href="form.php" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Gerar novo frete</a>
</div>

<div class="card-x p-0 table-wrap">
  <table class="table table-x align-middle mb-0">
    <thead><tr><th>Frete</th><th>Rota</th><th>Transportador</th><th>Data</th><th>Status</th><th class="text-end">Valor</th></tr></thead>
    <tbody>
      <?php foreach ($fretes as $f): ?>
        <tr onclick="location.href='ver.php?id=<?= $f['id'] ?>'" class="clickable">
          <td><strong><?= e($f['codigo']) ?></strong><div class="card-meta"><?= (int)$f['qtd_produtos'] ?> produto<?= $f['qtd_produtos'] == 1 ? '' : 's' ?></div></td>
          <td><strong><?= e($f['origem']) ?></strong><div class="card-meta">→ <?= e($f['destino']) ?></div></td>
          <td><?= e($f['transportador']) ?></td>
          <td><?= data_br($f['data_frete']) ?></td>
          <td><?= status_badge($f['status']) ?></td>
          <td class="text-end"><strong><?= moeda($f['valor']) ?></strong></td>
        </tr>
      <?php endforeach; ?>
      <?php if (!$fretes): ?><tr><td colspan="6"><div class="empty m-2">Nenhum frete encontrado. Use <strong>Gerar novo frete</strong> para criar o primeiro.</div></td></tr><?php endif; ?>
    </tbody>
  </table>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
