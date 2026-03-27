<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurisprudence extends Model
{
    protected $table = 'jurisprudence'; 

    protected $fillable = [
        'user_id',
        'gr_number', 
        'date', 
        'citation', 
        'ponente', 
        'reference', 
        'url', 
        'pdf_availability',
        'pdf_path',
        'subject' 
    ];

    protected $casts = [
        'pdf_availability' => 'boolean',
        'date' => 'date',
    ];
}