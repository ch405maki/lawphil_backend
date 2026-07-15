<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\ActImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ActImportController extends Controller
{
    public function import(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $file = $request->file('file');
            $import = new ActImport($file, 1);
            $result = $import->import();

            $message = "Successfully imported {$result['imported']} of {$result['total_rows']} records";
            if ($result['failed_count'] > 0) {
                $message .= " with {$result['failed_count']} error(s)";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $result
            ]);

        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $headers = [
                'A1' => 'act_number*',
                'B1' => 'date*',
                'C1' => 'citation',
                'D1' => 'tenure',
                'E1' => 'description',
                'F1' => 'url',
                'G1' => 'pdf_availability',
                'H1' => 'pdf_path',
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            $sheet->setCellValue('K1', 'INSTRUCTIONS:');
            $sheet->setCellValue('K2', '* = Required field (only Act Number and Date are required)');
            $sheet->setCellValue('K3', 'Date format: YYYY-MM-DD (e.g., 2024-01-15)');
            $sheet->setCellValue('K4', 'PDF Availability: Yes/No, True/False, 1/0 (blank defaults to 0/No)');
            $sheet->setCellValue('K5', 'All other fields are optional');
            $sheet->setCellValue('K6', 'Empty fields will be stored as NULL in database');

            $examples = [
                'A2' => 'Act No. 123',
                'B2' => '2024-01-15',
                'C2' => '123 SCRA 456',
                'D2' => 'Justice Dela Cruz',
                'E2' => 'Some description',
                'F2' => 'https://example.com/act',
                'G2' => 'Yes',
                'H2' => '/uploads/pdfs/act_123.pdf',

                'A3' => 'Act No. 124',
                'B3' => '2024-02-20',
                'C3' => '',
                'D3' => '',
                'E3' => '',
                'F3' => '',
                'G3' => 'No',
                'H3' => '',
            ];

            foreach ($examples as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ];
            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

            $requiredStyle = ['font' => ['color' => ['rgb' => 'FF0000']]];
            $sheet->getStyle('A1')->applyFromArray($requiredStyle);
            $sheet->getStyle('B1')->applyFromArray($requiredStyle);

            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->getColumnDimension('K')->setWidth(45);

            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');

            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="acts_template.xlsx"');
            header('Cache-Control: max-age=0');

            $writer->save('php://output');
            exit;

        } catch (\Exception $e) {
            Log::error('Template download error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate template: ' . $e->getMessage()
            ], 500);
        }
    }
}
