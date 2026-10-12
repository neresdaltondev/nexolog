<?php
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

$produtos = $pdo->query('SELECT id, nome, sku, peso, unidade FROM produtos ORDER BY nome')->fetchAll();
$enderecos = $pdo->query('SELECT id, nome, cidade, uf FROM enderecos ORDER BY nome')->fetchAll();
$transps = $pdo->query('SELECT id, nome FROM transportadores ORDER BY nome')->fetchAll();

$f = ['origem_id' => '', 'destino_id' => '', 'transportador_id' => '', 'data_frete' => date('Y-m-d'),
      'status' => 'Agendado', 'valor' => '', 'observacoes' => ''];
$itens = [];

if ($id) {
    $st = $pdo->prepare('SELECT * FROM fretes WHERE id = ?');
    $st->execute([$id]);
    $f = $st->fetch();
    if (!$f) { flash('danger', 'Frete não encontrado.'); redirect('fretes/index.php'); }
    $f['valor'] = number_format((float)$f['valor'], 2, ',', '.');
    $si = $pdo->prepare('SELECT produto_id, quantidade FROM frete_itens WHERE frete_id = ?');
    $si->execute([$id]);
    $itens = $si->fetchAll();
}

$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $f = [
        'origem_id'        => (int)($_POST['origem_id'] ?? 0),
        'destino_id'       => (int)($_POST['destino_id'] ?? 0),
        'transportador_id' => (int)($_POST['transportador_id'] ?? 0),
        'data_frete'       => $_POST['data_frete'] ?? '',
        'status'           => $_POST['status'] ?? 'Agendado',
        'valor'            => trim($_POST['valor'] ?? ''),
        'observacoes'      => trim($_POST['observacoes'] ?? ''),
    ];

    // junta linhas repetidas do mesmo produto somando as quantidades
    $itens = [];
    foreach (($_POST['produto_id'] ?? []) as $i => $pid) {
        $pid = (int)$pid;
        $qtd = (int)($_POST['quantidade'][$i] ?? 0);
        if ($pid > 0 && $qtd > 0) {
            $itens[$pid] = ($itens[$pid] ?? 0) + $qtd;
        }
    }
    $lista = [];
    foreach ($itens as $pid => $qtd) { $lista[] = ['produto_id' => $pid, 'quantidade' => $qtd]; }
    $itens = $lista;

    if (!$f['origem_id'])        $erros[] = 'Selecione o endereço de origem.';
    if (!$f['destino_id'])       $erros[] = 'Selecione o endereço de destino.';
    if ($f['origem_id'] && $f['origem_id'] === $f['destino_id']) $erros[] = 'Origem e destino não podem ser o mesmo endereço.';
    if (!$f['transportador_id']) $erros[] = 'Selecione o transportador.';
    if (!DateTime::createFromFormat('Y-m-d', $f['data_frete'])) $erros[] = 'Informe uma data válida.';
    if (!isset(STATUS_FRETE[$f['status']])) $erros[] = 'Status inválido.';
    if (decimal_br($f['valor']) <= 0) $erros[] = 'Informe o valor do frete.';
    if (!$itens)                 $erros[] = 'Adicione pelo menos um produto ao frete.';

    if (!$erros) {
        try {
            $pdo->beginTransaction();
            $dados = [$f['origem_id'], $f['destino_id'], $f['transportador_id'], $f['data_frete'],
                      $f['status'], decimal_br($f['valor']), $f['observacoes'] ?: null];
            if ($id) {
                $pdo->prepare('UPDATE fretes SET origem_id=?, destino_id=?, transportador_id=?, data_frete=?, status=?, valor=?, observacoes=? WHERE id=?')
                    ->execute([...$dados, $id]);
                $pdo->prepare('DELETE FROM frete_itens WHERE frete_id = ?')->execute([$id]);
                $freteId = $id;
            } else {
                $pdo->prepare('INSERT INTO fretes (origem_id, destino_id, transportador_id, data_frete, status, valor, observacoes) VALUES (?,?,?,?,?,?,?)')
                    ->execute($dados);
                $freteId = (int)$pdo->lastInsertId();
                $pdo->prepare('UPDATE fretes SET codigo = ? WHERE id = ?')->execute([frete_codigo($freteId), $freteId]);
            }
            $ins = $pdo->prepare('INSERT INTO frete_itens (frete_id, produto_id, quantidade) VALUES (?,?,?)');
            foreach ($itens as $it) { $ins->execute([$freteId, $it['produto_id'], $it['quantidade']]); }
            $pdo->commit();
            flash('success', $id ? 'Frete atualizado.' : 'Frete ' . frete_codigo($freteId) . ' gerado com sucesso.');
            redirect('fretes/ver.php?id=' . $freteId);
        } catch (PDOException $ex) {
            $pdo->rollBack();
            $erros[] = 'Não foi possível salvar o frete. Tente novamente.';
        }
    }
}
if (!$itens) { $itens = [['produto_id' => '', 'quantidade' => 1]]; }

