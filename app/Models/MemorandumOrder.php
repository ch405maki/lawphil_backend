<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandumOrder extends Model
{
    protected $table = 'memorandum_orders'; 

    protected $fillable = [
        'user_id',
        'mo_number', // Pinalitan ang gr_number
        'date', 
        'citation', 
        'signatory', // Pinalitan ang ponente
        'reference', 
        'url', 
        'pdf_availability',
        'pdf_path',
        'subject' 
    ];

    protected $casts = [
        'date' => 'date',
    ];
}