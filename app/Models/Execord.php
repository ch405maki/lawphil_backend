<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Execord extends Model
{
    protected $table = 'executive_order'; 

    protected $fillable = [
        'user_id',
        'execord_number', 
        'date', 
        'citation', 
        'tenure', 
        'url', 
        'pdf_availability',
        'description',
        'pdf_path',
    ];

    protected $casts = [
        'date' => 'date',
    ];
}