<?php

namespace App\Imports;

use App\Models\Jurisprudence;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JurisprudenceImport
{
    protected $file;
    protected $userId;
    protected $imported = 0;
    protected $errors = [];
    protected $totalRows = 0;
    protected $batchSize = 500; // Insert 500 records at once
    protected $batchData = [];
    
    /**
     * Constructor
     */
    public function __construct($file, $userId = null)
    {
        $this->file = $file;
        $this->userId = $userId ?? 1;
        
        // Increase execution limits
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '2048M');
    }
    
    /**
     * Process the import
     */
    public function import()
    {
        try {
            // Use read filter to only read data (skip formatting)
            $reader = IOFactory::createReaderForFile($this->file->getPathname());
            $reader->setReadDataOnly(true);
            
            // Load only the active sheet
            $spreadsheet = $reader->load($this->file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            
            // Get highest row number
            $highestRow = $worksheet->getHighestRow();
            $this->totalRows = $highestRow - 1; // Subtract header
            
            // Validate headers first
            $headerRow = $worksheet->rangeToArray('A1:I1', NULL, TRUE, FALSE)[0];
            $this->validateHeaders($headerRow);
            
            DB::beginTransaction();
            
            try {
                // Process in chunks using rangeToArray
                $chunkSize = 1000; // Read 1000 rows at a time
                
                for ($startRow = 2; $startRow <= $highestRow; $startRow += $chunkSize) {
                    $endRow = min($startRow + $chunkSize - 1, $highestRow);
                    
                    // Read chunk of rows
                    $rows = $worksheet->rangeToArray(
                        'A' . $startRow . ':I' . $endRow,
                        NULL,
                        TRUE,
                        FALSE
                    );
                    
                    foreach ($rows as $rowIndex => $row) {
                        $rowNumber = $startRow + $rowIndex;
                        $this->processRowForBatch($row, $rowNumber);
                    }
                    
                    // Insert remaining batch data
                    $this->flushBatch();
                }
                
                DB::commit();
                
                return [
                    'success' => true,
                    'imported' => $this->imported,
                    'total_rows' => $this->totalRows,
                    'failed_count' => count($this->errors),
                    'errors' => array_slice($this->errors, 0, 100) // Return first 100 errors only
                ];
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            throw $e;
        } finally {
            // Free memory
            if (isset($spreadsheet)) {
                $spreadsheet->disconnectWorksheets();
                unset($spreadsheet);
            }
        }
    }
    
    /**
     * Process a single row for batch insertion
     */
    protected function processRowForBatch($row, $rowNumber)
    {
        try {
            // Skip empty rows
            if (empty(array_filter($row))) {
                return;
            }
            
            // Get and clean values
            $grNumber = $this->cleanValue($row[0] ?? null);
            $dateValue = $this->cleanValue($row[1] ?? null);
            $citation = $this->cleanValue($row[2] ?? null);
            $ponente = $this->cleanValue($row[3] ?? null);
            $reference = $this->cleanValue($row[4] ?? null);
            $url = $this->cleanValue($row[5] ?? null);
            $pdfAvailability = $this->cleanValue($row[6] ?? null);
            $subject = $this->cleanValue($row[7] ?? null);
            $pdf_path = $this->cleanValue($row[8] ?? null);
            
            // Validate required fields
            $this->validateRequiredFields($grNumber, $dateValue, $rowNumber);
            
            // Process date
            $processedDate = $this->processDate($dateValue);
            if ($processedDate === false) {
                throw new \Exception("Row {$rowNumber}: Invalid date format. Use YYYY-MM-DD");
            }
            
            // Process PDF availability (default to false if empty)
            $pdfAvailabilityBool = false;
            if (!empty($pdfAvailability)) {
                $pdfAvailabilityBool = $this->parseBoolean($pdfAvailability);
            }
            
            // Prepare data for batch insert
            $this->batchData[] = [
                'user_id' => $this->userId,
                'gr_number' => $grNumber,
                'date' => $processedDate,
                'citation' => !empty($citation) ? $citation : null,
                'ponente' => !empty($ponente) ? $ponente : null,
                'reference' => !empty($reference) ? $reference : null,
                'url' => !empty($url) ? $url : null,
                'pdf_availability' => $pdfAvailabilityBool,
                'subject' => !empty($subject) ? $subject : null,
                'pdf_path' => !empty($pdf_path) ? $pdf_path : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
            
            // If batch size reached, insert
            if (count($this->batchData) >= $this->batchSize) {
                $this->flushBatch();
            }
            
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            Log::warning('Row import failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Insert batch data into database
     */
    protected function flushBatch()
    {
        if (empty($this->batchData)) {
            return;
        }
        
        try {
            // Use insert instead of create for each record
            DB::table('jurisprudence')->insert($this->batchData);
            $this->imported += count($this->batchData);
            $this->batchData = [];
            
            // Log progress every 5000 records
            if ($this->imported % 5000 === 0) {
                Log::info("Imported {$this->imported} records so far...");
            }
        } catch (\Exception $e) {
            Log::error('Batch insert failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequiredFields($grNumber, $dateValue, $rowNumber)
    {
        if (empty($grNumber)) {
            throw new \Exception("Row {$rowNumber}: GR Number is required");
        }
        
        if (empty($dateValue)) {
            throw new \Exception("Row {$rowNumber}: Date is required");
        }
    }
    
    /**
     * Validate Excel headers
     */
    protected function validateHeaders($header)
    {
        $expectedHeaders = [
            'gr_number*',
            'date*',
            'citation',
            'ponente',
            'reference',
            'url',
            'pdf_availability',
            'subject',
            'pdf_path'
        ];
        
        $errors = [];
        
        // Clean headers
        $cleanHeaders = array_map(function($headerItem) {
            return strtolower(trim($headerItem ?? ''));
        }, $header);
        
        // Check if all expected headers are present
        foreach ($expectedHeaders as $index => $expected) {
            $expectedClean = strtolower($expected);
            if (!isset($cleanHeaders[$index]) || $cleanHeaders[$index] !== $expectedClean) {
                $errors[] = "Column " . ($index + 1) . " should be '{$expected}' but found '" . ($cleanHeaders[$index] ?? 'empty') . "'";
            }
        }
        
        if (!empty($errors)) {
            throw new \Exception(implode(', ', $errors));
        }
    }
    
    /**
     * Process date from Excel
     */
    protected function processDate($dateValue)
    {
        try {
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
     * Parse boolean value
     */
    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        $value = strtolower(trim((string)$value));
        return in_array($value, ['yes', 'true', '1', 'y', 'available', 't']);
    }
    
    /**
     * Clean cell value
     */
    protected function cleanValue($value)
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