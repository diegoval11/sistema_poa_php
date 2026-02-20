<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

$sheet->setCellValue('H19', 1);
$sheet->setCellValue('I19', '=IF(H19>=1,"100%","0")');
$sheet->setCellValue('S19', '=IF(OR(I19="100%",M19="100%",Q19="100%"),"100%","VALORES NO COLOCADOS")');

// Test ISNUMBER(MATCH)
$sheet->setCellValue('S18', '=IF(ISNUMBER(MATCH("100%",S19:S19,0)),"100%","VALORES NO COLOCADOS")');

$sheet->setCellValue('S12', 0.5);
// Also test IF(S18="100%",S12*0.8+1*0.2) to avoid string multiplication bug in PhpSpreadsheet
$sheet->setCellValue('S11', '=IF(S18="100%",((S12*0.8)+(1*0.2)),S12)');

echo "S19 Calc: " . $sheet->getCell('S19')->getCalculatedValue() . "\n";
echo "S18 ISNUMBER MATCH Calc: " . $sheet->getCell('S18')->getCalculatedValue() . "\n";
echo "S11 Calc: " . $sheet->getCell('S11')->getCalculatedValue() . "\n";
