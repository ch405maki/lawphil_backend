<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AO extends Model
{
    protected $table = 'administrative_order'; 

    protected $fillable = [
        'user_id',
        'ao_number', 
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