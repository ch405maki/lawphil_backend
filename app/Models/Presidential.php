<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presidential extends Model
{
    protected $table = 'presidential_decrees'; 

    protected $fillable = [
        'user_id',
        'pd_number', 
        'date', 
        'citation', 
        'ponente', 
        'reference', 
        'url', 
        'pdf_availability',
        'pdf_path',
        'subject',
        'tenure'
    ];

    protected $casts = [
        'date' => 'date',
    ];
}