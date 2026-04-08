<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\AdministrativeOrderImport; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AdministrativeOrderImportController extends Controller
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
            $import = new AdministrativeOrderImport($file, auth()->id() ?? 1); 
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
            Log::error('AO Import error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            $headers = [
                'A1' => 'ao_number*',
                'B1' => 'date*',
                'C1' => 'description',
                'D1' => 'subject',
                'E1' => 'url',
                'F1' => 'pdf_availability',
                'G1' => 'pdf_path'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            $sheet->setCellValue('I1', 'INSTRUCTIONS:');
            $sheet->setCellValue('I2', 'Required fields: ao_number and date');
            $sheet->setCellValue('I3', 'Date format: YYYY-MM-DD (e.g. 2026-04-07)');
            $sheet->setCellValue('I4', 'PDF Availability: Yes or No');
            $sheet->setCellValue('I5', 'Column order must be exactly as shown (A to G)');

            $examples = [
                'A2' => '2026-001',
                'B2' => '2026-04-01',
                'C2' => 'Guidelines on Digital Executive Issuances Management',
                'D2' => 'Information and Communications Technology',
                'E2' => 'https://www.officialgazette.gov.ph/example',
                'F2' => 'Yes',
                'G2' => 'storage/orders/ao-2026-001.pdf',
            ];
            
            foreach ($examples as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D9EAD3'] 
                ]
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            
            $requiredStyle = ['font' => ['color' => ['rgb' => 'FF0000']]];
            $sheet->getStyle('A1:B1')->applyFromArray($requiredStyle);
            
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $writer = new Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="administrative_order_template.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            Log::error('AO Template download error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate template'], 500);
        }
    }
}