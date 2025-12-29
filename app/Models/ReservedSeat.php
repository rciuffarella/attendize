<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReservedSeat extends Model
{
    protected $fillable = [
        'seat_id',
        'event_id',
        'session_id',
        'expires_at',
    ];

    protected $dates = [
        'expires_at',
    ];

    public function seat(): BelongsTo
    {
        return $this->belongsTo(Seat::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}



