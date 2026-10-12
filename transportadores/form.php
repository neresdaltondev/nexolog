<?php
require_once __DIR__ . '/../includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$t = ['nome' => '', 'documento' => '', 'telefone' => '', 'email' => '', 'veiculo' => '', 'placa' => ''];
if ($id) {
    $st = db()->prepare('SELECT * FROM transportadores WHERE id = ?');
    $st->execute([$id]);
    $t = $st->fetch();
    if (!$t) { flash('danger', 'Transportador não encontrado.'); redirect('transportadores/index.php'); }
}

$erros = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($t as $k => $v) { $t[$k] = trim($_POST[$k] ?? ''); }
    $t['placa'] = strtoupper($t['placa']);

    if ($t['nome'] === '') $erros[] = 'Informe o nome do transportador.';
    $digitos = preg_replace('/\D/', '', $t['documento']);
    if (!in_array(strlen($digitos), [11, 14], true)) $erros[] = 'Informe um CPF (11 dígitos) ou CNPJ (14 dígitos).';
    if ($t['email'] !== '' && !filter_var($t['email'], FILTER_VALIDATE_EMAIL)) $erros[] = 'E-mail inválido.';

    if (!$erros) {
        $dados = [$t['nome'], $t['documento'], $t['telefone'] ?: null, $t['email'] ?: null, $t['veiculo'] ?: null, $t['placa'] ?: null];
        if ($id) {
            db()->prepare('UPDATE transportadores SET nome=?, documento=?, telefone=?, email=?, veiculo=?, placa=? WHERE id=?')->execute([...$dados, $id]);
            flash('success', 'Transportador atualizado.');
        } else {
            db()->prepare('INSERT INTO transportadores (nome, documento, telefone, email, veiculo, placa) VALUES (?,?,?,?,?,?)')->execute($dados);
            flash('success', 'Transportador cadastrado.');
        }
        redirect('transportadores/index.php');
    }
}

$active = 'transportadores'; $secao = 'CADASTROS'; $titulo = $id ? 'Editar transportador' : 'Novo transportador';
require __DIR__ . '/../includes/header.php';
?>
<h1 class="page-title"><?= e($titulo) ?></h1>
<p class="page-sub">A validade do CPF/CNPJ não é conferida, apenas o formato.</p>

<?php foreach ($erros as $er): ?><div class="alert alert-danger py-2"><?= e($er) ?></div><?php endforeach; ?>

<form method="post" class="form-card">
  <div class="row g-3">
    <div class="col-md-8">
      <label class="form-label" for="nome">Nome / Razão social</label>
      <input class="form-control" id="nome" name="nome" value="<?= e($t['nome']) ?>" required>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="documento">CPF ou CNPJ</label>
      <input class="form-control" id="documento" name="documento" value="<?= e($t['documento']) ?>" maxlength="18" required>
    </div>
    <div class="col-md-4">
      <label class="form-label" for="telefone">Telefone</label>
      <input class="form-control" id="telefone" name="telefone" value="<?= e($t['telefone']) ?>" placeholder="(00) 00000-0000">
    </div>
    <div class="col-md-8">
      <label class="form-label" for="email">E-mail</label>
      <input class="form-control" type="email" id="email" name="email" value="<?= e($t['email']) ?>">
    </div>
    <div class="col-md-8">
      <label class="form-label" for="veiculo">Veículo</label>
      <input class="form-control" id="veiculo" name="veiculo" value="<?= e($t['veiculo']) ?>" placeholder="Caminhão baú">
    </div>
    <div class="col-md-4">
      <label class="form-label" for="placa">Placa</label>
      <input class="form-control" id="placa" name="placa" value="<?= e($t['placa']) ?>" maxlength="8" placeholder="ABC-1D23">
    </div>
  </div>
  <div class="form-actions">
    <a href="index.php" class="btn btn-light">Cancelar</a>
    <button class="btn btn-dark">Salvar transportador</button>
  </div>
</form>
<?php require __DIR__ . '/../includes/footer.php'; ?>
