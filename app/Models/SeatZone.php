<?php namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SeatZone extends Model
{
    protected $fillable = [
        'seat_map_id',
        'ticket_id',
        'name',
        'color',
        'price_modifier',
        'position_x',
        'position_y',
        'start_row_alpha',
        'start_col_num',
    ];

    public function map(): BelongsTo
    {
        return $this->belongsTo(SeatMap::class, 'seat_map_id');
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class);
    }

    public function seats(): HasMany
    {
        return $this->hasMany(Seat::class);
    }
}



