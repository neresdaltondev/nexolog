<?php
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$en = ['nome' => '', 'cep' => '', 'logradouro' => '', 'numero' => '', 'complemento' => '', 'bairro' => '', 'cidade' => '', 'uf' => 'PR'];
if ($id) {
    $st = db()->prepare('SELECT * FROM enderecos WHERE id = ?');
    $st->execute([$id]);
    $en = $st->fetch();
    if (!$en) { flash('danger', 'Endereço não encontrado.'); redirect('enderecos/index.php'); }
}

$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($en as $k => $v) { $en[$k] = trim($_POST[$k] ?? ''); }
    $en['uf'] = strtoupper($en['uf']);

    if ($en['nome'] === '')       $erros[] = 'Informe o nome do local (ex.: Hub Campinas).';
    if (!preg_match('/^\d{5}-?\d{3}$/', $en['cep'])) $erros[] = 'CEP inválido. Use o formato 00000-000.';
    if ($en['logradouro'] === '') $erros[] = 'Informe o logradouro.';
    if ($en['numero'] === '')     $erros[] = 'Informe o número.';
    if ($en['cidade'] === '')     $erros[] = 'Informe a cidade.';
    if (!in_array($en['uf'], UFS, true)) $erros[] = 'Selecione uma UF válida.';

    if (!$erros) {
        $cep = preg_replace('/^(\d{5})-?(\d{3})$/', '$1-$2', $en['cep']);
        $dados = [$en['nome'], $cep, $en['logradouro'], $en['numero'], $en['complemento'] ?: null, $en['bairro'] ?: null, $en['cidade'], $en['uf']];
        if ($id) {
            db()->prepare('UPDATE enderecos SET nome=?, cep=?, logradouro=?, numero=?, complemento=?, bairro=?, cidade=?, uf=? WHERE id=?')->execute([...$dados, $id]);
            flash('success', 'Endereço atualizado.');
        } else {
            db()->prepare('INSERT INTO enderecos (nome, cep, logradouro, numero, complemento, bairro, cidade, uf) VALUES (?,?,?,?,?,?,?,?)')->execute($dados);
            flash('success', 'Endereço cadastrado.');
        }
        redirect('enderecos/index.php');
    }
}

$active = 'enderecos'; $secao = 'CADASTROS'; $titulo = $id ? 'Editar endereço' : 'Novo endereço';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title"><?= e($titulo) ?></h1>
<p class="page-sub">Digite o CEP para preencher o endereço automaticamente.</p>

<?php foreach ($erros as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>

<form method="post" class="form-card">
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label" for="nome">Nome do local</label>
      <input class="form-control" id="nome" name="nome" value="<?= e($en['nome']) ?>" placeholder="Centro de Distribuição SP" required>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="cep">CEP</label>
      <input class="form-control" id="cep" name="cep" value="<?= e($en['cep']) ?>" placeholder="00000-000" maxlength="9" required>
    </div>
    <div class="col-md-8">
      <label class="form-label" for="logradouro">Logradouro</label>
      <input class="form-control" id="logradouro" name="logradouro" value="<?= e($en['logradouro']) ?>" required>
    </div>
    <div class="col-md-2">
      <label class="form-label" for="numero">Número</label>
      <input class="form-control" id="numero" name="numero" value="<?= e($en['numero']) ?>" required>
    </div>
    <div class="col-md-2">
      <label class="form-label" for="complemento">Complemento</label>
      <input class="form-control" id="complemento" name="complemento" value="<?= e($en['complemento']) ?>">
    </div>
    <div class="col-md-4">
      <label class="form-label" for="bairro">Bairro</label>
      <input class="form-control" id="bairro" name="bairro" value="<?= e($en['bairro']) ?>">
    </div>
    <div class="col-md-5">
      <label class="form-label" for="cidade">Cidade</label>
      <input class="form-control" id="cidade" name="cidade" value="<?= e($en['cidade']) ?>" required>
    </div>
    <div class="col-md-3">
      <label class="form-label" for="uf">UF</label>
      <select class="form-select" id="uf" name="uf">
        <?php foreach (UFS as $uf): ?><option <?= $en['uf'] === $uf ? 'selected' : '' ?>><?= $uf ?></option><?php endforeach; ?>
      </select>
    </div>
  </div>
  <div class="form-actions">
    <a href="index.php" class="btn btn-light">Cancelar</a>
    <button class="btn btn-dark">Salvar endereço</button>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
