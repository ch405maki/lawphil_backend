<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Proclamation extends Model
{
    protected $table = 'proclamations'; 

    protected $fillable = [
        'user_id',
        'proc_number', 
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