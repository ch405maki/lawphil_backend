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
    
    /**
     * Constructor
     */
    public function __construct($file, $userId = null)
    {
        $this->file = $file;
        $this->userId = $userId ?? 1; // Default to 1 if no user ID
    }
    
    /**
     * Process the import
     */
    public function import()
    {
        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($this->file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            // Remove header row
            $header = array_shift($rows);
            $this->totalRows = count($rows);
            
            // Validate headers
            $this->validateHeaders($header);
            
            DB::beginTransaction();
            
            try {
                foreach ($rows as $rowIndex => $row) {
                    $this->processRow($row, $rowIndex + 2); // +2 for header and zero-index
                }
                
                DB::commit();
                
                return [
                    'success' => true,
                    'imported' => $this->imported,
                    'total_rows' => $this->totalRows,
                    'failed_count' => count($this->errors),
                    'errors' => $this->errors
                ];
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Import failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Process a single row
     */
    protected function processRow($row, $rowNumber)
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
            
            // Prepare data
            $data = [
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
            ];
            
            // Create record
            Jurisprudence::create($data);
            $this->imported++;
            
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            Log::warning('Row import failed: ' . $e->getMessage());
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
            'gr_number',
            'date',
            'citation',
            'ponente',
            'reference',
            'url',
            'pdf_availability',
            'subject',
            'pdf_path'
        ];
        
        $errors = [];
        
        // Clean headers (trim and lowercase, remove asterisk)
        $cleanHeaders = array_map(function($headerItem) {
            $headerItem = trim($headerItem);
            $headerItem = rtrim($headerItem, '*');
            return strtolower($headerItem);
        }, $header);
        
        // Check if all expected headers are present
        foreach ($expectedHeaders as $index => $expected) {
            if (!isset($cleanHeaders[$index]) || $cleanHeaders[$index] !== $expected) {
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
    
    /**
     * Get import statistics
     */
    public function getStatistics()
    {
        return [
            'imported' => $this->imported,
            'total_rows' => $this->totalRows,
            'failed_count' => count($this->errors),
            'errors' => $this->errors
        ];
    }
}