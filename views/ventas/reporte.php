<?php

declare(strict_types=1);
require_once __DIR__ . '/../../fpdf/fpdf.php';

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();
$pdf->SetFont('Arial', 'B', 14);
$pdf->SetTextColor(149, 6, 6);
$pdf->Cell(0, 10, 'Catastro Chilecito - Reporte de Minutas Vendidas', 0, 1, 'C');
$pdf->SetFont('Arial', '', 9);
$pdf->SetTextColor(0);
$periodo = 'Todas las fechas';
if ($inicio !== null || $fin !== null) {
    $desde = $inicio !== null ? date('d/m/Y', strtotime($inicio)) : 'inicio';
    $hasta = $fin !== null ? date('d/m/Y', strtotime($fin)) : 'actualidad';
    $periodo = $desde . ' - ' . $hasta;
}
$filtroPago = $filtros['forma_pago'] === Venta::FILTRO_SIN_ESPECIFICAR
    ? 'Sin especificar'
    : ($filtros['forma_pago'] === null ? 'Todas' : Venta::etiquetaFormaPago($filtros['forma_pago']));
$pdf->Cell(0, 6, 'Periodo: ' . $periodo . ' | Forma de pago: ' . $filtroPago, 0, 1, 'C');
$pdf->Ln(2);

// El resumen usa la misma consulta y filtros que el detalle y cuenta ventas, no renglones.
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(240, 240, 240);
$pdf->Cell(58, 7, 'Categoria', 1, 0, 'C', true);
$pdf->Cell(28, 7, 'Ventas', 1, 0, 'C', true);
$pdf->Cell(42, 7, 'Importe', 1, 1, 'C', true);
$resumenFilas = [
    ['Ventas en efectivo', $resumen['cantidad_efectivo'], $resumen['total_efectivo']],
    ['Transferencias a cuenta', $resumen['cantidad_transferencia'], $resumen['total_transferencia']],
    ['Sin especificar', $resumen['cantidad_sin_especificar'], $resumen['total_sin_especificar']],
    ['Total general', $resumen['cantidad_general'], $resumen['total_general']],
];
$pdf->SetFont('Arial', '', 9);
foreach ($resumenFilas as $filaResumen) {
    $pdf->Cell(58, 6, $filaResumen[0], 1);
    $pdf->Cell(28, 6, (string)(int)$filaResumen[1], 1, 0, 'C');
    $pdf->Cell(42, 6, '$' . number_format((float)$filaResumen[2], 2), 1, 1, 'R');
}
$pdf->Ln(4);

// Cabecera de tabla
$pdf->SetFont('Arial', 'B', 9);
$pdf->SetFillColor(149, 6, 6);
$pdf->SetTextColor(255);
$pdf->Cell(28, 7, 'Fecha', 1, 0, 'C', true);
$pdf->Cell(35, 7, 'Comprador', 1, 0, 'C', true);
$pdf->Cell(38, 7, 'Forma de pago', 1, 0, 'C', true);
$pdf->Cell(42, 7, 'Minuta', 1, 0, 'C', true);
$pdf->Cell(15, 7, 'No.', 1, 0, 'C', true);
$pdf->Cell(18, 7, 'Tipo', 1, 0, 'C', true);
$pdf->Cell(22, 7, 'Precio', 1, 0, 'C', true);
$pdf->Cell(65, 7, 'Observaciones', 1, 1, 'C', true);

$pdf->SetFont('Arial', '', 8);
$pdf->SetTextColor(0);

foreach ($ventasConItems as $venta) {
    $fecha = date('d/m/Y H:i', strtotime($venta['fecha']));
    $comprador = $venta['comprador_nombre'];
    $formaPago = Venta::etiquetaFormaPago($venta['forma_pago'] ?? null);
    $obsVenta = $venta['observaciones'] ?? '';
    $items = $venta['items'];

    if (empty($items)) continue; // no debería ocurrir

    $primero = true;
    foreach ($items as $item) {
        if ($primero) {
            $pdf->Cell(28, 6, $fecha, 1);
            $pdf->Cell(35, 6, $comprador, 1);
            $pdf->Cell(38, 6, $formaPago, 1);
            $primero = false;
        } else {
            $pdf->Cell(28, 6, '', 1);
            $pdf->Cell(35, 6, '', 1);
            $pdf->Cell(38, 6, '', 1);
        }
        $pdf->Cell(42, 6, $item['minuta_nombre'], 1);
        $pdf->Cell(15, 6, $item['numero_minuta'], 1, 0, 'C');
        $pdf->Cell(18, 6, $item['tipo_precio'], 1, 0, 'C');
        $totalItem = (float) $item['precio_unitario'];
        $pdf->Cell(22, 6, '$' . number_format($totalItem, 2), 1, 0, 'R');
        $pdf->Cell(65, 6, $obsVenta, 1, 1);
        $obsVenta = ''; // solo mostrar en primera línea
    }
    // Línea de total de la venta (opcional)
    $pdf->SetFont('Arial', 'B', 8);
    $pdf->Cell(28, 6, '', 0);
    $pdf->Cell(35, 6, '', 0);
    $pdf->Cell(38, 6, '', 0);
    $pdf->Cell(42, 6, '', 0);
    $pdf->Cell(15, 6, '', 0);
    $pdf->Cell(18, 6, '', 0);
    $pdf->Cell(22, 6, 'Total: $' . number_format((float)$venta['total'], 2), 0, 0, 'R');
    $pdf->Cell(65, 6, '', 0, 1);
    $pdf->SetFont('Arial', '', 8);
}
$pdf->Output('I', 'reporte_minutas.pdf');
