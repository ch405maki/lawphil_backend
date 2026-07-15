<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommonWealth extends Model
{
    protected $table = 'commonwealth';

    protected $fillable = [
        'user_id',
        'ca_number',
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
