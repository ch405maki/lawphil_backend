<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MO extends Model
{
    protected $table = 'memorandum_order'; 

    protected $fillable = [
        'user_id',
        'mo_number', 
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