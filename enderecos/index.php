<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    try {
        db()->prepare('DELETE FROM enderecos WHERE id = ?')->execute([(int)$_POST['excluir']]);
        flash('success', 'Endereço excluído.');
    } catch (PDOException $ex) {
        flash('danger', 'Este endereço é usado em fretes e não pode ser excluído.');
    }
    redirect('enderecos/index.php');
}

$q = trim($_GET['q'] ?? '');
$st = db()->prepare('SELECT * FROM enderecos WHERE nome LIKE :a OR cidade LIKE :b ORDER BY nome');
$st->execute([':a' => "%$q%", ':b' => "%$q%"]);
$enderecos = $st->fetchAll();

$active = 'enderecos'; $secao = 'CADASTROS'; $titulo = 'Endereços';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Endereços</h1>
<p class="page-sub">Organize os pontos de origem e destino.</p>

<div class="toolbar">
  <form class="search" method="get">
    <i class="bi bi-search"></i>
    <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Buscar nome ou cidade...">
  </form>
  <a href="form.php" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Novo endereço</a>
</div>

<?php if (!$enderecos): ?>
  <div class="empty">Nenhum endereço encontrado. Cadastre um ponto de origem ou destino.</div>
<?php endif; ?>

<div class="row g-3">
  <?php foreach ($enderecos as $en): ?>
    <div class="col-12 col-xl-6">
      <div class="card-x row-card">
        <span class="icon-box icon-blue"><i class="bi bi-geo-alt"></i></span>
        <div class="flex-grow-1">
          <h2 class="card-name"><?= e($en['nome']) ?></h2>
          <div class="card-line"><?= e($en['logradouro']) ?>, <?= e($en['numero']) ?><?= $en['complemento'] ? ' - ' . e($en['complemento']) : '' ?></div>
          <div class="card-meta"><?= e($en['cidade']) ?> · <?= e($en['uf']) ?> · CEP <?= e($en['cep']) ?></div>
        </div>
        <div class="actions">
          <a href="form.php?id=<?= $en['id'] ?>" title="Editar"><i class="bi bi-pencil"></i></a>
          <form method="post" onsubmit="return confirm('Excluir este endereço?')">
            <input type="hidden" name="excluir" value="<?= $en['id'] ?>">
            <button title="Excluir"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
