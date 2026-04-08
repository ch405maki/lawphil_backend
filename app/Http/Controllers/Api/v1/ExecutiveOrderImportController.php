<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Imports\ExecutiveOrderImport; // Gagawa tayo nito sa Step 2
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class ExecutiveOrderImportController extends Controller
{
    /**
     * Import executive orders from Excel file
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
            
            $import = new ExecutiveOrderImport($file, auth()->id() ?? 1); 
            
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
            Log::error('EO Import error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download Excel template for Executive Orders
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers specific to Executive Orders
            $headers = [
                'A1' => 'eo_number*',
                'B1' => 'date*',
                'C1' => 'subject',
                'D1' => 'reference',
                'E1' => 'url',
                'F1' => 'pdf_availability',
                'G1' => 'pdf_path'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Add instructions
            $sheet->setCellValue('I1', 'INSTRUCTIONS:');
            $sheet->setCellValue('I2', '* = Required field (EO Number and Date)');
            $sheet->setCellValue('I3', 'Date format: YYYY-MM-DD');
            $sheet->setCellValue('I4', 'PDF Availability: Yes/No or 1/0');
            
            // Style header row
            $headerStyle = [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E0E0E0']
                ]
            ];
            $sheet->getStyle('A1:G1')->applyFromArray($headerStyle);
            
            // Auto-size columns
            foreach (range('A', 'G') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            
            $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="executive_orders_template.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
            
        } catch (\Exception $e) {
            Log::error('EO Template download error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Failed to generate template'], 500);
        }
    }
}