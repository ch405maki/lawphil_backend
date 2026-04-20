<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralOrder extends Model
{
    protected $table = 'general_orders'; 

    protected $fillable = [
        'user_id',
        'go_number', // Pinalitan ang gr_number
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