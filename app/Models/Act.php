<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Act extends Model
{
    protected $table = 'acts';

    protected $fillable = [
        'user_id',
        'act_number',
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
