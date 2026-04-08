<?php

namespace App\Imports;

use App\Models\ExecutiveOrder;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ExecutiveOrderImport
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
            Log::error('EO Import failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    protected function processRow($row, $rowNumber)
    {
        try {
            if (empty(array_filter($row))) return;
            
            // Mapping columns based on EO structure
            $eoNumber   = $this->cleanValue($row[0] ?? null);
            $dateValue  = $this->cleanValue($row[1] ?? null);
            $subject    = $this->cleanValue($row[2] ?? null);
            $reference  = $this->cleanValue($row[3] ?? null);
            $url        = $this->cleanValue($row[4] ?? null);
            $pdfAvail   = $this->cleanValue($row[5] ?? null);
            $pdfPath    = $this->cleanValue($row[6] ?? null);
            
            // Validation
            if (empty($eoNumber)) throw new \Exception("Row {$rowNumber}: EO Number is required");
            if (empty($dateValue)) throw new \Exception("Row {$rowNumber}: Date is required");
            
            $processedDate = $this->processDate($dateValue);
            if ($processedDate === false) throw new \Exception("Row {$rowNumber}: Invalid date format");
            
            $data = [
                'user_id' => $this->userId,
                'eo_number' => $eoNumber,
                'date' => $processedDate,
                'subject' => $subject,
                'reference' => $reference,
                'url' => $url,
                'pdf_availability' => $this->parseBoolean($pdfAvail),
                'pdf_path' => $pdfPath,
            ];
            
            ExecutiveOrder::create($data);
            $this->imported++;
            
        } catch (\Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    protected function validateHeaders($header)
    {
        $expectedHeaders = ['eo_number', 'date', 'subject', 'reference', 'url', 'pdf_availability', 'pdf_path'];
        
        $cleanHeaders = array_map(function($h) {
            return strtolower(rtrim(trim($h), '*'));
        }, $header);
        
        foreach ($expectedHeaders as $index => $expected) {
            if (!isset($cleanHeaders[$index]) || $cleanHeaders[$index] !== $expected) {
                throw new \Exception("Column " . ($index + 1) . " should be '{$expected}'");
            }
        }
    }

    protected function processDate($dateValue)
    {
        try {
            if (is_numeric($dateValue)) {
                return ExcelDate::excelToDateTimeObject($dateValue)->format('Y-m-d');
            }
            return Carbon::parse($dateValue)->format('Y-m-d');
        } catch (\Exception $e) { return false; }
    }

    protected function parseBoolean($value): bool
    {
        if (empty($value)) return false;
        $value = strtolower(trim((string)$value));
        return in_array($value, ['yes', 'true', '1', 'y', 't']);
    }

    protected function cleanValue($value)
    {
        return ($value === null || $value === '') ? null : trim($value);
    }
}