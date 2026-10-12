<?php
require_once __DIR__ . '/../includes/functions.php';

$data = $_GET['data'] ?? '';
if (!DateTime::createFromFormat('Y-m-d', $data)) {
    flash('danger', 'Data inválida para o relatório.');
    redirect('relatorios/index.php');
}
$fretes = fretes_do_dia($data);

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="relatorio-fretes-' . $data . '.csv"');

$out = fopen('php://output', 'w');
fwrite($out, "\xEF\xBB\xBF"); // BOM: o Excel abre com acentos corretos
fputcsv($out, ['Relatório de fretes', data_br($data)], ';');
fputcsv($out, [], ';');
fputcsv($out, ['Código', 'Origem', 'Destino', 'Transportador', 'Produtos', 'Peso total (kg)', 'Status', 'Valor (R$)'], ';');

$total = 0;
foreach ($fretes as $f) {
    if ($f['status'] !== 'Cancelado') { $total += $f['valor']; }
    fputcsv($out, [
        $f['codigo'], $f['origem'], $f['destino'], $f['transportador'], $f['itens'],
        number_format($f['peso_total'], 3, ',', ''), $f['status'], number_format($f['valor'], 2, ',', ''),
    ], ';');
}
fputcsv($out, [], ';');
fputcsv($out, ['Total (sem cancelados)', '', '', '', '', '', '', number_format($total, 2, ',', '')], ';');
fclose($out);
exit;
