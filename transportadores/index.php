<?php
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['excluir'])) {
    try {
        db()->prepare('DELETE FROM transportadores WHERE id = ?')->execute([(int)$_POST['excluir']]);
        flash('success', 'Transportador excluído.');
    } catch (PDOException $ex) {
        flash('danger', 'Este transportador possui fretes e não pode ser excluído.');
    }
    redirect('transportadores/index.php');
}

$q = trim($_GET['q'] ?? '');
$st = db()->prepare('SELECT t.*, (SELECT COUNT(*) FROM fretes f WHERE f.transportador_id = t.id) AS total_fretes
                       FROM transportadores t WHERE t.nome LIKE :a OR t.documento LIKE :b ORDER BY t.nome');
$st->execute([':a' => "%$q%", ':b' => "%$q%"]);
$lista = $st->fetchAll();

$active = 'transportadores'; $secao = 'CADASTROS'; $titulo = 'Transportadores';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title">Transportadores</h1>
<p class="page-sub">Parceiros responsáveis pelas entregas.</p>

<div class="toolbar">
  <form class="search" method="get">
    <i class="bi bi-search"></i>
    <input type="text" name="q" value="<?= e($q) ?>" class="form-control" placeholder="Buscar nome ou CPF/CNPJ...">
  </form>
  <a href="form.php" class="btn btn-dark"><i class="bi bi-plus-lg"></i> Novo transportador</a>
</div>

<?php if (!$lista): ?>
  <div class="empty">Nenhum transportador encontrado. Cadastre o primeiro parceiro.</div>
<?php endif; ?>

<div class="row g-3">
  <?php foreach ($lista as $t): ?>
    <div class="col-12 col-xl-6">
      <div class="card-x row-card">
        <span class="icon-box icon-purple"><i class="bi bi-people"></i></span>
        <div class="flex-grow-1">
          <h2 class="card-name"><?= e($t['nome']) ?></h2>
          <div class="card-line"><?= e($t['documento']) ?><?= $t['telefone'] ? ' · ' . e($t['telefone']) : '' ?></div>
          <div class="card-meta"><?= e($t['veiculo'] ?: 'Veículo não informado') ?><?= $t['placa'] ? ' · ' . e($t['placa']) : '' ?> · <?= (int)$t['total_fretes'] ?> frete(s)</div>
        </div>
        <div class="actions">
          <a href="form.php?id=<?= $t['id'] ?>" title="Editar"><i class="bi bi-pencil"></i></a>
          <form method="post" onsubmit="return confirm('Excluir este transportador?')">
            <input type="hidden" name="excluir" value="<?= $t['id'] ?>">
            <button title="Excluir"><i class="bi bi-trash"></i></button>
          </form>
        </div>
      </div>
    </div>
  <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
