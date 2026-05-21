<?php
declare(strict_types=1);
require_once __DIR__ . '/../../fpdf/fpdf.php';

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->SetTextColor(149,6,6);
$pdf->Cell(0,10,utf8_decode('Catastro Chilecito - Reporte de Minutas Vendidas'),0,1,'C');
$pdf->Ln(5);

// Cabecera de tabla
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(149,6,6);
$pdf->SetTextColor(255);
$pdf->Cell(35,7,'Fecha',1,0,'C',true);
$pdf->Cell(50,7,'Minuta',1,0,'C',true);
$pdf->Cell(20,7,utf8_decode('Nº'),1,0,'C',true);
$pdf->Cell(20,7,'Tipo',1,0,'C',true);
$pdf->Cell(60,7,'Comprador',1,0,'C',true);
$pdf->Cell(25,7,'Total',1,0,'C',true);
$pdf->Cell(55,7,'Observaciones',1,1,'C',true);

$pdf->SetFont('Arial','',9);
$pdf->SetTextColor(0);
foreach ($ventas as $v) {
    $pdf->Cell(35,6,date('d/m/Y H:i',strtotime($v['fecha'])),1);
    $pdf->Cell(50,6,utf8_decode($v['minuta_nombre']),1);
    $pdf->Cell(20,6,$v['numero_minuta'],1,0,'C');
    $pdf->Cell(20,6,$v['tipo_precio'],1,0,'C');
    $pdf->Cell(60,6,utf8_decode($v['comprador_nombre']),1);
    $pdf->Cell(25,6,'$'.number_format($v['total'],2),1,0,'R');
    $pdf->Cell(55,6,utf8_decode($v['observaciones'] ?? ''),1,1);
}
$pdf->Output('I','reporte_minutas.pdf');