<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * AppointmentSeriesWeekday model representing a specific weekday time slot in a recurring series.
 * 
 * This model defines when a recurring appointment occurs (e.g., every Monday 9-10am).
 * Multiple weekday slots can belong to a single series.
 *
 * @property int $id
 * @property int $series_id
 * @property int $weekday (0=Sunday, 1=Monday, ..., 6=Saturday)
 * @property string $start_time
 * @property string $end_time
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AppointmentSeriesWeekday extends Model
{

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'series_id',
        'weekday',
        'start_time',
        'end_time',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'weekday' => 'integer',
    ];

    /**
     * Get the appointment series that owns this weekday slot.
     *
     * @return BelongsTo Relationship to AppointmentSeries model
     */
    public function appointmentSeries(): BelongsTo
    {
        return $this->belongsTo(AppointmentSeries::class, 'series_id');
    }
}
