<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Republic extends Model
{
    protected $table = 'republic'; 

    protected $fillable = [
        'user_id',
        'ra_number', 
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