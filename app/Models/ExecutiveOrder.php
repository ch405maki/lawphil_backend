<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExecutiveOrder extends Model
{
    // Itinugma natin sa pangalan ng table sa migration kanina
    protected $table = 'executive_orders'; 

    protected $fillable = [
        'user_id',          // Identical sa Jurisprudence
        'eo_number',        // Refactored mula gr_number
        'date', 
        'reference',        // Ginaya ang structure mo
        'url', 
        'pdf_availability',
        'pdf_path',
        'subject' 
    ];

    protected $casts = [
        'date' => 'date',   // Identical casting
    ];
}