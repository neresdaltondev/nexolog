<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    try {
        db()->prepare('DELETE FROM produtos WHERE id = ?')->execute([(int)$_POST['excluir']]);
        flash('success', 'Produto excluído.');
    } catch (PDOException $ex) {
        flash('danger', 'Este produto está vinculado a fretes e não pode ser excluído.');
    }
    redirect('produtos/index.php');
}

$q = trim($_GET['q'] ?? '');
$st = db()->prepare('SELECT * FROM produtos WHERE nome LIKE :a OR sku LIKE :b ORDER BY nome');
$st->execute([':a' => "%$q%", ':b' => "%$q%"]);
$produtos = $st->fetchAll();

$active = 'produtos'; $secao = 'CADASTROS'; $titulo = 'Produtos';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Produtos</h1>
<p class="page-sub">Gerencie os itens disponíveis para transporte.</p>

<div class="toolbar">
  <form class="search" method="get">
    <i class="bi bi-search"></i>
    <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Buscar produto ou SKU...">
  </form>
  <a href="form.php" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Novo produto</a>
</div>

<?php if (!$produtos): ?>
  <div class="empty">Nenhum produto encontrado. Cadastre o primeiro em <strong>Novo produto</strong>.</div>
<?php endif; ?>

<div class="row g-3">
  <?php foreach ($produtos as $p): ?>
    <div class="col-12 col-md-6 col-xl-4">
      <div class="card-x">
        <div class="d-flex justify-content-between">
          <span class="icon-box"><i class="bi bi-box-seam"></i></span>
          <div class="actions">
            <a href="form.php?id=<?= $p['id'] ?>" title="Editar"><i class="bi bi-pencil"></i></a>
            <form method="post" onsubmit="return confirm('Excluir este produto?')">
              <input type="hidden" name="excluir" value="<?= $p['id'] ?>">
              <button title="Excluir"><i class="bi bi-trash"></i></button>
            </form>
          </div>
        </div>
        <h2 class="card-name mt-4"><?= e($p['nome']) ?></h2>
        <div class="card-meta">SKU <?= e($p['sku']) ?></div>
        <div class="card-foot"><span>Peso unitário</span><strong><?= numero_br($p['peso'], 3) ?> kg/<?= e($p['unidade']) ?></strong></div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
