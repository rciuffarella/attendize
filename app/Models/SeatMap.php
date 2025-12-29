<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatMap extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'background_image_path',
        'capacity',
        'width',
        'height',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function zones(): HasMany
    {
        return $this->hasMany(SeatZone::class);
    }
}



