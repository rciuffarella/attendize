<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Seat extends Model
{
    protected $fillable = [
        'seat_zone_id',
        'row_label',
        'seat_number',
        'x',
        'y',
        'status',
        'price_override',
    ];

    public function zone(): BelongsTo
    {
        return $this->belongsTo(SeatZone::class, 'seat_zone_id');
    }

    public function attendees(): HasMany
    {
        return $this->hasMany(Attendee::class);
    }
}