function linha_item(array $produtos, $selecionado, $qtd): void { ?>
  <div class="item-row">
    <select name="produto_id[]" class="form-select">
      <option value="">Selecione o produto...</option>
      <?php foreach ($produtos as $p): ?>
        <option value="<?= $p['id'] ?>" <?= (string)$selecionado === (string)$p['id'] ? 'selected' : '' ?>>
          <?= e($p['nome']) ?> (<?= e($p['sku']) ?> · <?= numero_br($p['peso'], 3) ?> kg/<?= e($p['unidade']) ?>)
        </option>
      <?php endforeach; ?>
    </select>
    <input type="number" name="quantidade[]" class="form-control" min="1" value="<?= e($qtd) ?>" aria-label="Quantidade">
    <button type="button" class="btn btn-light btn-remove" title="Remover produto"><i class="bi bi-x-lg"></i></button>
  </div>
<?php }

$active = 'fretes'; $secao = 'OPERAÇÃO'; $titulo = $id ? 'Editar frete' : 'Gerar novo frete';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title"><?= e($titulo) ?></h1>
<p class="page-sub">Associe origem, destino, transportador e os produtos que serão levados.</p>

<?php if (!$produtos || count($enderecos) < 2 || !$transps): ?>
  <div class="alert alert-warning">
    Para gerar um frete é preciso ter cadastrado ao menos
    <a href="../produtos/form.php">1 produto</a>,
    <a href="../enderecos/form.php">2 endereços</a> (origem e destino) e
    <a href="../transportadores/form.php">1 transportador</a>.
  </div>
<?php endif; ?>

<?php foreach ($erros as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>

<form method="post" class="form-card">
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="origem_id">Origem</label>
      <select class="form-select" id="origem_id" name="origem_id" required>
        <option value="">Selecione...</option>
        <?php foreach ($enderecos as $en): ?>
          <option value="<?= $en['id'] ?>" <?= (int)$f['origem_id'] === (int)$en['id'] ? 'selected' : '' ?>><?= e($en['nome']) ?> — <?= e($en['cidade']) ?>/<?= e($en['uf']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="destino_id">Destino</label>
      <select class="form-select" id="destino_id" name="destino_id" required>
        <option value="">Selecione...</option>
        <?php foreach ($enderecos as $en): ?>
          <option value="<?= $en['id'] ?>" <?= (int)$f['destino_id'] === (int)$en['id'] ? 'selected' : '' ?>><?= e($en['nome']) ?> — <?= e($en['cidade']) ?>/<?= e($en['uf']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-6">
      <label class="form-label" for="transportador_id">Transportador</label>
      <select class="form-select" id="transportador_id" name="transportador_id" required>
        <option value="">Selecione...</option>
        <?php foreach ($transps as $t): ?>
          <option value="<?= $t['id'] ?>" <?= (int)$f['transportador_id'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['nome']) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="data_frete">Data do frete</label>
      <input type="date" class="form-control" id="data_frete" name="data_frete" value="<?= e($f['data_frete']) ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="status">Status</label>
      <select class="form-select" id="status" name="status">
        <?php foreach (array_keys(STATUS_FRETE) as $s): ?>
          <option <?= $f['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="valor">Valor do frete (R$)</label>
      <input class="form-control" id="valor" name="valor" inputmode="decimal" value="<?= e($f['valor']) ?>" placeholder="2.840,00" required>
    </div>
    <div class="col-md-8">
      <label class="form-label" for="observacoes">Observações</label>
      <input class="form-control" id="observacoes" name="observacoes" value="<?= e($f['observacoes']) ?>" placeholder="Opcional">
    </div>
  </div>

  <hr class="my-4">
  <div class="d-flex justify-content-between align-items-center mb-2">
    <div>
      <h2 class="card-name mb-0">Produtos do frete</h2>
      <div class="card-meta">Um transportador pode levar vários produtos no mesmo frete.</div>
    </div>
    <button type="button" class="btn btn-outline-dark btn-sm" id="addItem"><i class="bi bi-plus-lg"></i> Adicionar produto</button>
  </div>
  <div class="item-head"><span>Produto</span><span>Quantidade</span><span></span></div>
  <div id="itens">
    <?php foreach ($itens as $it) { linha_item($produtos, $it['produto_id'], $it['quantidade']); } ?>
  </div>
  <template id="tpl-item"><?php linha_item($produtos, '', 1); ?></template>

  <div class="form-actions">
    <a href="index.php" class="btn btn-light">Cancelar</a>
    <button class="btn btn-dark"><?= $id ? 'Salvar alterações' : 'Gerar frete' ?></button>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
