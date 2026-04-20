<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdministrativeOrder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'administrative_orders'; 

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'ao_number',    
        'date', 
        'description', 
        'subject',      
        'url', 
        'pdf_availability',
        'pdf_path'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date'             => 'date',
        'pdf_availability' => 'boolean',
    ];

    /**
     * Get the user that created the administrative order.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}