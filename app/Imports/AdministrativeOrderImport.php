<?php

namespace App\Imports;

use App\Models\AdministrativeOrder; 
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdministrativeOrderImport
{
    protected $file;
    protected $userId;
    protected $imported = 0;
    protected $errors = [];
    protected $totalRows = 0;
    
    public function __construct($file, $userId = null)
    {
        $this->file = $file;
        $this->userId = $userId ?? 1;
    }
    
    public function import()
    {
        try {
            $spreadsheet = IOFactory::load($this->file->getPathname());
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();
            
            $header = array_shift($rows);
            $this->totalRows = count($rows);
            
            // Validate the 7-column AO structure
            $this->validateHeaders($header);
            
            DB::beginTransaction();
            
            try {
                foreach ($rows as $rowIndex => $row) {
                    $this->processRow($row, $rowIndex + 2); 
                }
                
                DB::commit();
                return $this->getStatistics();
                
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
            
        } catch (\Exception $e) {
            Log::error('Administrative Order Import failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    protected function processRow($row, $rowNumber)
    {
        try {
            if (empty(array_filter($row))) {
                return;
            }
            
            /**
             * Mapping based on the reference:
             * 0: ao_number, 1: date, 2: description, 3: subject, 
             * 4: url, 5: pdf_availability, 6: pdf_path
             */
            $aoNumber        = $this->cleanValue($row[0] ?? null);
            $dateValue       = $this->cleanValue($row[1] ?? null);
            $description        = $this->cleanValue($row[2] ?? null);
            $subject         = $this->cleanValue($row[3] ?? null);
            $url             = $this->cleanValue($row[4] ?? null);
            $pdfAvailability = $this->cleanValue($row[5] ?? null);
            $pdfPath         = $this->cleanValue($row[6] ?? null);
            
            $this->validateRequiredFields($aoNumber, $dateValue, $rowNumber);
            
            $processedDate = $this->processDate($dateValue);
            if ($processedDate === false) {
                throw new \Exception("Row {$rowNumber}: Invalid date format. Use YYYY-MM-DD");
            }
            
            $pdfAvailabilityBool = !empty($pdfAvailability) ? $this->parseBoolean($pdfAvailability) : false;
            
            $data = [
                'user_id'          => $this->userId,
                'ao_number'        => $aoNumber,
                'date'             => $processedDate,
                'description'      => $description,
                'subject'          => $subject,
                'url'              => $url,
                'pdf_availability' => $pdfAvailabilityBool,
                'pdf_path'         => $pdfPath,
            ];
            
            AdministrativeOrder::create($data);
            $this->imported++;
            
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
            Log::warning("AO Row {$rowNumber} import failed: " . $e->getMessage());
        }
    }
    
    protected function validateHeaders($header)
    {
        $expectedHeaders = [
            'ao_number',
            'date',
            'description',
            'subject',
            'url',
            'pdf_availability',
            'pdf_path'
        ];
        
        $cleanHeaders = array_map(function($item) {
            return strtolower(trim(rtrim($item, '*')));
        }, $header);
        
        $mismatches = [];
        foreach ($expectedHeaders as $index => $expected) {
            if (!isset($cleanHeaders[$index]) || $cleanHeaders[$index] !== $expected) {
                $found = $cleanHeaders[$index] ?? 'empty';
                $mismatches[] = "Column " . ($index + 1) . " (Expected: '{$expected}', Found: '{$found}')";
            }
        }
        
        if (!empty($mismatches)) {
            throw new \Exception("Header Mismatch: " . implode(', ', $mismatches));
        }
    }

    protected function validateRequiredFields($aoNumber, $dateValue, $rowNumber)
    {
        if (empty($aoNumber)) throw new \Exception("Row {$rowNumber}: AO Number is required");
        if (empty($dateValue)) throw new \Exception("Row {$rowNumber}: Date is required");
    }
    
    protected function processDate($dateValue)
    {
        try {
            if (empty($dateValue)) return null;
            if (is_numeric($dateValue)) {
                return Date::excelToDateTimeObject($dateValue)->format('Y-m-d');
            }
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return false;
        }
    }
    
    protected function parseBoolean($value): bool
    {
        if (is_bool($value)) return $value;
        $val = strtolower(trim((string)$value));
        return in_array($val, ['yes', 'true', '1', 'y', 'available', 't']);
    }
    
    protected function cleanValue($value)
    {
        if ($value === null || $value === '') return null;
        return is_string($value) ? trim($value) : $value;
    }

    public function getStatistics()
    {
        return [
            'success' => true,
            'imported' => $this->imported,
            'total_rows' => $this->totalRows,
            'failed_count' => count($this->errors),
            'errors' => $this->errors
        ];
    }
}