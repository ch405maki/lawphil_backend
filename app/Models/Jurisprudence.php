<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurisprudence extends Model
{
    protected $table = 'jurisprudence'; 

    protected $fillable = [
        'gr_number', 'date', 'citation', 'ponente', 'reference', 'url', 'pdf_availability'
    ];
}