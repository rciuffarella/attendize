<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventDate extends MyBaseModel
{
    use SoftDeletes;

    protected $dates = ['start_date', 'end_date', 'deleted_at'];
    
    protected $fillable = [
        'event_id',
        'start_date',
        'end_date',
        'quantity_available',
        'is_active',
        'sort_order',
    ];

    /**
     * The event associated with this date.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function event()
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Get formatted start date
     *
     * @param string $format
     * @return string
     */
    public function getFormattedStartDate($format = null)
    {
        if (!$format) {
            $format = config('attendize.default_datetime_format');
        }
        return $this->start_date ? $this->start_date->format($format) : '';
    }

    /**
     * Get formatted end date
     *
     * @param string $format
     * @return string
     */
    public function getFormattedEndDate($format = null)
    {
        if (!$format) {
            $format = config('attendize.default_datetime_format');
        }
        return $this->end_date ? $this->end_date->format($format) : '';
    }

    /**
     * Check if this date is in the past
     *
     * @return bool
     */
    public function isPast()
    {
        return $this->end_date < Carbon::now();
    }

    /**
     * Check if this date is available for booking
     *
     * @return bool
     */
    public function isAvailable()
    {
        return $this->is_active && !$this->isPast();
    }

    /**
     * The orders associated with this event date.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
