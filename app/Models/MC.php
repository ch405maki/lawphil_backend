<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MC extends Model
{
    protected $table = 'memorandum_circular'; 

    protected $fillable = [
        'user_id',
        'mc_number', 
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