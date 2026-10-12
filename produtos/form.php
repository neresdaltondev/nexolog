<?php
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$p = ['nome' => '', 'sku' => '', 'peso' => '', 'unidade' => 'un'];
if ($id) {
    $st = db()->prepare('SELECT * FROM produtos WHERE id = ?');
    $st->execute([$id]);
    $p = $st->fetch();
    if (!$p) { flash('danger', 'Produto não encontrado.'); redirect('produtos/index.php'); }
    $p['peso'] = numero_br($p['peso'], 3);
}

$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $p = [
        'nome'    => trim($_POST['nome'] ?? ''),
        'sku'     => strtoupper(trim($_POST['sku'] ?? '')),
        'peso'    => trim($_POST['peso'] ?? ''),
        'unidade' => $_POST['unidade'] ?? 'un',
    ];
    if ($p['nome'] === '') $erros[] = 'Informe o nome do produto.';
    if ($p['sku'] === '')  $erros[] = 'Informe o SKU.';
    if (decimal_br($p['peso']) <= 0) $erros[] = 'Informe um peso unitário maior que zero.';
    if (!in_array($p['unidade'], ['un', 'cx', 'kg', 'pct', 'pç'], true)) $erros[] = 'Unidade inválida.';

    if (!$erros) {
        $dup = db()->prepare('SELECT id FROM produtos WHERE sku = ? AND id <> ?');
        $dup->execute([$p['sku'], $id]);
        if ($dup->fetch()) $erros[] = 'Já existe um produto com este SKU.';
    }
    if (!$erros) {
        $dados = [$p['nome'], $p['sku'], decimal_br($p['peso']), $p['unidade']];
        if ($id) {
            db()->prepare('UPDATE produtos SET nome=?, sku=?, peso=?, unidade=? WHERE id=?')->execute([...$dados, $id]);
            flash('success', 'Produto atualizado.');
        } else {
            db()->prepare('INSERT INTO produtos (nome, sku, peso, unidade) VALUES (?,?,?,?)')->execute($dados);
            flash('success', 'Produto cadastrado.');
        }
        redirect('produtos/index.php');
    }
}

$active = 'produtos'; $secao = 'CADASTROS'; $titulo = $id ? 'Editar produto' : 'Novo produto';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title"><?= e($titulo) ?></h1>
<p class="page-sub">Preencha os dados do item que será transportado.</p>

<?php foreach ($erros as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>

<form method="post" class="form-card">
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label" for="nome">Nome do produto</label>
      <input class="form-control" id="nome" name="nome" value="<?= e($p['nome']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="sku">SKU</label>
      <input class="form-control" id="sku" name="sku" value="<?= e($p['sku']) ?>" placeholder="MOB-042" required>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="peso">Peso unitário (kg)</label>
      <input class="form-control" id="peso" name="peso" inputmode="decimal" value="<?= e($p['peso']) ?>" placeholder="14,5" required>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="unidade">Unidade</label>
      <select class="form-select" id="unidade" name="unidade">
        <?php foreach (['un' => 'Unidade (un)', 'cx' => 'Caixa (cx)', 'kg' => 'Quilo (kg)', 'pct' => 'Pacote (pct)', 'pç' => 'Peça (pç)'] as $v => $l): ?>
          <option value="<?= $v ?>" <?= $p['unidade'] === $v ? 'selected' : '' ?>><?= $l ?></option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-actions">
    <a href="index.php" class="btn btn-light">Cancelar</a>
    <button class="btn btn-dark">Salvar produto</button>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
