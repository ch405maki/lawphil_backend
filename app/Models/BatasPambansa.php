<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BatasPambansa extends Model
{
    protected $table = 'batas_pambansa';

    protected $fillable = [
        'user_id',
        'bp_number',
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
