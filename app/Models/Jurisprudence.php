<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Jurisprudence extends Model
{
    use LogsActivity;

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
        'date' => 'date',
    ];

    // Activity Log Options
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'gr_number',
                'date',
                'citation',
                'ponente',
                'reference',
                'url',
                'subject',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('jurisprudence');
    }

    //Optional: Friendly log message
    public function getDescriptionForEvent(string $eventName): string
    {
        return "Jurisprudence {$this->gr_number} was {$eventName}";
    }
}