<?php
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$id = (int)($_GET['id'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fid = (int)($_POST['id'] ?? 0);
    if (isset($_POST['excluir'])) {
        $pdo->prepare('DELETE FROM fretes WHERE id = ?')->execute([$fid]);
        flash('success', 'Frete excluído.');
        redirect('fretes/index.php');
    }
    if (isset($_POST['status']) && isset(STATUS_FRETE[$_POST['status']])) {
        $pdo->prepare('UPDATE fretes SET status = ? WHERE id = ?')->execute([$_POST['status'], $fid]);
        flash('success', 'Status atualizado para "' . $_POST['status'] . '".');
    }
    redirect('fretes/ver.php?id=' . $fid);
}

$st = $pdo->prepare("SELECT f.*, o.nome AS origem_nome, o.logradouro AS o_log, o.numero AS o_num, o.cidade AS o_cid, o.uf AS o_uf, o.cep AS o_cep,
                            d.nome AS destino_nome, d.logradouro AS d_log, d.numero AS d_num, d.cidade AS d_cid, d.uf AS d_uf, d.cep AS d_cep,
                            t.nome AS transportador, t.documento AS t_doc, t.telefone AS t_tel, t.veiculo AS t_vei, t.placa AS t_placa
                       FROM fretes f
                       JOIN enderecos o ON o.id = f.origem_id
                       JOIN enderecos d ON d.id = f.destino_id
                       JOIN transportadores t ON t.id = f.transportador_id
                      WHERE f.id = ?");
$st->execute([$id]);
$f = $st->fetch();
if (!$f) { flash('danger', 'Frete não encontrado.'); redirect('fretes/index.php'); }

$si = $pdo->prepare('SELECT i.quantidade, p.nome, p.sku, p.peso, p.unidade
                       FROM frete_itens i JOIN produtos p ON p.id = i.produto_id
                      WHERE i.frete_id = ? ORDER BY p.nome');
$si->execute([$id]);
$itens = $si->fetchAll();
$pesoTotal = 0;
foreach ($itens as $it) { $pesoTotal += $it['peso'] * $it['quantidade']; }

$active = 'fretes'; $secao = 'OPERAÇÃO'; $titulo = $f['codigo'];
require __DIR__ . '/../includes/header.php';
?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
  <div>
    <h1 class="page-title"><?= e($f['codigo']) ?> <?= status_badge($f['status']) ?></h1>
    <p class="page-sub">Frete de <?= data_br($f['data_frete']) ?> · <?= moeda($f['valor']) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a href="index.php" class="btn btn-light">Voltar</a>
    <a href="form.php?id=<?= $f['id'] ?>" class="btn btn-dark"><i class="bi bi-pencil"></i> Editar</a>
    <form method="post" onsubmit="return confirm('Excluir este frete?')">
      <input type="hidden" name="id" value="<?= $f['id'] ?>">
      <button name="excluir" value="1" class="btn btn-outline-danger"><i class="bi bi-trash"></i> Excluir</button>
    </form>
  </div>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-4"><div class="card-x h-100">
    <div class="card-meta mb-1">Origem</div>
    <h2 class="card-name"><?= e($f['origem_nome']) ?></h2>
    <div class="card-line"><?= e($f['o_log']) ?>, <?= e($f['o_num']) ?></div>
    <div class="card-meta"><?= e($f['o_cid']) ?> · <?= e($f['o_uf']) ?> · CEP <?= e($f['o_cep']) ?></div>
  </div></div>
  <div class="col-lg-4"><div class="card-x h-100">
    <div class="card-meta mb-1">Destino</div>
    <h2 class="card-name"><?= e($f['destino_nome']) ?></h2>
    <div class="card-line"><?= e($f['d_log']) ?>, <?= e($f['d_num']) ?></div>
    <div class="card-meta"><?= e($f['d_cid']) ?> · <?= e($f['d_uf']) ?> · CEP <?= e($f['d_cep']) ?></div>
  </div></div>
  <div class="col-lg-4"><div class="card-x h-100">
    <div class="card-meta mb-1">Transportador</div>
    <h2 class="card-name"><?= e($f['transportador']) ?></h2>
    <div class="card-line"><?= e($f['t_doc']) ?><?= $f['t_tel'] ? ' · ' . e($f['t_tel']) : '' ?></div>
    <div class="card-meta"><?= e($f['t_vei'] ?: 'Veículo não informado') ?><?= $f['t_placa'] ? ' · ' . e($f['t_placa']) : '' ?></div>
  </div></div>
</div>

<div class="card-x p-0 table-wrap mt-3">
  <div class="p-3 pb-2"><h2 class="card-name mb-0">Produtos transportados</h2></div>
  <table class="table table-x align-middle mb-0">
    <thead><tr><th>Produto</th><th>SKU</th><th class="text-end">Qtd.</th><th class="text-end">Peso unit.</th><th class="text-end">Peso total</th></tr></thead>
    <tbody>
      <?php foreach ($itens as $it): ?>
        <tr>
          <td><strong><?= e($it['nome']) ?></strong></td>
          <td><?= e($it['sku']) ?></td>
          <td class="text-end"><?= (int)$it['quantidade'] ?> <?= e($it['unidade']) ?></td>
          <td class="text-end"><?= numero_br($it['peso'], 3) ?> kg</td>
          <td class="text-end"><?= numero_br($it['peso'] * $it['quantidade'], 3) ?> kg</td>
        </tr>
      <?php endforeach; ?>
    </tbody>
    <tfoot><tr><td colspan="4" class="text-end">Peso total da carga</td><td class="text-end"><strong><?= numero_br($pesoTotal, 3) ?> kg</strong></td></tr></tfoot>
  </table>
</div>

<div class="row g-3 mt-1">
  <div class="col-lg-8">
    <?php if ($f['observacoes']): ?><div class="card-x"><div class="card-meta mb-1">Observações</div><?= e($f['observacoes']) ?></div><?php endif; ?>
  </div>
  <div class="col-lg-4">
    <form method="post" class="card-x">
      <input type="hidden" name="id" value="<?= $f['id'] ?>">
      <label class="form-label" for="status">Atualizar status</label>
      <div class="d-flex gap-2">
        <select class="form-select" id="status" name="status">
          <?php foreach (array_keys(STATUS_FRETE) as $s): ?><option <?= $f['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option><?php endforeach; ?>
        </select>
        <button class="btn btn-dark">Salvar</button>
      </div>
    </form>
  </div>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
