<?php

namespace app\upload;

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class UploadExcelController
{
    public function upload($files)
    {
        rename($files['file']['tmp_name'], '/var/www/excel.xlsx');
        chmod('/var/www/excel.xlsx', 0755);

        $reader = IOFactory::createReader('Xlsx');
        $spreadsheet = $reader->load('/var/www/excel.xlsx');
        $reader->setReadDataOnly(true);
        $data = $spreadsheet->getActiveSheet()->toArray();

        $headerItems = [];
        $students = [];
        foreach ($data as $key => $col) {
            if ($key === 0) {
                $headerItems = $col;
                continue;
            }

            $balls = $col[4];
            $snils = trim($col[5]);

            if (empty($students[$snils]['balls'])) {
                $students[$snils]['balls'] = 0;
            }

            if (empty($students[$snils]['subject'])) {
                $students[$snils]['subject'] = '';
            }

            $students[$snils]['name'] = $col[0];
            $students[$snils]['surname'] = $col[1];
            $students[$snils]['otchestvo'] = $col[2];
            $students[$snils]['subject'] .= ' / ' . $col[3];
            $students[$snils]['balls'] += $balls;
            $students[$snils]['snils'] = $snils;
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setTitle('Студенты');
        $spreadsheet->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->fromArray($headerItems);

        $row = 2;
        foreach ($students as $key => $student) {
            $studentRow[0] = $student['surname'];
            $studentRow[1] = $student['name'];
            $studentRow[2] = $student['otchestvo'];
            $studentRow[3] = $student['subject'];
            $studentRow[4] = $student['balls'];
            $studentRow[5] = $key;

            $sheet->fromArray($studentRow, null, 'A' . $row); // A2 start
            $row++;
        }

        foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
            $spreadsheet->setActiveSheetIndex($spreadsheet->getIndex($worksheet));
            $sheet = $spreadsheet->getActiveSheet();
            $cellIterator = $sheet->getRowIterator()->current()->getCellIterator();
            $cellIterator->setIterateOnlyExistingCells(true);
            /** @var $cell */
            foreach ($cellIterator as $cell) {
                $sheet->getColumnDimension($cell->getColumn())->setWidth(35);
            }
        }

        $writer = new Xlsx($spreadsheet);
        $downloadPath = '/public/' . date('m.d.Y H:i:s') .'-formatted-excel.xlsx';
        $savePath = '/var/www/public/' . date('m.d.Y H:i:s') .'-formatted-excel.xlsx';
        $writer->save($savePath);
        $spreadsheet->disconnectWorksheets();

        return [
            'downloadPath' => $downloadPath,
            'tableData' => $students,
        ];
    }
}