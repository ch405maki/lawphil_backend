<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Genor extends Model
{
    protected $table = 'general_order'; 

    protected $fillable = [
        'user_id',
        'genor_number', 
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