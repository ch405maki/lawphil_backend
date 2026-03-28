<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use App\Models\Jurisprudence;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class JurisprudenceImportController extends Controller
{
    /**
     * Import jurisprudence from Excel file
     */
    public function import(Request $request)
    {
        try {
            $request->validate([
                'file' => 'required|file|mimes:xlsx,xls,csv|max:10240', // max 10MB
            ]);

            $file = $request->file('file');
            
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove header row
            $header = array_shift($rows);
            
            // Expected headers mapping
            $expectedHeaders = [
                'gr_number',
                'date',
                'citation',
                'ponente',
                'reference',
                'url',
                'pdf_availability',
                'subject'
            ];

            // Validate headers
            $headerValidation = $this->validateHeaders($header, $expectedHeaders);
            if (!$headerValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid file format',
                    'errors' => $headerValidation['errors']
                ], 422);
            }

            $imported = 0;
            $errors = [];
            $totalRows = count($rows);
            
            DB::beginTransaction();
            
            try {
                foreach ($rows as $rowIndex => $row) {
                    try {
                        // Skip empty rows (all columns empty)
                        if (empty(array_filter($row))) {
                            continue;
                        }
                        
                        // Map row data to columns with proper null handling
                        $grNumber = $this->cleanValue($row[0] ?? null);
                        $dateValue = $this->cleanValue($row[1] ?? null);
                        $citation = $this->cleanValue($row[2] ?? null);
                        $ponente = $this->cleanValue($row[3] ?? null);
                        $reference = $this->cleanValue($row[4] ?? null);
                        $url = $this->cleanValue($row[5] ?? null);
                        $pdfAvailability = $this->cleanValue($row[6] ?? null);
                        $subject = $this->cleanValue($row[7] ?? null);
                        
                        // Validate required fields
                        if (empty($grNumber)) {
                            throw new \Exception("Row " . ($rowIndex + 2) . ": GR Number is required");
                        }
                        
                        if (empty($dateValue)) {
                            throw new \Exception("Row " . ($rowIndex + 2) . ": Date is required");
                        }
                        
                        if (empty($subject)) {
                            throw new \Exception("Row " . ($rowIndex + 2) . ": Subject is required");
                        }
                        
                        // Process date (handle Excel date format)
                        $processedDate = $this->processDate($dateValue);
                        if ($processedDate === false) {
                            throw new \Exception("Row " . ($rowIndex + 2) . ": Invalid date format. Use YYYY-MM-DD");
                        }
                        
                        // Process PDF availability - default to false (0) if empty
                        $pdfAvailabilityBool = false; // Default to false
                        if (!empty($pdfAvailability)) {
                            $pdfAvailabilityBool = $this->parseBoolean($pdfAvailability);
                        }
                        // If empty, it stays false (0)
                        
                        // Validate URL if provided
                        if (!empty($url) && !filter_var($url, FILTER_VALIDATE_URL)) {
                            throw new \Exception("Row " . ($rowIndex + 2) . ": Invalid URL format");
                        }
                        
                        // Prepare data for insertion - empty strings become null, but pdf_availability defaults to false
                        $jurisprudenceData = [
                            'user_id' => 1, // Hardcoded for now, replace with auth()->id() when auth is ready
                            'gr_number' => $grNumber,
                            'date' => $processedDate,
                            'citation' => !empty($citation) ? $citation : null,
                            'ponente' => !empty($ponente) ? $ponente : null,
                            'reference' => !empty($reference) ? $reference : null,
                            'url' => !empty($url) ? $url : null,
                            'pdf_availability' => $pdfAvailabilityBool, // Will be false if empty
                            'subject' => $subject,
                        ];
                        
                        // Create jurisprudence record
                        Jurisprudence::create($jurisprudenceData);
                        $imported++;
                        
                    } catch (\Exception $e) {
                        $errors[] = $e->getMessage();
                        Log::warning('Row import failed: ' . $e->getMessage());
                    }
                }
                
                DB::commit();
                
                $message = "Successfully imported {$imported} of {$totalRows} records";
                if (count($errors) > 0) {
                    $message .= " with " . count($errors) . " error(s)";
                }
                
                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'data' => [
                        'imported' => $imported,
                        'total_rows' => $totalRows,
                        'failed_count' => count($errors),
                        'errors' => $errors
                    ]
                ]);
                
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error('Import failed: ' . $e->getMessage());
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Import error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Download Excel template with nullable fields
     */
    public function downloadTemplate()
    {
        try {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            // Set headers with clear nullable indicators
            $headers = [
                'A1' => 'gr_number*',
                'B1' => 'date*',
                'C1' => 'citation',
                'D1' => 'ponente',
                'E1' => 'reference',
                'F1' => 'url',
                'G1' => 'pdf_availability',
                'H1' => 'subject*'
            ];
            
            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }
            
            // Add notes about required fields and defaults
            $sheet->setCellValue('J1', 'INSTRUCTIONS:');
            $sheet->setCellValue('J2', '* = Required field');
            $sheet->setCellValue('J3', 'Date format: YYYY-MM-DD (e.g., 2024-01-15)');
            $sheet->setCellValue('J4', 'PDF Availability: Yes/No, True/False, 1/0');
            $sheet->setCellValue('J5', '⚠️ IMPORTANT: If PDF Availability is left BLANK, it will default to 0 (No)');
            $sheet->setCellValue('J6', 'Empty optional fields (citation, ponente, reference, url) will be NULL');
            $sheet->setCellValue('J7', 'URL must be a valid URL if provided');
            
            // Add example data with nulls
            $examples = [
                // Example 1: Complete data with PDF available
                'A2' => 'G.R. No. 123456',
                'B2' => '2024-01-15',
                'C2' => '123 SCRA 456',
                'D2' => 'Justice Dela Cruz',
                'E2' => 'Some reference',
                'F2' => 'https://example.com/case',
                'G2' => 'Yes',
                'H2' => 'Civil Law',
                
                // Example 2: Empty PDF availability (will default to 0/No)
                'A3' => 'G.R. No. 123457',
                'B3' => '2024-02-20',
                'C3' => '125 SCRA 789',
                'D3' => 'Justice Reyes',
                'E3' => 'Some reference',
                'F3' => 'https://example.com/case2',
                'G3' => '',  // Empty -> will default to false (0)
                'H3' => 'Criminal Law',
                
                // Example 3: With empty optional fields (will be NULL)
                'A4' => 'G.R. No. 123458',
                'B4' => '2024-03-10',
                'C4' => '',  // Empty citation -> will be NULL
                'D4' => '',  // Empty ponente -> will be NULL
                'E4' => '',  // Empty reference -> will be NULL
                'F4' => '',  // Empty url -> will be NULL
                'G4' => 'No',
                'H4' => 'Labor Law',
                
                // Example 4: PDF availability explicitly set to Yes
                'A5' => 'G.R. No. 123459',
                'B5' => '2024-04-05',
                'C5' => '',
                'D5' => 'Justice Santos',
                'E5' => '',
                'F5' => '',
                'G5' => 'Yes',
                'H5' => 'Commercial Law',
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
            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);
            
            // Style required fields (red asterisk)
            $requiredStyle = [
                'font' => ['color' => ['rgb' => 'FF0000']]
            ];
            $sheet->getStyle('A1')->applyFromArray($requiredStyle);
            $sheet->getStyle('B1')->applyFromArray($requiredStyle);
            $sheet->getStyle('H1')->applyFromArray($requiredStyle);
            
            // Style instruction area with warning highlight
            $instructionStyle = [
                'font' => ['italic' => true, 'size' => 10],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFF9C4']
                ]
            ];
            $sheet->getStyle('J1:J7')->applyFromArray($instructionStyle);
            
            // Highlight the default value note
            $warningStyle = [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FF6B00']],
            ];
            $sheet->getStyle('J5')->applyFromArray($warningStyle);
            
            // Auto-size columns
            foreach (range('A', 'H') as $col) {
                $sheet->getColumnDimension($col)->setAutoSize(true);
            }
            $sheet->getColumnDimension('J')->setWidth(45);
            
            // Freeze header row
            $sheet->freezePane('A2');
            
            // Create writer and output
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="jurisprudence_template.xlsx"');
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
    
    /**
     * Validate Excel headers
     */
    private function validateHeaders(array $headers, array $expectedHeaders): array
    {
        $errors = [];
        
        // Clean headers (trim and lowercase, remove asterisk)
        $cleanHeaders = array_map(function($header) {
            $header = trim($header);
            // Remove asterisk if present (for required fields indicator)
            $header = rtrim($header, '*');
            return strtolower($header);
        }, $headers);
        
        $cleanExpected = array_map('strtolower', $expectedHeaders);
        
        // Check if all expected headers are present
        foreach ($cleanExpected as $index => $expected) {
            if (!isset($cleanHeaders[$index]) || $cleanHeaders[$index] !== $expected) {
                $errors[] = "Column " . ($index + 1) . " should be '{$expected}' but found '" . ($cleanHeaders[$index] ?? 'empty') . "'";
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Process date from Excel (handles both Excel serial numbers and string dates)
     */
    private function processDate($dateValue)
    {
        try {
            // Handle empty values
            if (empty($dateValue)) {
                return null;
            }
            
            // Handle Excel serial number
            if (is_numeric($dateValue)) {
                $dateTime = Date::excelToDateTimeObject($dateValue);
                return $dateTime->format('Y-m-d');
            }
            
            // Handle string date
            if (is_string($dateValue)) {
                // Try to parse as date string
                $parsedDate = Carbon::parse($dateValue);
                if ($parsedDate) {
                    return $parsedDate->format('Y-m-d');
                }
            }
            
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * Parse boolean value from various formats
     */
    private function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim((string)$value));
        
        // Return true for affirmative values, false for everything else
        return in_array($value, ['yes', 'true', '1', 'y', 'available', 't']);
    }
    
    /**
     * Clean cell value (trim and handle empty strings)
     */
    private function cleanValue($value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        if (is_string($value)) {
            $trimmed = trim($value);
            return $trimmed === '' ? null : $trimmed;
        }
        
        return $value;
    }
}