<?php

require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$sheet = new Spreadsheet();
$ws = $sheet->getActiveSheet();

$ws->fromArray(['username', 'email', 'password', 'profile'], null, 'A1');
$rows = [
    ['anakmagang01', 'anakmagang01@example.com', 'pass1234', 'AnakMagang'],
    ['dosenmagang01', 'dosenmagang01@example.com', 'pass1234', 'DosenMagang'],
    ['mahasiswamagang01', 'mahasiswamagang01@example.com', 'pass1234', 'MahasiswaMagang'],
    ['staffmagang01', 'staffmagang01@example.com', 'pass1234', 'StaffMagang'],
    ['tamumagang01', 'tamumagang01@example.com', 'pass1234', 'TamuMagang'],
    ['anakmagang02', 'anakmagang02@example.com', 'pass1234', 'AnakMagang'],
    ['dosenmagang02', 'dosenmagang02@example.com', 'pass1234', 'DosenMagang'],
    ['mahasiswamagang02', 'mahasiswamagang02@example.com', 'pass1234', 'MahasiswaMagang'],
    ['staffmagang02', 'staffmagang02@example.com', 'pass1234', 'StaffMagang'],
    ['tamumagang02', 'tamumagang02@example.com', 'pass1234', 'TamuMagang'],
];

$ws->fromArray($rows, null, 'A2');

$path = __DIR__ . '/../storage/app/hotspot-users-demo.xlsx';
$writer = new Xlsx($sheet);
$writer->save($path);

echo $path . PHP_EOL;
