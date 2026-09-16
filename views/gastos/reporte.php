<?php
declare(strict_types=1);
require_once __DIR__ . '/../../fpdf/fpdf.php';

$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(149,6,6);
$pdf->Cell(0,10,'Catastro Chilecito - Reporte de Gastos',0,1,'C');
$pdf->Ln(4);

// Filtro aplicado (opcional)
if (isset($inicio)) {
    $pdf->SetFont('Arial','',9);
    $pdf->SetTextColor(0);
    $pdf->Cell(0,6,'Periodo: ' . date('d/m/Y', strtotime($inicio)) . ' - ' . date('d/m/Y', strtotime($fin)),0,1,'C');
    $pdf->Ln(4);
}

// Cabecera
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(149,6,6);
$pdf->SetTextColor(255);
$pdf->Cell(30,7,'Fecha',1,0,'C',true);
$pdf->Cell(70,7,'Descripcion',1,0,'C',true);
$pdf->Cell(40,7,'Nro. Factura',1,0,'C',true);
$pdf->Cell(30,7,'Importe',1,0,'C',true);
$pdf->Cell(30,7,'Usuario',1,1,'C',true);

// Datos
$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0);
$totalGeneral = 0;
foreach ($gastos as $g) {
    $pdf->Cell(30,6,date('d/m/Y', strtotime($g['fecha'])),1);
    $pdf->Cell(70,6,$g['descripcion'],1);
    $pdf->Cell(40,6,$g['numero_factura'],1);
    $importe = (float)$g['importe'];
    $pdf->Cell(30,6,'$'.number_format($importe,2),1,0,'R');
    $pdf->Cell(30,6,$g['usuario_nombre'],1,1);
    $totalGeneral += $importe;
}

// Total
$pdf->SetFont('Arial','B',10);
$pdf->Cell(140,7,'Total general',1,0,'R');
$pdf->Cell(30,7,'$'.number_format($totalGeneral,2),1,1,'R');

$pdf->Output('I','reporte_gastos.pdf');