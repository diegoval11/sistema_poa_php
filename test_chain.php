<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\Spreadsheet;

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Simulate activity row 19
// Month 1 realized (H19) is 1
$sheet->setCellValue('H19', 1);

// I19 formula
$sheet->setCellValue('I19', '=IF(H19>=1,"100%","0")');

// S19 formula (Q1 unplanned activity)
$sheet->setCellValue('S19', '=IF(OR(I19="100%",M19="100%",Q19="100%"),"100%","VALORES NO COLOCADOS")');

// Header row 18 formula (summary of unplanned Q1)
$sheet->setCellValue('S18', '=IF(COUNTIF(S19:S19,"100%")>0,"100%","VALORES NO COLOCADOS")');

// Row 12 (Planned Q1)
$sheet->setCellValue('S12', 0.5);

// Row 11 (Final result Q1)
// IF(S18="100%",((S12*0.8)+(1*0.2)),S12) -> wait, the formula used S18*0.2, let's see what happens if S18 is string
$sheet->setCellValue('S11', '=IF(S18="100%",((S12*0.8)+(S18*0.2)),S12)');

echo "H19 Value: " . $sheet->getCell('H19')->getValue() . "\n";
echo "I19 Calc: " . $sheet->getCell('I19')->getCalculatedValue() . "\n";
echo "S19 Calc: " . $sheet->getCell('S19')->getCalculatedValue() . "\n";
echo "S18 Calc: " . $sheet->getCell('S18')->getCalculatedValue() . "\n";
echo "S11 Calc: " . $sheet->getCell('S11')->getCalculatedValue() . "\n";
