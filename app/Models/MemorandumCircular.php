<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MemorandumCircular extends Model
{
    // Tinukoy natin ang table name dahil plural ang ginawa natin sa migration
    protected $table = 'memorandum_circulars'; 

    protected $fillable = [
        'user_id',
        'mc_number', // Ito ang katapat ng gr_number mo
        'date', 
        'reference', 
        'url', 
        'pdf_availability',
        'pdf_path',
        'subject' 
    ];

    protected $casts = [
        'date' => 'date',
        'pdf_availability' => 'boolean', // Dinagdag ko ito para laging true/false ang balik, hindi 1/0
    ];
}