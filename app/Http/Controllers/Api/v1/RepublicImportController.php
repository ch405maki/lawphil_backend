<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\RepublicImport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class RepublicImportController extends Controller
{
    /**
     * Import proclamation from Excel file
     */
    public function import(Request $request)
    {
        try {
            // Validate request
            $validator = Validator::make($request->all(), [
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // 10MB max
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()->all()
                ], 422);
            }

            $file = $request->file('file');
            
            // Create import instance
            $import = new RepublicImport($file, 1); // Hardcoded user_id for now
            
            // Process import
            $result = $import->import();
            
            // Prepare response message
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
    
    /**
     * Download Excel template
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers
            $headers = [
                'A1' => 'ra_number*',
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
            
            // Add instructions
            $sheet->setCellValue('K1', 'INSTRUCTIONS:');
            $sheet->setCellValue('K2', '* = Required field (only R.A. Number and Date are required)');
            $sheet->setCellValue('K3', 'Date format: YYYY-MM-DD (e.g., 2024-01-15)');
            $sheet->setCellValue('K4', 'PDF Availability: Yes/No, True/False, 1/0 (blank defaults to 0/No)');
            $sheet->setCellValue('K5', 'All other fields (citation, ponente, reference, url, description) are optional');
            $sheet->setCellValue('K6', 'Empty fields will be stored as NULL in database');
            
            // Add example data
            $examples = [
                'A2' => 'RA. No. 123456',
                'B2' => '2024-01-15',
                'C2' => '123 SCRA 456',
                'D2' => 'Justice Dela Cruz',
                'E2' => 'Some reference',
                'F2' => 'https://example.com/case',
                'G2' => 'Yes',
                'H2' => 'Civil Law',
                'I2' => '/uploads/pdfs/case_123456.pdf',
                
                'A3' => 'RA. No. 123457',
                'B3' => '2024-02-20',
                'C3' => '',
                'D3' => '',
                'E3' => '',
                'F3' => '',
                'G3' => 'No',
                'H3' => '',
                'I3' => '/uploads/pdfs/case_123456.pdf',
                
                'A4' => 'RA. No. 123458',
                'B4' => '2024-03-10',
                'C4' => '',
                'D4' => '',
                'E4' => '',
                'F4' => '',
                'G4' => '',
                'H4' => '',
                'I4' => '',
            ];
            
            foreach ($examples as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Style header row
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ];
            $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
            
            // Style required fields
            $requiredStyle = ['font' => ['color' => ['rgb' => 'FF0000']]];
            $sheet->getStyle('A1')->applyFromArray($requiredStyle);
            $sheet->getStyle('B1')->applyFromArray($requiredStyle);
            
            // Auto-size columns
            foreach (range('A', 'I') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->getColumnDimension('K')->setWidth(45);
            
            // Create writer and output
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="republic_template.xlsx"');
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