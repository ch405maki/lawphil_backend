<?php

namespace App\Imports;

use App\Models\Genor;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenorImport
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
                    $this->processRow($row, $rowIndex + 2); 
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
            
            // Map cells perfectly matching the 8 columns
            $genorNumber     = $this->cleanValue($row[0] ?? null);
            $dateValue       = $this->cleanValue($row[1] ?? null);
            $citation        = $this->cleanValue($row[2] ?? null);
            $tenure          = $this->cleanValue($row[3] ?? null);
            $description     = $this->cleanValue($row[4] ?? null); 
            $url             = $this->cleanValue($row[5] ?? null); 
            $pdfAvailability = $this->cleanValue($row[6] ?? null);
            $pdf_path        = $this->cleanValue($row[7] ?? null);
            
            // Validate required fields
            $this->validateRequiredFields($genorNumber, $dateValue, $rowNumber);
            
            // Process date
            $processedDate = $this->processDate($dateValue);
            if ($processedDate === false) {
                throw new \Exception("Row {$rowNumber}: Invalid date format. Use YYYY-MM-DD");
            }
            
            // Robust PDF activation logic: Check column value OR auto-activate if path exists
            $pdfAvailabilityBool = false;
            if (!empty($pdfAvailability)) {
                $pdfAvailabilityBool = $this->parseBoolean($pdfAvailability);
            } elseif (!empty($pdf_path)) {
                $pdfAvailabilityBool = true;
            }
            
            // Prepare data exactly mapped to database columns 
            $data = [
                'user_id'          => $this->userId,
                'genor_number'     => $genorNumber,
                'date'             => $processedDate,
                'citation'         => !empty($citation) ? $citation : null,
                'tenure'           => !empty($tenure) ? $tenure : null,
                'url'              => !empty($url) ? $url : null,
                'pdf_availability' => $pdfAvailabilityBool,
                'description'      => !empty($description) ? $description : null, 
                'pdf_path'         => !empty($pdf_path) ? $pdf_path : null,
            ];
            
            // Create record
            Genor::create($data);
            $this->imported++;
            
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            Log::warning('Row import failed: ' . $e->getMessage());
        }
    }
    
    /**
     * Validate required fields
     */
    protected function validateRequiredFields($genorNumber, $dateValue, $rowNumber)
    {
        if (empty($genorNumber)) {
            throw new \Exception("Row {$rowNumber}: General Order Number is required");
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
            'genor_number',
            'date',
            'citation',
            'tenure',
            'description',
            'url',
            'pdf_availability',
            'pdf_path'
        ];
        
        $errors = [];
        
        $cleanHeaders = array_map(function($headerItem) {
            $headerItem = trim($headerItem);
            $headerItem = rtrim($headerItem, '*');
            return strtolower($headerItem);
        }, $header);
        
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
            
            if (is_numeric($dateValue)) {
                $dateTime = Date::excelToDateTimeObject($dateValue);
                return $dateTime->format('Y-m-d');
            }
            
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